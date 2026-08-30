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
 * Event fired when an AI-generated report is exported.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_reportcreator\event;

/**
 * Event fired when a report's result rows are downloaded from the export page.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_exported extends \core\event\base {
    /**
     * Initialise the event data.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud']        = 'r';
        $this->data['edulevel']    = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_ai_reportcreator_rpts';
    }

    /**
     * Return the localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('event:report_exported', 'local_ai_reportcreator');
    }

    /**
     * Return a description of what happened.
     *
     * @return string
     */
    public function get_description() {
        $format = s($this->other['format'] ?? '');
        $rows   = (int) ($this->other['rowcount'] ?? 0);
        return "The user with id '{$this->userid}' exported the AI report with id '{$this->objectid}' " .
            "as '{$format}' ({$rows} rows).";
    }

    /**
     * Return the URL of the exported report.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/ai_reportcreator/view.php', ['id' => $this->objectid]);
    }
}
