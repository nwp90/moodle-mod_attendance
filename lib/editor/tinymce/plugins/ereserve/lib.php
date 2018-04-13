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
 * TinyMCE Moodle Plugin for eReserve Plus
 *
 * @package    tinymce_ereserve
 * @copyright  2018 eReserve Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Invalid access');

global $CFG;
require_once $CFG->dirroot . '/local/ereserve_plus/lib.php';
require_once 'ereserve/config.class.php';

class tinymce_ereserve extends editor_tinymce_plugin
{
    protected function update_init_params(array &$params, context $context, array $options = null)
    {
        global $COURSE, $CFG;
        $course_context = context_course::instance($COURSE->id);
        $config = new eReserve\Config();

        $params['disabled'] = !has_capability('tinymce/ereserve:visible', $course_context);
        $params['debug'] = $config->getSetting('debug_mode') == 1;
        $params['ereserve_instance_base_url'] = $config->instanceBasUrl();
        $params['moodle_base_url'] = $CFG->wwwroot;
        $params['course_id'] = $COURSE->id;

        if ($row = $this->find_button($params, 'image')) {
            $this->add_button_after($params, $row, 'ereserve', 'image');
        } else {
            $this->add_button_after($params, $this->count_button_rows($params), 'ereserve');
        }

        // Add JS file, which uses default name.
        $this->add_js_plugin($params);
    }
}
