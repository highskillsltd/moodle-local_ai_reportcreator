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
 * Admin settings for the AI Report Creator plugin.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_ai_reportcreator',
        get_string('pluginname', 'local_ai_reportcreator')
    );
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext(
        'local_ai_reportcreator/middleware_url',
        get_string('middlewareurl', 'local_ai_reportcreator'),
        get_string('middlewareurl_desc', 'local_ai_reportcreator'),
        '',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'local_ai_reportcreator/api_key',
        get_string('apikey', 'local_ai_reportcreator'),
        get_string('apikey_desc', 'local_ai_reportcreator'),
        '',
        PARAM_ALPHANUMEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_ai_reportcreator/moodle_version',
        get_string('moodleversion', 'local_ai_reportcreator'),
        '',
        isset($CFG->release) ? $CFG->release : '4.3',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_ai_reportcreator/stream_timeout',
        get_string('streamtimeout', 'local_ai_reportcreator'),
        get_string('streamtimeout_desc', 'local_ai_reportcreator'),
        '300',
        PARAM_INT
    ));

    // Navigation link: Site administration → Reports → AI Reports → AI Report Creator.
    $ADMIN->add('aireports', new admin_externalpage(
        'local_ai_reportcreator_index',
        get_string('pluginname', 'local_ai_reportcreator'),
        new moodle_url('/local/ai_reportcreator/index.php'),
        'local/ai_reportcreator:manage'
    ));

    // Test connection button (display-only, no stored value).
    require_once($CFG->dirroot . '/local/ai_reportcreator/classes/admin/setting_testconnection.php');
    $settings->add(new \local_ai_reportcreator\admin\setting_testconnection());
}
