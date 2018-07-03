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
 * Quiz cbmgrades report version information.
 * Derived by Tony Gardner-Medwin from the responses plugin
 * Version for grades based on CB Average (up to 300%) 
 * Adapted 18//5/2018 for GDPR requirements, thanks to J-M Vedrine for help
 * @package   quiz_cbmgrades
 * @copyright 2013, 2014 Tony Gardner-Medwin
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2018051800; //with GDPR requirements, thanks to J-M Vedrine
$plugin->requires = 2014051200;
$plugin->component = 'quiz_cbmgrades';
$plugin->maturity = MATURITY_STABLE; // This is considered as ready for production sites.
$plugin->release = 'v2.7-r4'; 
