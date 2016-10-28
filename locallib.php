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

    // Add time range limit.
    $userprefs = $DB->get_record('userquiz_monitor_prefs', array('userid' => $userid, 'blockid' => $blockid));
    $timerangefilterclause = '';
    if (@$userprefs->resultsdepth > 0) {
        $limit = time() - ($userprefs->resultsdepth * 7 * DAYSECS);
        $timerangefilterclause = " AND timestart >= $limit ";
    }

    list ($insql, $inparams) = $DB->get_in_or_equal($quizzesids);
    $params = array_merge(array($userid), $inparams);

    // Get user's attempts list.
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

function block_userquiz_monitor_init_rootcats($rootcategory, &$rootcats) {
    global $DB;

    if (!$rootcats = $DB->get_records('question_categories', array('parent' => $rootcategory), 'sortorder, id', 'id,name')) {
        return get_string('configwarningmonitor', 'block_userquiz_monitor');
    }

    foreach ($rootcats as $catid => $cat) {

        $rootcats[$catid]->cptA = 0; // Number of question type A.
        $rootcats[$catid]->cptC = 0; // Number of question type C.
        $rootcats[$catid]->cpt = 0; // Number of question type A or C.
        $rootcats[$catid]->goodA = 0; // Number of matched questions type A.
        $rootcats[$catid]->goodC = 0; // Number of matched questions type C.
        $rootcats[$catid]->good = 0; // Number of matched questions type A or C.
        $rootcats[$catid]->ratioA = 0; // Ratio type A.
        $rootcats[$catid]->ratioC = 0; // Ratio type C.
        $rootcats[$catid]->ratio = 0;
        $rootcats[$catid]->questiontypes = array();

        $catidarr = array($cat->id);
        $cattreelist = userquiz_monitor_get_cattreeids($cat->id, $catidarr);
        $cattreelist = implode("','", $catidarr);

        $select = " category IN ('$cattreelist') AND defaultmark = 1000 ";
        if ($DB->record_exists_select('question', $select, array())) {
            $rootcats[$catid]->questiontypes['C'] = 1;
        }
        $select = " category IN ('$cattreelist') AND defaultmark = 1 ";
        if ($DB->record_exists_select('question', $select, array())) {
            $rootcats[$catid]->questiontypes['A'] = 1;
        }
    }
    return;
}

function block_userquiz_monitor_get_exam_attempts($quizid) {
    global $DB, $USER;

    // Get user's attempts list.
    $sql  = "
        SELECT
            distinct(ua.id),
            ua.uniqueid,
            ua.timefinish
        FROM
            {quiz_attempts} ua
        WHERE
            ua.userid = ? AND
            ua.timefinish <> 0 AND
            ua.quiz = ?
        ORDER BY
            ua.timefinish ASC
    ";
    return $DB->get_records_sql($sql, array($USER->id, $quizid));
}

function block_userquiz_monitor_compute_all_results(&$userattempts, $rootcategory, &$rootcats, &$attempts, &$overall) {
    global $USER, $DB;

    $errormsg = false;
    $rootcatkeys = array_keys($rootcats);

    if (!empty($userattempts)) {
        foreach ($userattempts as $ua) {
            if ($allstates = get_all_user_records($ua->uniqueid, $USER->id, null, true)) {

                if ($allstates->valid()) {
                    foreach ($allstates as $state) {

                        // Get question informations.
                        $fields = 'id, defaultmark, category';
                        $question = $DB->get_record_select('question', " id = ? ", array($state->question), $fields);
                        $parent = $DB->get_field('question_categories', 'parent', array('id' => $question->category));

                        if (!$parent) {
                            // Fix lost states.
                            continue;
                        }

                        if (!in_array($parent, $rootcatkeys)) {
                            // Discard  all results that fall outside the revision tree.
                            $errormsg = get_string('errorquestionoutsidescope', 'block_userquiz_monitor');
                            continue;
                        }

                        while (!in_array($parent, array_keys($rootcats)) && $parent != 0) {
                            $parent = $DB->get_field('question_categories', 'parent', array('id' => $parent));
                        }

                        if (!isset($attempts[$state->uaid][$question->category])) {
                            $attempts[$state->uaid][$question->category] = new StdClass;
                        }

                        if (!isset($attempts[$state->uaid][$parent])) {
                            $attempts[$state->uaid][$parent] = new StdClass;
                        }

                        if (!isset($attempts[$state->uaid][$rootcategory])) {
                            $attempts[$state->uaid][$rootcategory] = new StdClass();
                        }

                        $attempts[$state->uaid][$question->category]->timefinish = $ua->timefinish;
                        $attempts[$state->uaid][$parent]->timefinish = $ua->timefinish;
                        $attempts[$state->uaid][$rootcategory]->timefinish = $ua->timefinish;

                        @$rootcats[$parent]->cpt++;

                        @$attempts[$state->uaid][$question->category]->cpt++;
                        @$attempts[$state->uaid][$parent]->cpt++;
                        @$attempts[$state->uaid][$rootcategory]->cpt++;

                        $overall->cpt++;

                        if ($question->defaultmark == '1000') {
                            $rootcats[$parent]->cptC++;
                            @$attempts[$state->uaid][$question->category]->cptC++;
                            @$attempts[$state->uaid][$parent]->cptC++;
                            @$attempts[$state->uaid][$rootcategory]->cptC++;
                            $overall->cptC++;
                            if ($state->grade > 0) {
                                $rootcats[$parent]->goodC++;
                                @$attempts[$state->uaid][$question->category]->goodC++;
                                @$attempts[$state->uaid][$parent]->goodC++;
                                @$attempts[$state->uaid][$rootcategory]->goodC++;
                                $overall->goodC++;
                            }
                        } else {
                            $rootcats[$parent]->cptA++;
                            @$attempts[$state->uaid][$question->category]->cptA++;
                            @$attempts[$state->uaid][$parent]->cptA++;
                            @$attempts[$state->uaid][$rootcategory]->cptA++;
                            $overall->cptA++;
                            if ($state->grade > 0) {
                                $rootcats[$parent]->goodA++;
                                @$attempts[$state->uaid][$question->category]->goodA++;
                                @$attempts[$state->uaid][$parent]->goodA++;
                                @$attempts[$state->uaid][$rootcategory]->goodA++;
                                $overall->goodA++;
                            }
                        }
                        if ($state->grade > 0) {
                            $rootcats[$parent]->good++;
                            @$attempts[$state->uaid][$question->category]->good++;
                            @$attempts[$state->uaid][$parent]->good++;
                            @$attempts[$state->uaid][$rootcategory]->good++;
                            $overall->good++;
                        }
                    }
                }
                $allstates->close();

            } else {
                $errormsg = get_string('error2', 'block_userquiz_monitor');
            }
        }
    }

    // Build the stucture of the reporting.

    // Post compute ratios.

    $overall->ratioA = ($overall->cptA == 0) ? 0 : round(($overall->goodA / $overall->cptA ) * 100);
    $overall->ratioC = ($overall->cptC == 0) ? 0 : round(($overall->goodC / $overall->cptC ) * 100);
    $overall->ratio = ($overall->cpt == 0) ? 0 : round(($overall->good / $overall->cpt ) * 100);

    return $errormsg;
}

function block_userquiz_monitor_compute_ratio(&$rootcats) {

    $maxratio = 0;

    foreach (array_keys($rootcats) as $catid) {
        $ratioc = $rootcats[$catid]->goodC / $rootcats[$catid]->cptC;
        $rootcats[$catid]->ratioC = (@$rootcats[$catid]->cptC == 0) ? 0 : round($ratioc * 100);
        $ratioa = $rootcats[$catid]->goodA / $rootcats[$catid]->cptA;
        $rootcats[$catid]->ratioA = (@$rootcats[$catid]->cptA == 0) ? 0 : round($ratioa * 100);
        $ratio = $rootcats[$catid]->good / $rootcats[$catid]->cpt;
        $rootcats[$catid]->ratio = (@$rootcats[$catid]->cpt == 0) ? 0 : round($ratio * 100);
        if ($maxratio < $rootcats[$catid]->ratio) {
            $maxratio = $rootcats[$catid]->ratio;
        }
    }

    if ($maxratio == 0) {
        $maxratio = 1;
    }

    return $maxratio;
}