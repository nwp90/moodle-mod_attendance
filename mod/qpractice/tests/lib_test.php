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
 * Unit tests for (some of) mod/qpractice/locallib.php.
 *
 * @package    mod_qpractice
 * @category   test
 * @copyright  2016 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/qpractice/lib.php');

/**
 * @copyright  2016 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class mod_qpractice_lib_testcase extends advanced_testcase {

    public function test_qpractice_delete_instance() {
        global $SITE, $DB;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Setup a qpractice instance.
        $qpracticegenerator = $this->getDataGenerator()->get_plugin_generator('mod_qpractice');
        $qpractice = $qpracticegenerator->create_instance(array('course' => $SITE->id));
        qpractice_delete_instance($qpractice->id);

        // Check that the qpractice was removed.
        $count = $DB->count_records('qpractice', array('id' => $qpractice->id));
        $this->assertEquals(0, $count);

    }

}
