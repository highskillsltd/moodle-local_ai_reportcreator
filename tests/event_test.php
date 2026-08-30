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
 * Unit tests for the local_ai_reportcreator event classes.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_reportcreator;

/**
 * Tests that each plugin event triggers cleanly and round-trips through the log store.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_ai_reportcreator\event\report_created
 * @covers     \local_ai_reportcreator\event\report_viewed
 * @covers     \local_ai_reportcreator\event\report_updated
 * @covers     \local_ai_reportcreator\event\report_deleted
 * @covers     \local_ai_reportcreator\event\report_exported
 * @covers     \local_ai_reportcreator\event\report_generation_failed
 * @covers     \local_ai_reportcreator\event\connection_tested
 */
final class event_test extends \advanced_testcase {
    /** @var string Table name used across tests. */
    private const TABLE = 'local_ai_reportcreator_rpts';

    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Insert a minimal report record and return its id.
     *
     * @return int
     */
    private function create_report(): int {
        global $DB;

        $record                    = new \stdClass();
        $record->userid            = 2;
        $record->name              = 'Test report';
        $record->nl_request        = 'Show me all users';
        $record->template_type     = 'report';
        $record->sql_query         = 'SELECT id FROM {user}';
        $record->template_html     = '<table>{{ROWS}}</table>';
        $record->semantics_json    = '{}';
        $record->embed_token       = str_pad('abc', 40, '0');
        $record->tokens_prompt     = 0;
        $record->tokens_completion = 0;
        $record->tokens_total      = 0;
        $record->generation_ms     = 0;
        $record->timecreated       = time();
        $record->timemodified      = time();

        return (int) $DB->insert_record(self::TABLE, $record);
    }

    /**
     * Trigger an event, assert it was captured, and check its describable output.
     *
     * @param \core\event\base $event The already-created (not yet triggered) event.
     * @return \core\event\base The captured event instance.
     */
    private function capture(\core\event\base $event): \core\event\base {
        $sink = $this->redirectEvents();
        $event->trigger();
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $captured = reset($events);
        $this->assertInstanceOf(get_class($event), $captured);

        // Name resolves to a real string and the description builds without throwing.
        $this->assertIsString($captured::get_name());
        $this->assertNotEmpty($captured->get_description());

        // The payload must survive a restore round-trip (log backup/restore path).
        $restored = \core\event\base::restore($captured->get_data(), ['origin' => 'restore']);
        $this->assertInstanceOf(get_class($event), $restored);

        return $captured;
    }

    /**
     * report_created carries the new report id and the create CRUD flag.
     */
    public function test_report_created(): void {
        $id = $this->create_report();

        $captured = $this->capture(event\report_created::create([
            'context'  => \context_system::instance(),
            'objectid' => $id,
            'other'    => [
                'name'          => 'Test report',
                'template_type' => 'report',
                'tokens_total'  => 123,
                'generation_ms' => 456,
            ],
        ]));

        $this->assertSame($id, (int) $captured->objectid);
        $this->assertSame('c', $captured->crud);
        $this->assertSame(self::TABLE, $captured->objecttable);
        $this->assertInstanceOf(\moodle_url::class, $captured->get_url());
    }

    /**
     * report_viewed uses the read CRUD flag.
     */
    public function test_report_viewed(): void {
        $id = $this->create_report();

        $captured = $this->capture(event\report_viewed::create([
            'context'  => \context_system::instance(),
            'objectid' => $id,
            'other'    => ['template_type' => 'report'],
        ]));

        $this->assertSame('r', $captured->crud);
        $this->assertSame($id, (int) $captured->objectid);
    }

    /**
     * report_updated uses the update CRUD flag.
     */
    public function test_report_updated(): void {
        $id = $this->create_report();

        $captured = $this->capture(event\report_updated::create([
            'context'  => \context_system::instance(),
            'objectid' => $id,
            'other'    => ['name' => 'Renamed'],
        ]));

        $this->assertSame('u', $captured->crud);
    }

    /**
     * report_deleted uses the delete CRUD flag and keeps the name in other data.
     */
    public function test_report_deleted(): void {
        $captured = $this->capture(event\report_deleted::create([
            'context'  => \context_system::instance(),
            'objectid' => 99,
            'other'    => ['name' => 'Gone report'],
        ]));

        $this->assertSame('d', $captured->crud);
        $this->assertStringContainsString('Gone report', $captured->get_description());
    }

    /**
     * report_exported records the format and row count.
     */
    public function test_report_exported(): void {
        $id = $this->create_report();

        $captured = $this->capture(event\report_exported::create([
            'context'  => \context_system::instance(),
            'objectid' => $id,
            'other'    => ['format' => 'csv', 'rowcount' => 7],
        ]));

        $this->assertSame('r', $captured->crud);
        $this->assertStringContainsString('csv', $captured->get_description());
        $this->assertStringContainsString('7', $captured->get_description());
    }

    /**
     * report_generation_failed carries metadata only and sets no objectid.
     */
    public function test_report_generation_failed(): void {
        $captured = $this->capture(event\report_generation_failed::create([
            'context' => \context_system::instance(),
            'other'   => [
                'template_type' => 'dashboard',
                'errortype'     => 'apierror',
                'http_code'     => 502,
            ],
        ]));

        $this->assertSame('r', $captured->crud);
        $this->assertNull($captured->objectid);
        $this->assertStringContainsString('apierror', $captured->get_description());
        $this->assertStringContainsString('502', $captured->get_description());
    }

    /**
     * connection_tested reports the outcome and HTTP status.
     */
    public function test_connection_tested(): void {
        $captured = $this->capture(event\connection_tested::create([
            'context' => \context_system::instance(),
            'other'   => ['success' => true, 'http_code' => 200],
        ]));

        $this->assertSame('r', $captured->crud);
        $this->assertStringContainsString('succeeded', $captured->get_description());
    }
}
