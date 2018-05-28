<?php

class block_cmapcore extends block_base {
    
    function has_config() {
        return true;
    }
    //$allowHTML = get_config('cmapcore', 'Allow_HTML');
    
    
    // Infuriatingly, this seems to get called twice.
    //
    public function init() {
        $this->title = get_string('curriculummap', 'block_cmapcore');
    }

    public function specialization() {
        global $DB, $PAGE;
        $this->cmap = new \block_cmapcore\output\cmap(
            [
                'baseurl' => 'https://medmap.otago.ac.nz',
                'page' => $this->page
            ]
        );
        $PAGE->requires->js_call_amd(
            'block_cmapcore/element-list',
            'init',
            array(
                $this->cmap->baseurl,
                $this->cmap->courseid,
                $this->cmap->courseshortname
            ));
    }

    public function applicable_formats() {
        return [
            'all' => true,
            'course' => true,
            'mod' => true,
            'mod-quiz' => false,
            'mod-*-mod' => true // course/modedit.php
        ];
    }
            
    public function get_content() {
        global $CFG, $PAGE;
    	if ($this->content !== null) {
            return $this->content;
        }
        $renderer = $this->page->get_renderer('block_cmapcore');

        $this->content         = new stdClass;
        $this->content->text   = $renderer->cmap_block($this->cmap);

        /* if $this->content comes back completely empty, block will not be displayed at all :) */

        return;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function hide_header() {
        return false;
    }

    // This appears to be unnecessary - we're getting class="block_cmapcore block block_cmapcore"
    public function html_attributes() {
        $attributes = parent::html_attributes(); // Get default values
        $attributes['class'] .= ' block_'. $this->name(); // Append our class to class attribute
        return $attributes;
    }

}