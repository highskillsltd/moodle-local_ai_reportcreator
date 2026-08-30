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
 * Tests for the local_ai_reportcreator event classes.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_reportcreator\event;

/**
 * Verifies each event triggers cleanly and carries the expected data.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class events_test extends \advanced_testcase {
    /** @var string Table name used across tests. */
    private const TABLE = 'local_ai_reportcreator_rpts';

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
     * Catch a single event raised by the callback and return it.
     *
     * @param callable $callback Code that triggers exactly one event.
     * @return \core\event\base
     */
    private function capture_event(callable $callback): \core\event\base {
        $sink = $this->redirectEvents();
        $callback();
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        return reset($events);
    }

    /**
     * report_created carries the new report id and create CRUD flag.
     *
     * @covers \local_ai_reportcreator\event\report_created
     */
    public function test_report_created(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $id = $this->create_report();

        $event = $this->capture_event(function () use ($id) {
            report_created::create([
                'context'  => \context_system::instance(),
                'objectid' => $id,
                'other'    => [
                    'name'          => 'Test report',
                    'template_type' => 'report',
                    'tokens_total'  => 123,
                    'generation_ms' => 456,
                ],
            ])->trigger();
        });

        $this->assertInstanceOf(report_created::class, $event);
        $this->assertSame($id, (int) $event->objectid);
        $this->assertSame('c', $event->crud);
        $this->assertSame(self::TABLE, $event->objecttable);
        $this->assertIsString($event->get_description());
        $this->assertIsString(report_created::get_name());
    }

    /**
     * report_viewed uses the read CRUD flag.
     *
     * @covers \local_ai_reportcreator\event\report_viewed
     */
    public function test_report_viewed(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $id = $this->create_report();

        $event = $this->capture_event(function () use ($id) {
            report_viewed::create([
                'context'  => \context_system::instance(),
                'objectid' => $id,
                'other'    => ['template_type' => 'report'],
            ])->trigger();
        });

        $this->assertSame('r', $event->crud);
        $this->assertSame($id, (int) $event->objectid);
        $this->assertIsString($event->get_description());
    }

    /**
     * report_updated uses the update CRUD flag.
     *
     * @covers \local_ai_reportcreator\event\report_updated
     */
    public function test_report_updated(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $id = $this->create_report();

        $event = $this->capture_event(function () use ($id) {
            report_updated::create([
                'context'  => \context_system::instance(),
                'objectid' => $id,
                'other'    => ['name' => 'Renamed'],
            ])->trigger();
        });

        $this->assertSame('u', $event->crud);
        $this->assertIsString($event->get_description());
    }

    /**
     * report_deleted uses the delete CRUD flag and keeps the name in other data.
     *
     * @covers \local_ai_reportcreator\event\report_deleted
     */
    public function test_report_deleted(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $event = $this->capture_event(function () {
            report_deleted::create([
                'context'  => \context_system::instance(),
                'objectid' => 99,
                'other'    => ['name' => 'Gone report'],
            ])->trigger();
        });

        $this->assertSame('d', $event->crud);
        $this->assertStringContainsString('Gone report', $event->get_description());
    }

    /**
     * report_exported records the format and row count.
     *
     * @covers \local_ai_reportcreator\event\report_exported
     */
    public function test_report_exported(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $id = $this->create_report();

        $event = $this->capture_event(function () use ($id) {
            report_exported::create([
                'context'  => \context_system::instance(),
                'objectid' => $id,
                'other'    => ['format' => 'csv', 'rowcount' => 7],
            ])->trigger();
        });

        $this->assertSame('r', $event->crud);
        $this->assertStringContainsString('csv', $event->get_description());
        $this->assertStringContainsString('7', $event->get_description());
    }

    /**
     * report_generation_failed carries metadata only and sets no objectid.
     *
     * @covers \local_ai_reportcreator\event\report_generation_failed
     */
    public function test_report_generation_failed(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $event = $this->capture_event(function () {
            report_generation_failed::create([
                'context' => \context_system::instance(),
                'other'   => [
                    'template_type' => 'dashboard',
                    'errortype'     => 'apierror',
                    'http_code'     => 502,
                ],
            ])->trigger();
        });

        $this->assertSame('r', $event->crud);
        $this->assertNull($event->objectid);
        $this->assertStringContainsString('apierror', $event->get_description());
        $this->assertStringContainsString('502', $event->get_description());
    }

    /**
     * connection_tested reports the outcome and HTTP status.
     *
     * @covers \local_ai_reportcreator\event\connection_tested
     */
    public function test_connection_tested(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $event = $this->capture_event(function () {
            connection_tested::create([
                'context' => \context_system::instance(),
                'other'   => ['success' => true, 'http_code' => 200],
            ])->trigger();
        });

        $this->assertSame('r', $event->crud);
        $this->assertStringContainsString('succeeded', $event->get_description());
    }
}
