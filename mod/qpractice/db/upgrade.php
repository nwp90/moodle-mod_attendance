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
 * This file keeps track of upgrades to the qpractice module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_qpractice
 * @copyright  2013 Jayesh Anandani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute qpractice upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_qpractice_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    // And upgrade begins here. For each one, you'll need one
    // block of code similar to the next one. Please, delete
    // this comment lines once this file start handling proper
    // upgrade code.

    // Lines below (this included)  MUST BE DELETED once you get the first version
    // of your module ready to be installed. They are here only
    // for demonstrative purposes and to show how the qpractice
    // iself has been upgraded.

    // For each upgrade block, the file qpractice/version.php
    // needs to be updated . Such change allows Moodle to know
    // that this file has to be processed.

    // To know more about how to write correct DB upgrade scripts it's
    // highly recommended to read information available at:
    // http://docs.moodle.org/en/Development:XMLDB_Documentation
    // and to play with the XMLDB Editor (in the admin menu) and its
    // PHP generation posibilities.

    // First example, some fields were added to install.xml on 2007/04/01
    // And that's all. Please, examine and understand the 3 example blocks above. Also
    // it's interesting to look how other modules are using this script. Remember that
    // the basic idea is to have "blocks" of code (each one being executed only once,
    // when the module version (version.php) is updated.
    if ($oldversion < 2013070400) {
        // Define field behaviour to be added to qpractice.
        $table = new xmldb_table('qpractice');
        $field = new xmldb_field('behaviour', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'introformat');

        // Conditionally launch add field behaviour.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Qpractice savepoint reached.
        upgrade_mod_savepoint(true, 2013070400, 'qpractice');
    }

    if ($oldversion < 2013070500) {

        // Define table qpractice_session to be created.
        $table = new xmldb_table('qpractice_session');

        // Adding fields to table qpractice_session.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('qpracticeid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('questionusageid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('typeofpractice', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('time', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('goalpercentage', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('noofquestions', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table qpractice_session.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
        $table->add_key('questionusageid', XMLDB_KEY_FOREIGN, array('questionusageid'), 'question_usages', array('id'));
        $table->add_key('qpracticeid', XMLDB_KEY_FOREIGN, array('qpracticeid'), 'qpractice', array('id'));
        $table->add_key('categoryid', XMLDB_KEY_FOREIGN, array('categoryid'), 'question_categories', array('id'));

        // Conditionally launch create table for qpractice_session.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Qpractice savepoint reached.
        upgrade_mod_savepoint(true, 2013070500, 'qpractice');
    }

    if ($oldversion < 2013081800) {

        // Define field practicedate to be added to qpractice_session.
        $table = new xmldb_table('qpractice_session');
        $field = new xmldb_field('practicedate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'noofquestions');

        // Conditionally launch add field practicedate.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Qpractice savepoint reached.
        upgrade_mod_savepoint(true, 2013081800, 'qpractice');
    }

    if ($oldversion < 2013081800) {

        // Define field timecreated to be added to qpractice_session.
        $table = new xmldb_table('qpractice_session');
        $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'practicedate');

        // Conditionally launch add field timecreated.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Qpractice savepoint reached.
        upgrade_mod_savepoint(true, 2013081800, 'qpractice');
    }

    if ($oldversion < 2013082600) {

        // Define field practicedate to be dropped from qpractice_session.
        $table = new xmldb_table('qpractice_session');
        $field = new xmldb_field('timecreated');

        // Conditionally launch drop field practicedate.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Qpractice savepoint reached.
        upgrade_mod_savepoint(true, 2013082600, 'qpractice');
    }

    if ($oldversion < 2013082600) {

        // Define field status to be added to qpractice_session.
        $table = new xmldb_table('qpractice_session');
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'inprogress', 'practicedate');

        // Conditionally launch add field status.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Qpractice savepoint reached.
        upgrade_mod_savepoint(true, 2013082600, 'qpractice');
    }

    if ($oldversion < 2013082606) {

        // Define field totalnoofquestions to be added to qpractice_session.
        $table = new xmldb_table('qpractice_session');
        $field = new xmldb_field('totalnoofquestions', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, '0', 'status');

        // Conditionally launch add field totalnoofquestions.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Qpractice savepoint reached.
        upgrade_mod_savepoint(true, 2013082606, 'qpractice');
    }

    if ($oldversion < 2013082606) {

        // Define field totalnoofquestionsright to be added to qpractice_session.
        $table = new xmldb_table('qpractice_session');
        $field = new xmldb_field('totalnoofquestionsright', XMLDB_TYPE_INTEGER, '20',
                null, XMLDB_NOTNULL, null, '0', 'totalnoofquestions');

        // Conditionally launch add field totalnoofquestionsright.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Qpractice savepoint reached.
        upgrade_mod_savepoint(true, 2013082606, 'qpractice');
    }

    if ($oldversion < 2013082609) {

        // Define field marksobtained to be added to qpractice_session.
        $table = new xmldb_table('qpractice_session');
        $field = new xmldb_field('marksobtained', XMLDB_TYPE_NUMBER, '10, 2', null,
                XMLDB_NOTNULL, null, '0', 'totalnoofquestionsright');

        // Conditionally launch add field marksobtained.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Qpractice savepoint reached.
        upgrade_mod_savepoint(true, 2013082609, 'qpractice');
    }

    if ($oldversion < 2013082609) {

        // Define field totalmarks to be added to qpractice_session.
        $table = new xmldb_table('qpractice_session');
        $field = new xmldb_field('totalmarks', XMLDB_TYPE_NUMBER, '10, 2', null, XMLDB_NOTNULL, null, '0', 'marksobtained');

        // Conditionally launch add field totalmarks.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Qpractice savepoint reached.
        upgrade_mod_savepoint(true, 2013082609, 'qpractice');
    }

    // Lines above (this included) MUST BE DELETED once you get the first version of
    // yout module working. Each time you need to modify something in the module (DB
    // related, you'll raise the version and add one upgrade block here.

    // Final return of upgrade result (true, all went good) to Moodle.
    return true;
}