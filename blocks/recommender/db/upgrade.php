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
 * This file keeps track of upgrades to the recommender block
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @since 2.0
 * @package blocks
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @param int $oldversion
 * @param object $block
 */
function xmldb_block_recommender_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2011110400) { // should only happen on acceptance & dev
        // delete all block instances
        $DB->delete_records('block_instances', array('blockname'=>'recommender'));
        // and reapply above handylinks where it exists
        $targets = $DB->get_records('block_instances', array('blockname'=>'handylinks'));
        if ($targets) {
            foreach ($targets as $target) {
                $newblock = $target;
                $newblock->blockname = 'recommender';
                $newblock->id = null;
                $newblock->defaultweight = $target->defaultweight - 5;
                $DB->insert_record('block_instances', $newblock);
            }
        }

        /// savepoint reached
        upgrade_block_savepoint(true,  2011110400, 'recommender');
    }

    if ($oldversion < 2013011502) {
        // Tidy up database for removal of OER service.
        set_config('oer_enabled', NULL, 'block_recommender');
        set_config('oer_indexed', NULL, 'block_recommender');

        // Savepoint reached.
        upgrade_block_savepoint(true,  2013011502, 'recommender');
    }

    return true;
}