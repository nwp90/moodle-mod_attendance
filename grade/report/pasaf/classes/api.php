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
 * kuraCloud integration block.
 *
 * @package    gradereport_pasaf
 * @copyright  2017 Catalyst IT
 * @author     Matt Clarkson <mattc@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace gradereport_pasaf;

define('HTTP_OK', 200);

defined('MOODLE_INTERNAL') || die();

/**
 * Assessment Mapping API abstraction
 *
 * @copyright 2017 Catalyst IT
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {

    /**
     * Transport object
     *
     * @var transport
     */
    private $transport;

    /**
     * Construct api class with the supplied transport
     *
     * @param apitransport $transport
     */
    public function __construct(apitransport $transport) {
        $this->transport = $transport;
    }


    /**
     * Get list of mapped courses
     *
     * @return \stdClass
     */
    public function get_courses() {
        $data = $this->do_api_get('/modules/?page_size=all');

        $courses = array();

        foreach ($data as $d) {
            $course = new \stdClass;

            //error_log("courseurl: " . clean_param($d->url, PARAM_TEXT));
            //error_log("debased courseurl: " . $this->transport->debased_url(clean_param($d->url, PARAM_TEXT)));
            $course->url = $this->transport->debased_url(clean_param($d->url, PARAM_TEXT));
            $course->name = clean_param($d->name, PARAM_TEXT);
            $course->shortname = clean_param($d->shortname, PARAM_TEXT);
            $course->manual = clean_param($d->manual, PARAM_BOOL);
            $course->moodle = clean_param($d->manual, PARAM_BOOL);
            $course->cmap = clean_param($d->manual, PARAM_BOOL);
            $course->courseYear = clean_param($d->course_year, PARAM_INT);
            $course->calendarYear = clean_param($d->calendar_year, PARAM_INT);
            $courses[$course->shortname] = $course;
        }

        return $courses;
    }


    /**
     * Get module assessment info
     *
     * @param stdClass $course Object representing course as returned from get_courses
     * @return \stdClass
     */
    public function get_course_assessments($course) {

        //error_log("getting assessments for course: " . print_r($course, true));

        $results = $this->do_api_get($course->url.'assessments/?page_size=all');

        $assessments = array();
        foreach ($results as $result) {
            $assessment = new \stdClass;
            $assessment->url = $this->transport->debased_url(clean_param($result->url, PARAM_TEXT));
            $assessment->name = clean_param($result->name, PARAM_RAW);
            $assessment->gradetype = clean_param($result->gradetype, PARAM_INT);
            $assessment->scalename = clean_param($result->scalename, PARAM_RAW);
            $assessment->scaleglobal = clean_param($result->scaleglobal, PARAM_BOOL);
            $assessment->record = clean_param($result->record, PARAM_BOOL);
            $assessment->moodle = clean_param($result->moodle, PARAM_BOOL);
            $assessment->moodle_id = clean_param($result->moodle_id, PARAM_INT);
            $assessment->gradeitem_id = clean_param($result->moodle_gradeitem_id, PARAM_INT);
            $assessment->ordering = clean_param($result->ordering, PARAM_INT);
            $assessment->courseurl = $this->transport->debased_url(clean_param($result->module, PARAM_TEXT));

            $assessments[$assessment->gradeitem_id] = $assessment;
        }

        return $assessments;
    }


    /**
     * Do a GET requests to the API
     *
     * @param string $url of the API
     * @throws \Exception From API, transport or unknown source
     * @return stdClass|array
     */
    private function do_api_get($url) {
        list($response, $code, $error) = $this->transport->get($url);

        $responseobj = json_decode($response);

        if ($code != HTTP_OK) {
            if (isset($responseobj->message)) {
                throw new \Exception(get_string('apierrorgeneral', 'gradereport_pasaf',
                    clean_param($responseobj->message, PARAM_TEXT)));
            } else if (!empty($error)) {
                throw new \Exception(get_string('apierrortransport', 'gradereport_pasaf', clean_param($error, PARAM_TEXT)));
            } else {
                throw new \Exception(get_string('apierrorunknown', 'gradereport_pasaf', $code));
            }
        }
        if (is_null($responseobj)) {
            throw new \Exception('Error decoding response');
        }
        return $responseobj;
    }


    /**
     * Do a PUT requests to the API
     *
     * @param string $url of the API
     * @param stdClass $params Parameters to pass to the api
     * @throws \Exception From API, transport or unknown source
     * @return stdClass|array
     */
    private function do_api_put($url, $params) {
        list($response, $code, $error) = $this->transport->put($url, json_encode($params));

        $responseobj = json_decode($response);

        if ($code != HTTP_OK) {
            if (isset($responseobj->message)) {
                throw new \Exception(get_string('apierrorgeneral', 'gradereport_pasaf',
                    clean_param($responseobj->message, PARAM_TEXT)));
            } else if (!empty($error)) {
                throw new \Exception(get_string('apierrortransport', 'gradereport_pasaf', clean_param($error, PARAM_TEXT)));
            } else {
                throw new \Exception(get_string('apierrorunknown', 'gradereport_pasaf', $code));
            }
        }

        if (is_null($responseobj)) {
            throw new \Exception('Error decoding response');
        }
        return $responseobj;
    }


    /**
     * Do a PUT requests to the API
     *
     * @param string $url of the API
     * @param stdClass $params Parameters to pass to the api
     * @param boolean $expectresponse Does the api return a JSON
     * @throws \Exception From API, transport or unknown source
     * @return stdClass|array
     */
    private function do_api_post($url, $params=null, $expectresponse=true) {
        list($response, $code, $error) = $this->transport->post($url, is_null($params) ? '' : json_encode($params));

        $responseobj = json_decode($response);

        if ($code != HTTP_OK) {
            if (isset($responseobj->message)) {
                throw new \Exception(get_string('apierrorgeneral', 'gradereport_pasaf',
                    clean_param($responseobj->message, PARAM_TEXT)));
            } else if (!empty($error)) {
                throw new \Exception(get_string('apierrortransport', 'gradereport_pasaf', clean_param($error, PARAM_TEXT)));
            } else {
                throw new \Exception(get_string('apierrorunknown', 'gradereport_pasaf', $code));
            }
        }

        if ($expectresponse) {
            if (is_null($responseobj)) {
                throw new \Exception('Error decoding response');
            }
            return $responseobj;
        }

        return true;
    }
}