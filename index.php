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
 * Report list page — shows all AI-generated reports for the current user.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();
$context = context_system::instance();
require_capability('local/ai_reportcreator:manage', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/ai_reportcreator/index.php'));
$PAGE->set_title(get_string('reportlist', 'local_ai_reportcreator'));
$PAGE->set_heading(get_string('reportlist', 'local_ai_reportcreator'));
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();

$reports = $DB->get_records('local_ai_reportcreator_rpts', null, 'timecreated DESC');

$templatedata = [
    'createurl'         => (new moodle_url('/local/ai_reportcreator/create.php'))->out(false),
    'createlabel'       => get_string('createreport', 'local_ai_reportcreator'),
    'hasreports'        => !empty($reports),
    'emptynotification' => '',
    'tablehtml'         => '',
];

if (empty($reports)) {
    $createlink = html_writer::link(
        new moodle_url('/local/ai_reportcreator/create.php'),
        get_string('createreport', 'local_ai_reportcreator')
    );
    $templatedata['emptynotification'] = $OUTPUT->notification(
        get_string('noreports', 'local_ai_reportcreator') . ' ' . $createlink,
        'info'
    );
} else {
    $table = new html_table();
    $table->head = [
        get_string('col_name', 'local_ai_reportcreator'),
        get_string('col_type', 'local_ai_reportcreator'),
        get_string('col_created', 'local_ai_reportcreator'),
        get_string('col_actions', 'local_ai_reportcreator'),
    ];
    $table->attributes['class'] = 'table table-striped generaltable';

    foreach ($reports as $report) {
        $viewurl   = new moodle_url('/local/ai_reportcreator/view.php', ['id' => $report->id]);
        $editurl   = new moodle_url('/local/ai_reportcreator/edit.php', ['id' => $report->id]);
        $csvurl    = new moodle_url('/local/ai_reportcreator/export.php', ['id' => $report->id, 'format' => 'csv']);
        $deleteurl = new moodle_url('/local/ai_reportcreator/delete.php', ['id' => $report->id]);

        $actions =
            html_writer::link(
                $editurl,
                get_string('editreport', 'local_ai_reportcreator'),
                ['class' => 'btn btn-sm btn-outline-warning me-1']
            ) .
            html_writer::link(
                $csvurl,
                get_string('exportcsv', 'local_ai_reportcreator'),
                ['class' => 'btn btn-sm btn-outline-success me-1']
            ) .
            html_writer::link(
                $viewurl,
                get_string('viewreport', 'local_ai_reportcreator'),
                ['class' => 'btn btn-sm btn-outline-primary me-1']
            ) .
            html_writer::link(
                $deleteurl,
                get_string('deletereport', 'local_ai_reportcreator'),
                ['class' => 'btn btn-sm btn-outline-danger']
            );

        $typebadge = '<span class="badge bg-secondary">' .
            htmlspecialchars($report->template_type, ENT_QUOTES) . '</span>';

        $table->data[] = [
            html_writer::link($viewurl, htmlspecialchars($report->name, ENT_QUOTES)),
            $typebadge,
            userdate($report->timecreated),
            $actions,
        ];
    }

    $templatedata['tablehtml'] = html_writer::table($table);
}

echo $OUTPUT->render_from_template('local_ai_reportcreator/report_list', $templatedata);

echo $OUTPUT->footer();
