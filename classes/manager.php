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
 * Manages global integration hooking outside this package.
 *
 * @package    block_userquiz_monitor
 * @category   blocks
 * @copyright  2018 Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_userquiz_monitor;

use \context_course;
use \moodle_exception;

defined('MOODLE_INTERNAL') || die();

class manager {

    protected $blockinstance;

    /**
     * Array of training quiz instance IDs
     */
    protected $trainingquizzes;

    /**
     * Exam quiz ID.
     */
    protected $examquiz;

    /**
     * Protected constructor. Singleton behaviour.
     * Get an internally instanciate the quiz_behaviour block intance for the course.
     */
    protected function __construct() {
        global $COURSE, $DB;

        $coursecontext = context_course::instance($COURSE->id);
        $params = array('blockname' => 'userquiz_monitor', 'parentcontextid' => $coursecontext->id);
        // Note should be one per course only.
        $blockrec = $DB->get_record('block_instances', $params);

        if ($blockrec) {
            $this->blockinstance = block_instance('userquiz_monitor', $blockrec);
            $this->trainingquizzes = $this->blockinstance->config->trainingquizzes;
            $this->examquiz = $this->blockinstance->config->examquiz;
        }
        return null;
    }

    /**
     * provides a single instance in the current course scope.
     */
    public static function instance() {
        static $manager;

        if (isset($manager)) {
            return $manager;
        }

        return new manager();
    }

    /**
     * Get all quiz instances in course.
     */
    public function get_quizzes() {
        global $DB, $COURSE;

        return $DB->get_records('quiz', array('course' => $COURSE->id), 'id,name');
    }

    /**
     * Checks if a quiz id is part of the training set or is the exam quiz.
     * @param int $qid the quiz instance id
     */
    public function is_training($qid) {
        global $DB, $CFG;

        if (!$DB->record_exists('quiz', array('id' => $qid))) {
            if ($CFG->debug == DEBUG_DEVELOPER) {
                // TODO : auto cleanup of the deleted instances.
                throw new moodle_exception("Invalid quiz. May be deleted");
            }
            return false;
        }

        if (empty($this->blockinstance)) {
            // Manager is ok, but there is no block in the course.
            return false;
        }

        return (in_array($qid, $this->trainingquizzes));
    }

    /**
     * Checks if a quiz id is part of the training set or is the exam quiz.
     * @param int $qid the quiz instance id
     */
    public function is_training_or_exam($qid) {
        global $DB, $CFG;

        if (!$DB->record_exists('quiz', array('id' => $qid))) {
            if ($CFG->debug == DEBUG_DEVELOPER) {
                // TODO : auto cleanup of the deleted instances.
                throw new moodle_exception("Invalid quiz. May be deleted");
            }
            return false;
        }

        if (empty($this->blockinstance)) {
            // Manager is ok, but there is no block in the course.
            return false;
        }

        return ($qid == $this->examquiz || $this->is_training($qid));
    }

    /**
     * Registers an attempt in report_examtraining
     * @param quiz_attempt $attempt the attempt from a quiz.
     */
    public function register_attempt($attempt) {
        global $CFG;

        include_once($CFG->dirroot.'/report/examtraining/xlib.php');
        report_examtraining_register_attempt($attempt);
    }

    /**
     * Counts all quiz activity (attempts) in all quizzes attached to this manager.
     * @param int $userid the user ID
     * @param bool $finished if false, will count all attempts, including non terminated.
     * @return int
     */
    public function count_user_attempts($userid, $finished = true) {
        global $DB;

        $quizzes = $this->trainingquizzes;
        if (!empty($this->examquiz)) {
            $quizzes[] = $this->examquiz;
        }

        if (empty($quizzes)) {
            return 0;
        }

        $finishedclause = '';
        if ($finished) {
            $finishedclause = ' AND timefinish != 0 ';
        }

        list($insql, $inparams) = $DB->get_in_or_equal($quizzes);
        $inparams[] = $userid;

        $select = "
            quiz {$insql} AND
            userid = ?
            {$finishedclause}
        ";
        return $DB->count_records_select('quiz_attempts', $select, $inparams);
    }
}
