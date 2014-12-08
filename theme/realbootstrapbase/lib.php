<?php
// This file is not part of Moodle - http://moodle.org/

/**
 * Add Jquery
 */
function theme_realbootstrapbase_page_init(moodle_page $page) {
   $page->requires->jquery();
   $page->requires->jquery_plugin('expander');
   $page->requires->jquery_plugin('rwdimagemaps');
 }
