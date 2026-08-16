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
$apinotification = '';
if (!$client->is_configured()) {
    $apinotification = $OUTPUT->notification(get_string('api_not_configured', 'local_ai_reportcreator'), 'warning');
}

$templatedata = [
    'apinotification'   => $apinotification,
    'formhtml'          => $form->render(),
    'generatinglabel'   => get_string('generating', 'local_ai_reportcreator'),
    'errorheadinglabel' => get_string('errorheading', 'local_ai_reportcreator'),
    'progressrows'      => [
        ['id' => 'row-sql', 'label' => get_string('agent_sql', 'local_ai_reportcreator')],
        ['id' => 'row-template', 'label' => get_string('agent_template', 'local_ai_reportcreator')],
    ],
];

echo $OUTPUT->render_from_template('local_ai_reportcreator/create_panels', $templatedata);

echo $OUTPUT->footer();
