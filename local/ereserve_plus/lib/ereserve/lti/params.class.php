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
    require_once $CFG->dirroot . '/lib/datalib.php';
    require_once $CFG->dirroot . '/lib/weblib.php';
    require_once $CFG->dirroot . '/lib/moodlelib.php';
    require_once 'ereserve/config.class.php';

    use \eReserve\Config;

    /**
     * Class for the generate of LTI Launch Parameters
     *
     * @package eResevre
     */
    class Params
    {
        private $_config;
        private $_site;
        private $_course;

        /**
         * Params constructor.
         * @param object $course Moodle course object
         */
        public function __construct($course)
        {
            $this->_config = new Config();
            $this->_course = $course;
        }


        /**
         * Checks to ensure the settings are available to sign the parameters for an LTI Launch
         *
         * @return bool If true then all settings are available
         */
        public function canSign()
        {
            return
                !empty($this->_config->getSetting('host')) &&
                !empty($this->_config->getSetting('consumer_key')) &&
                !empty($this->_config->getSetting('shared_secret'));
        }

        /**
         * Generate signed parameters for LTI Launch
         *
         * @param string $end_point Tool Provider endpoint for a LTI Launch
         * @param string $http_method (optional) HTTP method (GET or POST) used for communication with the Tool Provider (default: POST)
         * @param array $additional_params Array of params to override existing params or for addition
         * @return mixed
         */
        public function signedGeneration($end_point, $http_method = 'POST', $additional_params = array())
        {
            return lti_sign_parameters(
                $this->generate($additional_params),
                $end_point,
                $http_method,
                $this->_config->getSetting('consumer_key'),
                $this->_config->getSetting('shared_secret')
            );
        }

        /**
         * Generates parameters for an LTI launch
         *
         * @param array $additional_params Array of additional parameters to override or add to launch params
         * @return array Array of LTI parameters
         */
        public function generate($additional_params = array())
        {
            return array_replace(
                array_merge(
                    $this->lisPersonParams(),
                    $this->toolConsumerParams(),
                    $this->contextParams(),
                    $this->launchParams()
                ),
                $additional_params
            );
        }

        /**
         * Generates parameters for user performing the LTI Launch
         *
         * @return array
         */
        private function lisPersonParams()
        {
            $params = $this->guestUserParmas();
            if ($params == null) {
                $params = $this->authenticatedUserParmas();
            }

            return ($params);
        }

        private function guestUserParmas()
        {
            global $USER;

            if ($USER->id == 0 || $USER->username == 'guest') {
                return array(
                    'user_id' => 0,
                    'ext_user_username' => '',
                    'roles' => 'Guest',
                    'lis_person_sourcedid' => '',
                    'lis_person_name_family' => '',
                    'lis_person_name_full' => '',
                    'lis_person_name_given' => '',
                    'lis_person_contact_email_primary' => '',
                );
            } else {
                return(null);
            }

        }

        private function authenticatedUserParmas()
        {
            global $USER;

            return array(
                'user_id' => $USER->id,
                'ext_user_username' => $USER->username,
                'roles' => lti_get_ims_role($USER, 0, $this->_course->id, false),
                'lis_person_sourcedid' => trim($USER->idnumber),
                'lis_person_name_family' => trim($USER->lastname),
                'lis_person_name_full' => join(array_filter(array(trim($USER->firstname), trim($USER->lastname))), ' '),
                'lis_person_name_given' => trim($USER->firstname),
                'lis_person_contact_email_primary' => trim($USER->email),
            );
        }

        /**
         * Generates parameters for the tool consumer (i.e. the Moodle instance) performing the LTI Launch
         *
         * @return array
         */
        private function toolConsumerParams()
        {
            global $CFG;

            return array(
                'ext_lms' => 'moodle-2',
                'tool_consumer_info_product_family_code' => 'moodle',
                'tool_consumer_info_version' => (string)$CFG->version,
                'tool_consumer_instance_guid' => parse_url($CFG->wwwroot)['host'],
                'tool_consumer_instance_name' => $this->consumerInstanceName(),
                'tool_consumer_instance_description' => $this->getSite(),
            );
        }

        /**
         * Generate consumer instance name for LTI launch. Drawn from the Moodle LTI Institution Name if it is set
         * otherwise the Moodle Site Name (see get_site() in lib/datalib.php) is used.
         *
         * @return string
         */
        private function consumerInstanceName()
        {
            global $CFG;

            if (empty($CFG->mod_lti_institution_name)) {
                $name = $this->getSite();
            } else {
                $name = trim(html_to_text($CFG->mod_lti_institution_name, 0));
            }

            return ($name);
        }

        /**
         * Get the name of the site from Moodle (via get_site() in lib/datalib.php in Moodle source)
         *
         * @return string
         */
        private function getSite()
        {
            return isset($this->_site) ? $this->_site : ($this->_site = trim(html_to_text(get_site()->fullname, 0)));
        }

        /**
         * Generates parameters for context (i.e. the Moodle course) for use in an LTI Launch
         *
         * @return array
         */
        private function contextParams()
        {
            return array(
                'context_id' => $this->_course->id,
                'context_label' => $this->_course->shortname,
                'context_title' => $this->_course->fullname,
                'context_type' => 'CourseSection',
                'lis_course_section_sourcedid' => $this->_course->idnumber
            );
        }

        /**
         * Generate general launch parameters for use in an LTI Launch
         *
         * @return array
         */
        private function launchParams()
        {
            // TODO: Investigate the need for a return URL
            return array(
                "launch_presentation_locale" => current_language(),
                "launch_presentation_document_target" => "iframe",
                "launch_presentation_return_url" => ''
            );
        }
    }
}
