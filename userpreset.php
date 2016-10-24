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
 * Get user's dashbord selection an configure the url sending to the quiz 
 *
 * @package     block_userquiz_monitor
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

// Init variables.
$selectionstr = 0;

$courseid = required_param('courseid', PARAM_INT);
$nbquestions = optional_param('selectornbquestions', 0, PARAM_INT);    // Number of required questions.
$blockid = required_param('blockid', PARAM_INT);
$mode = required_param('mode', PARAM_TEXT);

$context = context_block::instance($blockid);
$PAGE->set_context($context);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('coursemisconf');
}

if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))) {
    print_error('badblockinstance', 'block_userquiz_monitor');
}

$theBlock = block_instance('userquiz_monitor', $instance);

if (!empty($courseid) && !empty($mode)) {

    if ($mode == 'test') {

        // Choose the appropriate quiz.

        $quizzlistforblock = implode("','", $theBlock->config->trainingquizzes);

        $sql =  "
            SELECT
                count(qs.questionid) as numquestions,
                qs.quizid
               FROM
                {quiz} q,
                {quiz_slots} qs
            WHERE
                q.course = ? AND
                qs.quizid = q.id AND
                q.id IN ('{$quizzlistforblock}')
            GROUP BY
                qs.quizid
        ";

        $quizes = $DB->get_records_sql($sql, array($courseid));

        if (!isset($quizes[$nbquestions])) {
            print_error('erroruserquiznoquiz', 'block_userquiz_monitor');
        }

        $testid = $quizes[$nbquestions]->quizid;

        // Retrieve settings checkbox on the left side.
        $cbpl = preg_grep('/^cb_pl/', array_keys($_GET));
        $cbpl_h = preg_grep('/^h_cb_pl/', array_keys($_GET));

        // If there is at least one selection.
        if (!empty($cbpl)) {
            // Retrieve settings checkbox on the right side.
            $cbpr = preg_grep('/^cbpr/', array_keys($_GET));

            // If there is at least one selection.
            if ($cbpr != '' && $cbpr != null) {
                // Filter subcategories id.
                $categorieslistpr = preg_replace('/^cbpr/', '', $cbpr);
                $selectionstrpr = implode(',', $categorieslistpr);
                $selectionstr = $selectionstrpr;
            } else {
                // Filter categories id.
                $categorieslistpl = preg_replace('/^cb_pl/', '', $cbpl);
                $selectionstrpl = implode(',', $categorieslistpl);
                $selectionstr = $selectionstrpl;
            }

            if ($testid == 0) {
                echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
            } else {
                if ($selectionstr != 0) {
                    $cm = get_coursemodule_from_instance('quiz', $testid);
                    $params = array('cmid' => $cm->id,
                                    'setconstraints' => 1,
                                    'constraints' => $selectionstr,
                                    'sesskey' => sesskey());
                    redirect(new moodle_url('/mod/quiz/startattempt.php', $params));
                } else {
                    echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
                }
            }
        } else {
            // Retrieve settings checkbox on the right side.
            $cbpr = preg_grep('/^cbpr/', array_keys($_GET));

            // If there is at least one selection.
            if ($cbpr != "" && $cbpr != null) {
                // Filter subcategories id.
                $categorieslistpr = preg_replace('/^cbpr/', '', $cbpr);
                $selectionstrpr = implode(',', $categorieslistpr);
                $selectionstr = $selectionstrpr;
                $nbquestions = optional_param('selectornbquestions', 0, PARAM_INT);
            } else {
                if ($cbpl_h != "" && $cbpl_h != null) {
                    $nbquestions = optional_param('selectornbquestions', 0, PARAM_INT);

                    // Filter categories id.
                    $categorieslistpl = preg_replace('/^h_cb_pl/', '', $cbpl_h);
                    $selectionstrpl = implode(',', $categorieslistpl);
                    $selectionstr = $selectionstrpl;
                }
            }

            if ($testid == 0) {
                if (debugging()) {
                    echo "Problem in finding a test";
                }
                $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
            } else {
                if ($selectionstr != 0) {
                    $cm = get_coursemodule_from_instance('quiz', $testid);
                    $params = array('cmid' => $cm->id,
                                    'setconstraints' => 1 ,
                                    'constraints' => $selectionstr,
                                    'sesskey' => sesskey());
                    redirect(new moodle_url('/mod/quiz/startattempt.php', $params));
                } else {
                    if (debugging()) {
                        echo "Problem in the selection";
                    }
                    echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
                }
            }
        }
    }

    if ($mode == 'examination') {
        redirect(new moodle_url('/mod/quiz/attempt.php', array('q' => $theBlock->config->examquiz)));
    }
}