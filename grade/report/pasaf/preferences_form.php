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
 * Form for grader report preferences
 *
 * @package    gradereport_grader
 * @copyright  2009 Nicolas Connault
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/formslib.php');

/**
 * First implementation of the preferences in the form of a moodleform.
 * TODO add "reset to site defaults" button
 */
class pasaf_report_preferences_form extends moodleform {
    function definition() {
        global $USER, $CFG, $DB;

        $mform    =& $this->_form;
        $course   = $this->_customdata['course'];

        $context = context_course::instance($course->id);

        $canviewhidden = has_capability('moodle/grade:viewhidden', $context);

        $checkbox_default = array(GRADE_REPORT_PASAF_PREFERENCE_DEFAULT => '*default*', 0 => get_string('collapse', 'gradereport_pasaf'), 1 => get_string('expand','gradereport_pasaf'));

        $advanced = array();
/// form definition with preferences defaults
//--------------------------------------------------------------------------------
        $preferences = array();

        // Initialise the preferences arrays with grade:manage capabilities
        if (has_capability('moodle/grade:manage', $context)) {

            $preferences['prefshow'] = array();

            //$preferences['prefshow']['showcalculations'] = $checkbox_default;

           //get all the grade categories in the grade book and say if they're shown or not
            //perhaps the better thing is to store a value in the db for category show in pasaf?

          $categories = $DB->get_records('grade_categories',array('courseid'=>$course->id));
          //print_r($categories);
            foreach ($categories as $category) {
                if($category->hidden==0){
                    $showcategoryname = 'showcategory_category'.$category->id.'_course'.$course->id;
                    //new lang_string($showcategoryname, 'gradereport_pasaf', $category->fullname, 'en');
                    //$string[$showcategoryname] = $category->fullname;
                    //echo get_string('showcategory'.$category->id, 'gradereport_pasaf');

                    //$showcategoryname = 'showcategory_course'.$course->id.'_category'.$category->id;
                    $preferences['prefshow'][$showcategoryname] = $checkbox_default;
                    //$preferences['prefshow'][$category->id][$category->fullname] = $checkbox_default;
                }
                }

            }


        foreach ($preferences as $group => $prefs) {
            $mform->addElement('header', $group, get_string($group, 'grades'));
            $mform->setExpanded($group);

            foreach ($prefs as $pref => $type) {
                // Detect and process dynamically numbered preferences
                if (preg_match('/([^[0-9]+)([0-9]+)/', $pref, $matches)) {
                    $lang_string = $matches[1];
                    $number = ' ' . $matches[2];
                } else {
                    $lang_string = $pref;
                    $number = null;
                }

                $full_pref  = 'gradereport_pasaf_' . $pref;

                $pref_value = get_user_preferences($full_pref);
                echo $pref_value;
                //if($pref_value == null){
                    //echo 'doing anythinng?';
                 // set_user_preference($full_pref, '1');
                //}
                //echo 'Printing the $perf_value->'.$pref_value.'<-';
                
                $options = null;
                if (is_array($type)) {
                    $options = $type;
                    $type = 'select';
                    // MDL-11478
                    // get default aggregationposition from grade_settings
                    $course_value = null;
                    $default = '';
                    //print_r($CFG->{$full_pref});
                    if (!empty($CFG->{$full_pref})) {
                        $course_value = grade_get_setting($course->id, $pref, $CFG->{$full_pref});
                    }
                
                   /*if ($pref == 'aggregationposition') {
                        if (!empty($options[$course_value])) {
                            echo 'here 1';
                            $default = $options[$course_value];
                        } else {
                            $default = $options[$CFG->grade_aggregationposition];
                            echo 'here 2';
                        }
                    } elseif (isset($options[$CFG->{$full_pref}])) {
                        //echo 'here 3';
                        $default = $options[$CFG->{$full_pref}];
                    } else {
                        //echo 'here 4';
                        $default = '';
                    }*/
                } /*else {
                    echo 'here 5';
                    $default = $CFG->$full_pref;
                }*/
              

                // Replace the '*default*' value with the site default language string - 'default' might collide with custom language packs
                if (!is_null($options) AND isset($options[GRADE_REPORT_PASAF_PREFERENCE_DEFAULT]) && $options[GRADE_REPORT_PASAF_PREFERENCE_DEFAULT] == '*default*') {
                    $options[GRADE_REPORT_PASAF_PREFERENCE_DEFAULT] = get_string('reportdefault', 'gradereport_pasaf', $default);
                }

                $label = $categories[(int)$number]->fullname;
                //$label = get_string($lang_string, 'gradereport_pasaf') . $number;

                $mform->addElement($type, $full_pref, $label, $options);
                if ($lang_string != 'showuserimage') {
                    $mform->addHelpButton($full_pref, $lang_string, 'gradereport_pasaf');
                }
                $mform->setDefault($full_pref, $pref_value);
                $mform->setType($full_pref, PARAM_ALPHANUM);
            }
       }

        /*foreach($advanced as $name) {
            $mform->setAdvanced('grade_report_pasaf'.$name);
        }*/

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $course->id);

        $this->add_action_buttons();
    }

/// perform some extra moodle validation
    function validation($data, $files) {
        return parent::validation($data, $files);
    }
}

