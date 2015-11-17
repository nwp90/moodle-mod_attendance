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
 * This file defines the quiz cbmgrades report class.
 * Derived by Tony Gardner-Medwin from the responses plugin (copyright 2006 Jean-Michel Vedrine)
 * @package   quiz_cbmgrades
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport.php');
require_once($CFG->dirroot . '/mod/quiz/report/cbmgrades/cbmgrades_options.php');
require_once($CFG->dirroot . '/mod/quiz/report/cbmgrades/cbmgrades_form.php');
require_once($CFG->dirroot . '/mod/quiz/report/cbmgrades/cbmgrades_table.php');


/**
 * Quiz report subclass for the cbmgrades report.
 *
 * This report lists some combination of
 *  * what question each student saw (this makes sense if random questions were used).
 *  * the response they gave,
 *  * and what the right answer is.
 *
 * Like the overview report, there are options for showing students with/without
 * attempts, and for deleting selected attempts.
 *
 * @copyright 1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_cbmgrades_report extends quiz_attempts_report {

    /**
     * Add all the grade and feedback columns, if applicable, to the $columns
     * and $headers arrays.
     * @param object $quiz the quiz settings.
     * @param bool $usercanseegrades whether the user is allowed to see grades for this quiz.
     * @param array $columns the list of columns. Added to.
     * @param array $headers the columns headings. Added to.
     * @param bool $includefeedback whether to include the feedbacktext columns
     */
    protected function add_grade_columns($quiz, $usercanseegrades, &$columns, &$headers, $includefeedback = true) {
        if ($usercanseegrades) {
            $columns[] = 'sumgrades';
            $headers[] = get_string('grade', 'quiz_cbmgrades') . '/<br>' . quiz_format_grade($quiz, $quiz->grade);
            $columns[] = 'resp_num';
            $headers[] = get_string('responses', 'quiz_cbmgrades');
            $columns[] = 'marks';
            $headers[] = get_string('marks', 'quiz_cbmgrades');
            if ($this->showchosenrs) {
                // adjust & show scores based on chosen responses in this column
                $alert = '<br> ** ';
                $columns[] = 'cbm_avchosen';
            }
            else {
                $alert = '';
                $columns[] = 'cbm_av';
            }
            $headers[] = get_string('cbm_av', 'quiz_cbmgrades') . $alert;
            $columns[] = 'accy';
            $headers[] = get_string('accy', 'quiz_cbmgrades') . $alert;
            $columns[] = 'cbm_bonus';
            $headers[] = get_string('cbm_bonus', 'quiz_cbmgrades') . $alert;
            $columns[] = 'cbm_accy';
            $headers[] = get_string('cbm_accy', 'quiz_cbmgrades') . $alert;
            if (!$this->showchosenrs) {
                $columns[] = 'cbm_grade';
                $headers[] = get_string('cbmgrade', 'quiz_cbmgrades') . '/<br>' . quiz_format_grade($quiz, $quiz->grade);
            }
        }

        if ($includefeedback && quiz_has_feedback($quiz)) {
            $columns[] = 'feedbacktext';
            $headers[] = get_string('feedback', 'quiz');
        }
    }

    public function display($quiz, $cm, $course) {
        global $OUTPUT;

        list($currentgroup, $students, $groupstudents, $allowed) =
                $this->init('cbmgrades', 'quiz_cbmgrades_settings_form', $quiz, $cm, $course);
        $options = new quiz_cbmgrades_options('cbmgrades', $quiz, $cm, $course);

        if ($fromform = $this->form->get_data()) {
            $options->process_settings_from_form($fromform);

        } else {
            $options->process_settings_from_params();
        }

        $this->form->set_data($options->get_initial_form_data());

        if ($options->attempts == self::ALL_WITH) {
            // This option is only available to users who can access all groups in
            // groups mode, so setting allowed to empty (which means all quiz attempts
            // are accessible, is not a security porblem.
            $allowed = array();
        }

        // Load the required questions.
        $questions = quiz_report_get_significant_questions($quiz);

        // Prepare for downloading, if applicable.
        $courseshortname = format_string($course->shortname, true,
                array('context' => context_course::instance($course->id)));
        $table = new quiz_cbmgrades_table($quiz, $this->context, $this->qmsubselect,
                $options, $groupstudents, $students, $questions, $options->get_url());
        $filename = quiz_report_download_filename(get_string('cbmgradesfilename', 'quiz_cbmgrades'),
                $courseshortname, $quiz->name);
        $table->is_downloading($options->download, $filename,
                $courseshortname . ' ' . format_string($quiz->name, true));
        if ($table->is_downloading()) {
            raise_memory_limit(MEMORY_EXTRA);
        }

        $this->process_actions($quiz, $cm, $currentgroup, $groupstudents, $allowed, $options->get_url());

        $this->showqdata = $options->showqdata;
        $this->showchosenrs = $options->showchosenrs;

        // Start output.
        if (!$table->is_downloading()) {
            // Only print headers if not asked to download data.
            $this->print_header_and_tabs($cm, $course, $quiz, $this->mode);
        }

        if ($groupmode = groups_get_activity_groupmode($cm)) {
            // Groups are being used, so output the group selector if we are not downloading.
            if (!$table->is_downloading()) {
                groups_print_activity_menu($cm, $options->get_url());
            }
        }

        // Print information on the number of existing attempts.
        if (!$table->is_downloading()) {
            // Do not print notices when downloading.
            if ($strattemptnum = quiz_num_attempt_summary($quiz, $cm, true, $currentgroup)) {
                echo '<div class="quizattemptcounts">' . $strattemptnum . '</div>';
            }
        }

        $hasquestions = quiz_has_questions($quiz->id);
        if (!$table->is_downloading()) {
            if (!$hasquestions) {
                echo quiz_no_questions_message($quiz, $cm, $this->context);
            } else if (!$students) {
                echo $OUTPUT->notification(get_string('nostudentsyet'));
            } else if ($currentgroup && !$groupstudents) {
                echo $OUTPUT->notification(get_string('nostudentsingroup'));
            }

            // Print the display options.
            $this->form->display();
        }

        $hasstudents = $students && (!$currentgroup || $groupstudents);
        if ($hasquestions && ($hasstudents || $options->attempts == self::ALL_WITH)) {

            list($fields, $from, $where, $params) = $table->base_sql($allowed);

            $table->set_count_sql("SELECT COUNT(1) FROM $from WHERE $where", $params);

            $table->set_sql($fields, $from, $where, $params);

            if (!$table->is_downloading()) {
                // Print information on the grading method.
                if ($strattempthighlight = quiz_report_highlighting_grading_method(
                        $quiz, $this->qmsubselect, $options->onlygraded)) {
                    echo '<div>' . $strattempthighlight . '</div>';
                }
                echo '<div><b>' . get_string( ($this->showchosenrs ? 'scoreschosenrs' : 'scoreswhole'), 'quiz_cbmgrades') . '</b> &nbsp; ';
                echo html_writer::tag('i', get_string('cbmexplanations', 'quiz_cbmgrades') . $OUTPUT->help_icon('cbmgrades', 'quiz_cbmgrades')) . '</div>';
            }

            // Define table columns.
            $columns = array();
            $headers = array();

            if (!$table->is_downloading() && $options->checkboxcolumn) {
                $columns[] = 'checkbox';
                $headers[] = null;
            }

            $this->add_user_columns($table, $columns, $headers);
            $this->add_time_columns($columns, $headers);

            $this->add_grade_columns($quiz, $options->usercanseegrades, $columns, $headers);

            if ($this->showqdata) { // show data for each Q
                        foreach ($questions as $id => $question) {
                            if ($options->showcbmgrades) {
                                $columns[] = 'response' . $id;
                                if ($table->is_downloading()) {
                                    $x=get_string('qx', 'quiz_cbmgrades', $question->number);
                                }
                                else $x=get_string('responsex', 'quiz_cbmgrades', $question->number);
                                if($question->maxmark!=1) {
                                    $x .= '/' . round($question->maxmark,1);
                                }
                                $headers[] = $x;
                            }
                        }
            }
            $table->define_columns($columns);
            $table->define_headers($headers);
            $table->sortable(true, 'uniqueid');

            // Set up the table.
            $table->define_baseurl($options->get_url());

            $this->configure_user_columns($table);

            $table->no_sorting('feedbacktext');
            $table->no_sorting('resp_num');
            $table->no_sorting('marks');
            $table->no_sorting('cbm_av');
            $table->no_sorting('cbm_avchosen');
            $table->no_sorting('accy');
            $table->no_sorting('cbm_bonus');
            $table->no_sorting('cbm_accy');
            $table->no_sorting('cbm_grade');

            $table->column_class('sumgrades', 'bold');

            $table->set_attribute('id', 'attempts');

            $table->collapsible(true);

            $table->out($options->pagesize, true);
        }
        return true;
    }
}
