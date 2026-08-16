// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * AMD module for local_ai_reportcreator's admin settings "Test Connection" button.
 *
 * Reads the unsaved middleware URL / API key field values from the
 * settings page and POSTs them to testconnection.php.
 *
 * @module     local_ai_reportcreator/testconnection
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/str'], function(Str) {

    'use strict';

    /** @type {Object} Config injected by setting_testconnection.php via js_call_amd. */
    var cfg = {};

    /**
     * Wire the test-connection button using the given resolved strings.
     *
     * @param {Object} strings Resolved strings.
     */
    function wireButton(strings) {
        var btn = document.getElementById('local-ai-testconn-btn');
        if (!btn) {
            return;
        }

        btn.addEventListener('click', function() {
            var result = document.getElementById('local-ai-testconn-result');
            var urlField = document.querySelector('[name="s_local_ai_reportcreator_middleware_url"]');
            var pwdField = document.querySelector('[name="s_local_ai_reportcreator_api_key"]');
            var url = urlField ? urlField.value : '';
            var pwd = pwdField ? pwdField.value : '';

            btn.disabled = true;
            result.className = 'align-middle ms-2 text-muted';
            result.textContent = strings.testingconnection;

            var body = new URLSearchParams();
            body.append('sesskey', cfg.sesskey);
            body.append('middleware_url', url);
            body.append('api_password', pwd);

            fetch(cfg.testurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: body.toString(),
            }).then(function(r) {
                return r.json();
            }).then(function(data) {
                btn.disabled = false;
                if (data.ok) {
                    result.textContent = strings.testconnectionSuccess;
                    result.className = 'align-middle ms-2 text-success fw-bold';
                } else {
                    result.textContent = strings.testconnectionFail + ': '
                        + (data.error || strings.httpstatuslabel + ' ' + data.http_code);
                    result.className = 'align-middle ms-2 text-danger';
                }
                return null;
            }).catch(function(e) {
                btn.disabled = false;
                result.textContent = strings.errorheading + ': ' + e.message;
                result.className = 'align-middle ms-2 text-danger';
            });
        });
    }

    return {
        /**
         * Initialise the "Test Connection" button.
         *
         * Called by classes/admin/setting_testconnection.php via js_call_amd.
         *
         * @param {Object} config          Configuration object from the server.
         * @param {string} config.testurl  URL of the testconnection.php AJAX endpoint.
         * @param {string} config.sesskey  Moodle session key.
         */
        init: function(config) {
            cfg = config || {};

            Str.get_strings([
                {key: 'testingconnection', component: 'local_ai_reportcreator'},
                {key: 'testconnection_success', component: 'local_ai_reportcreator'},
                {key: 'testconnection_fail', component: 'local_ai_reportcreator'},
                {key: 'httpstatuslabel', component: 'local_ai_reportcreator'},
                {key: 'errorheading', component: 'local_ai_reportcreator'},
            ]).then(function(s) {
                wireButton({
                    testingconnection: s[0],
                    testconnectionSuccess: s[1],
                    testconnectionFail: s[2],
                    httpstatuslabel: s[3],
                    errorheading: s[4],
                });

                return null;
            }).catch(function() {
                // If string loading fails, fall back to English literals so the button still works.
                wireButton({
                    testingconnection: 'Testing…',
                    testconnectionSuccess: 'Connection successful',
                    testconnectionFail: 'Connection failed',
                    httpstatuslabel: 'HTTP',
                    errorheading: 'Error',
                });
            });
        },
    };
});
