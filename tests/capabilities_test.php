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
 * Tests for the local_ai_reportcreator capability model.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_reportcreator;

/**
 * Verifies how :view and :manage are granted to the standard archetypes.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class capabilities_test extends \advanced_testcase {
    /**
     * Both plugin capabilities are registered in the database.
     */
    public function test_capabilities_are_registered(): void {
        global $DB;
        $this->resetAfterTest();

        $this->assertTrue($DB->record_exists('capabilities', ['name' => 'local/ai_reportcreator:view']));
        $this->assertTrue($DB->record_exists('capabilities', ['name' => 'local/ai_reportcreator:manage']));
    }

    /**
     * The plugin no longer ships a bundled "ai_reportcreator" role.
     */
    public function test_no_bundled_role_is_created(): void {
        global $DB;
        $this->resetAfterTest();

        $this->assertFalse($DB->record_exists('role', ['shortname' => 'ai_reportcreator']));
    }

    /**
     * An ordinary authenticated user may view reports but not manage them.
     */
    public function test_authenticated_user_can_view_but_not_manage(): void {
        $this->resetAfterTest();

        $user    = $this->getDataGenerator()->create_user();
        $context = \context_system::instance();

        $this->assertTrue(has_capability('local/ai_reportcreator:view', $context, $user));
        $this->assertFalse(has_capability('local/ai_reportcreator:manage', $context, $user));
    }

    /**
     * A user with the Manager role at system level has both capabilities.
     */
    public function test_system_manager_can_view_and_manage(): void {
        global $DB;
        $this->resetAfterTest();

        $manager = $this->getDataGenerator()->create_user();
        $context = \context_system::instance();
        $roleid  = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);
        role_assign($roleid, $manager->id, $context->id);

        $this->assertTrue(has_capability('local/ai_reportcreator:view', $context, $manager));
        $this->assertTrue(has_capability('local/ai_reportcreator:manage', $context, $manager));
    }
}
