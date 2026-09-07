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
        // This step used to create a bundled "ai_reportcreator" role. The plugin no
        // longer ships that role (capabilities now sit on standard archetypes), and the
        // 2024010107 step below removes it, so there is nothing to do here.
        upgrade_plugin_savepoint(true, 2024010104, 'local', 'ai_reportcreator');
    }

    if ($oldversion < 2024010107) {
        // Drop the bundled "ai_reportcreator" role. local/ai_reportcreator:manage is now
        // granted to the manager archetype and local/ai_reportcreator:view to the
        // authenticated user archetype, so the dedicated role is no longer needed.
        $role = $DB->get_record('role', ['shortname' => 'ai_reportcreator']);
        if ($role) {
            delete_role($role->id);
        }

        upgrade_plugin_savepoint(true, 2024010107, 'local', 'ai_reportcreator');
    }

    return true;
}
