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
 * Export report data as CSV or Excel for all template types.
 *
 * Always exports the raw SQL result rows regardless of template_type —
 * charts and dashboards share the same underlying data as table reports.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();
$context = context_system::instance();
require_capability('local/ai_reportcreator:manage', $context);

$id     = required_param('id', PARAM_INT);
$format = required_param('format', PARAM_ALPHA); // Csv or excel.
$record = $DB->get_record('local_ai_reportcreator_rpts', ['id' => $id], '*', MUST_EXIST);

if (!local_ai_reportcreator_validate_sql_readonly($record->sql_query)) {
    throw new moodle_exception('sqlreadonlyerror', 'local_ai_reportcreator');
}

$rows      = local_ai_reportcreator_run_report_sql($record->sql_query);
$semantics = json_decode($record->semantics_json, true) ?: [];
$firstrow  = !empty($rows) ? (array) reset($rows) : [];
$keys      = array_keys($firstrow);

// Build key → label map from semantics, fall back to raw column keys.
$labelmap = [];
foreach ($semantics['columns'] ?? [] as $col) {
    $labelmap[$col['key']] = $col['label'];
}
$headers = array_map(fn($k) => $labelmap[$k] ?? $k, $keys);

$safename = clean_filename($record->name);

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $safename . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens the file correctly.
    fputcsv($out, $headers, ',', '"', '\\');
    foreach ($rows as $row) {
        fputcsv($out, array_values((array) $row), ',', '"', '\\');
    }
    fclose($out);
}
exit;
