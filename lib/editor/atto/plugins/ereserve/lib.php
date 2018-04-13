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
 * Atto text editor integration version file.
 *
 * @package    atto_ereserve
 * @copyright  2018 eReserve Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once $CFG->dirroot . '/local/ereserve_plus/lib.php';
require_once 'ereserve/config.class.php';

/**
 * Initialise this plugin
 * @param string $elementid
 */
function atto_ereserve_strings_for_js()
{
    global $PAGE;

    $PAGE->requires->strings_for_js(
        array(
            'insert',
            'cancel',
            'dialogtitle',
            'buttontitle'
        ),
        'atto_ereserve');
}

/**
 * Return the js params required for this module.
 * @return array of additional params to pass to javascript init function for this module.
 */
function atto_ereserve_params_for_js($elementid, $options, $fpoptions)
{
    global $COURSE, $CFG;
    $course_context = context_course::instance($COURSE->id);
    $config = new eReserve\Config();

    return array(
        'disabled' => !has_capability('atto/ereserve:visible', $course_context),
        'ereserve_instance_base_url' => $config->instanceBasUrl(),
        'debug' => $config->getSetting('debug_mode') == 1,
        'moodle_base_url' => $CFG->wwwroot,
        'course_id' => $COURSE->id
    );
}

