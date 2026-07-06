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
 * AJAX endpoint for the settings page "Test Connection" button.
 *
 * Calls GET /api/{tenant_key}/ping on the middleware and returns JSON:
 * {"ok": true} or {"ok": false, "error": "...", "http_code": N}
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php');

require_login();
require_sesskey();

$context = context_system::instance();
require_capability('local/ai_reportcreator:manage', $context);

$middlewareurl = required_param('middleware_url', PARAM_RAW);
$apipassword   = required_param('api_password', PARAM_RAW);

header('Content-Type: application/json');

if (empty(trim($middlewareurl))) {
    echo json_encode(['ok' => false, 'error' => 'Middleware URL is empty.', 'http_code' => 0]);
    exit;
}

// Derive ping URL: swap /query suffix for /ping (lightweight auth-only endpoint).
$pingurl = preg_replace('#/query$#i', '/ping', rtrim($middlewareurl, '/'));
if ($pingurl === rtrim($middlewareurl, '/')) {
    // No /query suffix found — append /ping as a fallback.
    $pingurl = rtrim($middlewareurl, '/') . '/ping';
}

$curl = new curl();
$curl->setHeader([
    'Authorization: Bearer ' . $apipassword,
]);

$responseraw = $curl->get($pingurl);
$info        = $curl->get_info();
$httpcode    = (int) ($info['http_code'] ?? 0);

if ($httpcode === 200) {
    echo json_encode(['ok' => true, 'http_code' => $httpcode]);
} else {
    $response = json_decode($responseraw, true);
    $error    = $response['error'] ?? $response['detail'] ?? substr($responseraw, 0, 300);
    if (empty($error)) {
        $error = 'HTTP ' . $httpcode;
    }
    echo json_encode(['ok' => false, 'error' => $error, 'http_code' => $httpcode]);
}
