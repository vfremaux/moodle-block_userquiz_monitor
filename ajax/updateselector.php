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

define('AJAX_SCRIPT', 1);

// Include files.
require_once('../../../config.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/locallib.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/renderer.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/classes/output/block_userquiz_monitor_training_renderer.php');

// The current course ID needed to switch language.
$courseid = required_param('courseid', PARAM_INT);
$categorylist = optional_param('categoryid', '', PARAM_TEXT);
$location = optional_param('location', '', PARAM_TEXT);
$rootcategory = optional_param('rootcategory', '', PARAM_TEXT);
$quizlist = optional_param('quizlist', '', PARAM_TEXT);

$course = $DB->get_record('course', array('id' => $courseid));
if (!$course) {
    print_error('coursemisconf');
}

require_login($course);
$response = block_userquiz_monitor_update_selector($courseid, $categorylist, $location, $rootcategory, $quizlist);
echo($response);

