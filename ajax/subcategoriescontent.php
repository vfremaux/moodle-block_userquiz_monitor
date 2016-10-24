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

define('AJAX_SCRIPT', true);

require('../../../config.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/block_userquiz_monitor_lib.php');

// Init variable.
$response = '';

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid));

$PAGE->set_url(new moodle_url('/blocks/userquiz_monitor/ajax/subcategoriescontent.php'));
require_login($course);

$categoryid = optional_param('categoryid', '', PARAM_TEXT);
$quizid = optional_param('quizzeslist', '', PARAM_TEXT);
$positionheight = optional_param('positionheight', 0, PARAM_INT);
$mode = optional_param('mode', '', PARAM_TEXT);
$blockid = required_param('blockid', PARAM_INT);
if (!$instance = $DB->get_record('block_instances', array('id' => $blockid))) {
    print_error('badblockinstance', 'block_contact_form');
}
$theBlock = block_instance('userquiz_monitor', $instance);

$renderer = $PAGE->get_renderer('block_userquiz_monitor');
$renderer->set_block($theBlock);

if ($mode == 'training') {
    $rootcategory = required_param('rootcategory', PARAM_INT);
    $response .= $renderer->subcategories($courseid, $rootcategory, $categoryid, $quizid, $positionheight, $mode, $theBlock);
} else {
    $response .= $renderer->subcategories($courseid, null, $categoryid, $quizid, $positionheight, $mode, $theBlock);
}

echo($response);
