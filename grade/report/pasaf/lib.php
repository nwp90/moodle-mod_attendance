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
 * Definition of the grade_pasaf_report class is defined
 *
 * @package gradereport_pasaf
 * @copyright 2007 Nicolas Connault
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->libdir.'/tablelib.php');

//showhiddenitems values
define("GRADE_REPORT_PASAF_HIDE_HIDDEN", 0);
define("GRADE_REPORT_PASAF_HIDE_UNTIL", 1);
define("GRADE_REPORT_PASAF_SHOW_HIDDEN", 2);

define("GRADE_REPORT_PASAF_VIEW_SELF", 1);
define("GRADE_REPORT_PASAF_VIEW_USER", 2);

define("GRADE_REPORT_PASAF_PREFERENCE_DEFAULT", 1);

/**
 * Class providing an API for the pasaf report building and displaying.
 * @uses grade_report
 * @package gradereport_pasaf
 */
class grade_report_pasaf extends grade_report {

    /**
     * The user.
     * @var object $user
     */
    public $user;

    /**
     * The context of the course for which this report is being generated
     * @var int $coursecontext
     */
    public $coursecontext;

    /**
     * A flexitable to hold the data.
     * @var object $table
     */
    public $table;

    /**
     * An array of table headers
     * @var array
     */
    public $tableheaders = array();

    /**
     * An array of table columns
     * @var array
     */
    public $tablecolumns = array();

    /**
     * An array containing rows of data for the table.
     * @var type
     */
    public $tabledata = array();

    /**
     * An array containing the grade items data for external usage (web services, ajax, etc...)
     * @var array
     */
    public $gradeitemsdata = array();

    /**
     * The grade tree structure
     * @var grade_tree
     */
    public $gtree;

    /**
     * Flat structure similar to grade tree
     */
    public $gseq;

    /**
     * show student ranks
     */
    public $showrank;

    /**
     * show grade percentages
     */
    public $showpercentage;

    /**
     * show grade edit
     */
    public $showedit;

    /**
     * Show range
     */
    public $showrange = true;

    /**
     * Show grades in the report, default true
     * @var bool
     */
    public $showgrade = true;

    /**
     * Decimal points to use for values in the report, default 2
     * @var int
     */
    public $decimals = 2;

    /**
     * The number of decimal places to round range to, default 0
     * @var int
     */
    public $rangedecimals = 0;

    /**
     * Show grade feedback in the report, default true
     * @var bool
     */
    public $showfeedback = true;

    /**
     * Show grade weighting in the report, default true.
     * @var bool
     */
    public $showweight = true;

    /**
     * Show letter grades in the report, default false
     * @var bool
     */
    public $showlettergrade = false;

    /**
     * Show the calculated contribution to the course total column.
     * @var bool
     */
    public $showcontributiontocoursetotal = true;

    /**
     * Show average grades in the report, default false.
     * @var false
     */
    public $showaverage = false;

    public $maxdepth;
    public $evenodd;

    public $canviewhidden;

    public $switch;

    /**
     * Show hidden items even when user does not have required cap
     */
    public $showhiddenitems;
    public $showtotalsifcontainhidden;

    public $baseurl;
    public $pbarurl;


    public $target;

    /**
     * The modinfo objects to be used.
     *
     * @var course_modinfo
     */
    protected $modinfo = null;

    /**
     * Modinfo for subject of report, if needed
     *
     * @var course_modinfo
     */
    protected $usermodinfo = null;

    /**
     * View as user.
     *
     * When this is set to true, the visibility checks, and capability checks will be
     * applied to the user whose grades are being displayed. This is very useful when
     * a mentor/parent is viewing the report of their mentee because they need to have
     * access to the same information, but not more, not less.
     *
     * @var boolean
     */
    protected $viewasuser = false;

    /**
     * An array that collects the aggregationhints for every
     * grade_item. The hints contain grade, grademin, grademax
     * status, weight and parent.
     *
     * @var array
     */
    protected $aggregationhints = array();

    /**
     * An array that collects whether each tree element needs to be shown in this
     * report.
     *
     * @var array
     */
    protected $requiredelements = array();

    /**
     * Constructor. Sets local copies of user preferences and initialises grade_tree.
     * @param int $courseid
     * @param object $gpr grade plugin return tracking object
     * @param string $context
     * @param int $userid The id of the user
     * @param bool $viewasuser Set this to true when the current user is a mentor/parent of the targetted user.
     */
    public function __construct($courseid, $gpr, $context, $assessments, $userid, $viewasuser = null) {
        global $DB, $CFG;
        parent::__construct($courseid, $gpr, $context);

        $this->assessments     = $assessments;
        $this->showrank        = grade_get_setting($this->courseid, 'report_pasaf_showrank', $CFG->grade_report_pasaf_showrank);
        $this->showpercentage  = grade_get_setting($this->courseid, 'report_pasaf_showpercentage', $CFG->grade_report_pasaf_showpercentage);
        $this->showhiddenitems = grade_get_setting($this->courseid, 'report_pasaf_showhiddenitems', $CFG->grade_report_pasaf_showhiddenitems);
        $this->showtotalsifcontainhidden = array($this->courseid => grade_get_setting($this->courseid, 'report_pasaf_showtotalsifcontainhidden', $CFG->grade_report_pasaf_showtotalsifcontainhidden));

        $this->showgrade       = grade_get_setting($this->courseid, 'report_pasaf_showgrade',       !empty($CFG->grade_report_pasaf_showgrade));
        $this->showrange       = grade_get_setting($this->courseid, 'report_pasaf_showrange',       !empty($CFG->grade_report_pasaf_showrange));
        $this->showfeedback    = grade_get_setting($this->courseid, 'report_pasaf_showfeedback',    !empty($CFG->grade_report_pasaf_showfeedback));

        $this->showweight = grade_get_setting($this->courseid, 'report_pasaf_showweight',
            !empty($CFG->grade_report_pasaf_showweight));

$this->showedit = grade_get_setting($this->courseid, 'report_pasaf_showedit',
            !empty($CFG->grade_report_pasaf_showedit));

        $this->showcontributiontocoursetotal = grade_get_setting($this->courseid, 'report_pasaf_showcontributiontocoursetotal',
            !empty($CFG->grade_report_pasaf_showcontributiontocoursetotal));

        $this->showlettergrade = grade_get_setting($this->courseid, 'report_pasaf_showlettergrade', !empty($CFG->grade_report_pasaf_showlettergrade));
        $this->showaverage     = grade_get_setting($this->courseid, 'report_pasaf_showaverage',     !empty($CFG->grade_report_pasaf_showaverage));

        $this->viewasuser = $viewasuser;

        // The default grade decimals is 2
        $defaultdecimals = 2;
        if (property_exists($CFG, 'grade_decimalpoints')) {
            $defaultdecimals = $CFG->grade_decimalpoints;
        }
        $this->decimals = grade_get_setting($this->courseid, 'decimalpoints', $defaultdecimals);

        // The default range decimals is 0
        $defaultrangedecimals = 0;
        if (property_exists($CFG, 'grade_report_pasaf_rangedecimals')) {
            $defaultrangedecimals = $CFG->grade_report_pasaf_rangedecimals;
        }
        $this->rangedecimals = grade_get_setting($this->courseid, 'report_pasaf_rangedecimals', $defaultrangedecimals);

        $this->switch = grade_get_setting($this->courseid, 'aggregationposition', $CFG->grade_aggregationposition);

        // Grab the grade_tree for this course
        $this->gtree = new grade_tree($this->courseid, false, $this->switch, null, !$CFG->enableoutcomes);

        // Get the user (for full name).
        $this->user = $DB->get_record('user', array('id' => $userid));

        // What user are we viewing this as?
        // We could use $this->context here, but it's not clear what it will be (it is whatever is passed in
        // to grade_report_foo constructor).
        $this->coursecontext = context_course::instance($this->courseid);
        if ($viewasuser) {
            $this->modinfo = new course_modinfo($this->course, $this->user->id);
            $this->canviewhidden = has_capability('moodle/grade:viewhidden', $this->coursecontext, $this->user->id);
        } else {
            $this->modinfo = $this->gtree->modinfo;
            $this->canviewhidden = has_capability('moodle/grade:viewhidden', $this->coursecontext);
            $this->usermodinfo = new course_modinfo($this->course, $this->user->id);
        }

        // Determine the number of rows and indentation.
        $this->maxdepth = 1;
        $this->inject_rowspans($this->gtree->top_element);
        $this->maxdepth++; // Need to account for the lead column that spans all children.
        for ($i = 1; $i <= $this->maxdepth; $i++) {
            $this->evenodd[$i] = 0;
        }

        $this->tabledata = array();

        // base url for sorting by first/last name
        $this->baseurl = $CFG->wwwroot.'/grade/report?id='.$courseid.'&amp;userid='.$userid;
        $this->pbarurl = $this->baseurl;

        // no groups on this report - rank is from all course users
        $this->setup_table();

        //optionally calculate grade item averages
        $this->calculate_averages();
    }

    /**
     * Recurses through a tree of elements setting the rowspan property on each element
     *
     * @param array $element Either the top element or, during recursion, the current element
     * @return int The number of elements processed
     */
    function inject_rowspans(&$element) {

        if ($element['depth'] > $this->maxdepth) {
            $this->maxdepth = $element['depth'];
        }
        if (empty($element['children'])) {
            return 1;
        }
        $count = 1;

        foreach ($element['children'] as $key=>$child) {
            // If category is hidden then do not include it in the rowspan.
            if ($child['type'] == 'category' && $child['object']->is_hidden() && !$this->canviewhidden
                    && ($this->showhiddenitems == GRADE_REPORT_PASAF_HIDE_HIDDEN
                    || ($this->showhiddenitems == GRADE_REPORT_PASAF_HIDE_UNTIL && !$child['object']->is_hiddenuntil()))) {
                // Just calculate the rowspans for children of this category, don't add them to the count.
                $this->inject_rowspans($element['children'][$key]);
            } else {
                $count += $this->inject_rowspans($element['children'][$key]);
            }
        }

        $element['rowspan'] = $count;
        return $count;
    }


    /**
     * Prepares the headers and attributes of the flexitable.
     */
    public function setup_table() {
        /*
         * Table has 1-8 columns
         *| All columns except for itemname/description are optional
         */

        // setting up table headers

        $this->tablecolumns = array('itemname');
        $this->tableheaders = array($this->get_lang_string('gradeitem', 'grades'));

        if ($this->showweight) {
            $this->tablecolumns[] = 'weight';
            $this->tableheaders[] = $this->get_lang_string('weightuc', 'grades');
        }

        if ($this->showgrade) {
            $this->tablecolumns[] = 'grade';
            $this->tableheaders[] = $this->get_lang_string('grade', 'grades');
        }

        if ($this->showrange) {
            $this->tablecolumns[] = 'range';
            $this->tableheaders[] = $this->get_lang_string('range', 'grades');
        }

        if ($this->showpercentage) {
            $this->tablecolumns[] = 'percentage';
            $this->tableheaders[] = $this->get_lang_string('percentage', 'grades');
        }

        if ($this->showlettergrade) {
            $this->tablecolumns[] = 'lettergrade';
            $this->tableheaders[] = $this->get_lang_string('lettergrade', 'grades');
        }

        if ($this->showrank) {
            $this->tablecolumns[] = 'rank';
            $this->tableheaders[] = $this->get_lang_string('rank', 'grades');
        }

        if ($this->showaverage) {
            $this->tablecolumns[] = 'average';
            $this->tableheaders[] = $this->get_lang_string('average', 'grades');
        }

        if ($this->showfeedback) {
            $this->tablecolumns[] = 'feedback';
            $this->tableheaders[] = $this->get_lang_string('feedback', 'grades');
        }
        if ($this->showedit) {
            $this->tablecolumns[] = 'edit';
            $this->tableheaders[] = $this->get_lang_string('edit', 'gradereport_pasaf');
        }

        if ($this->showcontributiontocoursetotal) {
            $this->tablecolumns[] = 'contributiontocoursetotal';
            $this->tableheaders[] = $this->get_lang_string('contributiontocoursetotal', 'grades');
        }
    }

    function fill_table() {
        //print "<pre>";
        //print_r($this->gtree->top_element);
        $this->set_element_required($this->gtree->top_element);
        $this->fill_table_recursive($this->gtree->top_element);
        //print_r($this->tabledata);
        //print "</pre>";
        return true;
    }

    /*
     * Recursively set requiredelements array, indicating which elements' rows are required
     * in pasaf report.
     *
     * @return boolean Whether element passed in is required as part of pasaf report
     */
    public function set_element_required($element) {
        // Recursively iterate through all child elements.
        $pasaf = false;
        $grade_object = $element['object'];
        if (isset($element['children'])) {
            if ($element['type'] != 'category') {
                error_log("gtree element with type '".$element['type']."' with children!");
            }
            $eid = $this->gtree->get_category_eid($grade_object);
            foreach ($element['children'] as $key=>$child) {
                $pasaf = $this->set_element_required($element['children'][$key]) || $pasaf;
            }
        } else if ($element['type'] == 'item' || $element['type'] == 'categoryitem' || $element['type'] == 'courseitem') {
            // This is a grade item
            $itemid = $grade_object->id;
            if (array_key_exists($itemid, $this->assessments)) {
                if ($this->assessments[$itemid]->record) {
                    $pasaf = true;
                }
            }
            $eid = $this->gtree->get_item_eid($grade_object);
        }
        $this->requiredelements[$eid] = $pasaf;
        return $pasaf;
    }

    /**
     * Is this element required in report?
     *
     * @param $element - A grade_tree element.
     * @return boolean
     */
    protected function element_required($element) {
        if ($element['type'] == 'category') {
            return $this->requiredelements[$this->gtree->get_category_eid($element['object'])];
        } else {
            return $this->requiredelements[$this->gtree->get_item_eid($element['object'])];
        }
    }

    /**
     * Fill the table with data.
     *
     * @param $element - An array containing the table data for the current row.
     */
    private function fill_table_recursive(&$element) {
        global $DB, $CFG;

        if (!array_key_exists('ancestors', $element)) {
            $element['ancestors'] = array();
        }
        $type = $element['type'];
        $depth = $element['depth'];
        $grade_object = $element['object'];
        $eid = $grade_object->id;
        $element['userid'] = $this->user->id;
        // element, link?, icon?, spacer? (if no icon), description?, named total? (rather than "course total" or "category total", new tab?)
        $fullname = $this->gtree->get_element_header($element, true, true, true, false, true, true);
        $data = array();
        $gradeitemdata = array();
        $hidden = '';
        $excluded = '';
        $itemlevel = ($type == 'categoryitem' || $type == 'category' || $type == 'courseitem') ? $depth : ($depth + 1);
        $class = 'level' . $itemlevel . ' level' . ($itemlevel % 2 ? 'odd' : 'even');
        $classfeedback = '';

        $required = $this->element_required($element);

        $tmpnam = $element['object']->get_name();

        // If this is a hidden grade category, hide it completely from the user
        if ($type == 'category' && $grade_object->is_hidden() && !$this->canviewhidden && (
                $this->showhiddenitems == GRADE_REPORT_PASAF_HIDE_HIDDEN ||
                ($this->showhiddenitems == GRADE_REPORT_PASAF_HIDE_UNTIL && !$grade_object->is_hiddenuntil()))) {
            return false;
        }

        if ($required) {
            $alter = ($this->evenodd[$depth] == 0) ? 'even' : 'odd';
            if ($type == 'category') {
                $this->evenodd[$depth] = (($this->evenodd[$depth] + 1) % 2);
                $data['itemname']['content'] = $fullname;
                $data['itemname']['celltype'] = 'th';
                $data['itemname']['id'] = "cat_{$grade_object->id}_{$this->user->id}";
                $data['leader']['class'] = $class.' '.$alter."d$depth b1t b2b b1l";
                $data['leader']['rowspan'] = $element['rowspan'];

                if ($this->switch) { // alter style based on whether aggregation is first or last
                    $data['itemname']['class'] = $class.' '.$alter."d$depth b1b b1t";
                } else {
                    $data['itemname']['class'] = $class.' '.$alter."d$depth b2t";
                }
                $data['itemname']['colspan'] = ($this->maxdepth - $depth + count($this->tablecolumns) - 1);
            }
        }

        /// Process those items that have scores associated
        if ($type == 'item' or $type == 'categoryitem' or $type == 'courseitem') {
            $header_row = "row_{$eid}_{$this->user->id}";
            $header_cat = "cat_{$grade_object->categoryid}_{$this->user->id}";

            if (! $grade_grade = grade_grade::fetch(array('itemid'=>$grade_object->id,'userid'=>$this->user->id))) {
                $grade_grade = new grade_grade();
                $grade_grade->userid = $this->user->id;
                $grade_grade->itemid = $grade_object->id;
            }

            $grade_grade->load_grade_item();

            /// Hidden Items
            if ($grade_grade->grade_item->is_hidden()) {
                $hidden = ' dimmed_text';
            }

            $hide = false;
            // If this is a hidden grade item, hide it completely from the user.
            if ($grade_grade->is_hidden() && !$this->canviewhidden && (
                    $this->showhiddenitems == GRADE_REPORT_PASAF_HIDE_HIDDEN ||
                    ($this->showhiddenitems == GRADE_REPORT_PASAF_HIDE_UNTIL && !$grade_grade->is_hiddenuntil()))) {
                $hide = true;
            } else if (!empty($grade_object->itemmodule) && !empty($grade_object->iteminstance)) {
                // We want to know availability to student as well as to us...
                if (isset($this->usermodinfo)) {
                    // viewing as self
                    $studentmodinfo = $this->usermodinfo;
                    $viewermodinfo = $this->modinfo;
                } else {
                    // viewing as student
                    $studentmodinfo = null;
                    $viewermodinfo = $this->modinfo;
                }
                // If not available to student, don't show. If available but not visible,
                // show if it's visible to us.

                // The grade object can be marked visible but still be hidden if
                // the student cannot see the activity due to conditional access
                // and it's set to be hidden entirely.
                $instances = $viewermodinfo->get_instances_of($grade_object->itemmodule);
                if (!empty($instances[$grade_object->iteminstance])) {
                    $cm = $instances[$grade_object->iteminstance];
                    $gradeitemdata['cmid'] = $cm->id;
                    if (!$cm->uservisible) {
                        // If there is 'availableinfo' text then it is only greyed
                        // out and not entirely hidden.
                        if (!$cm->availableinfo) {
                            $hide = true;
                        }
                    }
                    else if (isset($this->usermodinfo)) {
                        $instances = $studentmodinfo->get_instances_of($grade_object->itemmodule);
                        if (!empty($instances[$grade_object->iteminstance])) {
                            $cm = $instances[$grade_object->iteminstance];
                            $gradeitemdata['cmid'] = $cm->id;
                            if (!$cm->available) {
                                $hide = true;
                            }
                        }
                    }
                }
            }

            // Actual Grade - We need to calculate this whether the row is hidden or not.
            $gradeval = $grade_grade->finalgrade;
            $hint = $grade_grade->get_aggregation_hint();
            if (!$this->canviewhidden) {
                /// Virtual Grade (may be calculated excluding hidden items etc).
                $adjustedgrade = $this->blank_hidden_total_and_adjust_bounds($this->courseid,
                                                                             $grade_grade->grade_item,
                                                                             $gradeval);

                $gradeval = $adjustedgrade['grade'];

                // We temporarily adjust the view of this grade item - because the min and
                // max are affected by the hidden values in the aggregation.
                $grade_grade->grade_item->grademax = $adjustedgrade['grademax'];
                $grade_grade->grade_item->grademin = $adjustedgrade['grademin'];
                $hint['status'] = $adjustedgrade['aggregationstatus'];
                $hint['weight'] = $adjustedgrade['aggregationweight'];
            } else {
                // The max and min for an aggregation may be different to the grade_item.
                if (!is_null($gradeval)) {
                    $grade_grade->grade_item->grademax = $grade_grade->get_grade_max();
                    $grade_grade->grade_item->grademin = $grade_grade->get_grade_min();
                }
            }


            if (!$hide && $required) {
                /// Other class information
                $class .= $hidden . $excluded;
                if ($this->switch) { // alter style based on whether aggregation is first or last
                   $class .= ($type == 'categoryitem' or $type == 'courseitem') ? " ".$alter."d$depth baggt b2b" : " item b1b";
                } else {
                   $class .= ($type == 'categoryitem' or $type == 'courseitem') ? " ".$alter."d$depth baggb" : " item b1b";
                }
                if ($type == 'categoryitem' or $type == 'courseitem') {
                    $header_cat = "cat_{$grade_object->iteminstance}_{$this->user->id}";
                }

                /// Name
                $data['itemname']['content'] = $fullname;
                $data['itemname']['class'] = $class;
                $data['itemname']['colspan'] = ($this->maxdepth - $depth);
                $data['itemname']['celltype'] = 'th';
                $data['itemname']['id'] = $header_row;

                // Basic grade item information.
                $gradeitemdata['id'] = $grade_object->id;
                $gradeitemdata['itemname'] = $grade_object->itemname;
                $gradeitemdata['itemtype'] = $grade_object->itemtype;
                $gradeitemdata['itemmodule'] = $grade_object->itemmodule;
                $gradeitemdata['iteminstance'] = $grade_object->iteminstance;
                $gradeitemdata['itemnumber'] = $grade_object->itemnumber;
                $gradeitemdata['categoryid'] = $grade_object->categoryid;
                $gradeitemdata['outcomeid'] = $grade_object->outcomeid;
                $gradeitemdata['scaleid'] = $grade_object->outcomeid;

                if ($this->showfeedback) {
                    // Copy $class before appending itemcenter as feedback should not be centered
                    $classfeedback = $class;
                }
                $class .= " itemcenter ";
                if ($this->showweight) {
                    $data['weight']['class'] = $class;
                    $data['weight']['content'] = '-';
                    $data['weight']['headers'] = "$header_cat $header_row weight";
                    // has a weight assigned, might be extra credit

                    // This obliterates the weight because it provides a more informative description.
                    if (is_numeric($hint['weight'])) {
                        $data['weight']['content'] = format_float($hint['weight'] * 100.0, 2) . ' %';
                        $gradeitemdata['weightraw'] = $hint['weight'];
                        $gradeitemdata['weightformatted'] = $data['weight']['content'];
                    }
                    if ($hint['status'] != 'used' && $hint['status'] != 'unknown') {
                        $data['weight']['content'] .= '<br>' . get_string('aggregationhint' . $hint['status'], 'grades');
                        $gradeitemdata['status'] = $hint['status'];
                    }
                }

                if ($this->showgrade) {                   

                    $gradeitemdata['graderaw'] = null;
                    $gradeitemdata['gradehiddenbydate'] = false;
                    $gradeitemdata['gradeneedsupdate'] = $grade_grade->grade_item->needsupdate;
                    $gradeitemdata['gradeishidden'] = $grade_grade->is_hidden();
                    $gradeitemdata['gradedatesubmitted'] = $grade_grade->get_datesubmitted();
                    $gradeitemdata['gradedategraded'] = $grade_grade->get_dategraded();

                    if ($grade_grade->grade_item->needsupdate) {
                        $data['grade']['class'] = $class.' gradingerror';
                        $data['grade']['content'] = get_string('error');
                    } else if (!empty($CFG->grade_hiddenasdate) and $grade_grade->get_datesubmitted() and !$this->canviewhidden and $grade_grade->is_hidden()
                           and !$grade_grade->grade_item->is_category_item() and !$grade_grade->grade_item->is_course_item()) {
                        // the problem here is that we do not have the time when grade value was modified, 'timemodified' is general modification date for grade_grades records
                        $class .= ' datesubmitted';
                        $data['grade']['class'] = $class;
                        $data['grade']['content'] = get_string('submittedon', 'grades', userdate($grade_grade->get_datesubmitted(), get_string('strftimedatetimeshort')));
                        $gradeitemdata['gradehiddenbydate'] = true;
                    } else if ($grade_grade->is_hidden()) {
                        $data['grade']['class'] = $class.' dimmed_text';
                        $data['grade']['content'] = '-';

                        if ($this->canviewhidden) {
                            $gradeitemdata['graderaw'] = $gradeval;
                            $data['grade']['content'] = grade_format_gradevalue($gradeval,
                                                                                $grade_grade->grade_item,
                                                                                true);
                        }
                    } else {
                        $data['grade']['class'] = $class;
                        $data['grade']['content'] = grade_format_gradevalue($gradeval,
                                                                            $grade_grade->grade_item,
                                                                            true);
                        $gradeitemdata['graderaw'] = $gradeval;
                    }
                    $data['grade']['headers'] = "$header_cat $header_row grade";
                    $gradeitemdata['gradeformatted'] = $data['grade']['content'];
                }

                // Range
                if ($this->showrange) {
                    $data['range']['class'] = $class;
                    $data['range']['content'] = $grade_grade->grade_item->get_formatted_range(GRADE_DISPLAY_TYPE_REAL, $this->rangedecimals);
                    $data['range']['headers'] = "$header_cat $header_row range";

                    $gradeitemdata['rangeformatted'] = $data['range']['content'];
                    $gradeitemdata['grademin'] = $grade_grade->grade_item->grademin;
                    $gradeitemdata['grademax'] = $grade_grade->grade_item->grademax;
                }

                // Percentage
                if ($this->showpercentage) {
                    if ($grade_grade->grade_item->needsupdate) {
                        $data['percentage']['class'] = $class.' gradingerror';
                        $data['percentage']['content'] = get_string('error');
                    } else if ($grade_grade->is_hidden()) {
                        $data['percentage']['class'] = $class.' dimmed_text';
                        $data['percentage']['content'] = '-';
                        if ($this->canviewhidden) {
                            $data['percentage']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
                        }
                    } else {
                        $data['percentage']['class'] = $class;
                        $data['percentage']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
                    }
                    $data['percentage']['headers'] = "$header_cat $header_row percentage";
                    $gradeitemdata['percentageformatted'] = $data['percentage']['content'];
                }

                // Lettergrade
                if ($this->showlettergrade) {
                    if ($grade_grade->grade_item->needsupdate) {
                        $data['lettergrade']['class'] = $class.' gradingerror';
                        $data['lettergrade']['content'] = get_string('error');
                    } else if ($grade_grade->is_hidden()) {
                        $data['lettergrade']['class'] = $class.' dimmed_text';
                        if (!$this->canviewhidden) {
                            $data['lettergrade']['content'] = '-';
                        } else {
                            $data['lettergrade']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);
                        }
                    } else {
                        $data['lettergrade']['class'] = $class;
                        $data['lettergrade']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);
                    }
                    $data['lettergrade']['headers'] = "$header_cat $header_row lettergrade";
                    $gradeitemdata['lettergradeformatted'] = $data['lettergrade']['content'];
                }

                // Rank
                if ($this->showrank) {
                    $gradeitemdata['rank'] = 0;
                    if ($grade_grade->grade_item->needsupdate) {
                        $data['rank']['class'] = $class.' gradingerror';
                        $data['rank']['content'] = get_string('error');
                        } elseif ($grade_grade->is_hidden()) {
                            $data['rank']['class'] = $class.' dimmed_text';
                            $data['rank']['content'] = '-';
                    } else if (is_null($gradeval)) {
                        // no grade, no rank
                        $data['rank']['class'] = $class;
                        $data['rank']['content'] = '-';

                    } else {
                        /// find the number of users with a higher grade
                        $sql = "SELECT COUNT(DISTINCT(userid))
                                  FROM {grade_grades}
                                 WHERE finalgrade > ?
                                       AND itemid = ?
                                       AND hidden = 0";
                        $rank = $DB->count_records_sql($sql, array($grade_grade->finalgrade, $grade_grade->grade_item->id)) + 1;

                        $data['rank']['class'] = $class;
                        $numusers = $this->get_numusers(false);
                        $data['rank']['content'] = "$rank/$numusers"; // Total course users.

                        $gradeitemdata['rank'] = $rank;
                        $gradeitemdata['numusers'] = $numusers;
                    }
                    $data['rank']['headers'] = "$header_cat $header_row rank";
                }

                // Average
                if ($this->showaverage) {
                    $gradeitemdata['averageformatted'] = '';

                    $data['average']['class'] = $class;
                    if (!empty($this->gtree->items[$eid]->avg)) {
                        $data['average']['content'] = $this->gtree->items[$eid]->avg;
                        $gradeitemdata['averageformatted'] = $this->gtree->items[$eid]->avg;
                    } else {
                        $data['average']['content'] = '-';
                    }
                    $data['average']['headers'] = "$header_cat $header_row average";
                }

                // Feedback
                if ($this->showfeedback) {
                    $gradeitemdata['feedback'] = '';
                    $gradeitemdata['feedbackformat'] = $grade_grade->feedbackformat;

                    if ($grade_grade->overridden > 0 AND ($type == 'categoryitem' OR $type == 'courseitem')) {
                    $data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = get_string('overridden', 'grades').': ' . format_text($grade_grade->feedback, $grade_grade->feedbackformat);
                        $gradeitemdata['feedback'] = $grade_grade->feedback;
                    } else if (empty($grade_grade->feedback) or (!$this->canviewhidden and $grade_grade->is_hidden())) {
                        $data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = '&nbsp;';
                    } else {
                        $data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = format_text($grade_grade->feedback, $grade_grade->feedbackformat);
                        $gradeitemdata['feedback'] = $grade_grade->feedback;
                    }
                    $data['feedback']['headers'] = "$header_cat $header_row feedback";
                }
                //Edit column
                if($this->showedit){

                    global $CFG, $USER, $OUTPUT;

                    $editable = false;

                    // What context should we use here?
                    // grade/edit/tree/grade.php uses course; it seems gradebook capabilities are all-or-nothing,
                    // not set on individual items.
                    // Could add userid param and doanything param if we want edit icons
                    // to potentially not show when seeing report as user.
                    if (has_any_capability(array('moodle/grade:manage', 'moodle/grade:edit'), $this->coursecontext)) {
                        // OK to show
                        $editable = true;
                    }

                    // Init all icons
                    $editicon = '';

                    $grading_info = grade_get_grades($this->courseid, 'mod', $gradeitemdata['itemmodule'], $gradeitemdata['id'], $this->user->id);
                    //$user_final_grade = $grading_info->items[0]->grades[$this->user->id];
                    //print_object($grading_info);
                    //print_object($element);
                    //$grading_info->type='grade';

                    if ($element['type'] == 'grade') {
                        $item = $element['object']->grade_item;
                        if ($item->is_course_item() or $item->is_category_item()) {
                            $editable = $editable && (bool) get_config('moodle', 'grade_overridecat');
                        }
                    }
        
                    if ($element['type'] != 'categoryitem' && $element['type'] != 'courseitem' && $editable) {
                        //$editicon = $this->gtree->get_edit_icon($element, $this->gpr);
                    }

                    /*KJ - Naughty stuff we need to fathom*/
                    //$actual_type = $element['type'];
                    //$element['type'] = 'grade';
                    //$editicon = $this->gtree->get_edit_icon($element, $this->gpr);

                    //$element['type'] = $actual_type;
                    /*end of stuff to fathom*/

                    if ($editable) {
                        /*manual edit icon*/
                        $streditgrade = get_string('editgrade', 'grades');
                        $url = new moodle_url('/grade/edit/tree/grade.php',array('courseid' => $this->courseid, 'itemid' => $gradeitemdata['id'], 'userid' => $this->user->id));
                        $url = $this->gpr->add_url_params($url);
                        $newicon = $OUTPUT->action_icon($this->gpr->add_url_params($url), new pix_icon('t/edit', $streditgrade));
                        /*end of manual work*/
                    } else {
                        $newicon = ' ';
                    }

                    $gradeitemdata['edit'] = '';
                    $data['edit']['class'] = $class .' edittext';
                    $data['edit']['content'] = $newicon;
                    $data['edit']['headers'] = "$header_cat $header_row edit";
                }
                // Contribution to the course total column.
                if ($this->showcontributiontocoursetotal) {
                    $data['contributiontocoursetotal']['class'] = $class;
                    $data['contributiontocoursetotal']['content'] = '-';
                    $data['contributiontocoursetotal']['headers'] = "$header_cat $header_row contributiontocoursetotal";

                }
                $this->gradeitemsdata[] = $gradeitemdata;
            }
            // We collect the aggregation hints whether they are hidden or not.
            if ($this->showcontributiontocoursetotal) {
                $hint['grademax'] = $grade_grade->grade_item->grademax;
                $hint['grademin'] = $grade_grade->grade_item->grademin;
                $hint['grade'] = $gradeval;
                $parent = $grade_object->load_parent_category();
                if ($grade_object->is_category_item()) {
                    $parent = $parent->load_parent_category();
                }
                $hint['parent'] = $parent->load_grade_item()->id;
                $this->aggregationhints[$grade_grade->itemid] = $hint;
            }
        }
        
        /// Add this row to the overall system
        foreach ($data as $key => $celldata) {
            $data[$key]['class'] .= ' column-' . $key;
        }
        $this->tabledata[] = $data;

        /// Recursively iterate through all child elements
        if (isset($element['children'])) {
            $ancestors = $element['ancestors'];
            array_unshift($ancestors, $element['object']->id);
            foreach ($element['children'] as $key=>$child) {
                $child = $element['children'][$key];
                $child['ancestors'] = $ancestors;
                $this->fill_table_recursive($child);
            }
        }

        // Check we are showing this column, and we are looking at the root of the table.
        // This should be the very last thing this fill_table_recursive function does.
        if ($this->showcontributiontocoursetotal && ($type == 'category' && $depth == 1)) {
            // We should have collected all the hints by now - walk the tree again and build the contributions column.
            $this->fill_contributions_column($element);
        }
    }

    /**
     * This function is called after the table has been built and the aggregationhints
     * have been collected. We need this info to walk up the list of parents of each
     * grade_item.
     *
     * @param $element - An array containing the table data for the current row.
     */
    public function fill_contributions_column($element) {

        // Recursively iterate through all child elements.
        if (isset($element['children'])) {
            foreach ($element['children'] as $key=>$child) {
                $this->fill_contributions_column($element['children'][$key]);
            }
        } else if ($element['type'] == 'item') {
            // This is a grade item (We don't do this for categories or we would double count).
            $grade_object = $element['object'];
            $itemid = $grade_object->id;

            // Ignore anything with no hint - e.g. a hidden row.
            if (isset($this->aggregationhints[$itemid])) {

                // Normalise the gradeval.
                $gradecat = $grade_object->load_parent_category();
                if ($gradecat->aggregation == GRADE_AGGREGATE_SUM) {
                    // Natural aggregation/Sum of grades does not consider the mingrade, cannot traditionnally normalise it.
                    $graderange = $this->aggregationhints[$itemid]['grademax'];

                    if ($graderange != 0) {
                        $gradeval = $this->aggregationhints[$itemid]['grade'] / $graderange;
                    } else {
                        $gradeval = 0;
                    }
                } else {
                    $gradeval = grade_grade::standardise_score($this->aggregationhints[$itemid]['grade'],
                        $this->aggregationhints[$itemid]['grademin'], $this->aggregationhints[$itemid]['grademax'], 0, 1);
                }

                // Multiply the normalised value by the weight
                // of all the categories higher in the tree.
                $parent = null;
                do {
                    if (!is_null($this->aggregationhints[$itemid]['weight'])) {
                        $gradeval *= $this->aggregationhints[$itemid]['weight'];
                    } else if (empty($parent)) {
                        // If we are in the first loop, and the weight is null, then we cannot calculate the contribution.
                        $gradeval = null;
                        break;
                    }

                    // The second part of this if is to prevent infinite loops
                    // in case of crazy data.
                    if (isset($this->aggregationhints[$itemid]['parent']) &&
                            $this->aggregationhints[$itemid]['parent'] != $itemid) {
                        $parent = $this->aggregationhints[$itemid]['parent'];
                        $itemid = $parent;
                    } else {
                        // We are at the top of the tree.
                        $parent = false;
                    }
                } while ($parent);

                // Finally multiply by the course grademax.
                if (!is_null($gradeval)) {
                    // Convert to percent.
                    $gradeval *= 100;
                }

                // Now we need to loop through the "built" table data and update the
                // contributions column for the current row.
                $header_row = "row_{$grade_object->id}_{$this->user->id}";
                foreach ($this->tabledata as $key => $row) {
                    if (isset($row['itemname']) && ($row['itemname']['id'] == $header_row)) {
                        // Found it - update the column.
                        $content = '-';
                        if (!is_null($gradeval)) {
                            $decimals = $grade_object->get_decimals();
                            $content = format_float($gradeval, $decimals, true) . ' %';
                        }
                        $this->tabledata[$key]['contributiontocoursetotal']['content'] = $content;
                        break;
                    }
                }
            }
        }
    }

    /**
     * Prints or returns the HTML from the flexitable.
     * @param bool $return Whether or not to return the data instead of printing it directly.
     * @return string
     */
    public function print_table($return=false) {
        $maxspan = $this->maxdepth;
        $data_target = '';

        /// Build table structure
        $html = "
            <table cellspacing='0'
                   cellpadding='0'
                   summary='" . s($this->get_lang_string('tablesummary', 'gradereport_pasaf')) . "'
                   class='boxaligncenter generaltable user-grade'>
            <thead>
                <tr>
                    <th id='".$this->tablecolumns[0]."' class=\"header column-{$this->tablecolumns[0]}\" colspan='$maxspan'>".$this->tableheaders[0]."</th>\n";
            
        for ($i = 1; $i < count($this->tableheaders); $i++) {
            $html .= "<th id='".$this->tablecolumns[$i]."' class=\"header column-{$this->tablecolumns[$i]}\">".$this->tableheaders[$i]."</th>\n";
        }

        $html .= "
                </tr>
              </thead>
            <tbody>\n";

        /// Print out the table data
        $table_body = FALSE;

        for ($i = 0; $i < count($this->tabledata); $i++) {

            if(isset($this->tabledata[$i]['itemname']['rowclass'])) {
                $html .="<tr class='".$this->tabledata[$i]['itemname']['rowclass']."'>\n";
            } else {
                $html .="<tr>\n";
            }
        
            if (isset($this->tabledata[$i]['leader'])) {
                $rowspan = $this->tabledata[$i]['leader']['rowspan'];
                $class = $this->tabledata[$i]['leader']['class'];
                $html .= "<td class='$class' rowspan='$rowspan'></td>\n";

            }
            for ($j = 0; $j < count($this->tablecolumns); $j++) {
                $name = $this->tablecolumns[$j];                
                $class = (isset($this->tabledata[$i][$name]['class'])) ? $this->tabledata[$i][$name]['class'] : '';
                $colspan = (isset($this->tabledata[$i][$name]['colspan'])) ? "colspan='".$this->tabledata[$i][$name]['colspan']."'" : '';
                $content = (isset($this->tabledata[$i][$name]['content'])) ? $this->tabledata[$i][$name]['content'] : null;
                $celltype = (isset($this->tabledata[$i][$name]['celltype'])) ? $this->tabledata[$i][$name]['celltype'] : 'td';
                $id = (isset($this->tabledata[$i][$name]['id'])) ? "id='{$this->tabledata[$i][$name]['id']}'" : '';
                $headers = (isset($this->tabledata[$i][$name]['headers'])) ? "headers='{$this->tabledata[$i][$name]['headers']}'" : '';
                $aria_expanded = (isset($this->tabledata[$i][$name]['aria-expanded'])) ? $this->tabledata[$i][$name]['aria-expanded'] : '';
                $data_toggle = (isset($this->tabledata[$i][$name]['data-toggle'])) ? $this->tabledata[$i][$name]['data-toggle'] : '';
                $data_target = (isset($this->tabledata[$i][$name]['data-target'])) ? $this->tabledata[$i][$name]['data-target'] : '';
                $aria_controls = (isset($this->tabledata[$i][$name]['aria-controls'])) ? $this->tabledata[$i][$name]['aria-controls'] : '';
                if (isset($content)) {
                    //$html .= "<$celltype $id $headers class='$class' data-toggle='$data_toggle' data-target='$data_target' aria-expanded='$aria_expanded' aria-controls='$aria_controls' $colspan>$content</$celltype>\n";
                    $html .= "<$celltype $id $headers class='$class' $colspan>$content</$celltype>\n";
                }
            }
            $html .= "</tr>\n";
        }
        
        $html .= "</tbody></table>";

        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }

    /**
     * Processes the data sent by the form (grades and feedbacks).
     * @var array $data
     * @return bool Success or Failure (array of errors).
     */
    function process_data($data) {
    }
    function process_action($target, $action) {
    }

    /**
     * Builds the grade item averages.
     */
    function calculate_averages() {
        global $USER, $DB, $CFG;

        if ($this->showaverage) {
            // This settings are actually grader report settings (not user report)
            // however we're using them as having two separate but identical settings the
            // user would have to keep in sync would be annoying.
            $averagesdisplaytype   = $this->get_pref('averagesdisplaytype');
            $averagesdecimalpoints = $this->get_pref('averagesdecimalpoints');
            $meanselection         = $this->get_pref('meanselection');
            $shownumberofgrades    = $this->get_pref('shownumberofgrades');

            $avghtml = '';
            $groupsql = $this->groupsql;
            $groupwheresql = $this->groupwheresql;
            $totalcount = $this->get_numusers(false);

            // We want to query both the current context and parent contexts.
            list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($this->context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

            // Limit to users with a gradeable role ie students.
            list($gradebookrolessql, $gradebookrolesparams) = $DB->get_in_or_equal(explode(',', $this->gradebookroles), SQL_PARAMS_NAMED, 'grbr0');

            // Limit to users with an active enrolment.
            $coursecontext = $this->context->get_course_context(true);
            $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
            $showonlyactiveenrol = get_pasaf_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
            $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $coursecontext);
            list($enrolledsql, $enrolledparams) = get_enrolled_sql($this->context, '', 0, $showonlyactiveenrol);

            $params = array_merge($this->groupwheresql_params, $gradebookrolesparams, $enrolledparams, $relatedctxparams);
            $params['courseid'] = $this->courseid;

            // find sums of all grade items in course
            $sql = "SELECT gg.itemid, SUM(gg.finalgrade) AS sum
                      FROM {grade_items} gi
                      JOIN {grade_grades} gg ON gg.itemid = gi.id
                      JOIN {user} u ON u.id = gg.userid
                      JOIN ($enrolledsql) je ON je.id = gg.userid
                      JOIN (
                                   SELECT DISTINCT ra.userid
                                     FROM {role_assignments} ra
                                    WHERE ra.roleid $gradebookrolessql
                                      AND ra.contextid $relatedctxsql
                           ) rainner ON rainner.userid = u.id
                      $groupsql
                     WHERE gi.courseid = :courseid
                       AND u.deleted = 0
                       AND gg.finalgrade IS NOT NULL
                       AND gg.hidden = 0
                       $groupwheresql
                  GROUP BY gg.itemid";

            $sum_array = array();
            $sums = $DB->get_recordset_sql($sql, $params);
            foreach ($sums as $itemid => $csum) {
                $sum_array[$itemid] = $csum->sum;
            }
            $sums->close();

            $columncount=0;

            // Empty grades must be evaluated as grademin, NOT always 0
            // This query returns a count of ungraded grades (NULL finalgrade OR no matching record in grade_grades table)
            // No join condition when joining grade_items and user to get a grade item row for every user
            // Then left join with grade_grades and look for rows with null final grade (which includes grade items with no grade_grade)
            $sql = "SELECT gi.id, COUNT(u.id) AS count
                      FROM {grade_items} gi
                      JOIN {user} u ON u.deleted = 0
                      JOIN ($enrolledsql) je ON je.id = u.id
                      JOIN (
                               SELECT DISTINCT ra.userid
                                 FROM {role_assignments} ra
                                WHERE ra.roleid $gradebookrolessql
                                  AND ra.contextid $relatedctxsql
                           ) rainner ON rainner.userid = u.id
                      LEFT JOIN {grade_grades} gg
                             ON (gg.itemid = gi.id AND gg.userid = u.id AND gg.finalgrade IS NOT NULL AND gg.hidden = 0)
                      $groupsql
                     WHERE gi.courseid = :courseid
                           AND gg.finalgrade IS NULL
                           $groupwheresql
                  GROUP BY gi.id";

            $ungraded_counts = $DB->get_records_sql($sql, $params);

            foreach ($this->gtree->items as $itemid=>$unused) {
                if (!empty($this->gtree->items[$itemid]->avg)) {
                    continue;
                }
                $item = $this->gtree->items[$itemid];

                if ($item->needsupdate) {
                    $avghtml .= '<td class="cell c' . $columncount++.'"><span class="gradingerror">'.get_string('error').'</span></td>';
                    continue;
                }

                if (empty($sum_array[$item->id])) {
                    $sum_array[$item->id] = 0;
                }

                if (empty($ungraded_counts[$itemid])) {
                    $ungraded_count = 0;
                } else {
                    $ungraded_count = $ungraded_counts[$itemid]->count;
                }

                //do they want the averages to include all grade items
                if ($meanselection == GRADE_REPORT_MEAN_GRADED) {
                    $mean_count = $totalcount - $ungraded_count;
                } else { // Bump up the sum by the number of ungraded items * grademin
                    $sum_array[$item->id] += ($ungraded_count * $item->grademin);
                    $mean_count = $totalcount;
                }

                // Determine which display type to use for this average
                if (!empty($USER->gradeediting) && $USER->gradeediting[$this->courseid]) {
                    $displaytype = GRADE_DISPLAY_TYPE_REAL;

                } else if ($averagesdisplaytype == GRADE_REPORT_PREFERENCE_INHERIT) { // no ==0 here, please resave the report and user preferences
                    $displaytype = $item->get_displaytype();

                } else {
                    $displaytype = $averagesdisplaytype;
                }

                // Override grade_item setting if a display preference (not inherit) was set for the averages
                if ($averagesdecimalpoints == GRADE_REPORT_PREFERENCE_INHERIT) {
                    $decimalpoints = $item->get_decimals();
                } else {
                    $decimalpoints = $averagesdecimalpoints;
                }

                if (empty($sum_array[$item->id]) || $mean_count == 0) {
                    $this->gtree->items[$itemid]->avg = '-';
                } else {
                    $sum = $sum_array[$item->id];
                    $avgradeval = $sum/$mean_count;
                    $gradehtml = grade_format_gradevalue($avgradeval, $item, true, $displaytype, $decimalpoints);

                    $numberofgrades = '';
                    if ($shownumberofgrades) {
                        $numberofgrades = " ($mean_count)";
                    }

                    $this->gtree->items[$itemid]->avg = $gradehtml.$numberofgrades;
                }
            }
        }
    }

    /**
     * Trigger the grade_report_viewed event
     *
     * @since Moodle 2.9
     */
    public function viewed() {
        $event = \gradereport_pasaf\event\grade_report_viewed::create(
            array(
                'context' => $this->context,
                'courseid' => $this->courseid,
                'relateduserid' => $this->user->id,
            )
        );
        $event->trigger();
    }
}

function grade_report_pasaf_settings_definition(&$mform) {
    global $CFG;

    $options = array(-1 => get_string('default', 'grades'),
                      0 => get_string('hide'),
                      1 => get_string('show'));

    if (empty($CFG->grade_report_pasaf_showrank)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_pasaf_showrank', get_string('showrank', 'grades'), $options);
    $mform->addHelpButton('report_pasaf_showrank', 'showrank', 'grades');

    if (empty($CFG->grade_report_pasaf_showpercentage)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_pasaf_showpercentage', get_string('showpercentage', 'grades'), $options);
    $mform->addHelpButton('report_pasaf_showpercentage', 'showpercentage', 'grades');

    if (empty($CFG->grade_report_pasaf_showgrade)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_pasaf_showgrade', get_string('showgrade', 'grades'), $options);

    if (empty($CFG->grade_report_pasaf_showfeedback)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_pasaf_showfeedback', get_string('showfeedback', 'grades'), $options);

    if (empty($CFG->grade_report_pasaf_showweight)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_pasaf_showweight', get_string('showweight', 'grades'), $options);

    if (empty($CFG->grade_report_pasaf_showaverage)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_pasaf_showaverage', get_string('showaverage', 'grades'), $options);
    $mform->addHelpButton('report_pasaf_showaverage', 'showaverage', 'grades');

    if (empty($CFG->grade_report_pasaf_showlettergrade)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_pasaf_showedit', get_string('showedit', 'gradereport_pasaf'), $options);

if (empty($CFG->grade_report_pasaf_showedit)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_pasaf_showlettergrade', get_string('showlettergrade', 'grades'), $options);


    if (empty($CFG->grade_report_pasaf_showcontributiontocoursetotal)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_pasaf_showcontributiontocoursetotal]);
    }

    $mform->addElement('select', 'report_pasaf_showcontributiontocoursetotal', get_string('showcontributiontocoursetotal', 'grades'), $options);
    $mform->addHelpButton('report_pasaf_showcontributiontocoursetotal', 'showcontributiontocoursetotal', 'grades');

    if (empty($CFG->grade_report_pasaf_showrange)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_pasaf_showrange', get_string('showrange', 'grades'), $options);

    $options = array(0=>0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5);
    if (! empty($CFG->grade_report_pasaf_rangedecimals)) {
        $options[-1] = $options[$CFG->grade_report_pasaf_rangedecimals];
    }
    $mform->addElement('select', 'report_pasaf_rangedecimals', get_string('rangedecimals', 'grades'), $options);

    $options = array(-1 => get_string('default', 'grades'),
                      0 => get_string('shownohidden', 'grades'),
                      1 => get_string('showhiddenuntilonly', 'grades'),
                      2 => get_string('showallhidden', 'grades'));

    if (empty($CFG->grade_report_pasaf_showhiddenitems)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_pasaf_showhiddenitems]);
    }

    $mform->addElement('select', 'report_pasaf_showhiddenitems', get_string('showhiddenitems', 'grades'), $options);
    $mform->addHelpButton('report_pasaf_showhiddenitems', 'showhiddenitems', 'grades');

    //showtotalsifcontainhidden
    $options = array(-1 => get_string('default', 'grades'),
                      GRADE_REPORT_HIDE_TOTAL_IF_CONTAINS_HIDDEN => get_string('hide'),
                      GRADE_REPORT_SHOW_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowexhiddenitems', 'grades'),
                      GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowinchiddenitems', 'grades') );

    if (empty($CFG->grade_report_pasaf_showtotalsifcontainhidden)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_pasaf_showtotalsifcontainhidden]);
    }

    $mform->addElement('select', 'report_pasaf_showtotalsifcontainhidden', get_string('hidetotalifhiddenitems', 'grades'), $options);
    $mform->addHelpButton('report_pasaf_showtotalsifcontainhidden', 'hidetotalifhiddenitems', 'grades');

}

/**
 * Profile report callback.
 *
 * @param object $course The course.
 * @param object $user The user.
 * @param boolean $viewasuser True when we are viewing this as the targetted user sees it.
 */
function grade_report_pasaf_profilereport($course, $user, $viewasuser = false) {
    global $OUTPUT;
    if (!empty($course->showgrades)) {

        $context = context_course::instance($course->id);

        /// return tracking object
        $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'pasaf', 'courseid'=>$course->id, 'userid'=>$user->id));
        // Create a report instance
        $report = new grade_report_pasaf($course->id, $gpr, $context, $user->id, $viewasuser);

        // print the page
        echo '<div class="grade-report-user">'; // css fix to share styles with real report page
        if ($report->fill_table()) {
            echo $report->print_table(true);
        }
        echo '</div>';
    }
}

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 */
/* function gradereport_pasaf_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) { */
/*     global $CFG, $USER; */
/*     if (empty($course)) { */
/*         // We want to display these reports under the site context. */
/*         $course = get_fast_modinfo(SITEID)->get_course(); */
/*     } */
/*     $usercontext = context_user::instance($user->id); */
/*     $anyreport = has_capability('moodle/user:viewuseractivitiesreport', $usercontext); */

/*     // Start capability checks. */
/*     if ($anyreport || $iscurrentuser) { */
/*         // Add grade hardcoded grade report if necessary. */
/*         $gradeaccess = false; */
/*         $coursecontext = context_course::instance($course->id); */
/*         if (has_capability('moodle/grade:viewall', $coursecontext)) { */
/*             // Can view all course grades. */
/*             $gradeaccess = true; */
/*         } else if ($course->showgrades) { */
/*             if ($iscurrentuser && has_capability('moodle/grade:view', $coursecontext)) { */
/*                 // Can view own grades. */
/*                 $gradeaccess = true; */
/*             } else if (has_capability('moodle/grade:viewall', $usercontext)) { */
/*                 // Can view grades of this user - parent most probably. */
/*                 $gradeaccess = true; */
/*             } else if ($anyreport) { */
/*                 // Can view grades of this user - parent most probably. */
/*                 $gradeaccess = true; */
/*             } */
/*         } */
/*         if ($gradeaccess) { */
/*             $url = new moodle_url('/course/user.php', array('mode' => 'grade', 'id' => $course->id, 'user' => $user->id)); */
/*             $node = new core_user\output\myprofile\node('reports', 'grade', get_string('grade'), null, $url); */
/*             $tree->add_node($node); */
/*         } */
/*     } */
/* } */
