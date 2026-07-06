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
 * Custom admin setting class for the Test Connection button.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_ai_reportcreator\admin;
/**
 * Renders a "Test Connection" button in the admin settings page.
 *
 * Display-only setting — does not store any value. The button fires an AJAX
 * request to testconnection.php using the unsaved field values on the page.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// phpcs:ignore Squiz.Classes.ValidClassName.NotCamelCaps
class setting_testconnection extends \admin_setting
{

    /**
     * Constructor — registers the setting with a placeholder name (no stored value).
     */
    public function __construct()
    {
        parent::__construct(
            'local_ai_reportcreator/testconnection_placeholder',
            get_string('testconnection', 'local_ai_reportcreator'),
            '',
            ''
        );
    }

    /**
     * Returns a truthy value so Moodle renders the element.
     *
     * @return bool
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function get_setting()
    {
        return true;
    }

    /**
     * No value to persist — always returns an empty string (no error).
     *
     * @param mixed $data Form data (unused).
     * @return string Empty string indicating success.
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function write_setting($data)
    {
        return '';
    }

    /**
     * This setting is never matched by the admin search.
     *
     * @param string $query Search query (unused).
     * @return bool Always false.
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function is_related($query)
    {
        return false;
    }

    /**
     * Renders the Test Connection button and its inline AJAX script.
     *
     * @param mixed  $data  Current setting value (unused).
     * @param string $query Admin search query (unused).
     * @return string HTML output.
     */
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    public function output_html($data, $query = '')
    {
        global $CFG;

        $testurl     = $CFG->wwwroot . '/local/ai_reportcreator/testconnection.php';
        $sesskey     = sesskey();
        $btn_label   = get_string('testconnection', 'local_ai_reportcreator');
        $msg_ok      = addslashes(get_string('testconnection_success', 'local_ai_reportcreator'));
        $msg_fail    = addslashes(get_string('testconnection_fail', 'local_ai_reportcreator'));

        $html  = '<div class="form-item row">';
        $html .= '<div class="form-label col-sm-4 col-form-label d-flex pb-0 pr-md-0">';
        $html .= htmlspecialchars($btn_label, ENT_QUOTES);
        $html .= '</div>';
        $html .= '<div class="form-setting col-sm-8">';
        $html .= '<button type="button" class="btn btn-secondary" id="local-ai-testconn-btn">';
        $html .= htmlspecialchars($btn_label, ENT_QUOTES);
        $html .= '</button>';
        $html .= ' <span id="local-ai-testconn-result" class="align-middle ms-2"></span>';
        $html .= '</div></div>';

        $html .= <<<JS
<script>
(function () {
    var btn = document.getElementById('local-ai-testconn-btn');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var result = document.getElementById('local-ai-testconn-result');
        var urlField = document.querySelector('[name="s_local_ai_reportcreator_middleware_url"]');
        var pwdField = document.querySelector('[name="s_local_ai_reportcreator_api_key"]');
        var url = urlField ? urlField.value : '';
        var pwd = pwdField ? pwdField.value : '';

        btn.disabled = true;
        result.className = 'align-middle ms-2 text-muted';
        result.textContent = 'Testing…';

        var body = new URLSearchParams();
        body.append('sesskey', '{$sesskey}');
        body.append('middleware_url', url);
        body.append('api_password', pwd);

        fetch('{$testurl}', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: body.toString()
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.disabled = false;
            if (data.ok) {
                result.textContent = '{$msg_ok}';
                result.className = 'align-middle ms-2 text-success fw-bold';
            } else {
                result.textContent = '{$msg_fail}: ' + (data.error || 'HTTP ' + data.http_code);
                result.className = 'align-middle ms-2 text-danger';
            }
        })
        .catch(function (e) {
            btn.disabled = false;
            result.textContent = 'Error: ' + e.message;
            result.className = 'align-middle ms-2 text-danger';
        });
    });
})();
</script>
JS;

        return $html;
    }
}
