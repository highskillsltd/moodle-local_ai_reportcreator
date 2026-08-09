<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_ai_reportcreator;

/**
 * cURL-based client for the AI report creator streaming service.
 *
 * @package   local_ai_reportcreator
 * @copyright 2026 Highskills and more <info@highskills.co.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ApiClient {
    /** @var string Full middleware endpoint URL read from plugin config. */
    private string $middlewareurl;

    /** @var string Bearer token API key. */
    private string $apikey;

    /** @var int Streaming timeout in seconds (read from plugin config, min 30). */
    private int $streamtimeout;

    /**
     * Initialise the client by reading plugin configuration.
     */
    public function __construct() {
        $this->middlewareurl = rtrim(get_config('local_ai_reportcreator', 'middleware_url') ?? '', '/');
        $this->apikey        = get_config('local_ai_reportcreator', 'api_key') ?? '';
        $this->streamtimeout = max(30, (int) (get_config('local_ai_reportcreator', 'stream_timeout') ?: 300));
    }

    /**
     * Return the path to the per-user meta JSON file used to persist the
     * generated report between the "stream" and "save" actions.
     *
     * Keyed by user ID + session ID so it survives session_write_close().
     *
     * @return string Absolute path to the meta JSON file.
     */
    public static function meta_path(): string {
        global $USER;
        $key = md5($USER->id . '_' . session_id());
        return make_temp_directory('ai_reportcreator') . '/meta_' . $key . '.json';
    }

    /**
     * Returns true if all required config values are present.
     *
     * @return bool True when both middlewareurl and api_key are non-empty.
     */
    public function is_configured(): bool {
        return $this->middlewareurl !== '' && $this->apikey !== '';
    }

    /**
     * Returns true when the middleware URL is plain HTTP (not HTTPS).
     *
     * @return bool True when the URL begins with http://.
     */
    public function is_insecure_url(): bool {
        return stripos($this->middlewareurl, 'http://') === 0;
    }

    /**
     * Returns the full middleware endpoint URL that will be called.
     *
     * @return string The configured middleware endpoint URL.
     */
    public function get_middleware_url(): string {
        return $this->middlewareurl;
    }

    /**
     * Stream the AI report pipeline response for the given request.
     *
     * Uses CURLOPT_HEADERFUNCTION to inspect the HTTP status code before any
     * body bytes are forwarded. If the server returns a non-2xx response the
     * body is buffered (not sent to $chunkcallback) and a moodle_exception is
     * thrown after curl_exec so stream.php can emit an SSE error event.
     *
     * @param  string   $request       The natural-language report request.
     * @param  string   $system        Calling system identifier (e.g. "moodle").
     * @param  string   $systemversion Calling system version string.
     * @param  string   $templatetype  Output template type (report/dashboard/bar/...).
     * @param  callable $chunkcallback Called with each raw SSE chunk on 2xx responses.
     * @return void
     * @throws \moodle_exception on cURL error or non-2xx HTTP status.
     */
    public function stream(
        string $request,
        string $system,
        string $systemversion,
        string $templatetype,
        callable $chunkcallback
    ): void {
        $url = $this->get_middleware_url();
        $ch  = curl_init($url);

        $authheader = 'Authorization: Bearer ' . $this->apikey;

        // Track HTTP status from response headers so we can distinguish an
        // error page from a real SSE stream before forwarding body bytes.
        $httpstatus  = 0;
        $isbadstatus = false;
        $errorbody   = '';   // Buffered body when status is non-2xx.

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'request'        => $request,
                'system'         => $system,
                'system_version' => $systemversion,
                'template_type'  => $templatetype,
            ]),
            CURLOPT_HTTPHEADER     => [
                $authheader,
                'Content-Type: application/json',
                'Accept: text/event-stream',
            ],
            CURLOPT_TIMEOUT        => $this->streamtimeout,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            // Capture the response status line before any body data arrives.
            CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$httpstatus, &$isbadstatus) {
                if (preg_match('/^HTTP\/[\d.]+\s+(\d+)/', $header, $m)) {
                    $httpstatus  = (int) $m[1];
                    $isbadstatus = ($httpstatus < 200 || $httpstatus >= 300);
                }
                return strlen($header);
            },

            // Forward body only on 2xx; buffer it on error so we can report it.
            CURLOPT_WRITEFUNCTION  => function ($curl, $data) use ($chunkcallback, &$isbadstatus, &$errorbody) {
                if ($isbadstatus) {
                    $errorbody .= $data;
                    return strlen($data);
                }
                $chunkcallback($data);
                return strlen($data);
            },
        ]);

        if ($this->is_insecure_url()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        $ok    = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        if ($ok === false || $errno !== 0) {
            throw new \moodle_exception(
                'curlerror',
                'local_ai_reportcreator',
                '',
                "cURL error {$errno}: {$error}"
            );
        }

        if ($isbadstatus) {
            $snippet = substr(strip_tags($errorbody), 0, 300);
            throw new \moodle_exception(
                'apierror',
                'local_ai_reportcreator',
                '',
                "API returned HTTP {$httpstatus} from {$url} — {$snippet}"
            );
        }
    }
}
