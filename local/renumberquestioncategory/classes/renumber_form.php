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

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for selecting category to renumber.
 *
 * @package    local_renumberquestioncategory
 * @copyright  2016 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_renumberquestioncategory_renumber_form extends moodleform {

    /**
     * Form definition.
     */
    protected function definition() {
        global $OUTPUT;

        $mform = $this->_form;

        $context = $this->_customdata['context'];

        $mform->addElement('header', 'header', get_string('selectcategory', 'local_renumberquestioncategory'));

        $message = $OUTPUT->box(get_string('selectcategoryinfo', 'local_renumberquestioncategory'), 'generalbox boxaligncenter');
        $mform->addElement('html', $message);

        $qcontexts = new question_edit_contexts($context);
        $contexts = $qcontexts->having_cap('moodle/question:managecategory');

        $options = array();
        $options['contexts'] = $contexts;
        $options['top'] = true;
        $qcategory = $mform->addElement('questioncategory', 'category', get_string('category', 'question'), $options);

        $qcategory = $mform->addElement('text', 'prefix', get_string('prefix', 'local_renumberquestioncategory'));
        $mform->setType('prefix', PARAM_TEXT);

        $buttonarray = array();
        $classarray = array('class' => 'form-submit');
        $buttonarray[] = &$mform->createElement('submit', 'renumber',
                        get_string('renumberthiscategory', 'local_renumberquestioncategory'), $classarray);
        $buttonarray[] = &$mform->createElement('submit', 'removenumbering',
                        get_string('removenumbering', 'local_renumberquestioncategory'), $classarray);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Form validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $prefix = $data['prefix'];

        if (preg_match('/^[0-9.]*$/', $prefix) === 0) {
            $errors['prefix'] = get_string('prefixerror', 'local_renumberquestioncategory');;
        }

        return $errors;
    }

}
