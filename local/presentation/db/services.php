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
    'local_presentation_get_course_role_users' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_course_role_users',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of users in specified role in specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_resources_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_resources_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of resources tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_lessons_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_lessons_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of lessons tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_quizzes_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_quizzes_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of quizzes tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_urls_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_urls_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of urls tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_workshops_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_workshops_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of workshops tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_assignments_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_assignments_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of new-style assignments (mod_assign) tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_pages_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_pages_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of pages tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_books_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_books_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of books tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_scorms_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_scorms_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of scorms tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_glossaries_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_glossaries_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of glossaries tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_tagged_ltis_by_course' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_tagged_ltis_by_course',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of ltis tagged with at least one tag, from a specified course.',
        'type' => 'read',
    ),
    'local_presentation_get_resources_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_resources_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of resources tagged with a provided set of tags.',
        'type' => 'read',
    ),
    'local_presentation_get_lessons_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_lessons_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of lessons tagged with a provided set of tags.',
        'type' => 'read',
    ),
    'local_presentation_get_quizzes_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_quizzes_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of quizzes tagged with a provided set of tags.',
        'type' => 'read',
    ),
    'local_presentation_get_urls_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_urls_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of urls tagged with a provided set of tags.',
        'type' => 'read',
    ),
    'local_presentation_get_workshops_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_workshops_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of workshops tagged with a provided set of tags.',
        'type' => 'read',
    ),
    'local_presentation_get_assignments_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_assignments_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of new-style assignments (mod_assign) tagged with a provided set of tags.',
        'type' => 'read',
    ),
    'local_presentation_get_pages_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_pages_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of pages tagged with a provided set of tags.',
        'type' => 'read',
    ),
    'local_presentation_get_books_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_books_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of books tagged with a provided set of tags.',
        'type' => 'read',
    ),
    'local_presentation_get_scorms_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_scorms_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of scorms tagged with a provided set of tags.',
        'type' => 'read',
    ),
    'local_presentation_get_glossaries_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_glossaries_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of glossaries tagged with a provided set of tags.',
        'type' => 'read',
    ),
    'local_presentation_get_ltis_by_tag' => array(
        'classname' => 'local_presentation_external',
        'methodname' => 'get_ltis_by_tag',
        'classpath' => 'local/presentation/externallib.php',
        'description' => 'Returns a list of ltis tagged with a provided set of tags.',
        'type' => 'read',
    )
);
$services = array(
    'presentation' => array( //the name of the web service
        'functions' => array (
            'local_presentation_get_tagged_resources_by_course',
            'local_presentation_get_tagged_lessons_by_course',
            'local_presentation_get_tagged_quizzes_by_course',
            'local_presentation_get_tagged_urls_by_course',
            'local_presentation_get_tagged_workshops_by_course',
            'local_presentation_get_tagged_assignments_by_course',
            'local_presentation_get_tagged_pages_by_course',
            'local_presentation_get_tagged_books_by_course',
            'local_presentation_get_tagged_scorms_by_course',
            'local_presentation_get_tagged_glossaries_by_course',
            'local_presentation_get_tagged_ltis_by_course',
            'local_presentation_get_resources_by_tag',
            'local_presentation_get_lessons_by_tag',
            'local_presentation_get_quizzes_by_tag',
            'local_presentation_get_urls_by_tag',
            'local_presentation_get_workshops_by_tag',
            'local_presentation_get_assignments_by_tag',
            'local_presentation_get_pages_by_tag',
            'local_presentation_get_books_by_tag',
            'local_presentation_get_scorms_by_tag',
            'local_presentation_get_glossaries_by_tag',
            'local_presentation_get_ltis_by_tag',
        ),
        'restrictedusers' => 0, //if enabled, the Moodle administrator must link some user to this service
        'enabled' => 1, //if enabled, the service can be reachable on a default installation
    )
);
