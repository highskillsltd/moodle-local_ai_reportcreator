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

/**
 * SSE proxy and save endpoint for local_ai_reportcreator.
 *
 * Dispatches based on the `action` query parameter:
 *   stream — POST a report request; proxy the middleware's SSE stream to the browser.
 *   save   — Persist the report captured by the preceding "stream" call to the DB.
 *
 * @package   local_ai_reportcreator
 * @copyright 2026 Highskills and more <info@highskills.co.il>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// phpcs:disable moodle.Files.RequireLogin.Missing -- login/capability checked below.

require_once(__DIR__ . '/../../config.php');

use local_ai_reportcreator\ApiClient;

require_login();
$context = context_system::instance();
require_capability('local/ai_reportcreator:manage', $context);

$action = required_param('action', PARAM_ALPHAEXT);

// ACTION: stream.
if ($action === 'stream') {
    require_sesskey();

    $name         = required_param('name', PARAM_TEXT);
    $nlrequest    = required_param('nl_request', PARAM_RAW);
    $templatetype = required_param('template_type', PARAM_ALPHA);

    $moodleversion = get_config('local_ai_reportcreator', 'moodle_version')
        ?: (isset($CFG->release) ? $CFG->release : '4.3');

    $client = new ApiClient();

    // Release the session write lock before the long-running stream so other
    // browser tabs are not blocked for the full pipeline duration.
    \core\session\manager::write_close();

    // Clear all output buffering layers Moodle's bootstrap started so that
    // each flush() call sends bytes directly to the browser in real time.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    ob_implicit_flush(true);

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
    header('Content-Encoding: identity');

    if (!$client->is_configured()) {
        $msg = get_string('api_not_configured', 'local_ai_reportcreator');
        echo 'data: ' . json_encode(['event' => 'error', 'message' => $msg]) . "\n\n";
        flush();
        exit;
    }

    if (trim($nlrequest) === '') {
        echo 'data: ' . json_encode(['event' => 'error', 'message' => get_string('nlrequest', 'local_ai_reportcreator')]) . "\n\n";
        flush();
        exit;
    }

    $parsebuffer = '';
    $resultdata  = null;

    $streamcallback = function (string $chunk) use (&$parsebuffer, &$resultdata): void {
        echo $chunk;
        flush();

        if ($resultdata !== null) {
            return;
        }

        $parsebuffer .= $chunk;
        $blocks      = explode("\n\n", $parsebuffer);
        $parsebuffer = array_pop($blocks);

        foreach ($blocks as $block) {
            $dataline = null;
            foreach (explode("\n", $block) as $line) {
                if (strpos($line, 'data: ') === 0) {
                    $dataline = substr($line, 6);
                    break;
                }
            }
            if ($dataline === null) {
                continue;
            }

            $payload = json_decode($dataline, true);
            if (!is_array($payload) || ($payload['event'] ?? '') !== 'result') {
                continue;
            }

            $resultdata = $payload;
        }
    };

    try {
        $client->stream($nlrequest, 'moodle', $moodleversion, $templatetype, $streamcallback);
    } catch (\Throwable $e) {
        echo 'data: ' . json_encode(['event' => 'error', 'message' => $e->getMessage()]) . "\n\n";
        flush();
        exit;
    }

    // Persist the captured result to a meta file so the follow-up "save" call
    // can find it after session_write_close() above.
    if ($resultdata !== null) {
        file_put_contents(ApiClient::meta_path(), json_encode([
            'name'          => $name,
            'nl_request'    => $nlrequest,
            'template_type' => $templatetype,
            'sql_query'     => $resultdata['sql_query'] ?? '',
            'template'      => $resultdata['template'] ?? '',
            'semantics'     => $resultdata['semantics'] ?? [],
        ]));
    }

    exit;
}

// ACTION: save.
if ($action === 'save') {
    require_sesskey();

    $metapath = ApiClient::meta_path();
    header('Content-Type: application/json');

    if (!file_exists($metapath)) {
        http_response_code(400);
        echo json_encode(['error' => 'No pending report data found — please generate again.']);
        exit;
    }

    $info = json_decode(file_get_contents($metapath), true);
    @unlink($metapath);

    if (!$info || empty($info['sql_query'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No pending report data found — please generate again.']);
        exit;
    }

    $tokenstotal  = (int) optional_param('tokens_total', 0, PARAM_INT);
    $generationms = (int) optional_param('generation_ms', 0, PARAM_INT);

    $record                    = new stdClass();
    $record->userid            = $USER->id;
    $record->name              = $info['name'];
    $record->nl_request        = $info['nl_request'];
    $record->template_type     = $info['template_type'];
    $record->sql_query         = $info['sql_query'];
    $record->template_html     = $info['template'];
    $record->semantics_json    = json_encode($info['semantics']);
    $record->embed_token       = bin2hex(random_bytes(20));
    $record->tokens_prompt     = 0;
    $record->tokens_completion = 0;
    $record->tokens_total      = $tokenstotal;
    $record->generation_ms     = $generationms;
    $record->timecreated       = time();
    $record->timemodified      = time();

    $newid = $DB->insert_record('local_ai_reportcreator_reports', $record);

    echo json_encode([
        'id'      => $newid,
        'viewurl' => (new moodle_url('/local/ai_reportcreator/view.php', ['id' => $newid]))->out(false),
    ]);
    exit;
}

// Unknown action.
throw new moodle_exception('invalidparameter', 'error');
