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
 * Restore code
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_qpractice
 * @copyright  2013 Jayesh Anandani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Base class for restoring up all the qpractice settings and attempt data for an
 * access rule qpractice sub-plugin.
 */
class restore_mod_qpractice_access_subplugin extends restore_subplugin {

    /**
     * Use this method to describe the XML paths that store your sub-plugin's
     * settings for a particular qpractice.
     */
    protected function define_qpractice_subplugin_structure() {
        // Do nothing by default.
    }

    /**
     * Use this method to describe the XML paths that store your sub-plugin's
     * settings for a particular qpractice attempt.
     */
    protected function define_attempt_subplugin_structure() {
        // Do nothing by default.
    }
}
