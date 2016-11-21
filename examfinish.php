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

$courseid = required_param('id', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('coursemisconf');
}

if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))) {
    print_error('coursemisconf');
}

$theblock = block_instance('userquiz_monitor', $instance);

require_login($course);

$context = context_course::instance($courseid);
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/blocks/userquiz_monitor/examfinish.php', array('id' => $courseid, 'blockid' => $blockid)));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('examend', 'block_userquiz_monitor'));

$message = get_string('examfinishmessage', 'block_userquiz_monitor');
$message = str_replace('%%FIRSTNAME%%', $USER->firstname, $message);
$message = str_replace('%%LASTNAME%%', $USER->lastname, $message);
$message = str_replace('%%PROGRAMNAME%%', $theblock->config->trainingprogramname, $message);
echo $OUTPUT->box($message, 'exam-finish-message');

$buttonurl = new moodle_url('/course/view.php', array('id' => $course->id));

echo '<p>';
echo $OUTPUT->continue_button($buttonurl, '', get_string('backtocourse', 'block_userquiz_monitor'));
echo '</p>';

echo $OUTPUT->footer();
