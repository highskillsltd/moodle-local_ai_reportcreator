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
 * Moodle form for editing an existing AI-generated report name.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_reportcreator\form;

require_once($CFG->libdir . '/formslib.php');

/**
 * Form definition for editing an existing report's name.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class edit_form extends \moodleform {
    /**
     * Defines the form fields: hidden id and editable report name.
     */
    public function definition() {
        $mform  = $this->_form;
        $record = $this->_customdata;

        $mform->addElement('hidden', 'id', $record->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement(
            'text',
            'name',
            get_string('reportname', 'local_ai_reportcreator'),
            ['size' => 60, 'maxlength' => 255]
        );
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setDefault('name', $record->name);

        $this->add_action_buttons(true, get_string('savereport', 'local_ai_reportcreator'));
    }
}
