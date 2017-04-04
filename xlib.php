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

require_once($CFG->dirroot.'/blocks/userquiz_monitor/locallib.php');

/**
 * Cheks an attempt to see if it is bound to any usermonitor examquiz and
 * checks userquiz monitor config for returning immediately to course.
 */
function check_userquiz_monitor_review_applicability($attemptobj) {

    $course = $attemptobj->get_course();

    if ($config = block_userquiz_monitor_check_has_quiz($course, $attemptobj->get_quizid())) {
        if ($config->directreturn && ($config->mode == 'exam')) {
            if ($config->examdeadend) {
                $params = array('id' => $course->id, 'blockid' => $config->uqm->id);
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

    $PAGE->requires->jquery();
    $config = block_userquiz_monitor_check_has_quiz($course, $attemptobj->get_quizid());
    if ($config) {
        if (($config->mode == 'exam' && !empty($config->examforceanswer)) ||
                ($config->mode == 'training' && !empty($config->trainingforceanswer))) {
            $PAGE->requires->js('/blocks/userquiz_monitor/js/quizforceanswer.js');
        }
        if (($config->mode == 'exam' && !empty($config->examnobackwards)) ||
                ($config->mode == 'training' && !empty($config->trainingnobackwards))) {
            $PAGE->requires->js('/blocks/userquiz_monitor/js/quiznobackwards.js');
        }
        if ($config->protectcopy) {
            $PAGE->requires->js('/blocks/userquiz_monitor/js/quizprotectcopy.js');
        }
    }
}