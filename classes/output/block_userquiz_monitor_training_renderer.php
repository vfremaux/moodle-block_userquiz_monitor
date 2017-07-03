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

require_once($CFG->dirroot.'/blocks/userquiz_monitor/renderer.php');

class training_renderer extends \block_userquiz_monitor_renderer {

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

        $title = get_string('testtitle', 'block_userquiz_monitor', $this->theblock->config->trainingprogramname);

        $str = '<div>'; // Table.
        $str .= '<div class="userquiz-monitor-row">';

        $str .= '<div id="program-heading" class="userquiz-monitor-cell span5 md-col-5">';
        $str .= $this->output->heading($title, 1);
        $str .= '</div>';

        /*
        $str .= '<div class="userquiz-monitor-cell span6 md-col-6" style="text-align:right">';
        $str .= $this->filter_state('training', $this->theblock->instance->id);
        $str .= '</div>';
        */
        $str .= '<div class="userquiz-monitor-cell span7 md-col-7" style="text-align:right">';
        $str .= $this->filter_form('training');
        $str .= '</div>';

        $str .= '</div>';
        $str .= '</div>'; // Table.

        return $str;
    }

    public function filter_form() {
        return block_userquiz_monitor_training_filter_form($this->theblock);
    }

    public function global_monitor($total, $selector) {

        $totalstr = get_string('total', 'block_userquiz_monitor');

        $str = '';

        $str .= $this->output->box_start(); // Table.
        $str .= $this->output->box_start(); // Row.

        $str .= $this->output->box_start('userquiz-monitor-cell userquiz-cat-progress span9');

        $str .= '<h1>'.$totalstr.' '.$this->output->help_icon('total', 'block_userquiz_monitor', false).'</h1>';
        $str .= $this->output->box($total, 'trans100');

        $str .= $this->output->box_end();

        $str .= $this->output->box_start('userquiz-monitor-cell span3');

        $helpicon = $this->output->help_icon('launch', 'block_userquiz_monitor', false);
        $str .= '<h1>'.get_string('runtest', 'block_userquiz_monitor').' '.$helpicon.'</h1>';

        $str .= $this->output->box_start('trans100');
        $str .= '<div id="userquiz-training-selector" class="selectorcontainers" style="width:100%; font-size : 120%;">';
        $str .= $selector;
        $str .= '</div>';
        $str .= $this->output->box_end();

        $str .= $this->output->box_end();

        $str .= $this->output->box_end(); // Row
        $str .= $this->output->box_end(); // Table.

        return $str;
    }

    public function category_result($cat) {

        $seesubsstr = get_string('more', 'block_userquiz_monitor');

        $str = '';

        $str .= $this->output->box_start('trans100', 'divpl'.$cat->id);
        $str .= $this->output->box_start('userquiz-monitor-categorycontainer'); // Table.

        $str .= $this->box_start('userquiz-monitor-row'); // Row.

        $str .= $this->box_start('userquiz-monitor-cell categorychoice');
        $str .= '<input type="checkbox"
                        name="cb_pl'.$cat->id.'"
                        id="cbpl'.$cat->id.'"
                        onclick="'.$cat->jshandler1.'"
                        style="padding-left:2px;" />';
        $str .= $this->output->box_end(); // Cell.

        $str .= $this->output->box($cat->name, 'userquiz-monitor-cell categoryname');

        $str .= $this->output->box_start('userquiz-monitor-cell details-button');
        $pixurl = $this->get_area_url('detailsicon');
        if ($pixurl) {
            $str .= '<img class="userquiz-monitor-cat-button"
                          title="'.$seesubsstr.'"
                          src="'.$pixurl.'"
                          onclick="'.$cat->jshandler2.'"/>';
        } else {
            // If no detail image loaded keep a single button.
            $str .= '<input type="button"
                          id="userquiz-subcat-open'.$cat->id.'"
                          class="userquiz-monitor-cat-button btn"
                          title="'.$seesubsstr.'"
                          value="'.$seesubsstr.'"
                          onclick="'.$cat->jshandler2.'"/>';
        }
        $str .= $this->output->box_end();

        $str .= $this->output->box_end(); // Row.

        $str .= $this->output->box_start('userquiz-monitor-row'); // Row

        $str .= $this->output->box_start('userquiz-monitor-cell');
        $str .= $cat->accessorieslink;
        $str .= '<input type="hidden" name="h_cb_pl'.$cat->id.'" value="h_cb_pl'.$cat->id.'"/>';
        $str .= $this->output->box_end();

        // Blank cell.
        $str .= $this->output->box('', 'userquiz-monitor-cell');


        $str .= $this->output->box_end(); // Row.

        if (optional_param('qdebug', false, PARAM_BOOL)) {
            $str .= '<div class="qdebug"><pre>';
            if (!empty($cat->questions['A'])) {
                $str .= 'A questions'."\n";
                foreach ($cat->questions['A'] as $catid => $catqs) {
                    $str .= $catid.' => '.implode(', ', $catqs)."\n";
                }
            }
            if (!empty($cat->questions['C'])) {
                $str .= 'C questions'."\n";
                foreach ($cat->questions['C'] as $catid => $catqs) {
                    $str .= $catid.' => '.implode(', ', $catqs)."\n";
                }
            }
            $str .= '</pre></div>';
        }

        $str .= $this->output->box_start('category-bargraph'); // Not a row. Must collapse.
        $str .= '<table width="100%">';
        $str .= $this->render_bar_head_row('');

        // Ensure cat types are presented in sorted order.
        ksort($cat->questiontypes);
        if (!empty($cat->questiontypes)) {

            $keys = array_keys($cat->questiontypes);
            foreach ($keys as $questiontype) {

                if ($questiontype == 'A') {
                    $serieicon = $this->get_area_url('serie1icon', $this->output->pix_url('a', 'block_userquiz_monitor'));
                    $catcounts = new  \StdClass;
                    $catcounts->good = $cat->goodA;
                    $catcounts->cpt = $cat->cptA;
                    $str .= $this->render_bar_range_row($cat->progressbarA, $catcounts, $serieicon);
                }

                if ($this->theblock->config->dualserie && ($questiontype == 'C')) {
                    $serieicon = $this->get_area_url('serie2icon', $this->output->pix_url('c', 'block_userquiz_monitor'));
                    $catcounts = new \StdClass;
                    $catcounts->good = $cat->goodC;
                    $catcounts->cpt = $cat->cptC;
                    $str .= $this->render_bar_range_row($cat->progressbarC, $catcounts, $serieicon);
                }
            }
        }

        $str .= '</table>';
        $str .= $this->output->box_end(); // Not a row.

        $str .= $this->output->box_end(); // Table.
        $str .= $this->output->box_end();

        /*
         * Invisible subcat cat instance container for narrow screens.
         * In narrow screens we need to route the ajax return of subcats reload
         * in this container.
         */
        $str .= '<div id="category-subcatpod-'.$cat->id.'" class="category-subpod" style="visibility:hidden">'; // Not a Row
        $str .= '</div>'; // Not a Row.

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
                             <input type="submit" class="enabled" value="'.$runteststr.'" id="submit"/>
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
                            <input type="submit" class="disabled" value="'.$runteststr.'" id="submit" disabled />
                        </div>
                    </div>
                </div>';

        return $str;
    }

}