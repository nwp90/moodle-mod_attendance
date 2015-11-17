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
 * Presentation services for Otago University Faculty of Medicine, by Catalyst IT
 * @package    local_presentation
 * @subpackage db
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @since      Moodle 2.5
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die();

$functions = array(
    'local_presentation_get_resources_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_resources_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of resources tagged with a provided set of tags.',
        'type' => 'read',
    )
);
$services = array(
    'presentation' => array( //the name of the web service
        'functions' => array (
            'local_presentation_get_resources_by_tag',
        ),
        'restrictedusers' => 0, //if enabled, the Moodle administrator must link some user to this service
        'enabled' => 1, //if enabled, the service can be reachable on a default installation
    )
);
