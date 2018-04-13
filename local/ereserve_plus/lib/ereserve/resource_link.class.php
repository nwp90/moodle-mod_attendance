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

namespace eReserve {
    require_once 'lti/launch.class.php';
    require_once 'config.class.php';

    use eReserve\LTI\Launch;

    /**
     * Class for the generation of LTI Launch for Resource Link Managment
     * @package eResevre
     */
    class ResourceLink
    {
        private $_config;
        private $_course;
        private $_creation_path = '/app/integration/lti_resource_link/launch';
        private $_show_path = '/app/integration/lti_resource_link/:lti_resource_link_id/show';

        /**
         * ResourceLink constructor.
         * @param object $course Moodle course object
         */
        public function __construct($course)
        {
            $this->_config = new Config();
            $this->_course = $course;
        }

        /**
         * Generate HTML for Resource Link creation via LTI Launch
         *
         * @return string HTML for LTI Launch
         */
        public function launchForCreation()
        {
            return (new Launch($this->_course))->generatePostHtml($this->creationEndPoint());
        }

        /**
         * Generate HTML for Resource Link Showing via LTI Launch
         *
         * @param integer $id identifier for the resource link in eReserve Plus
         * @return string HTML for LTI Launch
         */
        public function launchForShow($id)
        {
            return (new Launch($this->_course))->generatePostHtml($this->showEndPoint($id));
        }

        /**
         * Create the LTI Launch end point for creation of a new Resource Link
         * @return string URL for LTI Lauch
         */
        private function creationEndPoint()
        {
            return join(array(
                $this->_config->getSetting('scheme') . '://',
                'host' => $this->_config->getSetting('host'),
                'path' => $this->_creation_path
            ));
        }

        /**
         * Create the LTI Launch end point for showing a new Resource Link
         * @return string URL for LTI Lauch
         */
        private function showEndPoint($id)
        {
            return join(array(
                $this->_config->getSetting('scheme') . '://',
                'host' => $this->_config->getSetting('host'),
                'path' => str_replace(':lti_resource_link_id', $id, $this->_show_path)
            ));
        }
    }
}