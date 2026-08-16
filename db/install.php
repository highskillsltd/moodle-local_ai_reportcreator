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
 * Post-install hook: creates the "AI Report creator" role.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_local_ai_reportcreator_install() {
    global $DB;

    // On a fresh install, Moodle runs this hook (xmldb_local_ai_reportcreator_install())
    // before it registers this plugin's capabilities from db/access.php into the database
    // (that normally happens in update_capabilities(), called right after this hook returns).
    // Force registration now so assign_capability() below can find the capability it needs.
    // Calling update_capabilities() again afterward (as Moodle always does) is a harmless
    // no-op once the database already matches db/access.php.
    update_capabilities('local_ai_reportcreator');

    if ($DB->record_exists('role', ['shortname' => 'ai_reportcreator'])) {
        return;
    }

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
