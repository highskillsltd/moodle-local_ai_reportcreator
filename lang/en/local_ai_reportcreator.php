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

$string['agent_sql'] = 'SQL Agent';
$string['agent_template'] = 'Template Agent';
$string['ai_reportcreator:manage'] = 'Generate AI reports';
$string['aireports'] = 'AI Reports';
$string['aistats'] = 'AI Generation Stats';
$string['api_not_configured'] = 'The AI middleware is not configured. Please contact your site administrator.';
$string['apierror'] = 'The AI middleware returned an error: {$a}';
$string['apikey'] = 'API key (Bearer token)';
$string['apikey_desc'] = '64-character hex API key shown in the middleware admin panel after creating or regenerating the tenant.';
$string['back'] = 'Back';
$string['col_actions'] = 'Actions';
$string['col_created'] = 'Created';
$string['col_name'] = 'Name';
$string['col_type'] = 'Type';
$string['confirmdelete'] = 'Are you sure you want to delete this report?';
$string['copied'] = 'Copied!';
$string['copycode'] = 'Copy';
$string['createreport'] = 'Create new report';
$string['curlerror'] = 'Could not reach the AI middleware: {$a}';
$string['deletereport'] = 'Delete';
$string['done'] = 'Done';
$string['editreport'] = 'Edit';
$string['editreporttitle'] = 'Edit Report';
$string['embed'] = 'Embed';
$string['embedcode'] = 'Embed this report';
$string['embedinstructions'] = 'Copy the code below and paste it into any webpage to embed this report. Viewers must be logged in to this site to see it.';
$string['errorheading'] = 'Error';
$string['exportcsv'] = 'Export CSV';
$string['generate'] = 'Generate';
$string['generating'] = 'Calling AI middleware...';
$string['generationtime'] = 'Time taken';
$string['httpstatus'] = 'HTTP {$a}';
$string['httpstatuslabel'] = 'HTTP';
$string['manage'] = 'Manage AI reports';
$string['middlewareurl'] = 'Middleware endpoint URL';
$string['middlewareurl_desc'] = 'Full URL including the tenant UUID path segment. Format: https://your-host/api/v1/{tenant-uuid}/report-creator — e.g. https://api.example.com/api/v1/550e8400-e29b-41d4-a716-446655440000/report-creator';
$string['middlewareurlempty'] = 'Middleware URL is empty.';
$string['moodleversion'] = 'Moodle version sent to middleware';
$string['nlrequest'] = 'Describe what you want to see';
$string['nlrequest_help'] = 'Tips for a good prompt:<ul><li>Start with what you want to <strong>see</strong>: a count, list, total, average, etc.</li><li>Specify a <strong>time range</strong> if relevant — e.g. <em>for the last 30 days</em>.</li><li>Add <strong>course custom fields</strong> by writing: <em>course custom fields = isfrontal,isrequired</em>.</li><li>Add <strong>user custom fields</strong> by writing: <em>user info fields = department,ouid,ouname,managerid</em>.</li><li><strong>Example:</strong> <em>Show me the number of active enrollments per course for the last 30 days, course custom fields = isfrontal,isrequired</em></li></ul>';
$string['nlrequest_placeholder'] = 'e.g. Show me the number of active enrollments per course for the last 30 days, course custom fields = isfrontal,isrequired';
$string['nopendingreportdata'] = 'No pending report data found — please generate again.';
$string['noreports'] = 'No reports yet. Create your first report.';
$string['pluginname'] = 'AI Report Creator';
$string['privacy:metadata:ai_middleware'] = 'To generate a report, the user\'s natural-language request is sent to an external AI middleware service configured by the site administrator.';
$string['privacy:metadata:ai_middleware:request'] = 'The natural-language request typed by the user.';
$string['privacy:metadata:ai_middleware:system'] = 'The calling system identifier (Moodle).';
$string['privacy:metadata:ai_middleware:system_version'] = 'The Moodle version making the request.';
$string['privacy:metadata:ai_middleware:template_type'] = 'The output type requested for the report.';
$string['privacy:metadata:local_ai_reportcreator_rpts'] = 'Information about the AI-generated reports created by each user.';
$string['privacy:metadata:local_ai_reportcreator_rpts:name'] = 'The name given to the report.';
$string['privacy:metadata:local_ai_reportcreator_rpts:nl_request'] = 'The natural-language request typed by the user to describe the report.';
$string['privacy:metadata:local_ai_reportcreator_rpts:sql_query'] = 'The SQL query generated from the user\'s request.';
$string['privacy:metadata:local_ai_reportcreator_rpts:template_html'] = 'The HTML template generated for the report.';
$string['privacy:metadata:local_ai_reportcreator_rpts:template_type'] = 'The output type chosen for the report (table, dashboard, chart, etc.).';
$string['privacy:metadata:local_ai_reportcreator_rpts:timecreated'] = 'The time the report was created.';
$string['privacy:metadata:local_ai_reportcreator_rpts:timemodified'] = 'The time the report was last modified.';
$string['privacy:metadata:local_ai_reportcreator_rpts:tokens_completion'] = 'The number of AI completion tokens used to generate the report.';
$string['privacy:metadata:local_ai_reportcreator_rpts:tokens_prompt'] = 'The number of AI prompt tokens used to generate the report.';
$string['privacy:metadata:local_ai_reportcreator_rpts:tokens_total'] = 'The total number of AI tokens used to generate the report.';
$string['privacy:metadata:local_ai_reportcreator_rpts:userid'] = 'The ID of the user who created the report.';
$string['progress_almost'] = 'Almost there…';
$string['progress_calling'] = 'Calling AI middleware…';
$string['progress_processing'] = 'Processing your request…';
$string['progress_sql'] = 'Generating SQL query…';
$string['progress_template'] = 'Building the report template…';
$string['reportdeleted'] = 'Report deleted successfully.';
$string['reportlist'] = 'My Reports';
$string['reportname'] = 'Report name';
$string['reportupdated'] = 'Report updated successfully.';
$string['request'] = 'Request';
$string['role:ai_reportcreator'] = 'AI Report creator';
$string['role:ai_reportcreator_desc'] = 'Can generate AI-powered reports. Based on the Manager archetype; assignable at system or category level by a site administrator only.';
$string['running'] = 'Running…';
$string['savefailed'] = 'Failed to save the report.';
$string['savereport'] = 'Save changes';
$string['savingerror'] = 'The report was generated but could not be saved: {$a}';
$string['sqlerror'] = 'The SQL query returned an error: {$a}';
$string['sqlreadonlyerror'] = 'The SQL query contains write or DDL statements and cannot be executed for safety reasons.';
$string['streamtimeout'] = 'Stream timeout (seconds)';
$string['streamtimeout_desc'] = 'Maximum time to wait for the middleware to finish streaming a report before giving up. Minimum 30 seconds.';
$string['templatetype'] = 'Output type';
$string['testconnection'] = 'Test Connection';
$string['testconnection_fail'] = 'Connection failed';
$string['testconnection_success'] = 'Connection successful';
$string['testingconnection'] = 'Testing…';
$string['tokencompletion'] = 'Completion tokens';
$string['tokenprompt'] = 'Prompt tokens';
$string['tokentotal'] = 'Total tokens';
$string['type_bar'] = 'Chart — Bar';
$string['type_dashboard'] = 'Dashboard (stat cards + table)';
$string['type_doughnut'] = 'Chart — Doughnut';
$string['type_line'] = 'Chart — Line';
$string['type_pie'] = 'Chart — Pie';
$string['type_radar'] = 'Chart — Radar';
$string['type_report'] = 'Report (table)';
$string['unknownerror'] = 'Unknown error';
$string['viewreport'] = 'View Report';
$string['viewsql'] = 'View SQL';
$string['viewsqlfor'] = 'SQL Query — {$a}';
