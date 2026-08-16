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

namespace local_ai_reportcreator\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Privacy API provider for the AI Report Creator plugin.
 *
 * Reports are stored per-user (no course/module linkage), so all data lives
 * in the owning user's context.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Describe the personal data stored and disclosed by this plugin.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection The updated collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_ai_reportcreator_reports',
            [
                'userid'            => 'privacy:metadata:local_ai_reportcreator_reports:userid',
                'name'              => 'privacy:metadata:local_ai_reportcreator_reports:name',
                'nl_request'        => 'privacy:metadata:local_ai_reportcreator_reports:nl_request',
                'template_type'     => 'privacy:metadata:local_ai_reportcreator_reports:template_type',
                'sql_query'         => 'privacy:metadata:local_ai_reportcreator_reports:sql_query',
                'template_html'     => 'privacy:metadata:local_ai_reportcreator_reports:template_html',
                'tokens_prompt'     => 'privacy:metadata:local_ai_reportcreator_reports:tokens_prompt',
                'tokens_completion' => 'privacy:metadata:local_ai_reportcreator_reports:tokens_completion',
                'tokens_total'      => 'privacy:metadata:local_ai_reportcreator_reports:tokens_total',
                'timecreated'       => 'privacy:metadata:local_ai_reportcreator_reports:timecreated',
                'timemodified'      => 'privacy:metadata:local_ai_reportcreator_reports:timemodified',
            ],
            'privacy:metadata:local_ai_reportcreator_reports'
        );

        $collection->add_external_location_link(
            'ai_middleware',
            [
                'request'        => 'privacy:metadata:ai_middleware:request',
                'system'         => 'privacy:metadata:ai_middleware:system',
                'system_version' => 'privacy:metadata:ai_middleware:system_version',
                'template_type'  => 'privacy:metadata:ai_middleware:template_type',
            ],
            'privacy:metadata:ai_middleware'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain personal data for the given user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the user's context.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $contextlist->add_user_context($userid);
        return $contextlist;
    }

    /**
     * Export all user data for the approved contextlist.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_USER || (int) $context->instanceid !== (int) $user->id) {
                continue;
            }

            $reports = $DB->get_records('local_ai_reportcreator_reports', ['userid' => $user->id]);
            if (empty($reports)) {
                continue;
            }

            $data = [];
            foreach ($reports as $report) {
                $data[] = (object) [
                    'name'              => $report->name,
                    'nl_request'        => $report->nl_request,
                    'template_type'     => $report->template_type,
                    'sql_query'         => $report->sql_query,
                    'tokens_prompt'     => $report->tokens_prompt,
                    'tokens_completion' => $report->tokens_completion,
                    'tokens_total'      => $report->tokens_total,
                    'timecreated'       => transform::datetime($report->timecreated),
                    'timemodified'      => transform::datetime($report->timemodified),
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_ai_reportcreator')],
                (object) ['reports' => $data]
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel !== CONTEXT_USER) {
            return;
        }

        $DB->delete_records('local_ai_reportcreator_reports', ['userid' => $context->instanceid]);
    }

    /**
     * Delete all user data for the approved contextlist.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_USER || (int) $context->instanceid !== (int) $user->id) {
                continue;
            }

            $DB->delete_records('local_ai_reportcreator_reports', ['userid' => $user->id]);
        }
    }
}
