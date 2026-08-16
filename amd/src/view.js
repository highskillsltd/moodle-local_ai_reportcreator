// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * AMD module for local_ai_reportcreator view.php.
 *
 * Wires up the "Copy" button in the embed panel's clipboard handler.
 *
 * @module     local_ai_reportcreator/view
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/str'], function(Str) {

    'use strict';

    /**
     * Wire the embed-panel copy button using the given resolved strings.
     *
     * @param {Object} strings Resolved strings {copied, copycode}.
     */
    function wireCopyButton(strings) {
        var btn = document.getElementById('embed-copy-btn');
        if (!btn) {
            return;
        }

        btn.addEventListener('click', function() {
            var ta = document.getElementById('embed-textarea');
            if (navigator.clipboard && ta) {
                navigator.clipboard.writeText(ta.value).then(function() {
                    btn.textContent = strings.copied;
                    setTimeout(function() {
                        btn.textContent = btn.dataset.orig || strings.copycode;
                    }, 2000);
                    return null;
                }).catch(function() {
                    // Clipboard write failed (e.g. permission denied) — leave the button as-is.
                });
            }
        });

        btn.dataset.orig = btn.textContent;
    }

    return {
        /**
         * Initialise the view page's embed-copy behaviour.
         *
         * Called by view.php via js_call_amd.
         */
        init: function() {
            Str.get_strings([
                {key: 'copied', component: 'local_ai_reportcreator'},
                {key: 'copycode', component: 'local_ai_reportcreator'},
            ]).then(function(s) {
                wireCopyButton({copied: s[0], copycode: s[1]});

                return null;
            }).catch(function() {
                // If string loading fails, fall back to English literals so the button still works.
                wireCopyButton({copied: 'Copied!', copycode: 'Copy'});
            });
        },
    };
});
