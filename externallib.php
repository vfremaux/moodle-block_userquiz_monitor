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
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * this file provides with WS document requests to externalize report documents
 * from within an external management system
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/quiz/classes/external.php');

class block_userquiz_monitor_external extends mod_quiz_external {

    /**
     * Returns review information for the given finished attempt, can be used by users or teachers.
     *
     * @param int $attemptid attempt id
     * @param int $page page number, empty for all the questions in all the pages
     * @return array of warnings and the attempt data, feedback and questions
     * @since Moodle 3.1
     * @throws  moodle_exception
     * @throws  moodle_quiz_exception
     */
    public static function get_attempt_review($attemptid, $page = -1) {
        global $PAGE;

        $warnings = array();

        $params = array(
            'attemptid' => $attemptid,
            'page' => $page,
        );
        $params = self::validate_parameters(self::get_attempt_review_parameters(), $params);

        list($attemptobj, $displayoptions) = self::validate_attempt_review($params);

        if ($params['page'] !== -1) {
            $page = $attemptobj->force_page_number_into_range($params['page']);
        } else {
            $page = 'all';
        }

        // Prepare the output.
        $result = array();
        $result['attempt'] = $attemptobj->get_attempt();
        $result['questions'] = self::get_attempt_questions_data($attemptobj, true, $page, true);

        $result['additionaldata'] = array();
        // Summary data (from behaviours).
        $summarydata = $attemptobj->get_additional_summary_data($displayoptions);
        foreach ($summarydata as $key => $data) {
            // This text does not need formatting (no need for external_format_[string|text]).
            $result['additionaldata'][] = array(
                'id' => $key,
                'title' => $data['title'], $attemptobj->get_quizobj()->get_context()->id,
                'content' => $data['content'],
            );
        }

        // Feedback if there is any, and the user is allowed to see it now.
        $grade = quiz_rescale_grade($attemptobj->get_attempt()->sumgrades, $attemptobj->get_quiz(), false);

        $feedback = $attemptobj->get_overall_feedback($grade);
        if ($displayoptions->overallfeedback && $feedback) {
            $result['additionaldata'][] = array(
                'id' => 'feedback',
                'title' => get_string('feedback', 'quiz'),
                'content' => $feedback,
            );
        }

        $result['grade'] = $grade;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Describes the get_attempt_review return value.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function get_attempt_review_returns() {
        return new external_single_structure(
            array(
                'grade' => new external_value(PARAM_RAW, 'grade for the quiz (or empty or "notyetgraded")'),
                'attempt' => self::attempt_structure(),
                'additionaldata' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_ALPHANUMEXT, 'id of the data'),
                            'title' => new external_value(PARAM_TEXT, 'data title'),
                            'content' => new external_value(PARAM_RAW, 'data content'),
                        )
                    )
                ),
                'questions' => new external_multiple_structure(self::question_structure()),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes a single question structure.
     *
     * @return external_single_structure the question structure
     * @since  Moodle 3.1
     */
    private static function question_structure() {
        return new external_single_structure(
            array(
                'slot' => new external_value(PARAM_INT, 'slot number'),
                'type' => new external_value(PARAM_ALPHANUMEXT, 'question type, i.e: multichoice'),
                'page' => new external_value(PARAM_INT, 'page of the quiz this question appears on'),
                'html' => new external_value(PARAM_RAW, 'the question rendered'),
                'flagged' => new external_value(PARAM_BOOL, 'whether the question is flagged or not'),
                'number' => new external_value(PARAM_INT, 'question ordering number in the quiz', VALUE_OPTIONAL),
                'state' => new external_value(PARAM_ALPHA, 'the state where the question is in', VALUE_OPTIONAL),
                'status' => new external_value(PARAM_RAW, 'current formatted state of the question', VALUE_OPTIONAL),
                'mark' => new external_value(PARAM_RAW, 'the mark awarded', VALUE_OPTIONAL),
                'maxmark' => new external_value(PARAM_FLOAT, 'the maximum mark possible for this question attempt', VALUE_OPTIONAL),
                'default_grade' => new external_value(PARAM_FLOAT, 'The default grade of this question attempt', VALUE_OPTIONAL),
            )
        );
    }

    /**
     * Return questions information for a given attempt.
     * Overrides original function to get default grade information.
     *
     * @param  quiz_attempt  $attemptobj  the quiz attempt object
     * @param  bool  $review  whether if we are in review mode or not
     * @param  mixed  $page  string 'all' or integer page number
     * @return array array of questions including data
     */
    private static function get_attempt_questions_data(quiz_attempt $attemptobj, $review, $page = 'all') {
        global $PAGE;

        $questions = array();
        $contextid = $attemptobj->get_quizobj()->get_context()->id;
        $displayoptions = $attemptobj->get_display_options($review);
        $renderer = $PAGE->get_renderer('mod_quiz');

        foreach ($attemptobj->get_slots($page) as $slot) {

            $question = array(
                'slot' => $slot,
                'type' => $attemptobj->get_question_type_name($slot),
                'page' => $attemptobj->get_question_page($slot),
                'flagged' => $attemptobj->is_question_flagged($slot),
//                'html' => $attemptobj->render_question($slot, $review, $renderer) . $PAGE->requires->get_end_code()
                'html' => ''
            );

            if ($attemptobj->is_real_question($slot)) {
                $question['number'] = $attemptobj->get_question_number($slot);
                $question['state'] = (string) $attemptobj->get_question_state($slot);
                $question['status'] = $attemptobj->get_question_status($slot, $displayoptions->correctness);
            }
            $questionattempt = $attemptobj->get_question_attempt($slot);
            if ($displayoptions->marks >= question_display_options::MAX_ONLY) {
                $question['maxmark'] = $questionattempt->get_max_mark();
            }
            if ($displayoptions->marks >= question_display_options::MARK_AND_MAX) {
                $question['mark'] = $attemptobj->get_question_mark($slot);
            }

            $internalquestion = $questionattempt->get_question();
            $question['default_grade'] = $internalquestion->defaultmark;

            $questions[] = $question;
        }
        return $questions;
    }

    /**
     * Describes a single attempt structure.
     *
     * @return external_single_structure the attempt structure
     */
    private static function attempt_structure() {
        return new external_single_structure(
            array(
                'id' => new external_value(PARAM_INT, 'Attempt id.', VALUE_OPTIONAL),
                'quiz' => new external_value(PARAM_INT, 'Foreign key reference to the quiz that was attempted.',
                                                VALUE_OPTIONAL),
                'userid' => new external_value(PARAM_INT, 'Foreign key reference to the user whose attempt this is.',
                                                VALUE_OPTIONAL),
                'attempt' => new external_value(PARAM_INT, 'Sequentially numbers this students attempts at this quiz.',
                                                VALUE_OPTIONAL),
                'uniqueid' => new external_value(PARAM_INT, 'Foreign key reference to the question_usage that holds the
                                                    details of the the question_attempts that make up this quiz
                                                    attempt.', VALUE_OPTIONAL),
                'layout' => new external_value(PARAM_RAW, 'Attempt layout.', VALUE_OPTIONAL),
                'currentpage' => new external_value(PARAM_INT, 'Attempt current page.', VALUE_OPTIONAL),
                'preview' => new external_value(PARAM_INT, 'Whether is a preview attempt or not.', VALUE_OPTIONAL),
                'state' => new external_value(PARAM_ALPHA, 'The current state of the attempts. \'inprogress\',
                                                \'overdue\', \'finished\' or \'abandoned\'.', VALUE_OPTIONAL),
                'timestart' => new external_value(PARAM_INT, 'Time when the attempt was started.', VALUE_OPTIONAL),
                'timefinish' => new external_value(PARAM_INT, 'Time when the attempt was submitted.
                                                    0 if the attempt has not been submitted yet.', VALUE_OPTIONAL),
                'timemodified' => new external_value(PARAM_INT, 'Last modified time.', VALUE_OPTIONAL),
                'timecheckstate' => new external_value(PARAM_INT, 'Next time quiz cron should check attempt for
                                                        state changes.  NULL means never check.', VALUE_OPTIONAL),
                'sumgrades' => new external_value(PARAM_FLOAT, 'Total marks for this attempt.', VALUE_OPTIONAL),
            )
        );
    }
}
