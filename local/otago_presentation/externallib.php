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
 * External resource API - read only
 * @package    local_ws_resource
 * @category   external
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') or die();

require_once("$CFG->libdir/externallib.php");

/**
 * Resource external functions.
 *
 * @package    local_ws_resource
 * @category   external
 * @copyright  2013 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.5
 */
class local_otago_presentation_external extends external_api {

    /**
     * Describes the parameters for get_resources_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 2.5
     */
    public static function get_resources_by_tag_parameters() {
        return new external_function_parameters (
            array(
                'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag',
                        '', VALUE_REQUIRED, '', NULL_NOT_ALLOWED), 'Array of tags', VALUE_DEFAULT, array()),
            )
        );
    }

    /**
     * Returns a list of resources tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the resource details
     * @since Moodle 2.5
     */
    public static function get_resources_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $params = self::validate_parameters(self::get_resources_by_tag_parameters(), array('tags' => $tags));
        $return = array();
        if (!empty($tags)) {
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        r.id, r.name, r.intro, r.introformat
                    from
                        mdl_resource r
                        join mdl_tag_instance ti
                            on ti.itemid = r.id and ti.itemtype = 'resource'
                        join mdl_tag t
                            on t.id = ti.tagid
                    where
                        t.name $tagsql";
            $resources = $DB->get_records_sql($sql, $tagvalues);
            foreach ($resources as $resource) {
                $return[$resource->id] = (array) $resource;
            }
        }
        return $return;
    }

    /**
     * Describes the get_resource_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_resources_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Resouce ID'),
                    'name' => new external_value(PARAM_TEXT, 'Resource name'),
                    'intro' => new external_value(PARAM_RAW, 'The Resource intro'),
                    'introformat' => new external_format_value('intro'),
                ), 'resource'
            )
        );
    }
}
