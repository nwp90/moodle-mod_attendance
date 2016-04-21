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
class local_presentation_external extends external_api {

    /**
     * Describes the parameters for get_objects_by_tag
     * To be used by any method getting objects purely by tag, with no
     * other parameters.
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    private static function get_objects_by_tag_parameters() {
        return new external_function_parameters (
            array(
                'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag',
                        '', VALUE_REQUIRED, '', NULL_NOT_ALLOWED), 'Array of tags', VALUE_DEFAULT, array()),
            )
        );
    }

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
        $returnfiles = array();
        $params = self::validate_parameters(self::get_resources_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        r.id as id, r.name as name,
                        f.id as fileid, f.filename, f.filesize as size, f.itemid, f.filearea, f.filepath,
                        f.mimetype, f.author, f.license,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as resourcecontext
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {resource} r
                            on r.id = cm.instance
                        join {course} c
                            on r.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = r.id and ti.itemtype = 'resource'
                        join {tag} t
                            on t.id = ti.tagid
                        join {files} f
                            on f.contextid=cx.id
                    where
                        t.name $tagsql
                        and m.name='resource'
                        and f.filename not like '.'";
            $files = $DB->get_records_sql($sql, $tagvalues);
            foreach ($files as $file) {
                $returnfile = new StdClass();
                $keys = array('id', 'name', 'filename', 'size', 'mimetype', 'author', 'license', 'courseid', 'coursename', 'courseshortname');
                foreach ($keys as $key) {
                    $returnfile->$key = $file->$key;
                }
                $returnfile->link = "$CFG->wwwroot/pluginfile.php/$file->resourcecontext/mod_resource/" .
                        "$file->filearea/$file->itemid/$file->filepath$file->filename";
                $returnfiles[] = $returnfile;
            }
        }
        return $returnfiles;
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
                    'id' => new external_value(PARAM_INT, 'Resource ID'),
                    'name' => new external_value(PARAM_TEXT, 'Resource name'),
                    'filename' => new external_value(PARAM_TEXT, 'Name of file'),
                    'size' => new external_value(PARAM_INT, 'size of file'),
                    'mimetype' => new external_value(PARAM_TEXT, 'mimetype of file'),
                    'author' => new external_value(PARAM_TEXT, 'author of file'),
                    'license' => new external_value(PARAM_TEXT, 'licence of file'),
                    'courseid' => new external_value(PARAM_INT, 'moodle id of course'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'link' => new external_value(PARAM_TEXT, 'link to file'),
                ), 'resource'
            )
        );
    }

    /**
     * Describes the parameters for get_lessons_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_lessons_by_tag_parameters() {
        return self::get_objects_by_tag_parameters();
    }

    /**
     * Returns a list of lessons tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the lesson details
     * @since Moodle 3.0
     */
    public static function get_lessons_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $returnlessons = array();
        $params = self::validate_parameters(self::get_objects_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        l.id as id, l.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as lessoncontext, cm.id as cmid
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {lesson} l
                            on l.id = cm.instance
                        join {course} c
                            on l.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = l.id and ti.itemtype = 'lesson'
                        join {tag} t
                            on t.id = ti.tagid
                    where
                        t.name $tagsql
                        and m.name='lesson'";
            $lessons = $DB->get_records_sql($sql, $tagvalues);
            foreach ($lessons as $lesson) {
                $returnlesson = new StdClass();
                $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                foreach ($keys as $key) {
                    $returnlesson->$key = $lesson->$key;
                }
                $returnlesson->link = "$CFG->wwwroot/mod/lesson/view.php?id=$lesson->cmid";
                $returnlessons[] = $returnlesson;
            }
        }
        return $returnlessons;
    }

    /**
     * Describes the get_lessons_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_lessons_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Lesson ID'),
                    'name' => new external_value(PARAM_TEXT, 'Lesson name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to lesson'),
                ), 'lesson'
            )
        );
    }

    /**
     * Describes the parameters for get_quizzes_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_quizzes_by_tag_parameters() {
        return self::get_objects_by_tag_parameters();
    }

    /**
     * Returns a list of quizzes tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the quiz details
     * @since Moodle 3.0
     */
    public static function get_quizzes_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $returnquizzes = array();
        $params = self::validate_parameters(self::get_objects_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        q.id as id, q.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as quizcontext, cm.id as cmid
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {quiz} q
                            on q.id = cm.instance
                        join {course} c
                            on q.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = q.id and ti.itemtype = 'quiz'
                        join {tag} t
                            on t.id = ti.tagid
                    where
                        t.name $tagsql
                        and m.name='quiz'";
            $quizzes = $DB->get_records_sql($sql, $tagvalues);
            foreach ($quizzes as $quiz) {
                $returnquiz = new StdClass();
                $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                foreach ($keys as $key) {
                    $returnquiz->$key = $quiz->$key;
                }
                $returnquiz->link = "$CFG->wwwroot/mod/quiz/view.php?id=$quiz->cmid";
                $returnquizzes[] = $returnquiz;
            }
        }
        return $returnquizzes;
    }

    /**
     * Describes the get_quizzes_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_quizzes_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Quiz ID'),
                    'name' => new external_value(PARAM_TEXT, 'Quiz name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to quiz'),
                ), 'quiz'
            )
        );
    }

    /**
     * Describes the parameters for get_urls_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_urls_by_tag_parameters() {
        return self::get_objects_by_tag_parameters();
    }

    /**
     * Returns a list of urls tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the url details
     * @since Moodle 3.0
     */
    public static function get_urls_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $returnurls = array();
        $params = self::validate_parameters(self::get_objects_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        u.id as id, u.name as name, u.externalurl as url,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as urlcontext, cm.id as cmid
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {url} u
                            on u.id = cm.instance
                        join {course} c
                            on u.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = u.id and ti.itemtype = 'url'
                        join {tag} t
                            on t.id = ti.tagid
                    where
                        t.name $tagsql
                        and m.name='url'";
            $urls = $DB->get_records_sql($sql, $tagvalues);
            foreach ($urls as $url) {
                $returnurl = new StdClass();
                $keys = array('id', 'name', 'url', 'coursename', 'courseshortname', 'courseid');
                foreach ($keys as $key) {
                    $returnurl->$key = $url->$key;
                }
                $returnurl->link = "$CFG->wwwroot/mod/url/view.php?id=$url->cmid";
                $returnurls[] = $returnurl;
            }
        }
        return $returnurls;
    }

    /**
     * Describes the get_urls_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_urls_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'URL ID'),
                    'name' => new external_value(PARAM_TEXT, 'URL name'),
                    'url' => new external_value(PARAM_TEXT, 'URL'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to URL page in Moodle'),
                ), 'url'
            )
        );
    }

    /**
     * Describes the parameters for get_workshops_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_workshops_by_tag_parameters() {
        return self::get_objects_by_tag_parameters();
    }

    /**
     * Returns a list of workshops tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the workshop details
     * @since Moodle 3.0
     */
    public static function get_workshops_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $returnworkshops = array();
        $params = self::validate_parameters(self::get_objects_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        w.id as id, w.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as workshopcontext, cm.id as cmid
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {workshop} w
                            on w.id = cm.instance
                        join {course} c
                            on w.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = w.id and ti.itemtype = 'workshop'
                        join {tag} t
                            on t.id = ti.tagid
                    where
                        t.name $tagsql
                        and m.name='workshop'";
            $workshops = $DB->get_records_sql($sql, $tagvalues);
            foreach ($workshops as $workshop) {
                $returnworkshop = new StdClass();
                $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                foreach ($keys as $key) {
                    $returnworkshop->$key = $workshop->$key;
                }
                $returnworkshop->link = "$CFG->wwwroot/mod/workshop/view.php?id=$workshop->cmid";
                $returnworkshops[] = $returnworkshop;
            }
        }
        return $returnworkshops;
    }

    /**
     * Describes the get_workshops_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_workshops_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Workshop ID'),
                    'name' => new external_value(PARAM_TEXT, 'Workshop name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to workshop in Moodle'),
                ), 'workshop'
            )
        );
    }

    /**
     * Describes the parameters for get_assignments_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_assignments_by_tag_parameters() {
        return self::get_objects_by_tag_parameters();
    }

    /**
     * Returns a list of assignments tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the assignment details
     * @since Moodle 3.0
     */
    public static function get_assignments_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $returnassignments = array();
        $params = self::validate_parameters(self::get_objects_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        a.id as id, a.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as assignmentcontext, cm.id as cmid
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {assign} a
                            on a.id = cm.instance
                        join {course} c
                            on a.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = a.id and ti.itemtype = 'assign'
                        join {tag} t
                            on t.id = ti.tagid
                    where
                        t.name $tagsql
                        and m.name='assign'";
            $assignments = $DB->get_records_sql($sql, $tagvalues);
            foreach ($assignments as $assignment) {
                $returnassignment = new StdClass();
                $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                foreach ($keys as $key) {
                    $returnassignment->$key = $assignment->$key;
                }
                $returnassignment->link = "$CFG->wwwroot/mod/assignment/view.php?id=$assignment->cmid";
                $returnassignments[] = $returnassignment;
            }
        }
        return $returnassignments;
    }

    /**
     * Describes the get_assignments_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_assignments_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Assignment ID'),
                    'name' => new external_value(PARAM_TEXT, 'Assignment name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to assignment in Moodle'),
                ), 'assignment'
            )
        );
    }

    /**
     * Describes the parameters for get_pages_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_pages_by_tag_parameters() {
        return self::get_objects_by_tag_parameters();
    }

    /**
     * Returns a list of pages tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the page details
     * @since Moodle 3.0
     */
    public static function get_pages_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $returnpages = array();
        $params = self::validate_parameters(self::get_objects_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        p.id as id, p.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as pagecontext, cm.id as cmid
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {page} p
                            on p.id = cm.instance
                        join {course} c
                            on p.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = p.id and ti.itemtype = 'page'
                        join {tag} t
                            on t.id = ti.tagid
                    where
                        t.name $tagsql
                        and m.name='page'";
            $pages = $DB->get_records_sql($sql, $tagvalues);
            foreach ($pages as $page) {
                $returnpage = new StdClass();
                $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                foreach ($keys as $key) {
                    $returnpage->$key = $page->$key;
                }
                $returnpage->link = "$CFG->wwwroot/mod/page/view.php?id=$page->cmid";
                $returnpages[] = $returnpage;
            }
        }
        return $returnpages;
    }

    /**
     * Describes the get_pages_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_pages_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Page ID'),
                    'name' => new external_value(PARAM_TEXT, 'Page name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to page in Moodle'),
                ), 'page'
            )
        );
    }

    /**
     * Describes the parameters for get_books_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_books_by_tag_parameters() {
        return self::get_objects_by_tag_parameters();
    }

    /**
     * Returns a list of books tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the book details
     * @since Moodle 3.0
     */
    public static function get_books_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $returnbooks = array();
        $params = self::validate_parameters(self::get_objects_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        b.id as id, b.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as bookcontext, cm.id as cmid
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {book} b
                            on b.id = cm.instance
                        join {course} c
                            on b.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = b.id and ti.itemtype = 'book'
                        join {tag} t
                            on t.id = ti.tagid
                    where
                        t.name $tagsql
                        and m.name='book'";
            $books = $DB->get_records_sql($sql, $tagvalues);
            foreach ($books as $book) {
                $returnbook = new StdClass();
                $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                foreach ($keys as $key) {
                    $returnbook->$key = $book->$key;
                }
                $returnbook->link = "$CFG->wwwroot/mod/book/view.php?id=$book->cmid";
                $returnbooks[] = $returnbook;
            }
        }
        return $returnbooks;
    }

    /**
     * Describes the get_books_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_books_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Book ID'),
                    'name' => new external_value(PARAM_TEXT, 'Book name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to book in Moodle'),
                ), 'book'
            )
        );
    }

    /**
     * Describes the parameters for get_scorms_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_scorms_by_tag_parameters() {
        return self::get_objects_by_tag_parameters();
    }

    /**
     * Returns a list of scorms tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the scorm details
     * @since Moodle 3.0
     */
    public static function get_scorms_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $returnscorms = array();
        $params = self::validate_parameters(self::get_objects_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        s.id as id, s.name as name,
                        f.id as fileid, f.filename, f.filesize as size, f.itemid, f.filearea, f.filepath,
                        f.mimetype, f.author, f.license,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as scormcontext, cm.id as cmid
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {scorm} s
                            on s.id = cm.instance
                        join {course} c
                            on s.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = s.id and ti.itemtype = 'scorm'
                        join {tag} t
                            on t.id = ti.tagid
                        join {files} f
                            on f.contextid=cx.id
                    where
                        t.name $tagsql
                        and m.name='scorm'
                        and f.filearea = 'package'
                        and f.filename not like '.'";
            $scorms = $DB->get_records_sql($sql, $tagvalues);
            foreach ($scorms as $scorm) {
                $returnscorm = new StdClass();
                $keys = array('id', 'name', 'filename', 'size', 'mimetype', 'author', 'license', 'courseid', 'coursename', 'courseshortname');
                foreach ($keys as $key) {
                    $returnscorm->$key = $scorm->$key;
                }
                $returnscorm->link = "$CFG->wwwroot/mod/scorm/view.php?id=$scorm->cmid";
                $returnscorms[] = $returnscorm;
            }
        }
        return $returnscorms;
    }

    /**
     * Describes the get_scorms_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_scorms_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'SCORM ID'),
                    'name' => new external_value(PARAM_TEXT, 'SCORM name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'filename' => new external_value(PARAM_TEXT, 'Name of file'),
                    'size' => new external_value(PARAM_INT, 'size of file'),
                    'mimetype' => new external_value(PARAM_TEXT, 'mimetype of file'),
                    'author' => new external_value(PARAM_TEXT, 'author of file'),
                    'license' => new external_value(PARAM_TEXT, 'licence of file'),
                    'link' => new external_value(PARAM_TEXT, 'Link to SCORM in Moodle'),
                ), 'scorm'
            )
        );
    }

    /**
     * Describes the parameters for get_glossaries_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_glossaries_by_tag_parameters() {
        return self::get_objects_by_tag_parameters();
    }

    /**
     * Returns a list of glossaries tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the glossary details
     * @since Moodle 3.0
     */
    public static function get_glossaries_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $returnglossaries = array();
        $params = self::validate_parameters(self::get_objects_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        g.id as id, g.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as glossarycontext, cm.id as cmid
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {glossary} g
                            on g.id = cm.instance
                        join {course} c
                            on g.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = g.id and ti.itemtype = 'glossary'
                        join {tag} t
                            on t.id = ti.tagid
                    where
                        t.name $tagsql
                        and m.name='glossary'";
            $glossaries = $DB->get_records_sql($sql, $tagvalues);
            foreach ($glossaries as $glossary) {
                $returnglossary = new StdClass();
                $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                foreach ($keys as $key) {
                    $returnglossary->$key = $glossary->$key;
                }
                $returnglossary->link = "$CFG->wwwroot/mod/glossary/view.php?id=$glossary->cmid";
                $returnglossaries[] = $returnglossary;
            }
        }
        return $returnglossaries;
    }

    /**
     * Describes the get_glossaries_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_glossaries_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Glossary ID'),
                    'name' => new external_value(PARAM_TEXT, 'Glossary name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to glossary in Moodle'),
                ), 'glossary'
            )
        );
    }

    /**
     * Describes the parameters for get_ltis_by_tag
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_ltis_by_tag_parameters() {
        return self::get_objects_by_tag_parameters();
    }

    /**
     * Returns a list of ltis tagged by a provided list of tags.
     *
     * @param array $tags an array of tags
     * @return array the lti details
     * @since Moodle 3.0
     */
    public static function get_ltis_by_tag($tags = array()) {
        global $CFG, $DB, $USER;
        $returnltis = array();
        $params = self::validate_parameters(self::get_objects_by_tag_parameters(), array('tags' => $tags));
        $tags = $params['tags'];
        $return = array();
        if (!empty($tags)) {
            foreach($tags as $i => $tag) {
                $tags[$i] = strtolower($tag);
            }
            list($tagsql, $tagvalues) = $DB->get_in_or_equal($tags);
            $sql = "select
                        l.id as id, l.name as name, l.typeid,
                        lt.name as ltitype,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as lticontext, cm.id as cmid
                    from
                        {modules} m
                        join {course_modules} cm
                            on cm.module = m.id
                        join {context} cx
                            on cx.instanceid=cm.id and cx.contextlevel=70
                        join {lti} l
                            on l.id = cm.instance
                        join {lti_types} lt
                            on l.typeid = lt.id
                        join {course} c
                            on l.course = c.id
                        join {tag_instance} ti
                            on ti.itemid = l.id and ti.itemtype = 'lti'
                        join {tag} t
                            on t.id = ti.tagid
                    where
                        t.name $tagsql
                        and m.name='lti'";
            $ltis = $DB->get_records_sql($sql, $tagvalues);
            foreach ($ltis as $lti) {
                $returnlti = new StdClass();
                $keys = array('id', 'name', 'ltitype', 'coursename', 'courseshortname', 'courseid');
                foreach ($keys as $key) {
                    $returnlti->$key = $lti->$key;
                }
                $returnlti->link = "$CFG->wwwroot/mod/lti/view.php?id=$lti->cmid";
                $returnltis[] = $returnlti;
            }
        }
        return $returnltis;
    }

    /**
     * Describes the get_ltis_by_tag return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_ltis_by_tag_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'LTI ID'),
                    'name' => new external_value(PARAM_TEXT, 'LTI name'),
                    'ltitype' => new external_value(PARAM_TEXT, 'LTI type name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to LTI instance in Moodle'),
                ), 'lti'
            )
        );
    }
}
