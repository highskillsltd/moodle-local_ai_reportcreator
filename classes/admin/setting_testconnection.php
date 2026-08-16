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
 * Custom admin setting class for the Test Connection button.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_ai_reportcreator\admin;
/**
 * Renders a "Test Connection" button in the admin settings page.
 *
 * Display-only setting — does not store any value. The button fires an AJAX
 * request to testconnection.php using the unsaved field values on the page.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_testconnection extends \admin_setting {
    /**
     * Constructor — registers the setting with a placeholder name (no stored value).
     */
    public function __construct() {
        parent::__construct(
            'local_ai_reportcreator/testconnection_placeholder',
            get_string('testconnection', 'local_ai_reportcreator'),
            '',
            ''
        );
    }

    /**
     * Returns a truthy value so Moodle renders the element.
     *
     * @return bool
     */
    public function get_setting() {
        return true;
    }

    /**
     * No value to persist — always returns an empty string (no error).
     *
     * @param mixed $data Form data (unused).
     * @return string Empty string indicating success.
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * This setting is never matched by the admin search.
     *
     * @param string $query Search query (unused).
     * @return bool Always false.
     */
    public function is_related($query) {
        return false;
    }

    /**
     * Renders the Test Connection button and its inline AJAX script.
     *
     * @param mixed  $data  Current setting value (unused).
     * @param string $query Admin search query (unused).
     * @return string HTML output.
     */
    public function output_html($data, $query = '') {
        global $CFG, $OUTPUT, $PAGE;

        $testurl  = $CFG->wwwroot . '/local/ai_reportcreator/testconnection.php';
        $btnlabel = get_string('testconnection', 'local_ai_reportcreator');

        $html = $OUTPUT->render_from_template('local_ai_reportcreator/testconnection_setting', [
            'btnlabel' => $btnlabel,
        ]);

        $PAGE->requires->strings_for_js(
            ['testingconnection', 'testconnection_success', 'testconnection_fail', 'httpstatuslabel', 'errorheading'],
            'local_ai_reportcreator'
        );
        $PAGE->requires->js_call_amd('local_ai_reportcreator/testconnection', 'init', [
            [
                'testurl' => $testurl,
                'sesskey' => sesskey(),
            ],
        ]);

        return $html;
    }
}
