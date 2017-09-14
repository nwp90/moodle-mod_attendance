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
 * Renderer for outputting the topics course format.
 *
 * @package format_medschool_topics
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

/**
 * Basic renderer for topics format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_medschool_topics_renderer extends format_section_renderer_base {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_medschool_topics_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
        // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return html_writer::start_tag('ul', array('class' => 'medschool_topics'));
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
    	$fmt = course_get_format($course);
    	$title = $this->render($fmt->inplace_editable_render_section_name($section));
		$course_format_options = $fmt->get_format_options();
    	
    	if ($section->section != 0) {
            $section_number = $section->section;
            $section_icon = $fmt->get_format_options($section_number)['section_icon'];
    	
            if ($section_icon === '') {
                if ($section_number === 1) {
					$section_icon = 'info-circle';
                }
                else if ($section_number === 2) {
					$section_icon = 'check-square';
                }
                else {
					$section_icon = 'star-o';
                }
            }
            $title = html_writer::span('', 'fa fa-' . $section_icon) . $title;
    	}
    	return $title;
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     *
     * In this case:
     *  - Checks if the section is the first section (section 0) and adds a banner image if it is.
     *  - Banner image is either default image from this course format (Header.jpg) or an image
     *    called Header.jpg from the course summary files. 
     *  - Also automatically adds a navigation bar to section0 based on the settings of
     *    each subsequent section - icons resize based on number of sections in course.
     */
    function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        global $CFG, $DB;

        require_once($CFG->libdir. '/coursecatlib.php');

        if ($section->section !== 0) {
            return parent::section_header($section, $course, $onsectionpage, $sectionreturn);
        }

        $course_modinfo = get_fast_modinfo($course); 
        $course = new course_in_list($course);
        $fmt = course_get_format($course->id);
        $course_format_options = $fmt->get_format_options();
        $num_sections = $course_format_options['numsections'];

        $banner_url = $CFG->wwwroot . "/course/format/medschool_topics/pix/Header.jpg";

        $navbar = '';
        if ($course_format_options['navigationbardisplay']) {
            switch(true) {
            case $num_sections <= 6:
                $icon_size = 4;
                break;
            case $num_sections <= 9:
                $icon_size = 3;
                break;
            default:
                $icon_size = 2;
            }

            $sectionlinks = [];
            foreach (range(1, $num_sections) as $section_number) {
                $current_section = $course_modinfo->get_section_info($section_number);
                $section_format_options = $fmt->get_format_options($current_section);
                $section_icon = $section_format_options['section_icon'];
                $section_name = $section_format_options['nav_name'];

                if ($section_icon === '') {
                    switch($section_number) {
                    case 1:
                        $section_icon = 'info-circle';
                        break;
                    case 2:
                        $section_icon = 'check-square';
                        break;
                    default:
                        $section_icon = 'star-o';
                    }
                }

                if ($section_name === '') {
                    $section_name = get_section_name($course, $current_section);
                }

                if ($current_section->visible) {
                    $sectionlinks[] = html_writer::link(
                        '#section-' . $section_number,
                        html_writer::span('', 'fa fa-' . $icon_size . 'x fa-fw fa-' . $section_icon) . 
                        html_writer::span($section_name)

                    );
                }
            }
            $navbar = html_writer::div(join("\n", $sectionlinks), 'iconbar-solid');
        }

        if ($course_format_options['coursebannerdisplay']) {
            foreach ($course->get_course_overviewfiles() as $file) {
                $isimage = $file->is_valid_image();
                $isHeader = !strcmp($file->get_filename(),'Header.jpg');
                if ($isimage && $isHeader) {
                    $banner_url = file_encode_url(
                        $CFG->wwwroot . '/pluginfile.php',
                        '/' . $file->get_contextid() .
                        '/' . $file->get_component() .
                        '/' . $file->get_filearea() . $file->get_filepath() . $file->get_filename(),
                        !$isimage
                    );
                    break;
                }
            }
            $attrs = [ 'style' => 'background-image:url(' . $banner_url . ');' ];
            $section->summary = html_writer::span('', 'header-image', $attrs) . $navbar . $section->summary;
        }
        else {
            $section->summary = html_writer::span('', 'no-header-image') . $navbar . $section->summary;
        }

        return parent::section_header($section, $course, $onsectionpage, $sectionreturn);
    }

    /**
     * Generate the section title to be displayed on the section page, without a link
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $coursecontext = context_course::instance($course->id);

        if ($onsectionpage) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $isstealth = $section->section > $course->numsections;
        $controls = array();
        if (!$isstealth && $section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $markedthistopic = get_string('markedthistopic');
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                               'name' => $highlightoff,
                                               'pixattr' => array('class' => '', 'alt' => $markedthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markedthistopic));
            } else {
                $url->param('marker', $section->section);
                $markthistopic = get_string('markthistopic');
                $highlight = get_string('highlight');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                               'name' => $highlight,
                                               'pixattr' => array('class' => '', 'alt' => $markthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markthistopic));
            }
        }

        $parentcontrols = parent::section_edit_control_items($course, $section, $onsectionpage);

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $merged = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            return $merged;
        } else {
            return array_merge($controls, $parentcontrols);
        }
    }
}
