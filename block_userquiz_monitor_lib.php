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
 * @return the question amount selector
 */
function update_selector($courseid, $catidslist, $mode, $rootcat, $quizzeslist = '') {
    global $DB, $PAGE, $CFG;

    $response = '';
    $options = '';

    $renderer = $PAGE->get_renderer('block_userquiz_monitor', 'training');

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
                $response .= $renderer->launch_gui($options, $quizzeslist);
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

            $response .= $renderer->launch_gui($options, $quizzeslist);
        }
    } else {
        $response .= $renderer->empty_launch_gui();
    }

    return $response;
}
