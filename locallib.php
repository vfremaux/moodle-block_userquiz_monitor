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
 * @package     block_userquiz_monitor
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

function block_userquiz_monitor_get_categories_for_root() {
    global $COURSE, $DB;

    $coursecontext = context_course::instance($COURSE->id);

    $categories = array();

    $categories = $categories + $DB->get_records_menu('question_categories', array('contextid' => $coursecontext->id));

    $coursecat = new StdClass;
    $coursecat->parent = $COURSE->category;

    while ($coursecat->parent != 0) {
        $catcontext = context_coursecat::instance($coursecat->parent);
        $coursecatcats = $DB->get_records_menu('question_categories', array('contextid' => $catcontext->id));
        if ($coursecatcats) {
            $categories = $categories + $coursecatcats;
        }
        $coursecat = $DB->get_record('course_categories', array('id' => $coursecat->parent), 'id, parent');
    }

    $systemcats = $DB->get_records_menu('question_categories', array('contextid' => context_system::instance()->id));
    if ($systemcats) {
        $categories = $categories + $systemcats;
    }

    return $categories;
}

function block_userquiz_monitor_get_user_attempts($blockid, $quizzesids, $userid = 0) {
    global $DB, $USER;

    if ($userid == 0) {
        $userid = $USER->id;
    }

    // add time range limit
    $userprefs = $DB->get_record('userquiz_monitor_prefs', array('userid' => $userid, 'blockid' => $blockid));
    $timerangefilterclause = '';
    if (@$userprefs->resultsdepth > 0) {
        $limit = time() - ($userprefs->resultsdepth * 7 * DAYSECS);
        $timerangefilterclause = " AND timestart >= $limit ";
    }

    list ($insql, $inparams) = $DB->get_in_or_equal($quizzesids);
    $params = array_merge(array($userid), $inparams);

    // Get user's attempts list
    $sql = "
        SELECT
            distinct(ua.id),
            ua.uniqueid,
            ua.timefinish
        FROM
            {quiz_attempts} ua
        WHERE
            ua.userid = ? AND
            ua.timefinish <> 0 AND
            ua.quiz $insql
            $timerangefilterclause
        ORDER BY
            ua.timefinish;
    ";

    return $DB->get_records_sql($sql, $params);
}

function block_userquiz_monitor_init_overall() {

    $overall = new StdClass;
    $overall->cpt = 0;
    $overall->cptA = 0;
    $overall->cptC = 0;
    $overall->good = 0;
    $overall->goodA = 0;
    $overall->goodC = 0;
    $overall->ratio = 0;
    $overall->ratioA = 0;
    $overall->ratioC = 0;

    return $overall;
}