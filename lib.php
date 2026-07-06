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

defined('MOODLE_INTERNAL') || die();

/**
 * Validates that a SQL string is read-only (SELECT only).
 *
 * Strips block and line comments before checking for write or DDL keywords,
 * so that keywords appearing only inside comments do not cause rejection.
 *
 * @param string $sql The SQL string to validate.
 * @return bool True if the SQL is read-only, false if it contains write/DDL keywords.
 */
function local_ai_reportcreator_validate_sql_readonly(string $sql): bool
{
    // Strip block comments /* ... */
    $clean = preg_replace('/\/\*.*?\*\//s', ' ', $sql);
    // Strip line comments -- ...
    $clean = preg_replace('/--[^\n]*/', ' ', $clean);
    // Reject any write or DDL keyword found as a whole word
    if (preg_match(
        '/\b(INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|TRUNCATE|REPLACE|EXEC|EXECUTE|CALL|GRANT|REVOKE|LOCK|MERGE)\b/i',
        $clean
    )) {
        return false;
    }
    return true;
}
