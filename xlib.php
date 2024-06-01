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
require_once($CFG->dirroot.'/blocks/userquiz_monitor/classes/manager.php');

function get_block_userquiz_monitor_manager() {
    return block_userquiz_monitor\manager::instance();
}

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
 * Renders attempt link buttons at bottom of quiz page.
 * @param object $attemptobj the current quiz_attempt instance
 * @param int $page the current quiz page.
 * @see : called by the block_quiz_behaviour overriden quiz_renderer
 */
function block_userquiz_monitor_attempt_buttons($attemptobj, $page) {
    global $OUTPUT, $CFG;

    $course = $attemptobj->get_course();

    $config = block_userquiz_monitor_check_has_quiz($course, $attemptobj->get_quizid());
    if ($config) {
        $template = new StdClass;

        if (is_dir($CFG->dirroot.'/mod/quiz/accessrule/usernumattempts')) {
            $ruleinstance = quizaccess_usernumattempts::make($attemptobj->get_quizobj(), time(), true);
            if ($ruleinstance && $ruleinstance->is_enabled()) {
                $template->label = get_string('returntotraining', 'block_userquiz_monitor');
            } else {
                // $template->label = get_string('returntoquiz', 'block_userquiz_monitor');
            }
        } else {
            // $template->label = get_string('returntoquiz', 'block_userquiz_monitor');
        }

        // Get the url for finishing and registering attempt.
        $params = array(
            'attempt' => $attemptobj->get_attemptid(),
            'finishattempt' => 1,
            'timeup' => 0,
            'slots' => '',
            'sesskey' => sesskey(),
        );

        $template->finishurl = new moodle_url($attemptobj->processattempt_url(), $params);

        $template->isnotfirstpage = false;
        if ($page > 0) {
            $navmethod = $attemptobj->get_quiz()->navmethod;
            if ($navmethod == 'free') {
                // This accessorily disables back nav.
                $template->isnotfirstpage = true;
            }
            $template->navigatepreviousstr = get_string('navigateprevious', 'quiz');
        }
        if ($attemptobj->is_last_page($page)) {
            $template->nextlabelstr = get_string('endtest', 'quiz');
        } else {
            $template->nextlabelstr = get_string('navigatenext', 'quiz');
        }

        return $OUTPUT->render_from_template('block_userquiz_monitor/attemptpagenavigation', $template);
    }

    return false;
}

function block_userquiz_monitor_check_has_quiz_ext($course, $quizid) {
    return block_userquiz_monitor_check_has_quiz($course, $quizid);
}

/**
 * Get the block instance of the userquiz monitor (one per course)
 * @param int $courseid the id of the surrounding course.
 * @param string $mode 'training', 'exam' or ''. If mode is given will only return if the blovck candidate
 * matches the required configuration mode.
 */
function block_userquiz_monitor_get_block($courseid, $mode = '') {
    return _block_userquiz_monitor_get_block($courseid, $mode);
}

/**
 * Get all courses having one instance of userquiz_monitor block in its context.
 */
function block_userquiz_monitor_get_block_courses() {
    return _block_userquiz_monitor_get_block_courses();
}

/**
 * Get the block instance top categories for training, from the block's configuration
 * @param int $theblock a userquiz_monitor block instance.
 */
function block_userquiz_monitor_get_top_cats($theblock, $withqcount = false) {
    return _block_userquiz_monitor_get_top_cats($theblock, $withqcount);
}

function block_userquiz_monitor_get_exam_grades($block, $userid, $serie) {

    $rootcategory = @$this->theblock->config->rootcategory;
    $overall = block_userquiz_monitor_init_overall();
    block_userquiz_monitor_init_rootcats($rootcategory, $rootcats);

    $errors = block_userquiz_monitor_compute_all_results($usedattemptarr, $rootcategory, $rootcats,
                                                                       $attempts, $overall, 'exam');
    switch($serie) {
        case 'A' : {
            return $overrall->ratioA;
        }
        case 'C' : {
            return $overrall->ratioC;
        }
        default : 
            return $overrall->ratio;
    }
}
