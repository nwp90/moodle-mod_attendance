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
 * Tool for hierarchical numbering of question categories.
 *
 * @package    local_renumberquestioncategory
 * @copyright  2016 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Renumber question categories';
$string['renumbercategory'] = 'Renumber categories';
$string['renumberthiscategory'] = 'Renumber this category';
$string['removenumbering'] = 'Remove numbering';
$string['selectcategory'] = 'Select category to renumber';
$string['selectcategoryinfo'] = 'Specify category you want to renumber. All subcategories will be hierarchicaly renumbered. Select "Top" to number all categories in context. All existing digits, dots and spaces will be removed from the beginning of category names before adding new numbers.';
$string['prefix'] = 'Prefix';
$string['prefixerror'] = 'Prefix may contain only digits and dots';
