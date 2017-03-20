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
defined('MOODLE_INTERNAL') || die();

/**
 * Seeks for an instance of a userquiz_monitor block that would be attached to
 * this attempt.
 *
 * @param int $quizid
 * @param string $mode
 * @return object the matching block configuration, or false.
 */
function block_userquiz_monitor_check_has_quiz($course, $quizid, $mode = 'exam') {
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

        switch ($mode) {
            case ('exam'): {
                if ($quizid == $config->examquiz) {
                    $config->mode = 'exam';
                    return $config;
                }
                break;
            }

            case ('training'): {
                $quizconfigarr = explode(',', $config->trainingquizzes);
                break;
            }

            default:
                $quizconfigarr = explode(',', $config->trainingquizzes);
                if (!empty($config->examquiz)) {
                    if ($quizid == $config->examquiz) {
                        $config->mode = 'exam';
                        return $config;
                    }
                }
        }

        if (!empty($quizconfigarr)) {
            if (in_array($quizid, $quizconfigarr)) {
                $config->mode = 'training';
                return $config;
            }
        }
    }
    return false;
}

/**
 * Cheks an attempt to see if it is bound to any usermonitor examquiz and
 * checks userquiz monitor config for returning immediately to course.
 */
function check_userquiz_monitor_review_applicability($attemptobj) {

    $course = $attemptobj->get_course();
 
    if ($config = block_userquiz_monitor_check_has_quiz($course, $attemptobj->get_quizid())) {
        if ($config->directreturn) {
            if ($config->examdeadend) {
                $params = array('id' => $course->id, 'blockid' => $uqm->id);
                redirect(new moodle_url('/blocks/userquiz_monitor/examfinish.php', $params));
            } else {
                redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
            }
        }
    }
}

/**
 * Adds Jquery form control for single question quizzes
 */
function block_userquiz_monitor_attempt_adds($attemptobj) {
    global $PAGE;

    $course = $attemptobj->get_course();

    if ($config = block_userquiz_monitor_check_has_quiz($course, $attemptobj->get_quizid())) {
        if (($config->mode == 'exam' && !empty($config->examforceanswer)) ||
                ($config->mode == 'training' && !empty($config->trainingforceanswer))) {
            $PAGE->requires->jquery();
            $PAGE->requires->js('/blocks/userquiz_monitor/js/quizforceanswer.js');
        }
    }
}