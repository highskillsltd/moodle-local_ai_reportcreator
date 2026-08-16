// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * AMD module for local_ai_reportcreator.
 *
 * Intercepts the create-report form submit, streams the middleware's SSE
 * response, updates a progress panel live, then saves the finished report
 * via stream.php?action=save and redirects to the view page.
 *
 * @module     local_ai_reportcreator/create
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/str'], function (Str) {

    'use strict';

    /** @type {Object} Config injected by create.php via js_call_amd. */
    var cfg = {};

    /** @type {Object} Language strings, resolved before the form is wired up. */
    var strings = {};

    // ── Helpers ────────────────────────────────────────────────────────────

    /**
     * Format elapsed milliseconds as a seconds string.
     *
     * @param {number} ms Elapsed time in milliseconds.
     * @returns {string} Formatted string (e.g. "3.5s").
     */
    function formatElapsed(ms)
    {
        return (ms / 1000).toFixed(1) + 's';
    }

    /**
     * Set a progress table row to the running state (spinner badge).
     *
     * @param {string} rowId The DOM id of the table row element.
     */
    function rowSetRunning(rowId)
    {
        var row = document.getElementById(rowId);
        if (!row) {
            return;
        }
        row.querySelector('.status-badge').innerHTML =
            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' + strings.running;
        row.querySelector('.detail-cell').textContent = '';
    }

    /**
     * Set a progress table row to the done state (success badge).
     *
     * @param {string} rowId  The DOM id of the table row element.
     * @param {string} detail Optional detail text to display.
     */
    function rowSetDone(rowId, detail)
    {
        var row = document.getElementById(rowId);
        if (!row) {
            return;
        }
        row.querySelector('.status-badge').innerHTML = '<span class="badge bg-success">' + strings.done + '</span>';
        row.querySelector('.detail-cell').textContent = detail || '';
    }

    /**
     * Show the error panel with a message and re-enable the submit button.
     *
     * @param {string} message Error text to display.
     */
    function showError(message)
    {
        document.getElementById('error-message').textContent = message || strings.unknownerror;
        document.getElementById('error-panel').classList.remove('d-none');
        var btn = document.getElementById('id_submitbutton');
        if (btn) {
            btn.disabled = false;
        }
    }

    /**
     * Persist the generated report and redirect to its view page.
     *
     * @param {number} tokensTotal  Summed tokens across both agent stages.
     * @param {number} generationMs Wall-clock generation time in milliseconds.
     */
    function saveReport(tokensTotal, generationMs)
    {
        var body = new URLSearchParams();
        body.append('sesskey', cfg.sesskey);
        body.append('tokens_total', tokensTotal);
        body.append('generation_ms', generationMs);

        fetch(cfg.saveUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: body.toString(),
        }).then(function (r) {
            return r.json();
        }).then(function (data) {
            if (data && data.viewurl) {
                window.location.href = data.viewurl;
            } else {
                showError((data && data.error) || strings.savefailed);
            }
        }).catch(function (e) {
            showError(String(e));
        });
    }

    /**
     * Dispatch a single parsed SSE event to the appropriate UI update.
     *
     * @param {Object} msg   Parsed JSON event from the SSE stream.
     * @param {Object} state Mutable state shared across events for this run.
     */
    function handleEvent(msg, state)
    {
        var rowId = msg.stage === 'template' ? 'row-template' : 'row-sql';

        switch (msg.event) {
            case 'agent_start':
                rowSetRunning(rowId);
                break;

            case 'agent_done': {
                var detail = formatElapsed(msg.elapsed_ms || 0) + ' · ' + (msg.tokens || 0) + ' tokens';
                rowSetDone(rowId, detail);
                state.tokensTotal += (msg.tokens || 0);
                break;
            }

            case 'error':
                state.errored = true;
                showError(msg.message);
                break;

            case 'done':
                if (!state.errored) {
                    saveReport(state.tokensTotal, Date.now() - state.startedAt);
                }
                break;
        }
    }

    // ── SSE stream consumer ────────────────────────────────────────────────

    /**
     * Submit the report request and consume the SSE stream.
     *
     * @param {string} name         Report name.
     * @param {string} nlRequest    Natural-language report request.
     * @param {string} templateType Output template type.
     */
    function startGeneration(name, nlRequest, templateType)
    {
        document.getElementById('error-panel').classList.add('d-none');

        ['row-sql', 'row-template'].forEach(function (id) {
            var row = document.getElementById(id);
            if (row) {
                row.querySelector('.status-badge').innerHTML =
                    '<span class="spinner-grow spinner-grow-sm text-secondary" role="status"></span>';
                row.querySelector('.detail-cell').textContent = '';
            }
        });

        var btn = document.getElementById('id_submitbutton');
        if (btn) {
            btn.disabled = true;
        }

        document.getElementById('form-container').classList.add('d-none');
        document.getElementById('progress-panel').classList.remove('d-none');

        var state = {tokensTotal: 0, startedAt: Date.now(), errored: false};

        var formData = new FormData();
        formData.append('sesskey', cfg.sesskey);
        formData.append('name', name);
        formData.append('nl_request', nlRequest);
        formData.append('template_type', templateType);

        fetch(cfg.streamUrl, {
            method: 'POST',
            body: formData,
        }).then(function (response) {
            if (!response.ok) {
                throw new Error(strings.httpstatuslabel + ' ' + response.status);
            }

            var reader  = response.body.getReader();
            var decoder = new TextDecoder();
            var buffer  = '';

            /**
             * Read one chunk from the stream and schedule the next read.
             *
             * @returns {Promise} Resolves when the stream is exhausted.
             */
            function pump()
            {
                return reader.read().then(function (result) {
                    if (result.done) {
                        return;
                    }

                    buffer += decoder.decode(result.value, {stream: true});

                    var blocks = buffer.split('\n\n');
                    buffer = blocks.pop();

                    blocks.forEach(function (block) {
                        var dataLine = null;
                        block.split('\n').forEach(function (line) {
                            if (line.indexOf('data: ') === 0 && dataLine === null) {
                                dataLine = line.slice(6);
                            }
                        });
                        if (dataLine === null) {
                            return;
                        }
                        try {
                            handleEvent(JSON.parse(dataLine), state);
                        } catch (e) {
                            // Ignore malformed JSON lines.
                        }
                    });

                    return pump();
                });
            }

            return pump();
        }).catch(function (err) {
            showError(String(err));
        });
    }

    // ── Public API ─────────────────────────────────────────────────────────

    return {
        /**
         * Initialise the report generator UI.
         *
         * Called by create.php via js_call_amd.
         *
         * @param {Object} config           Configuration object from the server.
         * @param {string} config.sesskey   Moodle session key.
         * @param {string} config.streamUrl URL of the SSE streaming endpoint.
         * @param {string} config.saveUrl   URL of the save endpoint.
         */
        init: function (config) {
            cfg = config || {};

            var formContainer = document.getElementById('form-container');
            var form = formContainer ? formContainer.querySelector('form') : null;
            if (!form) {
                return;
            }

            var wireForm = function () {
                form.addEventListener('submit', function (e) {
                    if (e.submitter && e.submitter.name === 'cancel') {
                        return;
                    }
                    e.preventDefault();

                    var nameField    = document.getElementById('id_name');
                    var requestField = document.getElementById('id_nl_request');
                    var typeField    = document.getElementById('id_template_type');

                    var name         = nameField ? nameField.value.trim() : '';
                    var nlRequest    = requestField ? requestField.value.trim() : '';
                    var templateType = typeField ? typeField.value : 'report';

                    if (!name || !nlRequest) {
                        return;
                    }

                    startGeneration(name, nlRequest, templateType);
                });
            };

            Str.get_strings([
                {key: 'running', component: 'local_ai_reportcreator'},
                {key: 'done', component: 'local_ai_reportcreator'},
                {key: 'unknownerror', component: 'local_ai_reportcreator'},
                {key: 'savefailed', component: 'local_ai_reportcreator'},
                {key: 'httpstatuslabel', component: 'local_ai_reportcreator'},
            ]).then(function (s) {
                strings.running         = s[0];
                strings.done            = s[1];
                strings.unknownerror    = s[2];
                strings.savefailed      = s[3];
                strings.httpstatuslabel = s[4];

                wireForm();

                return null;
            }).catch(function () {
                // If string loading fails, fall back to English literals so the form still works.
                strings.running         = 'Running…';
                strings.done            = 'Done';
                strings.unknownerror    = 'Unknown error';
                strings.savefailed      = 'Failed to save the report.';
                strings.httpstatuslabel = 'HTTP';

                wireForm();
            });
        },

        /**
         * Handle a single parsed SSE event. Exposed for testing.
         *
         * @param {Object} msg   Parsed JSON event from the SSE stream.
         * @param {Object} state Mutable state shared across events for this run.
         */
        _handleEvent: handleEvent,
    };
});
