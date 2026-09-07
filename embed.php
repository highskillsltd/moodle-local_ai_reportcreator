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
 * Access requires a logged-in Moodle session and the local/ai_reportcreator:view
 * capability (system context).
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();
$context = context_system::instance();
require_capability('local/ai_reportcreator:view', $context);

$id     = required_param('id', PARAM_INT);
$record = $DB->get_record('local_ai_reportcreator_rpts', ['id' => $id], '*', MUST_EXIST);

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
    $rows = local_ai_reportcreator_run_report_sql($record->sql_query);
} catch (Exception $e) {
    echo '<!DOCTYPE html><html><body><p style="color:red;font-family:sans-serif;">'
        . htmlspecialchars(get_string('sqlerror', 'local_ai_reportcreator', $e->getMessage()), ENT_QUOTES)
        . '</p></body></html>';
    exit;
}

$semantics = json_decode($record->semantics_json, true) ?: [];

// Build rendered output — shared with view.php.
$output = local_ai_reportcreator_render_report_output($record, $rows, $semantics, $OUTPUT);

// Output bare HTML — allow embedding from any origin.
header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: ALLOWALL');
header('Content-Security-Policy: frame-ancestors *');

echo $OUTPUT->render_from_template('local_ai_reportcreator/embed_page', [
    'title'  => $record->name,
    'output' => $output,
]);
