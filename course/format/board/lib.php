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

require_once($CFG->dirroot. '/course/format/topics/lib.php');

/**
 * format_board
 *
 */
class format_board extends format_topics {
    
    /**
     * get_section_name
     *
     */
    public function get_section_name($section) {
        global $COURSE;
        $course = course_get_format($COURSE->id)->get_course();
        $section = $this->get_section($section);
        if ((string)$section->name !== '') {
            return format_string($section->name, true, array('context' => context_course::instance($this->courseid)));
        } elseif ($course->showdefaultsectionname) {
            return $this->get_default_section_name($section);
        }
        return false;
    }

    /**
     * course_format_options
     *
     */
    public function course_format_options($foreditform = false) {
        global $PAGE;
        static $courseformatoptions = false;
        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');
            $courseformatoptions = array(
                'numsections' => array(
                    'default' => $courseconfig->numsections,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT,
                ),
                'coursedisplay' => array(
                    'default' => $courseconfig->coursedisplay,
                    'type' => PARAM_INT,
                ),
            );
            $max = $courseconfig->maxsections;
            if (!isset($max) || !is_numeric($max)) {
                $max = 52;
            }
            $courseformatoptions['designermode'] = array(
                'default' => get_config('format_board', 'designermode'),
                'type' => PARAM_INT,
            );
            $courseformatoptions['showdefaultsectionname'] = array(
                'default' => get_config('format_board', 'showdefaultsectionname'),
                'type' => PARAM_INT,
            );
            for ($i=1; $i<=4; $i++) {
                $courseformatoptions['widthcol'.$i] = array(
                    'default' => get_config('format_board', 'widthcol'.$i),
                    'type' => PARAM_INT,
                );
                $courseformatoptions['numsectionscol'.$i] = array(
                    'default' => get_config('format_board', 'numsectionscol'.$i),
                    'type' => PARAM_INT,
                );
            }
            for ($i = 1; $i <= $max; $i++) {
                $courseformatoptions['widthsection'.$i] = array(
                    'default' => get_config('format_board', 'widthsection'.$i),
                    'type' => PARAM_INT,
                );
                $courseformatoptions['heightsection'.$i] = array(
                    'default' => get_config('format_board', 'heightsection'.$i),
                    'type' => PARAM_INT,
                );
            }
            if ($PAGE->theme->name == 'board') {
                $courseformatoptions['logo'] = array(
                    'default' => get_config('format_board', 'logo'),
                    'type' => PARAM_CLEANFILE,
                );
                $courseformatoptions['height'] = array(
                    'default' => get_config('format_board', 'height'),
                    'type' => PARAM_TEXT,
                );
                $courseformatoptions['color'] = array(
                    'default' => get_config('format_board', 'color'),
                    'type' => PARAM_TEXT,
                );
                $courseformatoptions['contrast'] = array(
                    'default' => get_config('format_board', 'contrast'),
                    'type' => PARAM_TEXT,
                );
                $courseformatoptions['customcss'] = array(
                    'default' => get_config('format_board', 'customcss'),
                    'type' => PARAM_TEXT,
                );
            }
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $courseconfig = get_config('moodlecourse');
            $max = $courseconfig->maxsections;
            if (!isset($max) || !is_numeric($max)) {
                $max = 52;
            }
            $sectionmenu = array();
            for ($i = 0; $i <= $max; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit = array(
                'numsections' => array(
                    'label' => new lang_string('numberweeks'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
                        )
                    ),
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                )
            );
            $sectionmenu[0] = get_string('unlimited', 'format_board');
            $courseformatoptionsedit['designermode'] = array(
                'label' => get_string('designermode', 'format_board'),
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        0 => get_string('no', 'format_board'),
                        1 => get_string('yes', 'format_board'),
                    ),
                ),
            );
            $courseformatoptionsedit['showdefaultsectionname'] = array(
                'label' => get_string('showdefaultsectionname', 'format_board'),
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => get_string('yes', 'format_board'),
                        0 => get_string('no', 'format_board'),
                    ),
                ),
            );
            /*
            $courseformatoptionsedit['collapsetopics'] = array(
                'label' => get_string('collapsetopics', 'format_board'),
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('Yes'),
                        0 => new lang_string('No'),
                    ),
                ),
            );
            $courseformatoptionsedit['ignorecollapseintopics'] = array(
                'label' => get_string('ignorecollapseintopics', 'format_board'),
                'element_type' => 'text',
                'element_attributes' => '',
            );
            */
            for ($i = 1; $i <= 4; $i++) {
                $courseformatoptionsedit['widthcol'.$i] = array(
                    'label' => get_string('widthcol', 'format_board').' ('.$i.')',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            100 => '100%',
                            75 => '75%',
                            66 => '66%',
                            50 => '50%',
                            33 => '33%',
                            25 => '25%',
                        ),
                    ),
                );
                $courseformatoptionsedit['numsectionscol'.$i] = array(
                    'label' => get_string('numsectionscol', 'format_board').' ('.$i.')',
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                );
            }
            for ($i = 1; $i <= $max; $i++) {
                $courseformatoptionsedit['widthsection'.$i] = array(
                    'label' => get_string('widthsection', 'format_board').' ('.$i.')',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            100 => '100%',
                            75 => '75%',
                            66 => '66%',
                            50 => '50%',
                            33 => '33%',
                            25 => '25%',
                        ),
                    ),
                );
                $courseformatoptionsedit['heightsection'.$i] = array(
                    'label' => get_string('heightsection', 'format_board').' ('.$i.')',
                    'element_type' => 'text',
                );
            }
            if ($PAGE->theme->name == 'board') {
                $courseformatoptionsedit['color'] = array(
                    'label' => get_string('color', 'format_board'),
                    'element_type' => 'text',
                );
                $courseformatoptionsedit['contrast'] = array(
                    'label' => get_string('contrast', 'format_board'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => get_string('yes', 'format_board'),
                            0 => get_string('no', 'format_board'),
                        ),
                    ),
                );
                $courseformatoptionsedit['logo'] = array(
                    'label' => get_string('logo', 'format_board'),
                    'element_type' => 'filemanager',
                    'element_attributes' => array (null,
                        array (
                            'subdirs' => 0,
                            'maxfiles' => 1,
                            'accepted_types' => array ('.jpg', '.gif', '.png')
                        )
                    ),
                );
                $courseformatoptionsedit['height'] = array(
                    'label' => get_string('height', 'format_board'),
                    'element_type' => 'text',
                );
                $courseformatoptionsedit['customcss'] = array(
                    'label' => get_string('customcss', 'format_board'),
                    'element_type' => 'textarea',
                );
                
            }
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * update_course_format_options
     *
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB;
        $data = (array)$data;
        if ($oldcourse !== null) {
            $oldcourse = (array)$oldcourse;
            $options = $this->course_format_options();
            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse))
                        $data[$key] = $oldcourse[$key];
                    else if ($key === 'numsections') {
                        $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections} WHERE course = ?', array($this->courseid));
                        if ($maxsection)
                            $data['numsections'] = $maxsection;
                    }
                }
            }
        }
        if (isset($data['logo'])) {
            $context = context_course::instance($this->courseid);
            $saved = file_save_draft_area_files($data['logo'], $context->id, 'format_board', 'logo', 0, array('subdirs' => 0, 'maxfiles' => 1));    
        }
        $changed = $this->update_format_options($data);
        if ($changed && array_key_exists('numsections', $data)) {
            $numsections = (int)$data['numsections'];
            $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                        WHERE course = ?', array($this->courseid));
            for ($sectionnum = $maxsection; $sectionnum > $numsections; $sectionnum--)
                if (!$this->delete_section($sectionnum, false))
                    break;
        }
        return $changed;
    }

}

/**
 * format_board_pluginfile
 *
 */
function format_board_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($context->contextlevel != CONTEXT_COURSE)
        return false;
    require_login();
    if ($filearea != 'logo')
        return false;
    $itemid = (int)array_shift($args);
    if ($itemid != 0)
        return false;
    $fs = get_file_storage();
    $filename = array_pop($args);
    $filepath = empty($args) ? '/' : '/'.implode('/', $args).'/';
    $file = $fs->get_file($context->id, 'format_board', $filearea, $itemid, $filepath, $filename);
    if (!$file)
        return false;
    send_stored_file($file, 0, 0, $forcedownload, $options);
}
