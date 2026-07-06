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
 * Create report page — submits a natural language request to the AI middleware.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/form/create_form.php');

require_login();
$context = context_system::instance();
require_capability('local/ai_reportcreator:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/ai_reportcreator/create.php'));
$PAGE->set_title(get_string('createreport', 'local_ai_reportcreator'));
$PAGE->set_heading(get_string('createreport', 'local_ai_reportcreator'));
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(
    get_string('reportlist', 'local_ai_reportcreator'),
    new moodle_url('/local/ai_reportcreator/index.php')
);
$PAGE->navbar->add(get_string('createreport', 'local_ai_reportcreator'));

$form = new \local_ai_reportcreator\form\create_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/ai_reportcreator/index.php'));
}

$apierror = null;
$success  = null;

if ($formdata = $form->get_data()) {
    $middleware_url = get_config('local_ai_reportcreator', 'middleware_url');
    $api_password   = get_config('local_ai_reportcreator', 'api_key');
    $moodle_version = get_config('local_ai_reportcreator', 'moodle_version')
        ?: (isset($CFG->release) ? $CFG->release : '4.3');

    if (empty($middleware_url)) {
        $apierror = 'Middleware URL is not configured. Please check plugin settings.';
    } else {
        $payload = json_encode([
            'request'        => $formdata->nl_request,
            'system'         => 'moodle',
            'system_version' => $moodle_version,
            'template_type'  => $formdata->template_type,
        ]);

        $curl = new curl();
        $curl->setHeader([
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_password,
        ]);

        $t_start       = microtime(true);
        $response_raw  = $curl->post($middleware_url, $payload);
        $generation_ms = (int) round((microtime(true) - $t_start) * 1000);

        $info     = $curl->get_info();
        $response = json_decode($response_raw, true);

        if (($info['http_code'] ?? 0) !== 200 || empty($response['sql_query'])) {
            $apierror = isset($response['error'])
                ? $response['error']
                : ('HTTP ' . ($info['http_code'] ?? 0) . ': ' . substr($response_raw, 0, 200));
        } else {
            // Generate a cryptographically random embed token (40 hex chars).
            $embed_token = bin2hex(random_bytes(20));

            $record                 = new stdClass();
            $record->userid         = $USER->id;
            $record->name           = $formdata->name;
            $record->nl_request     = $formdata->nl_request;
            $record->template_type  = $formdata->template_type;
            $record->sql_query      = $response['sql_query'];
            $record->template_html  = $response['template'] ?? '';
            $record->semantics_json = json_encode($response['semantics'] ?? []);
            $record->embed_token    = $embed_token;
            $record->timecreated    = time();
            $record->timemodified   = time();

            $newid = $DB->insert_record('local_ai_reportcreator_reports', $record);

            // Stats displayed once here — not persisted to DB.
            $success = [
                'id'                => $newid,
                'tokens_prompt'     => $response['tokens_used']['prompt'] ?? 0,
                'tokens_completion' => $response['tokens_used']['completion'] ?? 0,
                'tokens_total'      => $response['tokens_used']['total'] ?? 0,
                'generation_ms'     => $generation_ms,
            ];
        }
    }
}

echo $OUTPUT->header();

if ($apierror !== null) {
    echo $OUTPUT->notification(
        get_string('apierror', 'local_ai_reportcreator', $apierror),
        'error'
    );
}

if (!empty($success)) {
    // AI Generation Stats .
    echo '<div class="card mt-4"><div class="card-body">';
    echo '<h5 class="card-title">' . get_string('aistats', 'local_ai_reportcreator') . '</h5>';
    echo '<div class="row g-2">';
    $stats = [
        get_string('tokenprompt', 'local_ai_reportcreator') => number_format($success['tokens_prompt']),
        get_string('tokencompletion', 'local_ai_reportcreator') => number_format($success['tokens_completion']),
        get_string('tokentotal', 'local_ai_reportcreator') => number_format($success['tokens_total']),
        get_string('generationtime', 'local_ai_reportcreator') =>
        number_format($success['generation_ms'] / 1000, 2) . ' s',
    ];
    foreach ($stats as $label => $value) {
        echo '<div class="col-sm-3">';
        echo '<div class="border rounded p-2 text-center">';
        echo '<div class="fs-5 fw-bold">' . htmlspecialchars($value, ENT_QUOTES) . '</div>';
        echo '<div class="text-muted small">' . htmlspecialchars($label, ENT_QUOTES) . '</div>';
        echo '</div></div>';
    }
    echo '</div></div></div>';

    // View Report button .
    echo '<div class="mt-3">';
    echo html_writer::link(
        new moodle_url('/local/ai_reportcreator/view.php', ['id' => $success['id']]),
        get_string('viewreport', 'local_ai_reportcreator'),
        ['class' => 'btn btn-primary']
    );
    echo '</div>';
} else {
    // Progress panel (hidden until form is submitted).
    echo '<div id="progress-panel" style="display:none;" class="card p-4 text-center my-4">';
    echo '<h4>' . get_string('generating', 'local_ai_reportcreator') . '</h4>';
    echo '<div class="progress mt-3" style="height:20px;">';
    echo '<div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" '
        . 'role="progressbar" style="width:100%"></div>';
    echo '</div>';
    echo '<p class="mt-3 text-muted" id="progress-status">'
        . get_string('progress_calling', 'local_ai_reportcreator') . '</p>';
    echo '</div>';

    echo '<div id="form-container">';
    $form->display();
    echo '</div>';

    $progress_messages = json_encode([
        get_string('progress_calling', 'local_ai_reportcreator'),
        get_string('progress_processing', 'local_ai_reportcreator'),
        get_string('progress_sql', 'local_ai_reportcreator'),
        get_string('progress_template', 'local_ai_reportcreator'),
        get_string('progress_almost', 'local_ai_reportcreator'),
    ]);

    echo '<script>';
    echo '(function () {';
    echo "    var messages = {$progress_messages};";
    echo '    var formContainer = document.getElementById(\'form-container\');';
    echo '    var form = formContainer ? formContainer.querySelector(\'form\') : null;';
    echo '    if (!form) return;';
    echo '    form.addEventListener(\'submit\', function () {';
    echo '        formContainer.style.display = \'none\';';
    echo '        document.getElementById(\'progress-panel\').style.display = \'block\';';
    echo '        var idx = 0;';
    echo '        setInterval(function () {';
    echo '            idx = (idx + 1) % messages.length;';
    echo '            var el = document.getElementById(\'progress-status\');';
    echo '            if (el) el.textContent = messages[idx];';
    echo '        }, 2000);';
    echo '    });';
    echo '})();';
    echo '</script>';
}

echo $OUTPUT->footer();
