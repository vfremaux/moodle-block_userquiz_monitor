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

defined('MOODLE_INTERNAL') || die();

class training_renderer extends \block_userquiz_monitor_renderer {

    protected $block;

    protected $course;

    protected $settings;

    public function training_second_button($selector) {

        $str = '<div class="userquiz-monitor-bottom-launch">';

        $str .= '<div class="userquiz-monitor-row">';
        $str .= '<div class="userquiz-monitor-cell span12">';
        $str .= '<div class="trans100">';
        $str .= '<div class="selectorcontainers" style="width:100%; font-size : 120%;">';
        $str .= $selector;
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</div>';

        $str .= '</div>';

        return $str;
    }

    public function heading() {

        $title = get_string('testtitle', 'block_userquiz_monitor');

        $str = '<div>'; // Table.
        $str .= '<div class="userquiz-monitor-row">';

        $str .= '<div class="userquiz-monitor-cell span6 md-col-6">';
        $str .= $this->output->heading( $title, 1);
        $str .= '</div>';

        $str .= '<div class="userquiz-monitor-cell span6 md-col-6" style="text-align:right">';
        $str .= $this->filter_state('training', $this->theblock->instance->id);
        $str .= '</div>';

        $str .= '</div>';
        $str .= '</div>'; // Table.

        return $str;
    }

    public function global_monitor($total, $selector) {

        $totalstr = get_string('total', 'block_userquiz_monitor');

        $str = '';

        $str .= '<div>'; // Table.
        $str .= '<div class="userquiz-monitor-row">'; // Row.
        $str .= '<div class="userquiz-monitor-cell span3">';

        $helpicon = $this->output->help_icon('launch', 'block_userquiz_monitor', false);
        $str .= '<h1>'.get_string('runtest', 'block_userquiz_monitor').' '.$helpicon.'</h1>';

        $str .= '<div class="trans100">';
        $str .= '<div class="selectorcontainers" style="width:100%; font-size : 120%;">';
        $str .= $selector;
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</div>';

        $str .= '<div class="userquiz-monitor-cell userquiz-cat-progress span9">';
        $str .= '<h1>'.$totalstr.' '.$this->output->help_icon('total', 'block_userquiz_monitor', false).'</h1>';
        $str .= '<div class="trans100">';
        $str .= $total;
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</div>'; // Row.
        $str .= '</div>'; // Table.

        return $str;
    }

    public function category_result($cat) {

        $seesubsstr = get_string('more', 'block_userquiz_monitor');

        $str = '';

        $str .= '<div class="trans100" id="divpl'.$cat->id.'">';
        $str .= '<div class="userquiz-monitor-categorycontainer">'; // Table.

        $str .= '<div class="userquiz-monitor-row">'; // Row.

        $str .= '<div class="userquiz-monitor-cell categoryname">';
        $str .= $cat->name;
        $str .= '</div>';
        $str .= '</div>'; // Row.

        $str .= '<div class="userquiz-monitor-row">'; // Row
        $str .= '<div class="userquiz-monitor-cell">';
        $str .= '<input type="checkbox"
                        name="cb_pl'.$cat->id.'"
                        id="cbpl'.$cat->id.'"
                        onclick="'.$cat->jshandler1.'"
                        style="padding-left:2px;" />';
        $str .= $cat->accessorieslink;
        $str .= '<input type="hidden" name="h_cb_pl'.$cat->id.'" value="h_cb_pl'.$cat->id.'"/>';
        $str .= '</div>';

        $str .= '<div class="userquiz-monitor-cell">';
        // Blank cell.
        $str .= '</div>';

        $str .= '<div class="userquiz-monitor-cell">';
        $pixurl = $this->get_area_url('detailsicon', $this->output->pix_url('detail', 'block_userquiz_monitor'));
        $str .= '<img class="userquiz-monitor-cat-button"
                      title="'.$seesubsstr.'"
                      src="'.$pixurl.'"
                      onclick="'.$cat->jshandler2.'"/>';
        $str .= '</div>';

        $str .= '</div>'; // Row.

        $str .= '<div class="userquiz-monitor-row">'; // Row.

        $str .= '<div class="userquiz-monitor-cell userquiz-monitor-bg ratio">';
        // Blank Cell.
        $str .= '</div>';
        if (!empty($this->theblock->config->dualserie)) {
            $level1str = get_string('level1', 'block_userquiz_monitor');
            $str .= '<div class="userquiz-monitor-cell userquiz-monitor-bg level">'.$level1str.'</div>';
        }
        $str .= '<div class="userquiz-monitor-cell userquiz-monitor-bg ratio">';
        $str .= get_string('ratio1', 'block_userquiz_monitor');
        $str .= '</div>';

        $str .= '</div>'; // Row.

        // Ensure cat types are presented in sorted order.
        ksort($cat->questiontypes);
        if (!empty($cat->questiontypes)) {

            $keys = array_keys($cat->questiontypes);
            foreach ($keys as $questiontype) {

                if ($questiontype == 'A') {

                    $str .= '<div class="userquiz-monitor-row">';

                    $str .= '<div class="userquiz-monitor-cell progressbar vertical-centered">';
                    $str .= '<div id="progressbarcontainerC'.$cat->id.'">';
                    $str .= $cat->progressbarA;
                    $str .= '</div>';
                    $str .= '</div>';
                    if (!empty($this->theblock->config->dualserie)) {
                        $str .= '<div class="userquiz-monitor-cell progressbarlabel vertical-centered">';
                        $pixurl = $this->get_area_url('serie1icon', $this->output->pix_url('a', 'block_userquiz_monitor'));
                        $str .= '<img class="userquiz-monitor->questiontype" src="'.$pixurl.'"/>';
                        $str .= '</div>';
                    }
                    $str .= '<div class="userquiz-monitor-cell progressbarlabel vertical-centered">';
                    $str .= '<h4>'.$cat->goodA.'/'.$cat->cptA.'</h4>';
                    $str .= '</div>';

                    $str .= '</div>'; // Row.
                }

                if ($this->theblock->config->dualserie && ($questiontype == 'C')) {

                    $str .= '<div class="userquiz-monitor-row">'; // Row.

                    $str .= '<div class="userquiz-monitor-cell progressbar vertical-centered">';
                    $str .= '<div id="progressbarcontainerC'.$cat->id.'">';
                    $str .= $cat->progressbarC;
                    $str .= '</div>';
                    $str .= '</div>';

                    $str .= '<div class="userquiz-monitor-cell progressbarlabel vertical-centered">';
                    $pixurl = $this->get_area_url('serie2icon', $this->output->pix_url('c', 'block_userquiz_monitor'));
                    $str .= '<img class="userquiz-monitor->questiontype" src="'.$pixurl.'" />';
                    $str .= '</div>';
                    $str .= '<div class="userquiz-monitor-cell progressbarlabel vertical-centered">';
                    $str .= '<h4>'.$cat->goodC.'/'.$cat->cptC.'</h4>';
                    $str .= '</div>';

                    $str .= '</div>'; // Row.
                }
            }
        }

        $str .= '</div>'; // Table.
        $str .= '</div>';

        return $str;
    }

    public function launch_gui($options, $quizzeslist) {
        global $COURSE;

        $numberofquestionsstr = get_string('numberquestions', 'block_userquiz_monitor');
        $runteststr = get_string('runtest', 'block_userquiz_monitor');
        $runtraininghelpstr = get_string('runtraininghelp', 'block_userquiz_monitor');
        $jshandler = 'sync_training_selectors(this)';

        $str = '<div class="userquiz-monitor-categorycontainer">
                    <div class="userquiz-monitor-row">
                        <div class="userquiz-monitor-cell">
                            <p>'.$runtraininghelpstr.'</p>
                        </div>
                    </div>
                    <div class="userquiz-monitor-row">
                        <div class="userquiz-monitor-cell">
                             '.$numberofquestionsstr.'
                            <select class="selectorsnbquestions" name="selectornbquestions" size="1" onchange="'.$jshandler.'">
                                '.$options.'
                            </select>
                        </div>
                    </div>
                    <div class="userquiz-monitor-row">
                        <div class="userquiz-monitor-cell">
                             <input type="hidden" name="mode" value="test"/>
                             <input type="hidden" name="courseid" value="'.$COURSE->id.'"/>
                             <input type="hidden" name="quizzeslist" value="'.$quizzeslist.'"/>
                             <input type="submit" value="'.$runteststr.'" id="submit"/>
                         </div>
                     </div>
                </div>';

        return $str;
    }

    public function empty_launch_gui() {
        global $COURSE;

        $runteststr = get_string('runtest', 'block_userquiz_monitor');
        $runtraininghelpstr = get_string('runtraininghelp', 'block_userquiz_monitor');

        $str = '<div class="userquiz-monitor-categorycontainer" >
                    <div class="userquiz-monitor-row">
                        <div class="userquiz-monitor-cell">
                            <p>'.$runtraininghelpstr.'</p>
                        </div>
                    </div>
                    <div class="userquiz-monitor-row">
                        <div class="userquiz-monitor-cell">
                            <input type="hidden" name="mode" value="test"/>
                            <input type="hidden" name="courseid" value="'.$COURSE->id.'"/>
                            <input type="submit" value="'.$runteststr.'" id="submit" disabled />
                        </div>
                    </div>
                </div>';

        return $str;
    }

}