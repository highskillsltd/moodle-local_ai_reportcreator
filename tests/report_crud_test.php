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
 * DB integration tests for the local_ai_reportcreator_reports table.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_reportcreator;

/**
 * Tests the report record lifecycle (insert, read, delete).
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_crud_test extends advanced_testcase {
    /** @var string Table name used across tests. */
    public const TABLE = 'local_ai_reportcreator_reports';

    /**
     * Build a minimal valid report record object.
     *
     * @return stdClass
     */
    private function make_record(): stdClass {
        $record                  = new stdClass();
        $record->userid          = 2;
        $record->name            = 'Test report';
        $record->nl_request      = 'Show me all users';
        $record->template_type   = 'report';
        $record->sql_query       = 'SELECT id, username FROM {user} LIMIT 10';
        $record->template_html   = '<table>{{ROWS}}</table>';
        $record->semantics_json  = '{}';
        $record->embed_token     = str_pad('abc', 40, '0');
        $record->tokens_prompt   = 100;
        $record->tokens_completion = 50;
        $record->tokens_total    = 150;
        $record->generation_ms   = 1234;
        $record->timecreated     = time();
        $record->timemodified    = time();
        return $record;
    }

    /**
     * Inserting a record returns a positive integer id.
     */
    public function test_insert_returns_id(): void {
        global $DB;
        $this->resetAfterTest();

        $id = $DB->insert_record(self::TABLE, $this->make_record());

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
    }

    /**
     * A record can be read back by id with all fields intact.
     */
    public function test_read_back_matches_inserted_data(): void {
        global $DB;
        $this->resetAfterTest();

        $record = $this->make_record();
        $id     = $DB->insert_record(self::TABLE, $record);
        $row    = $DB->get_record(self::TABLE, ['id' => $id], '*', MUST_EXIST);

        $this->assertSame($record->name, $row->name);
        $this->assertSame($record->nl_request, $row->nl_request);
        $this->assertSame($record->template_type, $row->template_type);
        $this->assertSame($record->sql_query, $row->sql_query);
        $this->assertSame($record->embed_token, $row->embed_token);
        $this->assertSame($record->tokens_total, (int) $row->tokens_total);
    }

    /**
     * Deleting a record removes it from the database.
     */
    public function test_delete_removes_record(): void {
        global $DB;
        $this->resetAfterTest();

        $id = $DB->insert_record(self::TABLE, $this->make_record());
        $DB->delete_records(self::TABLE, ['id' => $id]);

        $this->assertFalse($DB->record_exists(self::TABLE, ['id' => $id]));
    }

    /**
     * Multiple records can be retrieved ordered by timecreated descending.
     */
    public function test_multiple_records_ordered(): void {
        global $DB;
        $this->resetAfterTest();

        $r1 = $this->make_record();
        $r1->name = 'First';
        $r1->timecreated = 1000;

        $r2 = $this->make_record();
        $r2->name = 'Second';
        $r2->timecreated = 2000;

        $DB->insert_record(self::TABLE, $r1);
        $DB->insert_record(self::TABLE, $r2);

        $rows = array_values($DB->get_records(self::TABLE, null, 'timecreated DESC'));

        $this->assertCount(2, $rows);
        $this->assertSame('Second', $rows[0]->name);
        $this->assertSame('First', $rows[1]->name);
    }
}
