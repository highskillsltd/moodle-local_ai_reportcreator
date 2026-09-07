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
 * Capability definitions for the AI Report Creator plugin.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // Create, edit, delete and generate reports; view raw SQL; plugin settings.
    'local/ai_reportcreator:manage' => [
        'riskbitmask'  => RISK_PERSONAL | RISK_CONFIG,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => ['manager' => CAP_ALLOW],
    ],

    // View a rendered report and its data (view.php, embed.php). Report output can
    // contain personal data, so administrators may revoke this from Authenticated
    // user to restrict who can open reports; managers keep it either way.
    'local/ai_reportcreator:view' => [
        'riskbitmask'  => RISK_PERSONAL,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => [
            'user'    => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
];
