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
 * @package     local
 * @subpackage  ereserve_plus
 * @copyright   2018 eReserve Pty Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace eReserve\LTI {
    defined('MOODLE_INTERNAL') || die('Invalid access');

    global $CFG;
    require_once $CFG->dirroot . '/mod/lti/locallib.php';
    require_once 'params.class.php';

    /**
     * Class for generation of a LTI Launch
     *
     * @package eResevre
     */
    class Launch
    {
        private $_course;

        /**
         * Launch constructor.
         * @param object $course Moodle course object where the launch is being executed
         */
        public function __construct($course)
        {
            $this->_course = $course;
        }

        /**
         * Generates the HTML required for an LTI Launch via POST
         *
         * @param string $end_point Tool Provider endpoint for a LTI Launch
         * @param array $additional_params Array of params to override existing params or for addition
         * @param bool $debug If true then debug information is rendered
         * @return string HTML for LTI Launch via POST
         */
        public function generatePostHtml($end_point, $additional_params = array(), $debug=false)
        {
            $params = new Params($this->_course);
            if ($params->canSign()) {
                return lti_post_launch_html(
                    $params->signedGeneration($end_point, 'POST', $additional_params),
                    $end_point,
                    $debug
                );
            } else {
                return get_string('missing_lti_settings', LOCAL_ERESERVE_PLUS_PLUGIN_NAME);
            }
        }
    }
}