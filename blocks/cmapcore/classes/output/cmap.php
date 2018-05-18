<?php

/* my_overview block seems to be good example */

namespace block_cmapcore\output;
defined('MOODLE_INTERNAL') || die();

use renderable;

class cmap implements renderable {

    /**
     * @var string Base URL of CMap instance
     */
    public $baseurl;

    /**
     * Constructor.
     *
     * @param string $tab The tab to display.
     */
    public function __construct($cmap) {
        $this->baseurl = $cmap['baseurl'];
        // store this so we can maybe check pagetype later
        $this->page = $cmap['page'];

        // Pinched from block_course_summary
        // Page type starts with 'course-view' and the page's course ID is not equal to the site ID.
        /* if (strpos($page->pagetype, PAGE_COURSE_VIEW) === 0 && $page->course->id != SITEID) { */
        /*     $course = $this->page->course; */
        /* } */

        // This will always be the course we are within, or the site course.
        $this->course = $this->page->course;
    }

    /* This is yuck, but it makes code elsewhere simpler.
       Get property from cmap or cmap->course as appropriate,
       return something usable from defaults, or throw an
       exception if desired property not known.
    */
    public function __get($property) {
        $defaults = [
            'baseurl' => '',
            'coursefullname' => '',
            'courseshortname' => '',
            'courseid' => 0
        ];

        $base = $this;
        $prop = $property;
        if (strncmp('course', $property, strlen('course')) == 0) {
            $base = $this->course;
            $prop = substr($property, strlen('course'));
        }
        if (property_exists($base, $prop) && isset($base->{$prop})) {
            return $base->{$prop};
        }
        elseif (array_key_exists($property, $defaults)) {
            return $defaults[$property];
        }
        throw new \coding_exception('Attempted to get nonexistent property of cmap object ('.$property.')');
    }

    public function show() {
        return blocks_name_allowed_in_format('cmapcore', $this->page->pagetype);
    }

}
