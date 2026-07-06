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
 * Upgrade steps for the AI Report Creator plugin.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade steps for the local_ai_reportcreator plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Always true.
 */
function xmldb_local_ai_reportcreator_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2024010104) {
        if (!$DB->record_exists('role', ['shortname' => 'ai_reportcreator'])) {
            $roleid = create_role(
                get_string('role:ai_reportcreator', 'local_ai_reportcreator'),
                'ai_reportcreator',
                get_string('role:ai_reportcreator_desc', 'local_ai_reportcreator'),
                'manager'
            );

            reset_role_capabilities($roleid);

            set_role_contextlevels($roleid, [CONTEXT_SYSTEM, CONTEXT_COURSECAT]);

            $context = context_system::instance();
            assign_capability('local/ai_reportcreator:manage', CAP_ALLOW, $roleid, $context->id, true);
        }

        upgrade_plugin_savepoint(true, 2024010104, 'local', 'ai_reportcreator');
    }

    return true;
}
