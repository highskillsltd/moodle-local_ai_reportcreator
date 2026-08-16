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

$PAGE->requires->strings_for_js(
    ['running', 'done', 'unknownerror', 'savefailed', 'httpstatuslabel'],
    'local_ai_reportcreator'
);

$PAGE->requires->js_call_amd('local_ai_reportcreator/create', 'init', [
    [
        'sesskey'   => sesskey(),
        'streamUrl' => (new moodle_url('/local/ai_reportcreator/stream.php', ['action' => 'stream']))->out(false),
        'saveUrl'   => (new moodle_url('/local/ai_reportcreator/stream.php', ['action' => 'save']))->out(false),
    ],
]);

echo $OUTPUT->header();

$client = new \local_ai_reportcreator\ApiClient();
if (!$client->is_configured()) {
    echo $OUTPUT->notification(get_string('api_not_configured', 'local_ai_reportcreator'), 'warning');
}

echo '<div class="row g-3 align-items-start">';

echo '<div class="col-md-7">';
echo '<div id="form-container">';
$form->display();
echo '</div>';
echo '</div>';

echo '<div class="col-md-5">';

// Progress panel (hidden until the form is submitted).
echo '<div id="progress-panel" class="card mb-3 d-none">';
echo '<div class="card-header fw-semibold">' . get_string('generating', 'local_ai_reportcreator') . '</div>';
echo '<div class="card-body p-0">';
echo '<table class="table table-sm mb-0" id="progress-table"><tbody>';

$progressrows = [
    ['id' => 'row-sql', 'label' => get_string('agent_sql', 'local_ai_reportcreator')],
    ['id' => 'row-template', 'label' => get_string('agent_template', 'local_ai_reportcreator')],
];
foreach ($progressrows as $row) {
    echo '<tr id="' . $row['id'] . '">';
    echo '<td class="ps-3 py-2 w-50">' . htmlspecialchars($row['label'], ENT_QUOTES) . '</td>';
    echo '<td class="py-2"><span class="status-badge">'
        . '<span class="spinner-grow spinner-grow-sm text-secondary" role="status"></span>'
        . '</span></td>';
    echo '<td class="py-2 text-end pe-3 text-muted small detail-cell"></td>';
    echo '</tr>';
}

echo '</tbody></table>';
echo '</div></div>';

// Error banner (hidden until an error event).
echo '<div id="error-panel" class="alert alert-danger d-none" role="alert">';
echo '<strong>' . get_string('errorheading', 'local_ai_reportcreator') . ':</strong> ';
echo '<span id="error-message"></span>';
echo '</div>';

echo '</div>'; // /.col-md-5
echo '</div>'; // /.row

echo $OUTPUT->footer();
