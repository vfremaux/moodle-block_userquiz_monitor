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
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/blocks/userquiz_monitor/lib.php');

if ($ADMIN->fulltree) {

    if (block_userquiz_monitor_supports_feature('emulate/community') == 'pro') {
        // This will accept any.
        include_once($CFG->dirroot.'/blocks/userquiz_monitor/pro/prolib.php');
        $promanager = \block_userquiz_monitor\pro_manager::instance();
        $promanager->add_settings($ADMIN, $settings);
    } else {
        $label = get_string('plugindist', 'block_userquiz_monitor');
        $desc = get_string('plugindist_desc', 'block_userquiz_monitor');
        $settings->add(new admin_setting_heading('plugindisthdr', $label, $desc));
    }
}
