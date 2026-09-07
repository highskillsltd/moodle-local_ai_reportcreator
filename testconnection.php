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
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/filelib.php');

require_login();
require_sesskey();

$context = context_system::instance();
require_capability('local/ai_reportcreator:manage', $context);

// The middleware probe below can take several seconds (or hang until timeout)
// on an unreachable host — release the session write lock so the admin's other
// browser tabs are not blocked for the duration.
\core\session\manager::write_close();

$middlewareurl = required_param('middleware_url', PARAM_URL);
$apipassword   = required_param('api_password', PARAM_ALPHANUMEXT);

header('Content-Type: application/json');

if (empty(trim($middlewareurl))) {
    echo json_encode([
        'ok'        => false,
        'error'     => get_string('middlewareurlempty', 'local_ai_reportcreator'),
        'http_code' => 0,
    ]);
    exit;
}

// Derive ping URL from the configured endpoint (task-code segment → "ping").
$trimmedurl = rtrim($middlewareurl, '/');
$pingurl    = local_ai_reportcreator_derive_ping_url($middlewareurl);
$insecure   = stripos($trimmedurl, 'http://') === 0;

$curl = new curl();
$curl->setHeader([
    'Authorization: Bearer ' . $apipassword,
]);

// Moodle's curl wrapper defaults SSL verification off; restore it explicitly and
// only relax it for an admin-configured plain-http:// endpoint. Cap the wait so a
// dead host fails the "test" button quickly rather than after the wrapper default.
$responseraw = $curl->get($pingurl, [], [
    'CURLOPT_SSL_VERIFYPEER' => !$insecure,
    'CURLOPT_SSL_VERIFYHOST' => $insecure ? 0 : 2,
    'CURLOPT_TIMEOUT'        => 10,
    'CURLOPT_FOLLOWLOCATION' => true,
]);
$info        = $curl->get_info();
$httpcode    = (int) ($info['http_code'] ?? 0);

\local_ai_reportcreator\event\connection_tested::create([
    'context' => $context,
    'other'   => ['success' => $httpcode === 200, 'http_code' => $httpcode],
])->trigger();

if ($httpcode === 200) {
    echo json_encode(['ok' => true, 'http_code' => $httpcode]);
} else {
    $response = json_decode($responseraw, true);
    $error    = $response['error'] ?? $response['detail'] ?? substr($responseraw, 0, 300);
    if (empty($error)) {
        $error = get_string('httpstatus', 'local_ai_reportcreator', $httpcode);
    }
    echo json_encode(['ok' => false, 'error' => $error, 'http_code' => $httpcode]);
}
