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
 * Library functions for the AI Report Creator plugin.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Validates that a SQL string is read-only (SELECT only).
 *
 * Strips block and line comments before checking for write or DDL keywords,
 * so that keywords appearing only inside comments do not cause rejection.
 *
 * @param string $sql The SQL string to validate.
 * @return bool True if the SQL is read-only, false if it contains write/DDL keywords.
 */
function local_ai_reportcreator_validate_sql_readonly(string $sql): bool {
    // Strip block comments /* ... */.
    $clean = preg_replace('/\/\*.*?\*\//s', ' ', $sql);

    // Strip line comments -- ...
    $clean = preg_replace('/--[^\n]*/', ' ', $clean);

    // Define patterns to block write/DDL operations.
    $forbiddenpatterns = '/\b(INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|TRUNCATE|REPLACE|' .
                         'EXEC|EXECUTE|CALL|GRANT|REVOKE|LOCK|MERGE)\b/i';

    // Reject any write or DDL keyword found as a whole word.
    if (preg_match($forbiddenpatterns, $clean)) {
        return false;
    }

    return true;
}

/**
 * Execute an AI-generated report SQL query and return all rows as a plain array.
 *
 * Uses get_recordset_sql() rather than get_records_sql(): the latter builds
 * its return value as an array keyed by the first selected column and
 * requires that column to be unique across rows, which AI-generated report
 * queries cannot guarantee (e.g. a plain per-enrollment list legitimately
 * repeats userid). get_recordset_sql() has no such requirement.
 *
 * @param string $sql Read-only SQL query text.
 * @return \stdClass[] Result rows as a plain sequential array.
 */
function local_ai_reportcreator_run_report_sql(string $sql): array {
    global $DB;

    $rows = [];
    $recordset = $DB->get_recordset_sql($sql);
    foreach ($recordset as $row) {
        $rows[] = $row;
    }
    $recordset->close();

    return $rows;
}
