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
require_once($CFG->dirroot . '/course/format/medschool_topics/lib.php');

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
					$section_icon = 'star';
                }
            }
            $title = html_writer::span('', 'fa fa-' . $section_icon) . "\n" . $title;
    	}
    	return $title;
    }

    protected function get_section($fmt, $modinfo, $number) {
        $section = new stdClass();
        $section->number = $number;
        $section->info = $modinfo->get_section_info($number, IGNORE_MISSING);
        // invalid section number
        if (is_null($section->info)) {
            return null;
        }
        $section->options = $fmt->get_format_options($section->info);
        $section->icon = $section->options['section_icon'];
        $section->name = $section->options['nav_name'];
        $section->is_subsection = (bool) $section->options['section_sub'];
        if ($section->icon === '' || ctype_space($section->icon)) {
            switch($number) {
            case 1:
                $section->icon = 'info-circle';
                break;
            case 2:
                $section->icon = 'check-square';
                break;
            default:
                $section->icon = 'star';
            }
        }
        return $section;
    }

    protected function get_next_section($fmt, $modinfo, $section) {
        for(
            $next = $this->get_section($fmt, $modinfo, $section->number + 1);
            !(is_null($next) || $this->show_section($next));
            $next = $this->get_section($fmt, $modinfo, $next->number + 1)
        );
        return $next;
    }
        
    protected function show_section($section) {
        $showsection = (
            $section->options['section_hide'] === FORMAT_MEDTOPICS_SHOW ||
            (
                $section->info->uservisible && (
                    $section->options['section_hide'] !== FORMAT_MEDTOPICS_HIDE
                )
            )
        );
        return $showsection;
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

        $section0 = $section;
        $course_modinfo = get_fast_modinfo($course);
        $fmt = course_get_format($course->id);

        // This (get_course) merges format options into course object,
        // so no need to call get_format_options later or maintain separate variable
        $course = new course_in_list($fmt->get_course());
        $num_sections = $course->numsections;
        $single_section_link = $course->navigationsectionlink;
        $course_url = $CFG->wwwroot.'/course/view.php?id='.$course->id;
        $banner_url = $CFG->wwwroot . "/course/format/medschool_topics/pix/Header.jpg";
 
 
        $navbar = '';
        if ($course->navigationbardisplay) {
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
           
            $section = $this->get_section($fmt, $course_modinfo, 1);
            for (
                $section = $this->get_section($fmt, $course_modinfo, 1);
                !is_null($section) && $section->number <= $num_sections;
                $section = $next_section
            ) {
                $next_section = $this->get_next_section($fmt, $course_modinfo, $section);
                if (!$this->show_section($section)) {
                    continue;
                }
                if ($single_section_link !== 0 ) {
                    $link_character = '&';
                    $link_character_dash = '=';
                } else {
                    $link_character = '#';
                    $link_character_dash = '-';
                }

                if ($section->name === '') {
                    $section->name = get_section_name($course, $section->info);
                }

                if (!$section->is_subsection) {
                    if (!$next_section->is_subsection) {
                        $sectionlinks[] = html_writer::start_span('dropdown-btn btn-medtopics').
                            html_writer::tag(
                                'a',
                                html_writer::span('', 'fa fa-' . $icon_size . 'x fa-fw fa-' . $section->icon) .
                                html_writer::span($section->name),
                                [
                                    'href' => $course_url . $link_character .'section' .$link_character_dash  . $section->number,
                                    'class' => 'btn btn-medtopics',
                                    'role' => 'button'
                                ]
                            ).html_writer::end_span();
                    } else {
                        if($single_section_link !== 0 ) {
                            $link_character = '&';
                            $link_character_dash = '=';
                        } else {
                            $link_character = '#';
                            $link_character_dash = '-';
                        }
                            
                        $sectionlinks[] = html_writer::start_span('dropdown-btn btn-medtopics').
                            html_writer::tag(
                                'a',
                                html_writer::span('', 'fa fa-' . $icon_size . 'x fa-fw fa-' . $section->icon) .
                                html_writer::start_span().$section->name.
                                html_writer::tag('i','',['class'=>'fa fa-caret-down']).
                                html_writer::end_span()
                                ,
                                [
                                    'href' => $course_url . $link_character .'section' .$link_character_dash . $section->number,
                                    'class' => 'btn btn-medtopics',
                                    'role' => 'button'
                                ]
                            );
                            
                        $sectionlinks[] = html_writer::start_span('dropdown-btn-content btn-medtopics').
                            html_writer::tag(
                                'a',
                                html_writer::span('', 'btn-medtopics-sub-icon fa fa-fw fa-' . $section->icon).
                                html_writer::span($section->name, 'btn-medtopics-sub-name'),
                                [
                                    'href' => $course_url . $link_character .'section' . $link_character_dash . $section->number,
                                    'class' => 'sub-btn btn-medtopics',
                                    'role' => 'button'
                                ]
                            );

                        for(
                            $subsection = $next_section;
                            !is_null($subsection) && $subsection->is_subsection;
                            $subsection = $this->get_next_section($fmt, $course_modinfo, $subsection)
                        ) {
                            if ($subsection->name === '') {
                                $subsection->name = get_section_name($course, $subsection->number);
                            }
                            if ($this->show_section($subsection)) {
                                $sectionlinks[] = html_writer::tag(
                                    'a',
                                    html_writer::span('', 'btn-medtopics-sub-icon fa fa-fw fa-' . $subsection->icon) .
                                    html_writer::span($subsection->name, 'btn-medtopics-sub-name'),
                                    [
                                        'href' => $course_url . $link_character . 'section' . $link_character_dash . $subsection->number,
                                        'class' => 'sub-btn btn-medtopics',
                                        'role' => 'button'
                                    ]
                                );
                            }
                        }
                        $sectionlinks[] = html_writer::end_span().html_writer::end_span();
                    }
                }
                $navbar = html_writer::tag('nolink', html_writer::div(join("\n", $sectionlinks), 'btn-toolbar iconbar-solid'));
            }
        }

        if ($course->coursebannerdisplay) {
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
            if($course->coursebannerheight > 0){
                $attrs = [ 'style' => 'height:80px; background-image:url(' . $banner_url . ');' ];
            }
            else{
                $attrs = [ 'style' => 'background-image:url(' . $banner_url . ');' ];
            }
            $section0->summary = html_writer::span('', 'header-image', $attrs) . $navbar . $section0->summary;
        }
        else {
            $section0->summary = html_writer::span('', 'no-header-image') . $navbar . $section0->summary;
        }
        
        return parent::section_header($section0, $course, $onsectionpage, $sectionreturn);
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

        $controls = array();
        if ($section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $markedthistopic = get_string('markedthistopic');
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                               'name' => $highlightoff,
                                               'pixattr' => array('class' => '', 'alt' => $markedthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markedthistopic,
                                                   'data-action' => 'removemarker'));
            } else {
                $url->param('marker', $section->section);
                $markthistopic = get_string('markthistopic');
                $highlight = get_string('highlight');
                $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                               'name' => $highlight,
                                               'pixattr' => array('class' => '', 'alt' => $markthistopic),
                                               'attr' => array('class' => 'editing_highlight', 'title' => $markthistopic,
                                                   'data-action' => 'setmarker'));
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
