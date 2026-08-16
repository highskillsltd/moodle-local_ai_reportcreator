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
     * Uses Moodle's \curl wrapper (so site proxy and security-helper settings
     * are respected) with a CURLOPT_WRITEFUNCTION callback to inspect the
     * HTTP status code before any body bytes are forwarded. If the server
     * returns a non-2xx response the body is buffered (not sent to
     * $chunkcallback) and a moodle_exception is thrown so stream.php can
     * emit an SSE error event.
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

        $postfields = json_encode([
            'request'        => $request,
            'system'         => $system,
            'system_version' => $systemversion,
            'template_type'  => $templatetype,
        ]);

        // Track HTTP status so we can distinguish an error page from a real
        // SSE stream before forwarding body bytes. Read from the live curl
        // handle on the first body chunk — cURL always finishes header
        // parsing before CURLOPT_WRITEFUNCTION is first invoked.
        $httpstatus  = 0;
        $isbadstatus = false;
        $errorbody   = '';   // Buffered body when status is non-2xx.

        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $curl = new \curl();
        $curl->setHeader([
            'Authorization: Bearer ' . $this->apikey,
            'Content-Type: application/json',
            'Accept: text/event-stream',
        ]);

        $options = [
            'CURLOPT_TIMEOUT'        => $this->streamtimeout,
            'CURLOPT_RETURNTRANSFER' => false,
            // Forward body only on 2xx; buffer it on error so we can report it.
            'CURLOPT_WRITEFUNCTION'  => function (
                $ch,
                $data
            ) use (
                $chunkcallback,
                &$httpstatus,
                &$isbadstatus,
                &$errorbody
            ) {
                if ($httpstatus === 0) {
                    $httpstatus  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $isbadstatus = ($httpstatus < 200 || $httpstatus >= 300);
                }

                if ($isbadstatus) {
                    $errorbody .= $data;
                    return strlen($data);
                }

                $chunkcallback($data);
                return strlen($data);
            },
        ];

        if ($this->is_insecure_url()) {
            $options['CURLOPT_SSL_VERIFYPEER'] = false;
            $options['CURLOPT_SSL_VERIFYHOST'] = 0;
        } else {
            $options['CURLOPT_SSL_VERIFYPEER'] = true;
            $options['CURLOPT_SSL_VERIFYHOST'] = 2;
        }

        $curl->post($url, $postfields, $options);

        if ($curl->errno !== 0) {
            throw new \moodle_exception(
                'curlerror',
                'local_ai_reportcreator',
                '',
                "cURL error {$curl->errno}: {$curl->error}"
            );
        }

        // No body bytes ever arrived (e.g. an empty-body error response) —
        // fall back to the status captured by the wrapper itself.
        if ($httpstatus === 0) {
            $httpstatus  = (int) ($curl->get_info()['http_code'] ?? 0);
            $isbadstatus = ($httpstatus < 200 || $httpstatus >= 300);
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
