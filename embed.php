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
 * Embed page — outputs a bare HTML document (no Moodle chrome) suitable for iframes.
 *
 * Access requires a logged-in Moodle session.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();

$id     = required_param('id', PARAM_INT);
$record = $DB->get_record('local_ai_reportcreator_reports', ['id' => $id], '*', MUST_EXIST);

// Safety: only execute read-only SQL.
if (!local_ai_reportcreator_validate_sql_readonly($record->sql_query)) {
    http_response_code(400);
    echo '<!DOCTYPE html><html><body><p style="color:red;font-family:sans-serif;">'
        . get_string('sqlreadonlyerror', 'local_ai_reportcreator')
        . '</p></body></html>';
    exit;
}

$rows = [];
try {
    $rows = $DB->get_records_sql($record->sql_query);
} catch (Exception $e) {
    echo '<!DOCTYPE html><html><body><p style="color:red;font-family:sans-serif;">'
        . htmlspecialchars(get_string('sqlerror', 'local_ai_reportcreator', $e->getMessage()), ENT_QUOTES)
        . '</p></body></html>';
    exit;
}

$semantics    = json_decode($record->semantics_json, true) ?: [];
$templatetype = $record->template_type;

// Build rendered output — same logic as view.php.
if (in_array($templatetype, ['bar', 'line', 'pie', 'doughnut', 'radar'], true)) {
    $data   = array_values(array_map(fn($r) => (array) $r, $rows));
    $output = '<script>window.__DATA__ = ' . json_encode($data) . ';</script>' . "\n"
            . $record->template_html;
} else if ($templatetype === 'report') {
    $columns = $semantics['columns'] ?? [];
    $tbody   = '';
    foreach ($rows as $row) {
        $row    = (array) $row;
        $tbody .= '<tr>';
        foreach ($columns as $col) {
            $val    = htmlspecialchars((string) ($row[$col['key']] ?? ''), ENT_QUOTES);
            $tbody .= "<td>{$val}</td>";
        }
        $tbody .= '</tr>';
    }
    $output = str_replace('{{ROWS}}', $tbody, $record->template_html);
} else if ($templatetype === 'dashboard') {
    $columns   = $semantics['columns'] ?? [];
    $tbody     = '';
    foreach ($rows as $row) {
        $row    = (array) $row;
        $tbody .= '<tr>';
        foreach ($columns as $col) {
            $val    = htmlspecialchars((string) ($row[$col['key']] ?? ''), ENT_QUOTES);
            $tbody .= "<td>{$val}</td>";
        }
        $tbody .= '</tr>';
    }
    $firstrow = !empty($rows) ? (array) reset($rows) : [];
    $output   = str_replace('{{ROWS}}', $tbody, $record->template_html);
    foreach ($semantics['highlight_columns'] ?? [] as $colkey) {
        $val    = htmlspecialchars((string) ($firstrow[$colkey] ?? '—'), ENT_QUOTES);
        $output = str_replace('{{STAT_' . $colkey . '}}', $val, $output);
    }
} else {
    $output = $record->template_html;
}

// Output bare HTML — allow embedding from any origin.
header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: ALLOWALL');
header('Content-Security-Policy: frame-ancestors *');

echo '<!DOCTYPE html>';
echo '<html lang="en"><head>';
echo '<meta charset="UTF-8">';
echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
echo '<title>' . htmlspecialchars($record->name, ENT_QUOTES) . '</title>';
echo '</head><body style="margin:0;padding:0;">';
echo $output;
echo '<script>';
echo '(function(){';
echo 'function laiH(){';
echo 'var h=Math.max(document.body.scrollHeight,document.documentElement.scrollHeight);';
echo 'window.parent.postMessage({type:"lai-resize",height:h},"*");';
echo '}';
echo 'window.addEventListener("load",function(){laiH();setTimeout(laiH,500);setTimeout(laiH,1500);});';
echo '})();';
echo '</script>';
echo '</body></html>';
