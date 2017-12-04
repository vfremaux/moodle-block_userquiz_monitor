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

class schedule_renderer extends \block_userquiz_monitor_renderer {

    function schedule($theblock) {
        global $COURSE, $PAGE;

        $template = new Stdclass;

        if (!empty($theblock->config->rootcategory)) {
            $PAGE->requires->js('/blocks/userquiz_monitor/js/block_js.js');

            $template->selectschedulestr = get_string('selectschedule', 'block_userquiz_monitor', $theblock->config->trainingprogramname);

            for ($i = 0; $i < 12; $i++) {
                $cattpl = new StdClass;
                $cattpl->class = ($i == 0) ? 'active' : 'inactive';
                $cattpl->jshandler = 'refreshcontent('.$COURSE->id.', '.$theblock->config->rootcategory.', '.$i.')';
                $cattpl->i = $i;
                $cattpl->nexti = $i + 1;
                $template->maincategories[] = $cattpl;
            }

            $template->jshandler = 'refreshcontent('.$COURSE->id.', '.$theblock->config->rootcategory.', 0);';

            return $this->output->render_from_template('block_userquiz_monitor/schedule', $template);
        }
    }
}