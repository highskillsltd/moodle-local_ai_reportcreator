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
 * Delete report confirmation page.
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
$record = $DB->get_record('local_ai_reportcreator_rpts', ['id' => $id], '*', MUST_EXIST);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/ai_reportcreator/delete.php', ['id' => $id]));
$PAGE->set_title(get_string('deletereport', 'local_ai_reportcreator'));
$PAGE->set_heading(get_string('deletereport', 'local_ai_reportcreator'));
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(
    get_string('reportlist', 'local_ai_reportcreator'),
    new moodle_url('/local/ai_reportcreator/index.php')
);
$PAGE->navbar->add(
    htmlspecialchars($record->name, ENT_QUOTES),
    new moodle_url('/local/ai_reportcreator/view.php', ['id' => $id])
);
$PAGE->navbar->add(get_string('deletereport', 'local_ai_reportcreator'));

$confirmed = optional_param('confirm', 0, PARAM_INT);

if ($confirmed && confirm_sesskey()) {
    $DB->delete_records('local_ai_reportcreator_rpts', ['id' => $id]);
    redirect(
        new moodle_url('/local/ai_reportcreator/index.php'),
        get_string('reportdeleted', 'local_ai_reportcreator'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$confirmurl = new moodle_url('/local/ai_reportcreator/delete.php', [
    'id'      => $id,
    'confirm' => 1,
    'sesskey' => sesskey(),
]);
$cancelurl = new moodle_url('/local/ai_reportcreator/view.php', ['id' => $id]);

echo $OUTPUT->header();
echo $OUTPUT->confirm(
    get_string('confirmdelete', 'local_ai_reportcreator')
        . ' <strong>' . htmlspecialchars($record->name, ENT_QUOTES) . '</strong>?',
    $confirmurl,
    $cancelurl
);
echo $OUTPUT->footer();
