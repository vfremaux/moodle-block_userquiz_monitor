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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/userquiz_monitor/generators/history_chart.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/generators/attempts.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/generators/progress_bar.php');

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
 */
function update_selector($courseid, $catidslist, $mode, $rootcat, $quizzeslist = '') {
    global $DB, $PAGE;

    $response = '';
    $options = '';

    $renderer = $PAGE->get_renderer('block_userquiz_monitor');

    if (!empty($catidslist) && ($catidslist != 'null')) {
        if ($mode == 'mode0') {
            // Init variables.
            $subcategorieslist = '';
            $cpt = 0;
            $sql = "
                SELECT
                    id
                FROM
                    {question_categories}
                WHERE
                    parent IN ({$catidslist})
            ";

            $select = " parent IN ({$catidslist}) ";
            if ($subcats = $DB->get_records_select_menu('question_categories', $select, array(), 'id,name')) {
                $subcategorieslist = implode("','", array_keys($subcats));
            }

            if (!empty($subcategorieslist)) {
                // Init variables.
                $nbquestions = 0;
                $options = '';
                $sql = "
                    SELECT
                        COUNT(id)
                    FROM
                        {question}
                    WHERE
                        category IN ('{$subcategorieslist}') AND
                        qtype != 'random' AND
                        qtype != 'randomconstrained'
                    ";

                $recordsgetnbquestions = $DB->get_record_sql($sql, array());

                foreach ($recordsgetnbquestions as $recordnbquestions) {
                        $nbquestions = $recordnbquestions;
                }

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
                $response .= $renderer->category_monitor_container($options, $quizzeslist);
            }
        } else {
            $select = "
                category in ({$catidslist}) AND
                qtype != 'random' AND
                qtype != 'randomconstrained'
            ";
            $nbquestions = $DB->count_records_select('question', $select, array());

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

            $response .= $renderer->category_monitor_container($options, $quizzeslist);
        }
    } else {
        $response .= $renderer->empty_category_monitor_container();
    }

    return $response;
}

/**
 * get all states from a user
 * @param int $attemptid
 * @param int $userid
 * @param mixed $grade if 'answered', get all answered questions, whether they have positive grade or not.
 * if 'graded' get all non 0 graded records, if numeric, get records with such grade, get all if not defined
 */
function get_all_user_records($attemptuniqueid, $userid, $grade = null, $asrecordset = false) {
    global $DB;

    $gradeclause = '';
    if ($grade === 'answered') {
        $gradeclause = " AND qas.state = 'completed' ";
    } else if ($grade === 'graded') {
        $gradeclause = " AND qas.fraction IS NOT NULL AND qas.fraction > 0 ";
    }

    $sql = "
        SELECT
            qas.id,
            qa.questionid as question,
            MAX(qas.fraction) as grade,
            qa.questionusageid as uaid
        FROM
            {question_attempt_steps} qas,
            {question_attempts} qa
        WHERE
            qas.questionattemptid = qa.id AND
            qa.questionusageid = ?
            $gradeclause
        GROUP BY
            qa.questionid
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

function userquizmonitor_count_available_attempts($userid, $quizid) {
    global $DB;

    $select = "
        userid = ? AND
        quiz = ?
    ";
    $usedattempts = $DB->get_records_select('quiz_attempts', $select, array($userid, $quizid));
    $params = array('userid' => $userid, 'quizid' => $quizid);
    $limitsenabled = $DB->get_field('qa_usernumattempts', 'enabled', array('quizid' => $quizid));
    if (!$limitsenabled) {
        // Always give a new attempts to requirer.
        return 1;
    }
    $availableattempts = $DB->get_field('qa_usernumattempts_limits', 'maxattempts', $params);

    $userattemptscount = (is_array($usedattempts)) ? count($usedattempts) : 0;
    return $availableattempts - $userattemptscount;
}

function userquiz_monitor_get_cattreeids($catid, &$catids) {
    global $DB;

    static $deepness = 0;

    if ($subcats = $DB->get_records_menu('question_categories', array('parent' => $catid), 'id,name')) {
        $catids = array_merge($catids, array_keys($subcats));
        foreach (array_keys($subcats) as $subcatid) {
            $deepness++;
            if ($deepness > 10) {
                die('too deep');
            }
            userquiz_monitor_get_cattreeids($subcatid, $catids);
            $deepness--;
        }
    }
}
