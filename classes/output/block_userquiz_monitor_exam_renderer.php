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

class exam_renderer extends \block_userquiz_monitor_renderer {

    protected $block;

    protected $course;

    protected $settings;

    public function heading() {

        if (!empty($this->theblock->config->alternateexamheading)) {
            $title = format_text($this->theblock->config->alternateexamheading);
        } else {
            $title = get_string('examtitle', 'block_userquiz_monitor', $this->theblock->config->trainingprogramname);
        }

        $str = '<div>';
        $str .= '<div class="userquiz-monitor-row">';

        $str .= '<div class="userquiz-monitor-cell span6 md-col-6">';
        $str .= $this->output->heading( $title, 1);
        $str .= '</div>';

        $str .= '<div class="userquiz-monitor-cell span6 md-col-6" style="text-align:right">';
        $str .= $this->filter_state('exams', $this->theblock->instance->id);
        $str .= '</div>';

        $str .= '</div>';
        $str .= '</div>';

        return $str;
    }

    public function available_attempts($userid, $quizid, $maxdisplay = 0) {
        global $DB;

        $nousedattemptsstr = $this->output->notification(get_string('nousedattemptsstr', 'block_userquiz_monitor'));
        $noavailableattemptsstr = get_string('noavailableattemptsstr', 'block_userquiz_monitor');
        $availablestr = get_string('available', 'block_userquiz_monitor');
        $attemptstr = get_string('attempt', 'block_userquiz_monitor');
        $stillavailablestr = get_string('stillavailable', 'block_userquiz_monitor');

        $str = '<div style="margin-top:5px" class="trans100" >';
        $str .= '<div class="userquiz-monitor-container" style="font-size:0.8em">';

        // Start printing used attempts.
        $select = "
            userid = ? AND
            quiz = ?
        ";

        if ($usedattempts = $DB->get_records_select('quiz_attempts', $select, array($userid, $quizid), 'timefinish DESC')) {
            $used = count($usedattempts);
            $printedellipse = false;
            $usedix = $used;
            foreach ($usedattempts as $usedattempt) {
                if ($used < $maxdisplay) {
                    $attemptsstr = get_string('attempt', 'quiz', $usedix);
                    $usedurl = new moodle_url('/mod/quiz/review.php', array('q' => $quizid, 'attempt' => $usedattempt->id));
                    $attemptdate = '<a href="'.$usedurl.'">'.userdate($usedattempt->timefinish).'</a>';
                    $iconurl = $this->output->pix_url('usedattempt', 'block_userquiz_monitor');
                    $str .= '<div class="userquiz-monitor-row">';
                    $str .= '<div userquiz-monitor-cell">'.$attemptsstr.'</div>';
                    $str .= '<div userquiz-monitor-cell">'.$attemptdate.'</div>';
                    $str .= '<div userquiz-monitor-cell"><img src="'.$iconurl.'" /></div>';
                    $str .= '</div>';
                } else {
                    if (!$printedellipse) {
                        $iconurl = $this->output->pix_url('usedattempt', 'block_userquiz_monitor');
                        $str .= '<div class="userquiz-monitor-row">';
                        $str .= '<div class="userquiz-monitor-cell">...</div>';
                        $str .= '<div class="userquiz-monitor-cell"></div>';
                        $str .= '<div class="userquiz-monitor-cell"><img src="'.$iconurl.'" /></div>';
                        $str .= '</div>';
                        $printedellipse = true;
                    }
                }
                $usedix--;
            }
        } else {
            $usedattempts = array();
            $str .= '<div class="userquiz-monitor-row">';
            $str .= '<div class="userquiz-monitor-cell">'.$nousedattemptsstr.'</div>';
            $str .= '</div>';
        }

        $limitsenabled = $DB->get_field('qa_usernumattempts', 'enabled', array('quizid' => $quizid));
        if (!$limitsenabled) {
            $iconurl = $this->output->pix_url('availableattempt', 'block_userquiz_monitor');
            $str .= '<div class="userquiz-monitor-row">';
            $str .= '<div class="userquiz-monitor-cell">'.$attemptstr.'</div>';
            $str .= '<div class="userquiz-monitor-cell">'.$availablestr.'</div>';
            $str .= '<div class="userquiz-monitor-cell"><img src="'.$iconurl.'" /></div>';
            $str .= '</div>';

            $str .= '</div>'; // Table.
            $str .= '</div>';
            return $str;
        }

        if ($maxattempts = $DB->get_record('qa_usernumattempts_limits', array('userid' => $userid, 'quizid' => $quizid))) {
            if ($availableattempts = $maxattempts->maxattempts - count($usedattempts)) {
                $iconurl = $this->output->pix_url('availableattempt', 'block_userquiz_monitor');
                $attemptsleft = $availableattempts;
                for ($i = 0; $i < min($maxdisplay, $availableattempts); $i++) {
                    // Display as many available as possible.
                    $iconurl = $this->output->pix_url('availableattempt', 'block_userquiz_monitor');
                    $str .= '<div class="userquiz-monitor-row">';
                    $str .= '<div class="userquiz-monitor-cell">'.$attemptstr.'</div>';
                    $str .= '<div class="userquiz-monitor-cell">'.$availablestr.'</div>';
                    $str .= '<div class="userquiz-monitor-cell"><img src="'.$iconurl.'" /></div>';
                    $str .= '</div>';
                    $attemptsleft--;
                }
                if ($attemptsleft) {
                    // If we could not display all available.
                    $str .= '<div class="userquiz-monitor-row">';
                    $str .= '<div class="userquiz-monitor-cell">'.$attemptsleft.' '.$stillavailablestr.'</div>';
                    $str .= '<div class="userquiz-monitor-cell"></div>';
                    $str .= '</div>';
                }
            } else {
                $str .= '<div class="userquiz-monitor-row">';
                $str .= '<div class="userquiz-monitor-cell" align="center" style="color:#ff0000">';
                $str .= $noavailableattemptsstr;
                $str .= '</div>';
                $str .= '</div>';
            }
        }

        $str .= '</div>'; // Table.
        $str .= '</div>';

        return $str;
    }

    public function launch_button($quizid, $mode) {
        global $COURSE;

        $str = '
            <div>
                <input type="hidden" name="quizid" value="'.$quizid.'"/>
                <input type="hidden" name="mode" value="'.$mode.'"/>
                <input type="hidden" name="courseid" value="'.$COURSE->id.'"/>
                <input type="submit" value="'.get_string('runexam', 'block_userquiz_monitor').'"/>
            </div>
        ';
        return $str;
    }

    public function launch_gui($runlaunchform, $quizid, $totalexamstr, $total) {
        global $USER;

        $str = '';

        $commenthist = get_string('commenthist', 'block_userquiz_monitor');

        $str .= '<div>'; // Table
        $str .= '<div class="userquiz-monitor-row">'; // Row.

        $str .= '<div class="userquiz-monitor-cell vertical-centered span3">'; // Cell.

        $str .= '<h1>'.get_string('runexam', 'block_userquiz_monitor').'</h1>';
        $str .= '<div class="trans100" style="text-align:center;">';
        $str .= $runlaunchform;
        $str .= '</div>';
        $str .= $this->available_attempts($USER->id, $quizid, 3);

        $str .= '</div>'; // Cell.

        if (!empty($this->theblock->config->examhidescoringinterface)) {
            $str .= '</div>';
            $str .= '</div>';
            $str .= '</div>';
            return $str;
        }

        $str .= '<div class="userquiz-monitor-cell vertical-centered  userquiz-cat-progress span9">';
        $str .= '<h1>'.$totalexamstr.' '.$this->output->help_icon('totalexam', 'block_userquiz_monitor', false).'</h1>';

        $str .= '<div class="trans100">';

        $str .= '<div class="userquiz-monitor-container">';

        $str .= '<div class="userquiz-monitor-row colspaned">';
        $str .= '</div>'; // Row.

        $str .= $total;

        $str .= '</div>'; // Container.

        $str .= '</div>'; // Trans

        $str .= '</div>'; // Cell.

        $str .= '</div>'; // Row.
        $str .= '</div>'; // Table.

        return $str;
    }

    public function category_results($cat) {

        $str = '';

        $str .= '<div class="userquiz-monitor-row">'; // Row.
        $str .= '<div class="userquiz-monitor-cell vertical-centered userquiz-cat-progress">';
        $str .= '<div id="progressbarcontainer'.$cat->skin.$cat->id.'">';
        $str .= $cat->progressbar;
        $str .= '</div>';
        $str .= '</div>';
        $str .= '<div class="userquiz-monitor-cell vertical-centered">';
        $pixurl = $this->output->pix_url(core_text::strtolower($cat->skin), 'block_userquiz_monitor');
        $str .= '<img class="userquiz-monitor-questiontype" src="'.$pixurl.'" />';
        $str .= '</div>';
        $str .= '<div class="userquiz-monitor-cell vertical-centered">';
        $good = 'good'.$cat->skin;
        $cpt = 'cpt'.$cat->skin;
        $str .= '<h4>'.$cat->$good.'/'.$cat->$cpt.'</h4>';
        $str .= '</div>';
        $str .= '</div>'; // Row.

        return $str;
    }

    public function main_category($cat, $jshandler) {

        $seesubsstr = get_string('more', 'block_userquiz_monitor', $cat->name);

        $str = '';

        $str .= '<div class="trans100" id="divpl'.$cat->id.'">';
        $str .= '<div class="userquiz-monitor-categorycontainer">'; // Table.

        $str .= '<div class="userquiz-monitor-row">'; // Row.
        $str .= '<div class="userquiz-monitor-cell categoryname">';
        $str .= $cat->name;
        $str .= '</div>';
        $str .= '</div>'; // Row.

        $str .= '<div class="userquiz-monitor-row">'; // Row.
        $str .= '<div class="userquiz-monitor-cell">';
        $str .= $cat->buttons;
        $str .= '</div>';
        $str .= '<div class="userquiz-monitor-cell">';
        // Blank cell.
        $str .= '</div>';
        $str .= '<div class="userquiz-monitor-cell" style="text-align:center;">';
        $str .= '<span style="float:right">';
        $pixurl = $this->get_area_url('detailsicon', $this->output->pix_url('detail', 'block_userquiz_monitor'));
        $str .= '<img class="userquiz-monitor-cat-button"
                      title="'.$seesubsstr.'"
                      src="'.$pixurl.'"
                      onclick="'.$jshandler.'" />';
        $str .= '</span>';
        $str .= '</div>';
        $str .= '</div>'; // Row.

        $str .= '<div class="userquiz-monitor-row">'; // Row.
        $str .= '<div class="userquiz-monitor-cell userquiz-monitor-bg">';
        // Blank cell.
        $str .= '</div>';
        $str .= '<div class="userquiz-monitor-cell userquiz-monitor-bg" style="font-size:0.8em;text-align:center">';
        $str .= get_string('level1', 'block_userquiz_monitor');
        $str .= '</div>';
        $str .= '<div class="userquiz-monitor-cell userquiz-monitor-bg" style="font-size:0.8em; text-align:center">';
        $str .= get_string('ratio1', 'block_userquiz_monitor');
        $str .= '</div>';
        $str .= '</div>'; // Row.

        if (!empty($cat->questiontypes)) {
            ksort($cat->questiontypes);
            $keys = array_keys($cat->questiontypes);

            foreach ($keys as $questiontype) {
                if ($questiontype == 'A') {
                    $cat->skin = 'A';
                    $cat->progressbar = $this->progress_bar_html_jqw($cat->id, $cat->dataA);
                    $str .= $this->category_results($cat);
                }

                if ($this->theblock->config->dualserie && ($questiontype == 'C')) {
                    $cat->skin = 'C';
                    $cat->progressbar = $this->progress_bar_html_jqw($cat->id, $cat->dataC);
                    $str .= $this->category_results($cat);
                }
            }
        }

        $str .= '</div>'; // Table.
        $str .= '</div>';

        return $str;
    }

    public function total_progress($overall, $rootcategory) {

        $graphwidth = 100;

        $data = array (
            'boxheight' => 50,
            'boxwidth' => 300,
            'skin' => 'A',
            'type' => 'global',
            'graphwidth' => $graphwidth,
            'stop' => $this->theblock->config->rateAserie,
            'successrate' => $overall->ratioA,
        );

        $progressbara = $this->progress_bar_html_jqw($rootcategory, $data);

        if (!empty($this->theblock->config->dualserie)) {
            $data = array (
                'boxheight' => 50,
                'boxwidth' => 300,
                'skin' => 'C',
                'type' => 'global',
                'graphwidth' => $graphwidth,
                'stop' => $this->theblock->config->rateCserie,
                'successrate' => $overall->ratioC,
            );
            $progressbarc = $this->progress_bar_html_jqw($rootcategory, $data);
        }

        $str = '';

        $str .= '<div class="userquiz-monitor-row">'; // Row.
        $str .= '<div class="userquiz-monitor-cell span6 md-col-6">';
        $str .= '</div>';
        $notenum = 1;
        if (!empty($this->theblock->config->dualserie)) {
            $str .= '<div class="userquiz-monitor-cell span3 md-col-3">';
            $str .= get_string('level', 'block_userquiz_monitor', $notenum);
            $notenum++;
            $str .= '</div>';
        }
        $str .= '<div class="userquiz-monitor-cell span3 md-col-3">';
        $str .= get_string('ratio', 'block_userquiz_monitor', $notenum);
        $str .= '</div>';
        $str .= '</div>'; // Row.

        $str .= '<div class="userquiz-monitor-row">'; // Row.
        $str .= '<div class="userquiz-monitor-cell vertical-centered span6 md-col-6">';
        $str .= '<div>';
        $str .= $progressbara;
        $str .= '</div>';
        $str .= '</div>';
        if (!empty($this->theblock->config->dualserie)) {
            $str .= '<div class="userquiz-monitor-cell vertical-centered span3 md-col-3">';
            $pixurl = $this->get_area_url('serie1icon', $this->output->pix_url('a', 'block_userquiz_monitor'));
            $str .= '<img class="userquiz-monitor-total-icon" src="'.$pixurl.'" />';
            $str .= '</div>';
        }
        $str .= '<div class="userquiz-monitor-cell vertical-centered span3 md-col-3">';
        $str .= '<h4>'.$overall->goodA.'/'.$overall->cptA.'</h4>';
        $str .= '</div>';
        $str .= '</div>'; // Row.

        if (!empty($this->theblock->config->dualserie)) {
            $str .= '<div class="userquiz-monitor-row">'; // Row.
            $str .= '<div class="userquiz-monitor-cell vertical-centered span6 md-col-6">';
            $str .= '<div>';
            $str .= $progressbarc;
            $str .= '</div>';
            $str .= '</div>';
            $str .= '<div class="userquiz-monitor-cell vertical-centered span3 md-col-3">';
            $pixurl = $this->get_area_url('serie2icon', $this->output->pix_url('c', 'block_userquiz_monitor'));
            $str .= '<img class="userquiz-monitor-total-icon" src="'.$pixurl.' "/>';
            $str .= '</div>';
            $str .= '<div class="userquiz-monitor-cell vertical-centered span3 md-col-3">';
            $str .= '<h4>'.$overall->goodC.'/'.$overall->cptC.'</h4>';
            $str .= '</div>';
            $str .= '</div>'; // Row.
        }

        return $str;
    }
}