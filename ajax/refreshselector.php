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
require('../../../config.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/block_userquiz_monitor_lib.php');

header('ContentType: text/html; charset=UTF-8');

require_login();

$courseid = required_param('courseid', PARAM_INT);
$idslist = required_param('categoryid', PARAM_INT);
$rootcategory = required_param('rootcategory', PARAM_INT);
$quizzeslist = required_param('quizzeslist', PARAM_RAW);
$response = update_selector($courseid, $idslist, 'mode1', $rootcategory, $quizzeslist);

echo($response);