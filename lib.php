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
 * Form for editing HTML block instances.
 *
 * @package   block_userquiz_monitor
 * @category  blocks
 * @copyright 2012 Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/moodleblock.class.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/block_userquiz_monitor.php');

function block_userquiz_monitor_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload) {

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    require_course_login($course);

    if (!in_array($filearea, block_userquiz_monitor::get_fileareas())) {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';
    $filepath = str_replace('/0/', '/', $filepath); // fix root files.

    if (!$file = $fs->get_file($context->id, 'block_userquiz_monitor', $filearea, 0, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    $forcedownload = false;

    send_stored_file($file, 60 * 60, 0, $forcedownload);
}
