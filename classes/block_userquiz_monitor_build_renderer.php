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
 * Main renderer.
 *
 * @package     block_userquiz_monitor
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class block_userquiz_monitor_build_renderer extends plugin_base_renderer {

    protected $block;

    protected $course;

    protected $settings;

    public function set_course($course, $block) {
        $this->course = $course;
        $this->block = $block;
    }

    public function nextstep($nextstep) {
    }

    public function checks() {
        // Check we are in page format.
    }

    public function params() {
    }

    public function build() {

        // Build page or sections
        if ($course->format == 'page') {
            $this->build_pages();
        } else {
            $this->build_sections();
        }

        // Build quizzes instances.

        // Build 1 to 10 quizzes
        for ($i = 1; $i <= 10; $i++) {
            $this->block->add_quiz_instance($numq, $this->quizsection);
        }

        // build 10 to NN quizzes

        // Bind instances to block
    }

    protected function build_pages() {
        // Load page libs
        include_once('/course/format/page/xlib.php');

        // Create page for training userquiz_monitor.
        if (empty())
        $monitorpage = new Stdclass() {
        $monitorpage->nameone = get_string('tmpmonitorpagename', 'block_userquiz_monitor');
        $monitorpage->nametwo = get_string('tmpmonitorpagenameshort', 'block_userquiz_monitor');
        $monitorpage->visible = FORMAT_PAGE_DISP_HIDDEN;
        $monitorpage->parent = 0;
        format_page_add_page($monitorpage);

        // Create page for training quizzes.
        $quizpage = new Stdclass() {
        $quizpage->nameone = get_string('tmptrainingquizpagename', 'block_userquiz_monitor');
        $quizpage->nametwo = get_string('tmptrainingquizpagenameshort', 'block_userquiz_monitor');
        $quizpage->visible = FORMAT_PAGE_DISP_HIDDEN;
        $quizpage->parent = 0;
        format_page_add_page($quizpage);

        // Create page for exam quiz.
        $quizpage = new Stdclass() {
        $quizpage->nameone = get_string('tmpexamquizpagename', 'block_userquiz_monitor');
        $quizpage->nametwo = get_string('tmpexamquizpagenameshort', 'block_userquiz_monitor');
        $quizpage->visible = FORMAT_PAGE_DISP_HIDDEN;
        $quizpage->parent = 0;
        format_page_add_page($quizpage);
    }

    protected function build_sections() {
    }
}