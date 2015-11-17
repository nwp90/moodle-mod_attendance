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
 * This file defines the quiz cbmgrades table.
 * v1.0.1 (15/11/2013) changes to correspond to those in responses_table.php for 2.6 release
 * Derived by Tony Gardner-Medwin from the responses plugin (copyright 2008 Jean-Michel Vedrine)
 * @package   quiz_cbmgrades
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_table.php');


/**
 * This is a table subclass for displaying the quiz cbmgrades report.
 * Derived from the responses plugin (@copyright 2008 Jean-Michel Vedrine)
 * @copyright 2013
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_cbmgrades_table extends quiz_attempts_report_table {

    /**
     * Constructor
     * @param object $quiz
     * @param context $context
     * @param string $qmsubselect
     * @param quiz_cbmgrades_options $options
     * @param array $groupstudents
     * @param array $students
     * @param array $questions
     * @param moodle_url $reporturl
     */
    public function __construct($quiz, $context, $qmsubselect, quiz_cbmgrades_options $options,
            $groupstudents, $students, $questions, $reporturl) {
        parent::__construct('mod-quiz-report-cbmgrades-report', $quiz, $context,
                $qmsubselect, $options, $groupstudents, $students, $questions, $reporturl);
    }

    protected function load_question_latest_steps(qubaid_condition $qubaids = null) {
        if ($qubaids === null) {
            $qubaids = $this->get_qubaids_condition();
        }
        $fields =  "qas.id,
            qa.id AS questionattemptid,
            qa.questionusageid,
            qa.slot,
            qa.behaviour,
            qa.questionid,
            qa.variant,
            qa.maxmark,
            qa.minfraction,
            qa.maxfraction,
            qa.flagged,
            qa.questionsummary,
            qa.rightanswer,
            qa.responsesummary,
            qa.timemodified,
            qas.id AS attemptstepid,
            qas.sequencenumber,
            qas.state,
            qas.fraction,
            qas.timecreated,
            qas.userid,
            (SELECT value FROM {question_attempt_step_data} qasd WHERE qasd.name = '-_rawfraction' AND attemptstepid = (
                    SELECT MAX(iqas.id)
                      FROM {question_attempt_steps} iqas
                      JOIN {question_attempt_step_data} iqasd ON iqasd.attemptstepid = iqas.id
                     WHERE iqas.questionattemptid = qa.id
                       AND iqasd.name = '-_rawfraction'
            )) AS rawfraction";

        $dm = new question_engine_data_mapper();
        $latesstepdata = $dm->load_questions_usages_latest_steps(
                $qubaids, array_keys($this->questions), $fields);

        $lateststeps = array();
        foreach ($latesstepdata as $step) {
            $lateststeps[$step->questionusageid][$step->slot] = $step;
        }
        return $lateststeps;
    }

    public function build_table() {
        if (!$this->rawdata) {
            return;
        }
        $this->strtimeformat = str_replace(',', ' ', get_string('strftimedatetime'));
        parent::build_table();
    }

    var $num_rs, $num_all, $sumwts, $sumcorr, $sumcbm;

    public function col_sumgrades($attempt) {
        $this->calc_scores($attempt); // Must be in 1st column
        $grade = round($this->quiz->grade * $this->cbm_av, 2);
        if ($this->is_downloading()) {
            return $grade;
        }
        $gradehtml = '<a href="review.php?q=' . $this->quiz->id . '&amp;attempt=' .
                $attempt->attempt . '">' . $grade . '</a>';
        return $gradehtml;
    }

    public function col_resp_num($attempt) {
        return $this->num_rs;
    }

    public function col_marks($attempt) {
        return $grade=round($this->sumcbm,1);
    }

    public function col_cbm_av($attempt) {
        if (!$this->iscbm) {
            return '-';
        }
        return round($this->cbm_av,2);
    }

    public function col_cbm_avchosen($attempt) {
        // NB must run before accy, cbm_bonus, cbm_accy when using chosen Rs
        $this->cbm_av = $this->sumcbm / $this->sumwts;
        $this->accy = $this->sumcorr / $this->sumwts;
        $m=max($this->accy, -2+4*$this->accy, -6+9*$this->accy);
        $this->bonus = 0.1 * ($this->cbm_av - $m);
        if (!$this->iscbm) {
            return '-';
        }
        return round($this->cbm_av,2);
    }

    public function col_accy($attempt) {
        return round(100 * $this->accy,1) . '%';
    }

    public function col_cbm_bonus($attempt) {
        if (!$this->iscbm) {
            return '-';
        }
        return round(100 * $this->bonus,1) . '%';
    }

    public function col_cbm_accy($attempt) {
        if (!$this->iscbm) {
            return '-';
        }
        return round(100 * ($this->accy + $this->bonus),1) . '%';
    }

    public function col_cbm_grade($attempt) {
        if (!$this->iscbm) {
            return '-';
        }
        return round($this->quiz->grade * ($this->accy + $this->bonus),2);
    }

    public function calc_scores($attempt) {
        $this->num_rs=0;
        $this->num_all=0;
        $this->sumwts=0;
        $this->sumcorr=0;
        $this->sumcbm=0;
        if ($attempt->usageid == 0) return;
        $this->iscbm=false;
        foreach($this->questions as $question) {
            $slot = $question->slot;
            if (!isset($this->lateststeps[$attempt->usageid][$slot])) {
                continue;
            }
            $stepdata = $this->lateststeps[$attempt->usageid][$slot];
            $this->num_all += 1;
            if (!is_null($stepdata->responsesummary)) {
                if (strpos($stepdata->responsesummary,'['.substr(get_string('certaintyshort1','qbehaviour_deferredcbm'),0,2))>0) {
                    $this->iscbm=true;
                }
                $this->num_rs += 1;
                $this->sumwts += $stepdata->maxmark;
                $this->sumcbm += $stepdata->maxmark * $stepdata->fraction;
                if ($stepdata->state == 'gradedright') {
                    $f=1;
                }
                else if ($stepdata->state == 'gradedpartial') {
                    if (array_key_exists('rawfraction',$stepdata)) {
                        $f=$stepdata->rawfraction;
                    }
                    else {
                        $f=0.5; //NB this is a token fraction for partially correct (shd be rawfraction)
                    }
                }
                else $f=0;
                $this->sumcorr += $f * $stepdata->maxmark;
            }
        }
        $this->accy = $this->sumcorr / $this->quiz->sumgrades;
        $this->cbm_av = $this->sumcbm / $this->quiz->sumgrades;
        $m=max($this->accy, -2+4*$this->accy, -6+9*$this->accy);
        $this->bonus = 0.1 * ($this->cbm_av - $m);
        $this->cbm_accy = $this->accy + $this->bonus;
    }

    public function data_col($slot, $field, $attempt) {
        global $CFG;

        if ($attempt->usageid == 0) {
            return '-';
        }

        $question = $this->questions[$slot];
        if (!isset($this->lateststeps[$attempt->usageid][$slot])) {
            return '-';
        }

        $stepdata = $this->lateststeps[$attempt->usageid][$slot];

         if (property_exists($stepdata, $field . 'full')) {
            $value = $stepdata->{$field . 'full'};
        } else {
            $value = $stepdata->$field;
        }

        if (is_null($value)) {
            $summary = '-';
        } else {
            $summary = round($stepdata->fraction,1);
        }

        if ($this->is_downloading() && $this->is_downloading() != 'xhtml') {
            return $summary;
        }
        $summary = s($summary);

        if ($this->is_downloading() || $field != 'responsesummary') {
            return $summary;
        }
        $x=strpos($stepdata->responsesummary,'[');
        if($stepdata->state != 'gradedright'){
            $x= '<br>' . $stepdata->responsesummary;
        }
        else $x='';
        return $this->make_review_link($summary, $attempt, $slot) . $x;
    }

    public function other_cols($colname, $attempt) {
         if(preg_match('/^response(\d+)$/', $colname, $matches)) {
             return $this->data_col($matches[1], 'responsesummary', $attempt);
         } else return null;
    }

    protected function requires_latest_steps_loaded() {
        return true;
    }

    protected function is_latest_step_column($column) {
        if (preg_match('/^(?:question|response|right)([0-9]+)/', $column, $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * Get any fields that might be needed when sorting on date for a particular slot.
     * @param int $slot the slot for the column we want.
     * @param string $alias the table alias for latest state information relating to that slot.
     */
    protected function get_required_latest_state_fields($slot, $alias) {
        global $DB;
        $sortableresponse = $DB->sql_order_by_text("{$alias}.questionsummary");
        if ($sortableresponse === "{$alias}.questionsummary") {
            // Can just order by text columns. No complexity needed.
            return "{$alias}.questionsummary AS question{$slot},
                    {$alias}.rightanswer AS right{$slot},
                    {$alias}.responsesummary AS response{$slot}";
        } else {
            // Work-around required.
            return $DB->sql_order_by_text("{$alias}.questionsummary") . " AS question{$slot},
                    {$alias}.questionsummary AS question{$slot}full,
                    " . $DB->sql_order_by_text("{$alias}.rightanswer") . " AS right{$slot},
                    {$alias}.rightanswer AS right{$slot}full,
                    " . $DB->sql_order_by_text("{$alias}.responsesummary") . " AS response{$slot},
                    {$alias}.responsesummary AS response{$slot}full";
        }
    }
}
