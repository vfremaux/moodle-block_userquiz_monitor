<?php
// This file keeps track of upgrades to 
// the course_summary block
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_block_userquiz_monitor_upgrade($oldversion=0) {
    global $CFG, $THEME, $DB;

    $result = true;

    $dbman = $DB->get_manager();

    if ($oldversion < 2010081700) {

        $table = new xmldb_table('userquiz_monitor_prefs');

        // Adding fields to table userquiz_monitor_prefs.
        $table->add_field_info('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->add_field_info('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('resultsdepth', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        // Adding keys to table userquiz_monitor_prefs.
        $table->add_key_info('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Launch create table for userquiz_monitor_prefs.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_block_savepoint(true, 2010081700, 'userquiz_monitor');
    }

    if ($result && $oldversion < 2011012800) {
        // Define field examsdepth to be added to userquiz_monitor_prefs.
        $table = new xmldb_table('userquiz_monitor_prefs');
        $field = new xmldb_field('examsdepth');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'resultsdepth');

        // Launch add field examsdepth.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_block_savepoint(true, 2011012800, 'userquiz_monitor');
    }

    if ($result && $oldversion < 2011021100) {
        // Define field examsdepth to be added to userquiz_monitor_prefs.
        $table = new xmldb_table('userquiz_monitor_prefs');
        $field = new xmldb_field('blockid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0', 'id');

        // Launch add field examsdepth.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_block_savepoint(true, 2011021100, 'userquiz_monitor');
    }

    if ($result && $oldversion < 2011041900) {

        // Define table userquiz_monitor_cat_stats to be created.
        $table = new xmldb_table('userquiz_monitor_cat_stats');

        // Adding fields to table userquiz_monitor_cat_stats.
        $table->add_field_info('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->add_field_info('userquiz', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('categoryid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('attemptid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('qcount', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('acount', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('ccount', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('amatched', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('cmatched', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        // Adding keys to table userquiz_monitor_cat_stats.
        $table->add_key_info('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Launch create table for userquiz_monitor_cat_stats.
        if (!$dbman->table_exists($table)) {
           $dbman->create_table($table);
        }

        upgrade_block_savepoint(true, 2011041900, 'userquiz_monitor');
    }

    if ($result && $oldversion < 2011042100) {

        // Define table userquiz_monitor_user_stats to be created.
        $table = new xmldb_table('userquiz_monitor_user_stats');

        // Adding fields to table userquiz_monitor_user_stats.
        $table->add_field_info('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->add_field_info('blockid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('attemptid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('coverageseen', XMLDB_TYPE_NUMBER, '6, 2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('coveragematched', XMLDB_TYPE_NUMBER, '6, 2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        // Adding keys to table userquiz_monitor_user_stats.
        $table->add_key_info('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table userquiz_monitor_user_stats.
        $table->add_index_info('index_userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        $table->add_index_info('index_attemptid', XMLDB_INDEX_NOTUNIQUE, array('attemptid'));
        $table->add_index_info('index_blockid', XMLDB_INDEX_NOTUNIQUE, array('blockid'));

        // Launch create table for userquiz_monitor_user_stats.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table userquiz_monitor_coverage to be created.
        $table = new xmldb_table('userquiz_monitor_coverage');

        // Adding fields to table userquiz_monitor_coverage.
        $table->add_field_info('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null, null);
        $table->add_field_info('blockid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('userid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('questionid', XMLDB_TYPE_INTEGER, '11', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('usecount', XMLDB_TYPE_INTEGER, '6', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');
        $table->add_field_info('matchcount', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, null, '0');

        // Adding keys to table userquiz_monitor_coverage.
        $table->add_key_info('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table userquiz_monitor_coverage.
        $table->add_index_info('index_userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        $table->add_index_info('index_questionid', XMLDB_INDEX_NOTUNIQUE, array('questionid'));
        $table->add_index_info('index_blockid', XMLDB_INDEX_NOTUNIQUE, array('blockid'));

        // Launch create table for userquiz_monitor_coverage.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define index index_userquiz (not unique) to be added to userquiz_monitor_cat_stats.
        $table = new xmldb_table('userquiz_monitor_cat_stats');
        $index = new xmldb_index('index_userquiz');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('userquiz'));

        // Launch add index index_userquiz.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define index index_categoryid (not unique) to be added to userquiz_monitor_cat_stats.
        $index = new xmldb_index('index_categoryid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('categoryid'));

        // Launch add index index_userquiz.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define index index_categoryid (not unique) to be added to userquiz_monitor_cat_stats.
        $index = new xmldb_index('index_userid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Launch add index index_userquiz.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define index index_categoryid (not unique) to be added to userquiz_monitor_cat_stats.
        $index = new xmldb_index('index_attemptid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('attemptid'));

        // Launch add index index_userquiz.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define index index_blockid (not unique) to be added to userquiz_monitor_prefs.
        $table = new xmldb_table('userquiz_monitor_prefs');
        $index = new xmldb_index('index_blockid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('blockid'));

        // Launch add index index_blockid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define index index_blockid (not unique) to be added to userquiz_monitor_prefs.
        $index = new xmldb_index('index_userid');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Launch add index index_blockid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        upgrade_block_savepoint(true, 2011042100, 'userquiz_monitor');
    }

    return $result;
}
