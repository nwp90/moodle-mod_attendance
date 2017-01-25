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
                        VALUE_REQUIRED, '', NULL_NOT_ALLOWED), 'Array of tags', VALUE_DEFAULT, array()),
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_objects_by_course
     * To be used by any method getting tagged objects purely by course, with no
     * other parameters.
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    private static function get_tagged_objects_by_course_parameters() {
        return new external_function_parameters (
            array(
                'course' => new external_value(PARAM_ALPHANUMEXT, 'course shortname'),
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_resources_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_resources_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of resources tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the resource details
     * @since Moodle 2.5
     */
    public static function get_tagged_resources_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnfiles = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        r.id as id, r.name as name,
                        f.id as fileid, f.filename, f.filesize as size, f.itemid, f.filearea, f.filepath,
                        f.mimetype, f.author, f.license,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as resourcecontext,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name = 'resource'
                        and f.filename not like '.'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $files = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $files)) {
                    $returnfile = new StdClass();
                    $keys = array('id', 'name', 'filename', 'size', 'mimetype', 'author', 'license', 'courseid', 'coursename', 'courseshortname');
                    foreach ($keys as $key) {
                        $returnfile->$key = $ti->$key;
                    }
                    $returnfile->link = "$CFG->wwwroot/pluginfile.php/$ti->resourcecontext/mod_resource/" .
                            "$ti->filearea/$ti->itemid/$ti->filepath$ti->filename";
                    $returnfile->tags = array();
                    $returnfiles[] = $returnfile;
                }
                $returnfile->tags[] = $ti->tag;
                $files[$ti->id] = $returnfile;
            }
        }
        return $returnfiles;
    }

    /**
     * Describes the get_tagged_resources_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_resources_by_course_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Resource ID'),
                    'name' => new external_value(PARAM_TEXT, 'Resource name'),
                    'filename' => new external_value(PARAM_TEXT, 'Name of file'),
                    'size' => new external_value(PARAM_INT, 'Size of file'),
                    'mimetype' => new external_value(PARAM_TEXT, 'Mimetype of file'),
                    'author' => new external_value(PARAM_TEXT, 'Author of file'),
                    'license' => new external_value(PARAM_TEXT, 'Licence of file'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'link' => new external_value(PARAM_TEXT, 'link to file'),
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'resource'
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_lessons_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_lessons_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of lessons tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the lesson details
     * @since Moodle 3.0
     */
    public static function get_tagged_lessons_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnlessons = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        l.id as id, l.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as lessoncontext, cm.id as cmid,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name='lesson'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $lessons = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $lessons)) {
                    $returnlesson = new StdClass();
                    $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                    foreach ($keys as $key) {
                        $returnlesson->$key = $ti->$key;
                    }
                    $returnlesson->link = "$CFG->wwwroot/mod/lesson/view.php?id=$ti->cmid";
                    $returnlesson->tags = array();
                    $returnlessons[] = $returnlesson;
                }
                $returnlesson->tags[] = $ti->tag;
                $lessons[$ti->id] = $returnlesson;
            }
        }
        return $returnlessons;
    }

    /**
     * Describes the get_tagged_lessons_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_lessons_by_course_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Lesson ID'),
                    'name' => new external_value(PARAM_TEXT, 'Lesson name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to lesson'),
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'lesson'
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_quizzes_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_quizzes_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of quizzes tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the quiz details
     * @since Moodle 3.0
     */
    public static function get_tagged_quizzes_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnquizzes = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        q.id as id, q.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as quizcontext, cm.id as cmid,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name='quiz'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $quizzes = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $quizzes)) {
                    $returnquiz = new StdClass();
                    $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                    foreach ($keys as $key) {
                        $returnquiz->$key = $ti->$key;
                    }
                    $returnquiz->link = "$CFG->wwwroot/mod/quiz/view.php?id=$ti->cmid";
                    $returnquiz->tags = array();
                    $returnquizzes[] = $returnquiz;
                }
                $returnquiz->tags[] = $ti->tag;
                $quizzes[$ti->id] = $returnquiz;
            }
        }
        return $returnquizzes;
    }

    /**
     * Describes the get_tagged_quizzes_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_quizzes_by_course_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Quiz ID'),
                    'name' => new external_value(PARAM_TEXT, 'Quiz name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to quiz'),
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'quiz'
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_urls_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_urls_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of urls tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the url details
     * @since Moodle 3.0
     */
    public static function get_tagged_urls_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnurls = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        u.id as id, u.name as name, u.externalurl as url,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as urlcontext, cm.id as cmid,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name='url'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $urls = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $urls)) {
                    $returnurl = new StdClass();
                    $keys = array('id', 'name', 'url', 'coursename', 'courseshortname', 'courseid');
                    foreach ($keys as $key) {
                        $returnurl->$key = $ti->$key;
                    }
                    $returnurl->link = "$CFG->wwwroot/mod/url/view.php?id=$ti->cmid";
                    $returnurl->tags = array();
                    $returnurls[] = $returnurl;
                }
                $returnurl->tags[] = $ti->tag;
                $urls[$ti->id] = $returnurl;
            }
        }
        return $returnurls;
    }

    /**
     * Describes the get_tagged_urls_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_urls_by_course_returns() {
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
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'url'
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_workshops_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_workshops_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of workshops tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the workshop details
     * @since Moodle 3.0
     */
    public static function get_tagged_workshops_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnworkshops = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        w.id as id, w.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as workshopcontext, cm.id as cmid,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name='workshop'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $workshops = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $workshops)) {
                    $returnworkshop = new StdClass();
                    $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                    foreach ($keys as $key) {
                        $returnworkshop->$key = $ti->$key;
                    }
                    $returnworkshop->link = "$CFG->wwwroot/mod/workshop/view.php?id=$ti->cmid";
                    $returnworkshop->tags = array();
                    $returnworkshops[] = $returnworkshop;
                }
                $returnworkshop->tags[] = $ti->tag;
                $workshops[$ti->id] = $returnworkshop;
            }
        }
        return $returnworkshops;
    }

    /**
     * Describes the get_tagged_workshops_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_workshops_by_course_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Workshop ID'),
                    'name' => new external_value(PARAM_TEXT, 'Workshop name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to workshop in Moodle'),
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'workshop'
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_assignments_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_assignments_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of assignments tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the assignment details
     * @since Moodle 3.0
     */
    public static function get_tagged_assignments_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnassignments = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        a.id as id, a.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as assignmentcontext, cm.id as cmid,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name='assign'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $assignments = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $assignments)) {
                    $returnassignment = new StdClass();
                    $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                    foreach ($keys as $key) {
                        $returnassignment->$key = $ti->$key;
                    }
                    $returnassignment->link = "$CFG->wwwroot/mod/assign/view.php?id=$ti->cmid";
                    $returnassignment->tags = array();
                    $returnassignments[] = $returnassignment;
                }
                $returnassignment->tags[] = $ti->tag;
                $assignments[$ti->id] = $returnassignment;
            }
        }
        return $returnassignments;
    }

    /**
     * Describes the get_tagged_assignments_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_assignments_by_course_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Assignment ID'),
                    'name' => new external_value(PARAM_TEXT, 'Assignment name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to assignment in Moodle'),
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'assignment'
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_pages_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_pages_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of pages tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the page details
     * @since Moodle 3.0
     */
    public static function get_tagged_pages_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnpages = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        p.id as id, p.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as pagecontext, cm.id as cmid,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name='page'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $pages = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $pages)) {
                    $returnpage = new StdClass();
                    $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                    foreach ($keys as $key) {
                        $returnpage->$key = $ti->$key;
                    }
                    $returnpage->link = "$CFG->wwwroot/mod/page/view.php?id=$ti->cmid";
                    $returnpage->tags = array();
                    $returnpages[] = $returnpage;
                }
                $returnpage->tags[] = $ti->tag;
                $pages[$ti->id] = $returnpage;
            }
        }
        return $returnpages;
    }

    /**
     * Describes the get_tagged_pages_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_pages_by_course_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Page ID'),
                    'name' => new external_value(PARAM_TEXT, 'Page name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to page in Moodle'),
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'page'
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_books_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_books_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of books tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the book details
     * @since Moodle 3.0
     */
    public static function get_tagged_books_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnbooks = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        b.id as id, b.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as bookcontext, cm.id as cmid,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name='book'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $books = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $books)) {
                    $returnbook = new StdClass();
                    $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                    foreach ($keys as $key) {
                        $returnbook->$key = $ti->$key;
                    }
                    $returnbook->link = "$CFG->wwwroot/mod/book/view.php?id=$ti->cmid";
                    $returnbook->tags = array();
                    $returnbooks[] = $returnbook;
                }
                $returnbook->tags[] = $ti->tag;
                $books[$ti->id] = $returnbook;
            }
        }
        return $returnbooks;
    }

    /**
     * Describes the get_tagged_books_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_books_by_course_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Book ID'),
                    'name' => new external_value(PARAM_TEXT, 'Book name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to book in Moodle'),
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'book'
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_scorms_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_scorms_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of scorms tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the scorm details
     * @since Moodle 3.0
     */
    public static function get_tagged_scorms_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnscorms = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        s.id as id, s.name as name,
                        f.id as fileid, f.filename, f.filesize as size, f.itemid, f.filearea, f.filepath,
                        f.mimetype, f.author, f.license,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as scormcontext, cm.id as cmid,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name='scorm'
                        and f.filearea = 'package'
                        and f.filename not like '.'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $scorms = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $scorms)) {
                    $returnscorm = new StdClass();
                    $keys = array('id', 'name', 'filename', 'size', 'mimetype', 'author', 'license', 'courseid', 'coursename', 'courseshortname');
                    foreach ($keys as $key) {
                        $returnscorm->$key = $ti->$key;
                    }
                    $returnscorm->link = "$CFG->wwwroot/mod/scorm/view.php?id=$ti->cmid";
                    $returnscorm->tags = array();
                    $returnscorms[] = $returnscorm;
                }
                $returnscorm->tags[] = $ti->tag;
                $scorms[$ti->id] = $returnscorm;
            }
        }
        return $returnscorms;
    }

    /**
     * Describes the get_tagged_scorms_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_scorms_by_course_returns() {
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
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'scorm'
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_glossaries_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_glossaries_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of glossaries tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the glossary details
     * @since Moodle 3.0
     */
    public static function get_tagged_glossaries_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnglossaries = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        g.id as id, g.name as name,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as glossarycontext, cm.id as cmid,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name='glossary'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $glossaries = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $glossaries)) {
                    $returnglossary = new StdClass();
                    $keys = array('id', 'name', 'coursename', 'courseshortname', 'courseid');
                    foreach ($keys as $key) {
                        $returnglossary->$key = $ti->$key;
                    }
                    $returnglossary->link = "$CFG->wwwroot/mod/glossary/view.php?id=$ti->cmid";
                    $returnglossary->tags = array();
                    $returnglossaries[] = $returnglossary;
                }
                $returnglossary->tags[] = $ti->tag;
                $glossaries[$ti->id] = $returnglossary;
            }
        }
        return $returnglossaries;
    }

    /**
     * Describes the get_tagged_glossaries_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_glossaries_by_course_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Glossary ID'),
                    'name' => new external_value(PARAM_TEXT, 'Glossary name'),
                    'coursename' => new external_value(PARAM_TEXT, 'Name of course'),
                    'courseshortname' => new external_value(PARAM_TEXT, 'Shortname of course'),
                    'courseid' => new external_value(PARAM_INT, 'Moodle id of course'),
                    'link' => new external_value(PARAM_TEXT, 'Link to glossary in Moodle'),
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'glossary'
            )
        );
    }

    /**
     * Describes the parameters for get_tagged_ltis_by_course
     *
     * @return external_external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_tagged_ltis_by_course_parameters() {
        return self::get_tagged_objects_by_course_parameters();
    }

    /**
     * Returns a list of ltis tagged at least once, for a given course.
     *
     * @param string $course a course shortname
     * @return array the lti details
     * @since Moodle 3.0
     */
    public static function get_tagged_ltis_by_course($course = '') {
        global $CFG, $DB, $USER;
        $returnltis = array();
        $params = self::validate_parameters(self::get_tagged_objects_by_course_parameters(), array('course' => $course));
        $course = $params['course'];
        if ($course != '') {
            $sql = "select
                        ti.id as taginstanceid,
                        l.id as id, l.name as name, l.typeid,
                        lt.name as ltitype,
                        c.id as courseid, c.fullname as coursename, c.shortname as courseshortname,
                        cx.id as lticontext, cm.id as cmid,
                        t.id as tagid, t.name as tag
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
                        c.shortname = ?
                        and m.name='lti'";
            $taginstances = $DB->get_records_sql($sql, array($course));
            $ltis = array();
            foreach ($taginstances as $ti) {
                if (! array_key_exists($ti->id, $ltis)) {
                    $returnlti = new StdClass();
                    $keys = array('id', 'name', 'ltitype', 'coursename', 'courseshortname', 'courseid');
                    foreach ($keys as $key) {
                        $returnlti->$key = $ti->$key;
                    }
                    $returnlti->link = "$CFG->wwwroot/mod/lti/view.php?id=$ti->cmid";
                    $returnlti->tags = array();
                    $returnltis[] = $returnlti;
                }
                $returnlti->tags[] = $ti->tag;
                $ltis[$ti->id] = $returnlti;
            }
        }
        return $returnltis;
    }

    /**
     * Describes the get_tagged_ltis_by_course return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_tagged_ltis_by_course_returns() {
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
                    'tags' => new external_multiple_structure(new external_value(PARAM_TAG, 'tag', VALUE_REQUIRED), 'List of tags'),
                ), 'lti'
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
                        VALUE_REQUIRED, '', NULL_NOT_ALLOWED), 'Array of tags', VALUE_DEFAULT, array()),
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
     * Describes the get_resources_by_tag return value.
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
                $returnassignment->link = "$CFG->wwwroot/mod/assign/view.php?id=$assignment->cmid";
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
