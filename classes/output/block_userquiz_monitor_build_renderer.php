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
namespace block_userquiz_monitor\output;

use \moodle_url;
use \core_text;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/userquiz_monitor/renderer.php');

class build_renderer extends \block_userquiz_monitor_renderer {

    protected $course;

    protected $settings;

    public function build_checks() {
    }

    public function build_params() {
    }

    /**
     *
     */
    public function build_build($report) {
        $str = '';

        $str .= '<div id="userquiz-build-report">';
        $str .= '<pre>'.$report.'</pre>';
        $str .= '</div>';

        return $str;
    }

    public function build_done() {
    }

    public function build_nextstep_link($params, $islast = false) {
        global $COURSE;

        $str = '';

        if (!$islast) {
            $url = new moodle_url('/blocks/userquiz_monitor/install.php', $params);
            $str .= '<div id="userquiz-build-next">';
            $nextstr = get_string('nextstep', 'block_userquiz_monitor');
            $str .= $this->output->single_button($url, $nextsr, 'post');
            $str .= '</div>';
        } else {
            $url = new moodle_url('/course/view.php?id='.$COURSE->id);
            $backstr = get_string('backtocourse', 'block_userquiz_monitor');
            $str .= $this->output->single_button($url, $backstr);
        }

        return $str;
    }
}