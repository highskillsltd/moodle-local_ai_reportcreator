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
 * English language strings for the AI Report Creator plugin.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Core plugin strings.
$string['pluginname']            = 'AI Report Creator';
$string['manage']                = 'Manage AI reports';
$string['ai_reportcreator:manage']  = 'Generate AI reports';
$string['role:ai_reportcreator']      = 'AI Report creator';
$string['role:ai_reportcreator_desc'] = 'Can generate AI-powered reports. Based on the Manager archetype; assignable at system or category level by a site administrator only.';

// Pages / navigation.
$string['aireports']             = 'AI Reports';
$string['createreport']          = 'Create new report';
$string['reportlist']            = 'My Reports';
$string['back']                  = 'Back';
$string['viewreport']            = 'View Report';

// Form fields.
$string['reportname']            = 'Report name';
$string['nlrequest']             = 'Describe what you want to see';
$string['nlrequest_placeholder'] = 'e.g. Show me the number of active enrollments per course for the last 30 days, course custom fields = isfrontal,isrequired';
$string['nlrequest_help']        = 'Tips for a good prompt:<ul><li>Start with what you want to <strong>see</strong>: a count, list, total, average, etc.</li><li>Specify a <strong>time range</strong> if relevant — e.g. <em>for the last 30 days</em>.</li><li>Add <strong>course custom fields</strong> by writing: <em>course custom fields = isfrontal,isrequired</em>.</li><li>Add <strong>user custom fields</strong> by writing: <em>user info fields = department,ouid,ouname,managerid</em>.</li><li><strong>Example:</strong> <em>Show me the number of active enrollments per course for the last 30 days, course custom fields = isfrontal,isrequired</em></li></ul>';
$string['templatetype']          = 'Output type';
$string['generate']              = 'Generate';

// Template type options.
$string['type_report']           = 'Report (table)';
$string['type_dashboard']        = 'Dashboard (stat cards + table)';
$string['type_bar']              = 'Chart — Bar';
$string['type_line']             = 'Chart — Line';
$string['type_pie']              = 'Chart — Pie';
$string['type_doughnut']         = 'Chart — Doughnut';
$string['type_radar']            = 'Chart — Radar';

// Actions.
$string['editreport']            = 'Edit';
$string['exportcsv']             = 'Export CSV';
$string['viewsql']               = 'View SQL';
$string['viewsqlfor']            = 'SQL Query — {$a}';
$string['deletereport']          = 'Delete';
$string['embed']                 = 'Embed';

// Edit page.
$string['editreporttitle']       = 'Edit Report';
$string['savereport']            = 'Save changes';
$string['reportupdated']         = 'Report updated successfully.';

// Table column headers.
$string['col_name']              = 'Name';
$string['col_type']              = 'Type';
$string['col_created']           = 'Created';
$string['col_actions']           = 'Actions';

// Messages.
$string['reportdeleted']         = 'Report deleted successfully.';
$string['confirmdelete']         = 'Are you sure you want to delete this report?';
$string['noreports']             = 'No reports yet. Create your first report.';

// Error strings.
$string['sqlerror']              = 'The SQL query returned an error: {$a}';
$string['apierror']              = 'The AI middleware returned an error: {$a}';
$string['sqlreadonlyerror']      = 'The SQL query contains write or DDL statements and cannot be executed for safety reasons.';
$string['curlerror']             = 'Could not reach the AI middleware: {$a}';
$string['api_not_configured']    = 'The AI middleware is not configured. Please contact your site administrator.';
$string['savingerror']           = 'The report was generated but could not be saved: {$a}';
$string['errorheading']          = 'Error';

// Admin settings.
$string['middlewareurl']         = 'Middleware endpoint URL';
$string['middlewareurl_desc']    = 'Full URL including the tenant UUID path segment. Format: https://your-host/api/v1/{tenant-uuid}/report-creator — e.g. https://api.example.com/api/v1/550e8400-e29b-41d4-a716-446655440000/report-creator';
$string['apikey']                = 'API key (Bearer token)';
$string['apikey_desc']           = '64-character hex API key shown in the middleware admin panel after creating or regenerating the tenant.';
$string['moodleversion']         = 'Moodle version sent to middleware';
$string['streamtimeout']         = 'Stream timeout (seconds)';
$string['streamtimeout_desc']    = 'Maximum time to wait for the middleware to finish streaming a report before giving up. Minimum 30 seconds.';

// Test connection.
$string['testconnection']        = 'Test Connection';
$string['testconnection_success'] = 'Connection successful';
$string['testconnection_fail']   = 'Connection failed';

// Embed.
$string['embedcode']             = 'Embed this report';
$string['embedinstructions']     = 'Copy the code below and paste it into any webpage to embed this report. Viewers must be logged in to this site to see it.';
$string['copycode']              = 'Copy';

// AI progress / stats.
$string['generating']            = 'Calling AI middleware...';
$string['progress_calling']      = 'Calling AI middleware…';
$string['progress_processing']   = 'Processing your request…';
$string['progress_sql']          = 'Generating SQL query…';
$string['progress_template']     = 'Building the report template…';
$string['progress_almost']       = 'Almost there…';
$string['aistats']               = 'AI Generation Stats';
$string['tokenprompt']           = 'Prompt tokens';
$string['tokencompletion']       = 'Completion tokens';
$string['tokentotal']            = 'Total tokens';
$string['generationtime']        = 'Time taken';
$string['agent_sql']             = 'SQL Agent';
$string['agent_template']        = 'Template Agent';
