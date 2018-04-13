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
    /**
     * Class encapsulating the configuration for the eReserve Plus plugin (local_ereserve_plus)
     * @package eResevre
     */
    class Config
    {
        private $_settings;

        /**
         * Config constructor.
         */
        public function __construct()
        {
            global $DB;

            $settings = $DB->get_records('config_plugins', array('plugin' => LOCAL_ERESERVE_PLUS_PLUGIN_NAME));
            $this->_settings = array();

            if (!empty($settings)) {
                foreach ($settings as $setting) {
                    $this->_settings[$setting->name] = $setting->value;
                }
            }
        }

        /**
         * Get configuration setting by name
         *
         * @param string $name name of the setting
         * @return mixed
         */
        public function getSetting($name)
        {
            return (array_key_exists($name, $this->_settings) ? $this->_settings[$name] : null);
        }

        /**
         * Generates the eReserve Plus instance base URL from configuration settings
         *
         * @return string
         */
        public function instanceBasUrl() {
            return $this->getSetting('scheme') . '://' . $this->getSetting('host');
        }
    }
}
