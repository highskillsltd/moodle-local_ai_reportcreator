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
 * View report page — renders the AI-generated report output.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();
$context = context_system::instance();
require_capability('local/ai_reportcreator:manage', $context);

$id     = required_param('id', PARAM_INT);
$record = $DB->get_record('local_ai_reportcreator_reports', ['id' => $id], '*', MUST_EXIST);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/ai_reportcreator/view.php', ['id' => $id]));
$PAGE->set_title(htmlspecialchars($record->name, ENT_QUOTES));
$PAGE->set_heading(htmlspecialchars($record->name, ENT_QUOTES));
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(
    get_string('reportlist', 'local_ai_reportcreator'),
    new moodle_url('/local/ai_reportcreator/index.php')
);
$PAGE->navbar->add(htmlspecialchars($record->name, ENT_QUOTES));

// Validate SQL is read-only before executing.
$sqlerror = null;
$rows     = [];

if (!local_ai_reportcreator_validate_sql_readonly($record->sql_query)) {
    $sqlerror = get_string('sqlreadonlyerror', 'local_ai_reportcreator');
} else {
    try {
        $rows = $DB->get_records_sql($record->sql_query);
    } catch (Exception $e) {
        $sqlerror = get_string('sqlerror', 'local_ai_reportcreator', $e->getMessage());
    }
}

// Parse semantics.
$semantics    = json_decode($record->semantics_json, true) ?: [];
$templatetype = $record->template_type;

// Build rendered output.
$output = '';
if ($sqlerror) {
    $output = '<div class="alert alert-danger">' . htmlspecialchars($sqlerror, ENT_QUOTES) . '</div>';
} else if (in_array($templatetype, ['bar', 'line', 'pie', 'doughnut', 'radar'], true)) {
    $data   = array_values(array_map(fn($r) => (array) $r, $rows));
    $output = '<script>window.__DATA__ = ' . json_encode($data) . ';</script>' . "\n"
            . $record->template_html;
} else if ($templatetype === 'report') {
    $columns = $semantics['columns'] ?? [];
    $tbody   = '';
    foreach ($rows as $row) {
        $row    = (array) $row;
        $tbody .= '<tr>';
        foreach ($columns as $col) {
            $val    = htmlspecialchars((string) ($row[$col['key']] ?? ''), ENT_QUOTES);
            $tbody .= "<td>{$val}</td>";
        }
        $tbody .= '</tr>';
    }
    $output = str_replace('{{ROWS}}', $tbody, $record->template_html);
} else if ($templatetype === 'dashboard') {
    $columns   = $semantics['columns'] ?? [];
    $tbody     = '';
    foreach ($rows as $row) {
        $row    = (array) $row;
        $tbody .= '<tr>';
        foreach ($columns as $col) {
            $val    = htmlspecialchars((string) ($row[$col['key']] ?? ''), ENT_QUOTES);
            $tbody .= "<td>{$val}</td>";
        }
        $tbody .= '</tr>';
    }
    $firstrow = !empty($rows) ? (array) reset($rows) : [];
    $output   = str_replace('{{ROWS}}', $tbody, $record->template_html);
    foreach ($semantics['highlight_columns'] ?? [] as $colkey) {
        $val    = htmlspecialchars((string) ($firstrow[$colkey] ?? '—'), ENT_QUOTES);
        $output = str_replace('{{STAT_' . $colkey . '}}', $val, $output);
    }
} else {
    $output = $record->template_html;
}

// Page output.
echo $OUTPUT->header();

echo $output;

// Embed panel.
$embedurl  = (new moodle_url('/local/ai_reportcreator/embed.php', [
    'id' => $record->id,
]))->out(false);
$iframeid  = 'lai-embed-' . $record->id;
$iframetag = '<iframe id="' . $iframeid . '" src="' . $embedurl
            . '" width="100%" height="400" frameborder="0" scrolling="no"></iframe>';
$resizejs  = '<script>window.addEventListener(\'message\',function(e){'
            . 'if(e.data&&e.data.type===\'lai-resize\'){'
            . 'var f=document.getElementById(\'' . $iframeid . '\');'
            . 'if(f)f.style.height=e.data.height+\'px\';}});</script>';
$iframesrc = htmlspecialchars($iframetag . "\n" . $resizejs, ENT_QUOTES);

echo $OUTPUT->render_from_template('local_ai_reportcreator/embed_panel', [
    'embedcodelabel'    => get_string('embedcode', 'local_ai_reportcreator'),
    'embedinstructions' => get_string('embedinstructions', 'local_ai_reportcreator'),
    'iframesrc'         => $iframesrc,
    'copylabel'         => get_string('copycode', 'local_ai_reportcreator'),
]);

$PAGE->requires->strings_for_js(['copied', 'copycode'], 'local_ai_reportcreator');
$PAGE->requires->js_call_amd('local_ai_reportcreator/view', 'init');

// Action buttons.
echo '<div class="mt-3 mb-4">';
echo html_writer::link(
    new moodle_url('/local/ai_reportcreator/edit.php', ['id' => $id]),
    get_string('editreport', 'local_ai_reportcreator'),
    ['class' => 'btn btn-outline-warning me-2']
);
echo html_writer::link(
    new moodle_url('/local/ai_reportcreator/export.php', ['id' => $id, 'format' => 'csv']),
    get_string('exportcsv', 'local_ai_reportcreator'),
    ['class' => 'btn btn-outline-success me-2']
);
echo html_writer::link(
    new moodle_url('/local/ai_reportcreator/viewsql.php', ['id' => $id]),
    get_string('viewsql', 'local_ai_reportcreator'),
    ['class' => 'btn btn-outline-secondary me-2']
);
echo html_writer::link(
    new moodle_url('/local/ai_reportcreator/delete.php', ['id' => $id]),
    get_string('deletereport', 'local_ai_reportcreator'),
    ['class' => 'btn btn-outline-danger']
);
echo '</div>';

echo $OUTPUT->footer();
