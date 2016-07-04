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
 * board
 *
 * @package    format_board
 * @author     Rodrigo Brandão (rodrigobrandao.com.br)
 * @copyright  2016 Rodrigo Brandão
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/format/topics/renderer.php');

/**
 * format_board_renderer
 *
 */
class format_board_renderer extends format_topics_renderer {
    
    /**
     * start_section_list
     *
     */
    protected function start_section_list($i = 0) {
        return html_writer::start_tag('ul', array('class' => 'board', 'id' => 'col-'.$i));
    }

    /**
     * print_multiple_section_page
     *
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;
        $modinfo = get_fast_modinfo($course);
        $course = course_get_format($course)->get_course();
        /* ini - format_board */
        $css = '';
        for ($i = 0; $i <= 4; $i++) {
            eval('@$width = (int)$course->widthcol'.$i.';');
            $width = $width ? ($width != 33 ? ($width != 66 ? $width : '66.666666') : '33.333333') : 100;
            $css .= '#col-'.$i.' { width: '.$width.'%; }';
        }
        for ($i = 0, $t = count($modinfo->get_section_info_all()); $i < $t; $i++) {
            @$width = (int)$course->widthsection{$i};
            @$height = (int)$course->heightsection{$i};
            $width = $width ? ($width != 33 ? ($width != 66 ? $width : '66.666666') : '33.333333') : 100;
            $height = $height ? $height : 1;
            $background = ($course->designermode) ? 'background: rgb('.rand(0, 255).', '.rand(0, 255).', '.rand(0, 255).');' : '';
            $css .= '#section-'.$i.' { width: '.$width.'%; min-height: '.$height.'px; '.$background.' }';
        }
        echo html_writer::tag('style', $css);
        /* end - format_board */
        $context = context_course::instance($course->id);
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');
        echo $this->course_activity_clipboard($course, 0);
        echo $this->start_section_list();
        /* ini - format_board */
        $cont = 1;
        $currentcol = 1;
        $numtopicscol[1] = $course->numsectionscol1;
        $numtopicscol[2] = $course->numsectionscol2;
        $numtopicscol[3] = $course->numsectionscol3;
        $numtopicscol[4] = $course->numsectionscol4;
        /* end - format_board */
        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                /* 0-section is displayed a little different then the others */
                if ($thissection->summary or !empty($modinfo->sections[0]) or $PAGE->user_is_editing()) {
                    echo $this->section_header($thissection, $course, false, 0);
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, 0, 0);
                    echo $this->section_footer();
                }
                /* ini - format_board */
                echo $this->end_section_list();
                echo $this->start_section_list(1);
                /* end - format_board */
                continue;
            }
            if ($section > $course->numsections) {
                continue;
            }
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));
            if (!$showsection) {
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }
                continue;
            }
            if (!$PAGE->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                echo $this->section_summary($thissection, $course, null);
            } else {
                echo $this->section_header($thissection, $course, false, 0);
                if ($thissection->uservisible) {
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);
                }
                echo $this->section_footer();
            }
            /* ini - format_board */
            if ($cont == @$numtopicscol[$currentcol] && @$numtopicscol[$currentcol] != 0) {
                $cont = 0;
                $currentcol++;
                echo $this->end_section_list();
                echo $this->start_section_list($currentcol);
            }
            $cont++;
            /* end - format_board */
        }
        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }
            echo $this->end_section_list();
            echo html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => 'mdl-right'));
            $straddsection = get_string('increasesections', 'moodle');
            $url = new moodle_url('/course/changenumsections.php',
                array('courseid' => $course->id,
                      'increase' => true,
                      'sesskey' => sesskey()));
            $icon = $this->output->pix_icon('t/switch_plus', $straddsection);
            echo html_writer::link($url, $icon.get_accesshide($straddsection), array('class' => 'increase-sections'));
            if ($course->numsections > 0) {
                $strremovesection = get_string('reducesections', 'moodle');
                $url = new moodle_url('/course/changenumsections.php',
                    array('courseid' => $course->id,
                          'increase' => false,
                          'sesskey' => sesskey()));
                $icon = $this->output->pix_icon('t/switch_minus', $strremovesection);
                echo html_writer::link($url, $icon.get_accesshide($strremovesection), array('class' => 'reduce-sections'));
            }
            echo html_writer::end_tag('div');
        } else {
            echo $this->end_section_list();
        }
    }

}
