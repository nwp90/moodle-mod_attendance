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
 * Lib for editing questions filters.
 *
 * @package    moodlecore
 * @subpackage questionbank
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/outputcomponents.php');
require_once($CFG->dirroot . '/question/editlib.php');

/**
 * Get question bank search conditions
 *
 * @return array
 */
function local_question_filters_get_question_bank_search_conditions() {
    return array(new local_question_filters_question_bank_search_condition());
}

/**
 * Get extra fields
 *
 * @param integer $questionid
 * @return array
 */
function local_question_filters_get_question_extra_fields($questionid) {
    global $DB;
    return $DB->get_record('local_question_filters', array('questionid' => $questionid));
}

/**
 * Save extra fields
 *
 * @param object $question
 * @return array
 */
function local_question_filters_save_question_extra_fields($question) {
    global $DB;
    $DB->delete_records('local_question_filters', array(
         'questionid' => $question->questionid
    ));
    return $DB->insert_record('local_question_filters', $question);
}

/**
 * Get filters from form
 *
 * @return object
 */
function local_question_filters_get_filter_from_form() {
    $filter = (object)array(
        'filter_name' => trim(optional_param('filter_name', '', PARAM_TEXT)),
        'filter_questiontext' => trim(optional_param('filter_questiontext', '', PARAM_TEXT)),
        'filter_meta_field1' => trim(optional_param('filter_meta_field1', '', PARAM_TEXT)),
        // Empty field -> null, anything else -> convert to integer.
        'filter_defaultmark' =>
            trim(optional_param('filter_defaultmark', '', PARAM_TEXT)) !== '' ?
                    optional_param('filter_defaultmark', 0, PARAM_INT) :
                    null,
        'filter_defaultmark_search' => optional_param('filter_defaultmark_search', null, PARAM_RAW),
    );
    if (!in_array($filter->filter_defaultmark_search, array('>' => '>', '>=' => '>=', '=' => '=', '<=' => '<=', '<' => '<'))) {
        $filter->filter_defaultmark_search = '=';
    }
    return $filter;
}

/**
 * Get filter SQL
 *
 * @param array $params
 * @param string $where
 * @param boolean $filter
 * @param boolean $sqlparamnames
 * @param string $sqlprefix
 * @return boolean
 */
function local_question_filters_get_filter_sql(&$params, &$where, $filter = null, $sqlparamnames = true, $sqlprefix = 'q.') {
    global $DB;
    if ($filter === null) {
         $filter = local_question_filters_get_filter_from_form();
    } else if (!$filter) {
         return;
    } else {
        if (!$filter->filter_defaultmark_search || $filter->filter_defaultmark == '') {
               $filter->filter_defaultmark = null;
        }
    }

    if (!$filter->filter_name
            && !$filter->filter_questiontext
            && !$filter->filter_meta_field1
            && $filter->filter_defaultmark === null) {
        // No filtering.
        if (!is_array($where) && empty($where)) {
            $where = '1=1';
        }
        return;
    }

    $addwhere = '(1=1';
    if ($filter->filter_name) {
        $params['filter_name'] = '%'.$filter->filter_name.'%';
        $addwhere .= ' AND '.$DB->sql_like($sqlprefix.'name', (!$sqlparamnames ? '?' : ':filter_name'), false);
    }
    if ($filter->filter_questiontext) {
        $params['filter_questiontext'] = '%'.$filter->filter_questiontext.'%';
        $addwhere .= ' AND '.$DB->sql_like($sqlprefix.'questiontext', (!$sqlparamnames ? '?' : ':filter_questiontext'), false);
    }
    if ($filter->filter_meta_field1) {
        $params['filter_meta_field1'] = '%'.$filter->filter_meta_field1.'%';
        $addwhere .= ' AND (SELECT COUNT(*)
                FROM {local_question_filters} lqf
                WHERE lqf.questionid='.($sqlprefix ? $sqlprefix : '{question}.').'id'.
                    ' AND '.$DB->sql_like('lqf.meta_field1', (!$sqlparamnames ? '?' : ':filter_meta_field1'), false).') >= 1';
    }
    if ($filter->filter_defaultmark !== null) {
        $params['filter_defaultmark'] = $filter->filter_defaultmark;
        $addwhere .= ' AND '.$sqlprefix."defaultmark ".
                        $filter->filter_defaultmark_search." ".(!$sqlparamnames ? '?' : ":filter_defaultmark");
    }
    $addwhere .= ')';

    if (is_array($where)) {
        $where[] = $addwhere;
    } else if (is_string($where)) {
        if ($where) {
            $where .= ' AND ';
        }
        $where .= $addwhere;
    } else {
        die('error wrong where');
    }
    // Filtered.
    return true;
}

/**
 * Filter questions, search conditions
 *
 * @package   mod_quiz
 * @copyright  2007 Jamie Pratt me@jamiep.org
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_question_filters_question_bank_search_condition  extends \core_question\bank\search\condition  {
    /**
     * @var $where for SQL
     */
    protected $where = array();
    /**
     * @var $params array of params
     */
    protected $params = array();

    /**
     * construct
     *
     * @return boolean
     */
    public function __construct() {
        $this->init();
    }

    /**
     * where + 'and'
     *
     * @return string
     */
    public function where() {
        return join(' AND ', $this->where);
    }

    /**
     * get params
     *
     * @return array
     */
    public function params() {
        return $this->params;
    }

    /**
     * display options
     *
     * @return string
     */
    public function display_options() {
        $return = '';
        $return .= html_writer::label(get_string('questionname','question'), 'filter_name');
        $return .= html_writer::empty_tag('input',
            array('name' => 'filter_name',
                'id' => 'filter_name',
                'class' => 'searchoptions',
                'value' => optional_param('filter_name', null, PARAM_TEXT)));

        $return .= html_writer::label(get_string('questiontext','question'), 'filter_questiontext');
        $return .= html_writer::empty_tag('input',
            array('name' => 'filter_questiontext',
                'id' => 'filter_questiontext',
                'class' => 'searchoptions',
                'value' => optional_param('filter_questiontext', null, PARAM_TEXT)));

        /*
        $return .= html_writer::label('Metadatenfeld', 'filter_meta_field1');
        $return .= html_writer::empty_tag('input',
            array('name' => 'filter_meta_field1',
                'id' => 'filter_meta_field1',
                'class' => 'searchoptions',
                'value' => optional_param('filter_meta_field1', null, PARAM_TEXT)));
        */
        
        $return .= html_writer::label(get_string('defaultmark','question'), 'filter_defaultmark');
        $return .= html_writer::select(
            array('>' => '>', '>=' => '>=', '=' => '=', '<=' => '<=', '<' => '<'),
                'filter_defaultmark_search',
                optional_param('filter_defaultmark_search', '=', PARAM_RAW), false);
        $return .= html_writer::empty_tag('input',
            array('name' => 'filter_defaultmark',
                'id' => 'filter_defaultmark',
                'class' => 'searchoptions',
                'value' => optional_param('filter_defaultmark', null, PARAM_TEXT)));

        $return .= '<div>'.
        html_writer::empty_tag('input',
        array('type' => 'submit', 'value' => get_string('search')))
        .'</div>';

        return $return;
    }

    /**
     * display advanced options
     *
     * @return nothing
     */
    public function display_options_adv() {
        // Return 'Advanced UI from search plugin here<br />';.
    }

    /**
     * init
     *
     * @return nothing
     */
    private function init() {
        global $DB;
        local_question_filters_get_filter_sql($this->params, $this->where);
    }
}


