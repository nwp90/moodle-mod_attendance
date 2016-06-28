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
 * Tool for hierarchical numbering of question categories.
 *
 * @package    local_renumberquestioncategory
 * @copyright  2016 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/question/category_class.php");
require_once("$CFG->dirroot/lib/questionlib.php");

/**
 * Class representing custom question category
 *
 * @package    local_renumberquestioncategory
 * @copyright  2016 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_renumberquestioncategory_question_category_object {

    /**
     * Number category and all subcategories.
     *
     * @param string $categorypluscontext
     * @param string $prefix
     */
    public function renumber_category($categorypluscontext, $prefix = '') {
        $parts = explode(',', $categorypluscontext);
        $categoryid = $parts[0];
        $contextid = $parts[1];
        $context = context::instance_by_id($contextid);
        require_capability('moodle/question:managecategory', $context);
        $this->renumber_category_recursive($categoryid, $contextid, $prefix);
    }

    /**
     * Number category and all subcategories.
     *
     * @param int $categoryid
     * @param int $contextid
     * @param string $prefix
     */
    private function renumber_category_recursive($categoryid, $contextid, $prefix = '') {
        global $DB;

        $subcategories = $DB->get_records('question_categories',
                array('parent' => $categoryid, 'contextid' => $contextid), 'sortorder ASC, name ASC');
        $significantdigits = floor(log(count($subcategories), 10)) + 1;
        $sortorder = 1;
        foreach ($subcategories as $subcategory) {
            $sortorderstring = (string) $sortorder;
            $newprefix = $prefix . str_pad($sortorderstring, $significantdigits, '0', STR_PAD_LEFT) . '.';
            $subcategory->name = ltrim($subcategory->name, '0123456789. ');
            $subcategory->name = $newprefix . ' ' . $subcategory->name;
            $DB->update_record('question_categories', $subcategory);
            $this->renumber_category_recursive($subcategory->id, $contextid, $newprefix);
            $sortorder++;
        }
    }

    /**
     * Remove numbers from category and all subcategories.
     *
     * @param string $categorypluscontext
     */
    public function unnumber_category($categorypluscontext) {
        $parts = explode(',', $categorypluscontext);
        $categoryid = $parts[0];
        $contextid = $parts[1];
        $context = context::instance_by_id($contextid);
        require_capability('moodle/question:managecategory', $context);
        $this->unnumber_category_recursive($categoryid, $contextid);
    }

    /**
     * Remove numbers from category and all subcategories.
     *
     * @param int $categoryid
     * @param int $contextid
     */
    private function unnumber_category_recursive($categoryid, $contextid) {
        global $DB;

        $subcategories = $DB->get_records('question_categories',
                array('parent' => $categoryid, 'contextid' => $contextid));
        foreach ($subcategories as $subcategory) {
            $subcategory->name = ltrim($subcategory->name, '0123456789. ');
            $DB->update_record('question_categories', $subcategory);
            $this->unnumber_category_recursive($subcategory->id, $contextid);
        }
    }

}
