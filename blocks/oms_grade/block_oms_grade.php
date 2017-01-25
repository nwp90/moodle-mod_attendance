<?php

class block_oms_grade extends block_base {
    public function init() {
        $this->title = get_string('defaulttitle', 'block_oms_grade');
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }
        
        $this->content         =  new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        
        $gradelinktext = get_string('defaulttitle', 'block_oms_grade');
        
        if ($this->page->context->contextlevel === CONTEXT_COURSE) {
            $course = $this->page->course;
            if (!can_access_course($course, null, '', true)) {
                return $this->content;
            }
            $coursegrades = new moodle_url('/grade/report/index.php', array('id' => $course->id));
            $this->content->text   = '<a class="gradelink" href="'.$coursegrades.'"><span class="fa fa-list-alt"></span> '.$gradelinktext.'</a>';
            $this->content->footer = '';
        }
        return $this->content;
    }

    public function applicable_formats() {
        return array(
            'site-index' => false,
            'course-view' => true, 
            'course-view-social' => false,
            'mod' => false
        );
    }
}