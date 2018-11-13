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

require_once($CFG->dirroot.'/blocks/userquiz_monitor/generators/history_chart.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/generators/attempts.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/generators/progress_bar.php');

function block_userquiz_monitor_get_categories_for_root() {
    global $COURSE, $DB;

    $coursecontext = context_course::instance($COURSE->id);

    $categories = array();
    $categories = $categories + $DB->get_records_menu('question_categories', array('contextid' => $coursecontext->id), 'sortorder', 'id,name');

    $coursecat = new StdClass;
    $coursecat->parent = $COURSE->category;

    while ($coursecat->parent != 0) {
        $catcontext = context_coursecat::instance($coursecat->parent);
        $coursecatcats = $DB->get_records_menu('question_categories', array('contextid' => $catcontext->id), 'parent,sortorder', 'id,name');
        if ($coursecatcats) {
            $categories = $categories + $coursecatcats;
        }
        $coursecat = $DB->get_record('course_categories', array('id' => $coursecat->parent), 'id, parent');
    }

    $systemcats = $DB->get_records_menu('question_categories', array('contextid' => context_system::instance()->id), 'parent,sortorder', 'id, name');
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

/**
 * Initializes the overall counter set.
 */
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

/**
 * Given a top root category for questions, initializes the child root cats
 * found under this absolute root. Root categories are initialized with counters for
 * aggregating results. Only the first sublevel is scanned.
 *
 * @param int $rootcategory id of the root question category where all the stuff resides.
 * @param arrayref &$rootcats an array to be filed by the function
 * @return void if success or an error message;
 */
function block_userquiz_monitor_init_rootcats($rootcategory, &$rootcats) {
    global $DB, $OUTPUT;

    if (!$rootcats = $DB->get_records('question_categories', array('parent' => $rootcategory), 'sortorder, id', 'id,name')) {
        return $OUTPUT->notification(get_string('configwarningemptycats', 'block_userquiz_monitor'), 'notifyproblem');
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
        $rootcats[$catid]->hassubs = $DB->count_records('question_categories', array('parent' => $catid));

        $catidarr = array($cat->id);
        $cattreelist = block_userquiz_monitor_get_cattreeids($cat->id, $catidarr);
        $cattreelist = implode("','", $catidarr);

        $select = " category IN ('$cattreelist') AND defaultmark = 1000 AND qtype NOT LIKE 'random%' ";
        if ($DB->record_exists_select('question', $select, array())) {
            $rootcats[$catid]->questiontypes['C'] = 1;
        }
        if (optional_param('qdebug', false, PARAM_BOOL)) {
            if ($questions = $DB->get_records_select('question', $select, array(), 'id', 'id,category')) {
                foreach ($questions as $q) {
                    $rootcats[$catid]->questions['C'][$q->category][] = $q->id;
                }
            }
        }

        $select = " category IN ('$cattreelist') AND defaultmark = 1 AND qtype NOT LIKE 'random%' ";
        if ($DB->record_exists_select('question', $select, array())) {
            $rootcats[$catid]->questiontypes['A'] = 1;
        }
        if (optional_param('qdebug', false, PARAM_BOOL)) {
            if ($questions = $DB->get_records_select('question', $select, array(), 'id', 'id,category')) {
                foreach ($questions as $q) {
                    $rootcats[$catid]->questions['A'][$q->category][] = $q->id;
                }
            }
        }

    }
    return;
}

/**
 * Gets all exam attempts for a quiz
 * @param int $quizid the quiz ID.
 */
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

/**
 * Computes the grades of a set of attempts.
 * @param arrayref &$userattempts an array of attempts
 * @param int $rootcategory the id of the root category for all questions
 * @param arrayref &$rootcats an array of all categories of first level as child of rootcategory
 * @param arrayref &$attempts the output stats array for each attempts
 * @param arrayref &$overall the summarized stats across all attempts.
 * @param string $mode 'training' or 'exam' mode. When training, non answered questions are ignored in stats.
 */
function block_userquiz_monitor_compute_all_results(&$userattempts, $rootcategory, &$rootcats, &$attempts, &$overall, $mode = 'training') {
    global $USER, $DB, $OUTPUT;
    static $qstates = array();

    $errormsg = false;
    $rootcatkeys = array_keys($rootcats);

    $graded = null;
    if ($mode == 'training') {
        $graded = 'answered';
    }

    if (!empty($userattempts)) {
        foreach ($userattempts as $ua) {
            if ($allstates = block_userquiz_monitor_get_all_user_records($ua->uniqueid, $USER->id, $graded, true)) {

                if ($allstates->valid()) {
                    foreach ($allstates as $state) {

                        if (($mode == 'training') && is_null($state->grade)) {
                            continue;
                        }

                        // Get question informations.
                        $fields = 'id, defaultmark, category';
                        $question = $DB->get_record_select('question', " id = ? ", array($state->question), $fields);
                        $parent = $DB->get_field('question_categories', 'parent', array('id' => $question->category));

                        if (!array_key_exists($question->id, $qstates)) {
                            if (!$parent) {
                                // Fix lost states.
                                $qstates[$question->id] = false;
                                continue;
                            }

                            while (!in_array($parent, $rootcatkeys) && $parent != 0) {
                                // Seek for parent in one of our rootcats.
                                $parent = $DB->get_field('question_categories', 'parent', array('id' => $parent));
                            }

                            if (!$parent) {
                                // We could not find any candidate rootcat.
                                // Discard  all results that fall outside the revision tree with error message.
                                $errormsg = get_string('errorquestionoutsidescope', 'block_userquiz_monitor');
                                // Mark status cache for next results.
                                $qstates[$question->id] = false;
                                continue;
                            }
                            $qstates[$question->id] = true; // Validate.
                        } else {
                            // Question has already been stated for, we can check it in cache.
                            if (!$qstates[$question->id]) {
                                continue;
                            }
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
                $errormsg = $OUTPUT->notification(get_string('error2', 'block_userquiz_monitor'), 'notifyproblem');
            }
        }

        $overall->ratioA = ($overall->cptA) ? round($overall->goodA / $overall->cptA * 100) : 0 ;
        $overall->ratioC = ($overall->cptC) ? round($overall->goodC / $overall->cptC * 100) : 0 ;
        $overall->ratio = ($overall->cpt) ? round($overall->good / $overall->cpt * 100) : 0 ;

        return $errormsg;
    }

    // Build the stucture of the reporting.

    // Post compute ratios.

    $overall->ratioA = ($overall->cptA == 0) ? 0 : round(($overall->goodA / $overall->cptA ) * 100);
    $overall->ratioC = ($overall->cptC == 0) ? 0 : round(($overall->goodC / $overall->cptC ) * 100);
    $overall->ratio = ($overall->cpt == 0) ? 0 : round(($overall->good / $overall->cpt ) * 100);

    return $errormsg;
}

function block_userquiz_monitor_is_passing($block, $attemptstats) {
    $pass = $attemptstats->ratioA >= $block->config->rateAserie;

    if (!empty($block->config->dualserie)) {
        $pass = $pass && ($attemptstats->ratioC >= $block->config->rateCserie);
    }

    return $pass;
}

function block_userquiz_monitor_compute_ratios(&$rootcats) {

    $maxratio = 0;

    foreach (array_keys($rootcats) as $catid) {
        if (@$rootcats[$catid]->cptC != 0) {
            $ratioc = $rootcats[$catid]->goodC / $rootcats[$catid]->cptC;
        } else {
            $ratioc = 0;
        }
        $rootcats[$catid]->ratioC = round($ratioc * 100);

        if (@$rootcats[$catid]->cptA != 0) {
            $ratioa = $rootcats[$catid]->goodA / $rootcats[$catid]->cptA;
        } else {
            $ratioa = 0;
        }
        $rootcats[$catid]->ratioA = round($ratioa * 100);

        if (@$rootcats[$catid]->cpt != 0) {
            $ratio = $rootcats[$catid]->good / $rootcats[$catid]->cpt;
        } else {
            $ratio = 0;
        }
        $rootcats[$catid]->ratio = round($ratio * 100);

        if ($maxratio < $rootcats[$catid]->ratio) {
            $maxratio = $rootcats[$catid]->ratio;
        }
    }

    if ($maxratio == 0) {
        $maxratio = 1;
    }

    return $maxratio;
}

/**
 * Seeks for an instance of a userquiz_monitor block that would be attached to
 * this attempt.
 *
 * @param int $quizid
 * @param string $mode
 * @return object the matching block configuration, or false.
 */
function block_userquiz_monitor_check_has_quiz($course, $quizid) {
    global $DB;

    $context = context_course::instance($course->id);

    // Get all candidates userquiz monitors in course.
    $params = array('blockname' => 'userquiz_monitor', 'parentcontextid' => $context->id);
    $uqmbs = $DB->get_records('block_instances', $params, 'id, configdata');
    if (empty($uqmbs)) {
        return;
    }

    // Check config and if current quiz is the exam quiz.
    foreach ($uqmbs as $uqm) {

        $config = unserialize(base64_decode($uqm->configdata));

        if ($quizid == $config->examquiz) {
            $config->mode = 'exam';
            $config->uqm = $uqm;
            return $config;
        }

        if (!empty($config->trainingquizzes)) {
            if (in_array($quizid, $config->trainingquizzes)) {
                $config->mode = 'training';
                $config->uqm = $uqm;
                return $config;
            }
        }
    }
    return false;
}

/**
 * Build scenario in a course.
 */
function userquiz_build_course() {
    global $COURSE;

    if ($COURSE->format == 'page') {
        userquiz_build_pages();
    }

    userquiz_add_quizzes();

}

/**
 * get all states from a user
 * @param int $attemptid
 * @param int $userid
 * @param mixed $grade if 'answered', get all answered questions, whether they have positive grade or not.
 * if 'graded' get all non 0 graded records, if numeric, get records with such grade, get all if not defined
 */
function block_userquiz_monitor_get_all_user_records($attemptuniqueid, $userid, $grade = null, $asrecordset = false) {
    global $DB;

    $gradeclause = '';
    if ($grade === 'answered') {
        $gradeclause = ' AND qas.fraction IS NOT NULL ';
    } else if ($grade === 'graded') {
        $gradeclause = ' AND qas.fraction IS NOT NULL AND qas.fraction > 0 ';
    }

    $sql = "
        SELECT
            qas.questionattemptid,
            qa.questionid as question,
            MAX(qas.fraction) as grade,
            qa.questionusageid as uaid
        FROM
            {question_attempt_steps} qas,
            {question_attempts} qa
        WHERE
            qas.questionattemptid = qa.id AND
            qa.questionusageid = ? AND
            qas.state != 'todo'
            $gradeclause
        GROUP BY
            qas.questionattemptid
    ";

    if ($asrecordset) {
        $rs = $DB->get_recordset_sql($sql, array($attemptuniqueid));
        return $rs;
    }

    if (!$records = $DB->get_records_sql($sql, array($attemptuniqueid))) {
        return array();
    }

    return $records;
}

function block_userquiz_monitor_count_available_attempts($userid, $quizid) {
    global $DB;

    $select = "
        userid = ? AND
        quiz = ?
    ";
    $usedattempts = $DB->get_records_select('quiz_attempts', $select, array($userid, $quizid));
    $params = array('userid' => $userid, 'quizid' => $quizid);
    $userlimitsenabled = $DB->get_field('qa_usernumattempts', 'enabled', array('quizid' => $quizid));
    if (!$userlimitsenabled) {
        if ($availableattempts = $DB->get_field('quiz', 'attempts', array('id' => $quizid))) {
            // globally limited quiz.
            $userattemptscount = (is_array($usedattempts)) ? count($usedattempts) : 0;
            return max(0, $availableattempts - $userattemptscount);
        }
        // Always give a new attempts to requirer.
        return 1;
    }
    $availableattempts = $DB->get_field('qa_usernumattempts_limits', 'maxattempts', $params);

    $userattemptscount = (is_array($usedattempts)) ? count($usedattempts) : 0;
    return max(0, $availableattempts - $userattemptscount);
}

function block_userquiz_monitor_get_cattreeids($catid, &$catids) {
    global $DB;

    static $deepness = 0;

    if ($subcats = $DB->get_records_menu('question_categories', array('parent' => $catid), 'sortorder', 'id,name')) {
        $catids = array_merge($catids, array_keys($subcats));
        foreach (array_keys($subcats) as $subcatid) {
            $deepness++;
            if ($deepness > 10) {
                die('too deep');
            }
            block_userquiz_monitor_get_cattreeids($subcatid, $catids);
            $deepness--;
        }
    }
}

function block_userquiz_monitor_get_quiz_by_numquestions($courseid, $theblock, $nbquestions) {
    global $DB;

    list($insql, $params) = $DB->get_in_or_equal($theblock->config->trainingquizzes);
    $params = array_merge(array($courseid), $params);

    $sql = "
        SELECT
            count(qs.questionid) as numquestions,
            qs.quizid
           FROM
            {quiz} q,
            {quiz_slots} qs
        WHERE
            q.course = ? AND
            qs.quizid = q.id AND
            q.id $insql
        GROUP BY
            qs.quizid
    ";

    $quizes = $DB->get_records_sql($sql, $params);

    if (!isset($quizes[$nbquestions])) {
        print_error('erroruserquiznoquiz', 'block_userquiz_monitor');
    }

    return $quizes[$nbquestions]->quizid;
}

/**
 * Format the data to feed the generator histogram graph
 * this can display category progress among attempts
 */
function calcul_hist($categoryid, &$counters) {

    $datas = null;

    if ($counters) {

        foreach ($counters as $attemptid => $counterset) {
            // Init variables.

            // If there are no types A, C questions then do nothing.
            if ((@$counterset[$categoryid]->cptA > 0) || @$counterset[$categoryid]->cptC > 0) {
                $data = array (
                    'attemptid' => $attemptid,
                    'attempttimefinish' => $counterset[$categoryid]->timefinish,
                    'cptgoodanswersA' => 0 + @$counterset[$categoryid]->goodA,
                    'cptgoodanswersC' => 0 + @$counterset[$categoryid]->goodC,
                    'nbquestionsA' => 0 + @$counterset[$categoryid]->cptA,
                    'nbquestionsC' => 0 + @$counterset[$categoryid]->cptC,
                );
                $datas[] = $data;
            }
        }
    }

    return urlencode(json_encode($datas));
}

/**
 * On changes of the current selection, update the question amount choice list
 * @param int $courseid
 * @param string $catidlist
 * @param string $mode mode0 works for master categories digging in all subcats. mode1 works directly with given subcategories.
 * @return the question amount selector
 */
function block_userquiz_monitor_update_selector($courseid, $catidslist, $mode, $rootcat, $quizzeslist = '') {
    global $DB, $PAGE, $CFG;

    $response = '';
    $options = '';

    $renderer = $PAGE->get_renderer('block_userquiz_monitor', 'training');

    if (!is_array($catidslist)) {
        $catids = explode(',', $catidslist);
    } else {
        $catids = $catidslist;
    }
    list($insql, $inparams) = $DB->get_in_or_equal($catids);

    if (!empty($catidslist) && ($catidslist != 'null')) {
        if ($mode == 'mode0') {
            // Processes update in subcategories from main categories.
            // Init variables.
            $subcategorieslist = '';
            $cpt = 0;

            $select = " parent $insql ";
            if ($subcats = $DB->get_records_select_menu('question_categories', $select, $inparams, 'id,name', 'sortorder')) {
                $subcategorieslist = array_keys($subcats);
            }

            if (!empty($subcategorieslist)) {

                list($insql, $inparams) = $DB->get_in_or_equal($subcategorieslist);

                // Init variables.
                $nbquestions = 0;
                $options = '';
                $select = "
                    category $insql AND
                    qtype != 'random' AND
                    qtype != 'randomconstrained'
                ";
                $recordsgetnbquestions = $DB->count_records_select('question', $select, $inparams);

                /*
                foreach ($recordsgetnbquestions as $recordnbquestions) {
                        $nbquestions = $recordnbquestions;
                }
                */
                $nbquestions = $recordsgetnbquestions;

                if (strlen($nbquestions) > 1) {
                    // Group nb questions per 10.
                    $val = 10;
                    $nbquestionstest = (substr($nbquestions, 0, -1)) * 10;

                    while ($val <= $nbquestionstest) {
                        if ($val <= 100) {
                            $options .= '<option value="'.$val.'">'.$val.'</option>';
                        }
                        $val = $val + 10;
                    }
                } else {
                    $nbquestionstest = $nbquestions;

                    for ($i = 1; $i <= $nbquestionstest; $i++) {
                        $options .= '<option value="'.$i.'">'.$i.'</option>';
                    }
                }
                $response .= $renderer->launch_gui($options, $quizzeslist);
            }
        } else {
            // Processes update in given categories.

            $select = "
                category $insql AND
                qtype != 'random' AND
                qtype != 'randomconstrained'
            ";
            $nbquestions = $DB->count_records_select('question', $select, $inparams);

            // Make question amount choice options.
            if (strlen($nbquestions) > 1) {
                $nbquestionstest = (substr($nbquestions, 0, -1)) * 10;

                $val = 10;
                while ($val <= $nbquestionstest) {
                    if ($val <= 100) {
                        $options .= '<option value="'.$val.'">'.$val.'</option>';
                    }
                    $val = $val + 10;
                }
            } else {
                $nbquestionstest = $nbquestions;

                for ($i = 1; $i <= $nbquestionstest; $i++) {
                    $options .= '<option value="'.$i.'">'.$i.'</option>';
                }
            }

            $response .= $renderer->launch_gui($options, $quizzeslist);
        }
    } else {
        $response .= $renderer->empty_launch_gui();
    }

    return $response;
}
