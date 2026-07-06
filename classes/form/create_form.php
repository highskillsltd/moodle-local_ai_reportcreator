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
 * Moodle form for creating a new AI-generated report.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_reportcreator\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
/**
 * Form definition for creating a new AI-generated report.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_form extends \moodleform {
    /**
     * Defines the form fields: report name, natural language request, output type.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'text',
            'name',
            get_string('reportname', 'local_ai_reportcreator'),
            ['size' => 60, 'maxlength' => 255]
        );
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement(
            'textarea',
            'nl_request',
            get_string('nlrequest', 'local_ai_reportcreator'),
            [
                'rows'        => 5,
                'cols'        => 60,
                'placeholder' => get_string('nlrequest_placeholder', 'local_ai_reportcreator'),
            ]
        );
        $mform->setType('nl_request', PARAM_TEXT);
        $mform->addRule('nl_request', null, 'required', null, 'client');
        $mform->addElement(
            'html',
            '<div class="form-text text-muted small mt-1">'
            . get_string('nlrequest_help', 'local_ai_reportcreator')
            . '</div>'
        );

        $types = [
            'report'    => get_string('type_report', 'local_ai_reportcreator'),
            'dashboard' => get_string('type_dashboard', 'local_ai_reportcreator'),
            'bar'       => get_string('type_bar', 'local_ai_reportcreator'),
            'line'      => get_string('type_line', 'local_ai_reportcreator'),
            'pie'       => get_string('type_pie', 'local_ai_reportcreator'),
            'doughnut'  => get_string('type_doughnut', 'local_ai_reportcreator'),
            'radar'     => get_string('type_radar', 'local_ai_reportcreator'),
        ];
        $mform->addElement('select', 'template_type', get_string('templatetype', 'local_ai_reportcreator'), $types);
        $mform->setDefault('template_type', 'report');

        $this->add_action_buttons(true, get_string('generate', 'local_ai_reportcreator'));
    }
}
