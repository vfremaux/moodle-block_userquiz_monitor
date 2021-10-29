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
 * An installation wizard that builds a complete course with quiz instances.
 *
 * @package     block_userquiz_monitor
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux (http://www.mylearningfactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

$id = required_param('course', PARAM_INT);
$step = optional_param('step', 0, PARAM_INT);

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('coursemisconf');
}

$url = new moodle_url('/blocks/userquiz_monitor/install.php', array('id' => $id));
$renderer = $PAGE->get_renderer('block_userquiz_monitor', 'build');

// Security.

$context = context_course::instance($course->id);
require_login($course);
require_capability('moodle/course:manageactivities', $context);

$buiderstr = get_string('coursebuilder', 'block_userquiz_monitor');
$PAGE->set_heading($builderstr);
$PAGE->set_context($context);
$PAGE->set_url($url);

echo $OUTPUT->header();

echo $OUTPUT->heading($builderstr);

switch ($step) {
    case 0:
        echo $renderer->build_checks();
        $nextstep = 1;
        break;

    case 1:
        echo $renderer->build_params();
        $nextstep = 2;
        break;

    case 2:
        $report = userquiz_build_course();
        echo $renderer->build_build();
        $nextstep = 3;
        break;

    case 3:
        echo $renderer->build_done();
        $nextstep = 0;
        break;

}

echo $renderer->build_nextstep_link($nexstep);

echo $OUTPUT->footer();