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
 * The qpractice_viewed event.
 *
 * @package    mod_qpractice
 * @copyright  2015 Marcus Green
 * @since     Moodle 2.9
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qpractice\finish;

defined('MOODLE_INTERNAL') || die();

namespace mod_qpractice\event;

class qpractice_finished extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'qpractice';
    }

    public function get_description() {
        return "On course: {$this->courseid} qpracticeid: {$this->objectid} was attempted";
    }

}
