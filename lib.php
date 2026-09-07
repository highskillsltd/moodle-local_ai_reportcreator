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

/**
 * Build the Mustache context for the local_ai_reportcreator/report_rows template.
 *
 * Each result row becomes one entry with a `cells` list holding the string value
 * of every configured column, in column order. Missing values become ''. Values
 * are only cast here — HTML escaping is left to the Mustache engine so it happens
 * once, consistently, at render time.
 *
 * @param iterable $rows    Result rows (arrays or stdClass objects).
 * @param array    $columns Column definitions from semantics; each needs a 'key'.
 * @return array{rows: array<int, array{cells: string[]}>}
 */
function local_ai_reportcreator_build_row_context(iterable $rows, array $columns): array {
    $context = ['rows' => []];
    foreach ($rows as $row) {
        $row   = (array) $row;
        $cells = [];
        foreach ($columns as $col) {
            $cells[] = (string) ($row[$col['key']] ?? '');
        }
        $context['rows'][] = ['cells' => $cells];
    }
    return $context;
}

/**
 * Render a stored report record into its final HTML output.
 *
 * Shared by view.php and embed.php so the placeholder substitution logic lives
 * in one place. SQL-error handling is deliberately left to the caller, as the
 * two pages present errors differently.
 *
 * Behaviour per template type:
 *  - chart types (bar/line/pie/doughnut/radar): prepend a
 *    `<script>window.__DATA__ = […];</script>` block to the stored template;
 *  - report: replace `{{ROWS}}` with the rendered report_rows fragment;
 *  - dashboard: as report, then replace each `{{STAT_<key>}}` with the matching
 *    first-row value (escaped), or an em dash when absent;
 *  - anything else: the stored template unchanged.
 *
 * @param \stdClass $record    Report record (needs template_type, template_html).
 * @param array     $rows      Result rows from the report SQL.
 * @param array     $semantics Decoded semantics_json (columns, highlight_columns, …).
 * @param \renderer_base|\core\output\bootstrap_renderer $output Renderer for the
 *      report_rows template — typically the global $OUTPUT, which is still the lazy
 *      bootstrap_renderer proxy when view.php/embed.php call this (it forwards
 *      render_from_template() to the real renderer), so no type declaration is used.
 * @return string Rendered HTML.
 */
function local_ai_reportcreator_render_report_output(
    \stdClass $record,
    array $rows,
    array $semantics,
    $output
): string {
    $templatetype = $record->template_type;

    if (in_array($templatetype, ['bar', 'line', 'pie', 'doughnut', 'radar'], true)) {
        $data = array_values(array_map(fn($r) => (array) $r, $rows));
        return '<script>window.__DATA__ = ' . json_encode($data) . ';</script>' . "\n"
            . $record->template_html;
    }

    if ($templatetype === 'report' || $templatetype === 'dashboard') {
        $columns  = $semantics['columns'] ?? [];
        $rowshtml = $output->render_from_template(
            'local_ai_reportcreator/report_rows',
            local_ai_reportcreator_build_row_context($rows, $columns)
        );
        $rendered = str_replace('{{ROWS}}', $rowshtml, $record->template_html);

        if ($templatetype === 'dashboard') {
            $firstrow = !empty($rows) ? (array) reset($rows) : [];
            foreach ($semantics['highlight_columns'] ?? [] as $colkey) {
                $val      = htmlspecialchars((string) ($firstrow[$colkey] ?? '—'), ENT_QUOTES);
                $rendered = str_replace('{{STAT_' . $colkey . '}}', $val, $rendered);
            }
        }

        return $rendered;
    }

    return $record->template_html;
}

/**
 * Derive the middleware "ping" URL from a configured endpoint URL.
 *
 * Replaces the last path segment (the task code, e.g. "report-creator") with
 * "ping", matching the middleware's GET /api/{version}/{tenant}/ping route.
 * A trailing slash on the input is ignored.
 *
 * @param string $middlewareurl The configured middleware endpoint URL.
 * @return string The corresponding ping URL.
 */
function local_ai_reportcreator_derive_ping_url(string $middlewareurl): string {
    $trimmedurl = rtrim($middlewareurl, '/');
    $lastslash  = strrpos($trimmedurl, '/');
    if ($lastslash !== false) {
        return substr($trimmedurl, 0, $lastslash) . '/ping';
    }
    return $trimmedurl . '/ping';
}

/**
 * Trigger a report_generation_failed event with structured metadata only.
 *
 * The raw middleware/cURL error text is deliberately not passed through, as it
 * can contain the endpoint URL or a snippet of the response body.
 *
 * @param \context $context      The context the event is raised in.
 * @param string   $templatetype The output type that was requested (report/dashboard/bar/...).
 * @param string   $errortype    A short error identifier (moodle_exception code, class name,
 *                                or 'not_configured' / 'empty_request').
 * @param int      $httpcode     The HTTP status returned by the middleware, or 0 if unknown.
 * @return void
 */
function local_ai_reportcreator_log_generation_failed(
    \context $context,
    string $templatetype,
    string $errortype,
    int $httpcode
): void {
    \local_ai_reportcreator\event\report_generation_failed::create([
        'context' => $context,
        'other'   => [
            'template_type' => $templatetype,
            'errortype'     => $errortype,
            'http_code'     => $httpcode,
        ],
    ])->trigger();
}
