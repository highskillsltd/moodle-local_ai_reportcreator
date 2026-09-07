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
 * Unit tests for local_ai_reportcreator\ApiClient.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_reportcreator;

use local_ai_reportcreator\ApiClient;

/**
 * Tests for the config-reading accessors on ApiClient.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_ai_reportcreator\ApiClient
 */
final class api_client_test extends \advanced_testcase {
    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * is_configured() is false unless both the URL and the key are set.
     *
     * @covers ::is_configured
     */
    public function test_is_configured_requires_both_settings(): void {
        set_config('middleware_url', '', 'local_ai_reportcreator');
        set_config('api_key', '', 'local_ai_reportcreator');
        $this->assertFalse((new ApiClient())->is_configured());

        set_config('middleware_url', 'https://api.example.com/report-creator', 'local_ai_reportcreator');
        $this->assertFalse((new ApiClient())->is_configured());

        set_config('api_key', 'abc123', 'local_ai_reportcreator');
        $this->assertTrue((new ApiClient())->is_configured());
    }

    /**
     * is_insecure_url() is true only for a plain http:// endpoint.
     *
     * @covers ::is_insecure_url
     */
    public function test_is_insecure_url(): void {
        set_config('api_key', 'key', 'local_ai_reportcreator');

        set_config('middleware_url', 'http://insecure.example.com/report-creator', 'local_ai_reportcreator');
        $this->assertTrue((new ApiClient())->is_insecure_url());

        set_config('middleware_url', 'https://secure.example.com/report-creator', 'local_ai_reportcreator');
        $this->assertFalse((new ApiClient())->is_insecure_url());
    }

    /**
     * get_middleware_url() returns the configured URL with any trailing slash removed.
     *
     * @covers ::get_middleware_url
     */
    public function test_get_middleware_url_strips_trailing_slash(): void {
        set_config('api_key', 'key', 'local_ai_reportcreator');
        set_config('middleware_url', 'https://api.example.com/report-creator/', 'local_ai_reportcreator');

        $this->assertSame('https://api.example.com/report-creator', (new ApiClient())->get_middleware_url());
    }
}
