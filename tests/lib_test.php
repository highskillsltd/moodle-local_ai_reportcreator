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
 * Unit tests for local_ai_reportcreator_validate_sql_readonly().
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_reportcreator;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Tests for the SQL read-only validation function.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers ::local_ai_reportcreator_validate_sql_readonly
 */
class lib_test extends advanced_testcase {
    /**
     * Plain SELECT statements are allowed.
     */
    public function test_select_is_allowed(): void {
        $this->assertTrue(local_ai_reportcreator_validate_sql_readonly(
            'SELECT id, name FROM mdl_user WHERE deleted = 0'
        ));
    }

    /**
     * SELECT with a subquery is allowed.
     */
    public function test_select_with_subquery_is_allowed(): void {
        $this->assertTrue(local_ai_reportcreator_validate_sql_readonly(
            'SELECT * FROM mdl_course WHERE id IN (SELECT courseid FROM mdl_enrol)'
        ));
    }

    /**
     * Each write/DDL keyword must be rejected.
     *
     * @dataProvider write_keyword_provider
     * @param string $sql SQL statement containing a write keyword.
     */
    public function test_write_keywords_are_rejected(string $sql): void {
        $this->assertFalse(local_ai_reportcreator_validate_sql_readonly($sql));
    }

    /**
     * Data provider: one statement per write/DDL keyword.
     *
     * @return array[]
     */
    public static function write_keyword_provider(): array {
        return [
            'INSERT'   => ['INSERT INTO mdl_user (name) VALUES ("x")'],
            'UPDATE'   => ['UPDATE mdl_user SET deleted = 1'],
            'DELETE'   => ['DELETE FROM mdl_user WHERE id = 1'],
            'DROP'     => ['DROP TABLE mdl_user'],
            'ALTER'    => ['ALTER TABLE mdl_user ADD COLUMN foo INT'],
            'CREATE'   => ['CREATE TABLE foo (id INT)'],
            'TRUNCATE' => ['TRUNCATE TABLE mdl_user'],
            'REPLACE'  => ['REPLACE INTO mdl_user (id) VALUES (1)'],
            'EXEC'     => ['EXEC sp_help'],
            'EXECUTE'  => ['EXECUTE sp_help'],
            'CALL'     => ['CALL my_proc()'],
            'GRANT'    => ['GRANT SELECT ON mdl_user TO someuser'],
            'REVOKE'   => ['REVOKE SELECT ON mdl_user FROM someuser'],
            'LOCK'     => ['LOCK TABLE mdl_user IN SHARE MODE'],
            'MERGE'    => ['MERGE INTO mdl_user USING src ON (1=1) WHEN MATCHED THEN UPDATE SET x=1'],
            'lowercase_delete' => ['delete from mdl_user where id = 1'],
            'mixed_case_drop'  => ['DrOp TaBle mdl_user'],
        ];
    }

    /**
     * Write keywords inside block comments are stripped and must not trigger rejection.
     */
    public function test_block_comment_with_write_keyword_is_allowed(): void {
        $this->assertTrue(local_ai_reportcreator_validate_sql_readonly(
            "SELECT id FROM mdl_user /* DELETE FROM mdl_user */"
        ));
    }

    /**
     * Write keywords on line comments are stripped and must not trigger rejection.
     */
    public function test_line_comment_with_write_keyword_is_allowed(): void {
        $this->assertTrue(local_ai_reportcreator_validate_sql_readonly(
            "SELECT id FROM mdl_user -- DROP TABLE mdl_user\nWHERE deleted = 0"
        ));
    }

    /**
     * An empty string is considered safe (no write keywords present).
     */
    public function test_empty_string_is_allowed(): void {
        $this->assertTrue(local_ai_reportcreator_validate_sql_readonly(''));
    }
}
