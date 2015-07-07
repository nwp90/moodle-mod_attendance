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
 * This file defines the setting form for the quiz cbmgrades report.
 * Derived by Tony Gardner-Medwin from the responses plugin (copyright 2008 Jean-Michel Vedrine)
 * @package   quiz_cbmgrades
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/attemptsreport_form.php');


/**
 * Quiz cbmgrades report settings form.
 *
 * @copyright 2008 Jean-Michel Vedrine
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_cbmgrades_settings_form extends mod_quiz_attempts_report_form {

    protected function other_preference_fields(MoodleQuickForm $mform) {

        $mform->addGroup(array(
          $mform->createElement('advcheckbox', 'chosenrs', '', get_string('chosenresps', 'quiz_cbmgrades')),
          $mform->createElement('advcheckbox', 'qdata', '', get_string('qdata', 'quiz_cbmgrades')),
          ), 'coloptions', get_string('showthe', 'quiz_cbmgrades'), array(' '), false);
        $mform->disabledIf('qdata', 'attempts', 'eq', quiz_attempts_report::ENROLLED_WITHOUT);
        $mform->disabledIf('chosenrs', 'attempts', 'eq', quiz_attempts_report::ENROLLED_WITHOUT);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
