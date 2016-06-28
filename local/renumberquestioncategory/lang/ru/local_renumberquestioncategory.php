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

$string['pluginname'] = 'Нумерация категорий вопросов';
$string['renumbercategory'] = 'Нумерация категорий';
$string['renumberthiscategory'] = 'Пронумеровать эту категорию';
$string['removenumbering'] = 'Удалить нумерацию';
$string['selectcategory'] = 'Выберите категорию для нумерации';
$string['selectcategoryinfo'] = 'Укажите категорию, которую вы хотите пронумеровать. Все подкатегории будут пронумерованы в соответствии с текущим порядком. Выберите "Верхний уровень", чтобы пронумеровать все категории в этом контексте. Все существующие цифры, точки и пробелы в начале названий категорий будут удалены перед добавлением новых номеров.';
$string['prefix'] = 'Префикс';
$string['prefixerror'] = 'Префикс может содержать только цифры и точки';
