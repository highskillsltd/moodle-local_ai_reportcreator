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
 * View SQL page — displays the natural language request and generated SQL query.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/ai_reportcreator:manage', $context);

$id     = required_param('id', PARAM_INT);
$record = $DB->get_record('local_ai_reportcreator_reports', ['id' => $id], '*', MUST_EXIST);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/ai_reportcreator/viewsql.php', ['id' => $id]));
$PAGE->set_title(get_string('viewsqlfor', 'local_ai_reportcreator', $record->name));
$PAGE->set_heading(get_string('viewsqlfor', 'local_ai_reportcreator', $record->name));
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(
    get_string('reportlist', 'local_ai_reportcreator'),
    new moodle_url('/local/ai_reportcreator/index.php')
);
$PAGE->navbar->add(
    htmlspecialchars($record->name, ENT_QUOTES),
    new moodle_url('/local/ai_reportcreator/view.php', ['id' => $id])
);
$PAGE->navbar->add(get_string('viewsql', 'local_ai_reportcreator'));

echo $OUTPUT->header();

// Natural language request note.
echo '<div class="alert alert-info">';
echo '<strong>Request:</strong> ' . htmlspecialchars($record->nl_request, ENT_QUOTES);
echo '</div>';

// SQL block.
echo '<pre class="bg-light border rounded p-3" style="white-space:pre-wrap;word-break:break-all;">';
echo '<code style="font-family:monospace;">';
echo htmlspecialchars($record->sql_query, ENT_QUOTES);
echo '</code></pre>';

// Back button.
echo html_writer::link(
    new moodle_url('/local/ai_reportcreator/view.php', ['id' => $id]),
    get_string('back', 'local_ai_reportcreator'),
    ['class' => 'btn btn-secondary']
);

echo $OUTPUT->footer();
