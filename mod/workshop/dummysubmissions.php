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
 * Create dummy submissions for students who have not (yet?) created their own,
 * for situations in which e.g. offline activity is to be assessed.
 *
 * @package    mod_workshop
 * @copyright  2019 Nick Phillips <nick.phillips@otago.ac.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/locallib.php');

$cmid     = required_param('cmid', PARAM_INT);            // course module
$confirm  = optional_param('confirm', false, PARAM_BOOL); // confirmation

$cm       = get_coursemodule_from_id('workshop', $cmid, 0, false, MUST_EXIST);
$course   = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$workshop = new workshop($DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST), $cm, $course);

$PAGE->set_url('/mod/workshop/dummysubmissions.php', array('cmid' => $cmid));

require_login($course, false, $cm);

# If you're going to allocate, you might need to be able to do this too...
require_capability('mod/workshop:allocate', $workshop->context);
require_capability('moodle/course:manageactivities', $workshop->context);

error_log("confirm is: (" . $confirm . ")");
if ($confirm) {
    error_log("confirmed confirm");
    if (!confirm_sesskey()) {
        throw new moodle_exception('confirmsesskeybad');
    }

    /* Populate blank submissions for all students that have not
     * submitted anything, to allow them to be assessed.
     */
    error_log("workshop phase = " . $workshop->phase);
    if ($workshop->phase == workshop::PHASE_SUBMISSION) {

        /**
        * Grab all students in this course
        */
        $students = $workshop->get_potential_authors(false);
        error_log("got " . sizeof($students) . " students: " . print_r($students, true));

        /**
        * Loop through all students
        * if they have not submitted anything, stub out the simplest dummy record
        */
        foreach ($students AS $student) {
            error_log("student: " . $student->id);
            $parameters = array(
                'workshopid' => $cm->instance,
                'authorid' => $student->id
            );

            if(!$DB->record_exists('workshop_submissions',$parameters)) {
                error_log("no record");
                $timestamp = time();
                $dummy = array(
                    'workshopid' => $cm->instance,
                    'authorid' => $student->id,
                    'timecreated' => $timestamp,
                    'timemodified' => $timestamp,
                    'title' => 'Offline work / dummy submission',
                    'content' => 'Offline work / dummy submission created by admin',
                    'contentformat' => 1
                );
                $DB->insert_record('workshop_submissions', $dummy);
            }
        }
    }
    redirect($workshop->view_url());
}

$PAGE->set_title($workshop->name);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add("Create dummy submissions");

//
// Output starts here
//
$output = $PAGE->get_renderer('mod_workshop');
echo $output->header();
echo $output->heading(format_string($workshop->name));
echo $output->confirm("Create dummy submissions to allow marking of offline work?",
                      new moodle_url($PAGE->url, array('confirm' => 1)), $workshop->view_url());
echo $output->footer();
