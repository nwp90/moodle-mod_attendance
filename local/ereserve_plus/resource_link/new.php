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

/**
 * This generates HTML for a LTI Launch to start the creation of a new Resource Link.
 * Requires the following query parameters:
 * course_id: the id of the course from what the LTI Launch is executed
 */

require_once('../../../config.php');
defined('MOODLE_INTERNAL') || die('Invalid access');

require_once $CFG->dirroot . '/local/ereserve_plus/lib.php';
require_once 'ereserve/resource_link.class.php';

$course_id = required_param('course_id', PARAM_INT);
$course = $DB->get_record('course', array('id' => (int)$course_id), '*', MUST_EXIST);

echo (new eReserve\ResourceLink($course))->launchForCreation();

