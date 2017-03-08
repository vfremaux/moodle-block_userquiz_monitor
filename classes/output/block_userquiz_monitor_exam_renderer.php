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

        $str .= '<div class="userquiz-monitor-cell span12 md-col-12">';
        $str .= $this->output->heading($title, 1);
        $str .= '</div>';

        /*
        // The results filtering scale
        $str .= '<div class="userquiz-monitor-cell span6 md-col-6" style="text-align:right">';
        $str .= $this->filter_state('exams', $this->theblock->instance->id);
        $str .= '</div>';
        */

        $str .= '</div>';
        $str .= '</div>';

        return $str;
    }

    public function available_attempts($userid, $quizid, $maxdisplay = 0) {
        global $DB;

        $rootcategory = @$this->theblock->config->rootcategory;
        $overall = block_userquiz_monitor_init_overall();
        block_userquiz_monitor_init_rootcats($rootcategory, $rootcats);

        $nousedattemptsstr = $this->output->notification(get_string('nousedattemptsstr', 'block_userquiz_monitor'));
        $noavailableattemptsstr = get_string('noavailableattemptsstr', 'block_userquiz_monitor');
        $availablestr = get_string('available', 'block_userquiz_monitor');
        $attemptstr = get_string('attempt', 'block_userquiz_monitor');
        $stillavailablestr = get_string('stillavailable', 'block_userquiz_monitor');

        $str = '<div style="margin-top:5px" class="trans100" >';
        $str .= '<table style="font-size:0.8em" width="100%">';

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
                $usedattempts = array($usedattempt);
                $errors = block_userquiz_monitor_compute_all_results($usedattempts, $rootcategory, $rootcats,
                                                                                   $attempts, $overall);

                $passed = block_userquiz_monitor_is_passing($this->theblock, $overall);
                if ($passed) {
                    $passingstr = get_string('examstatepassed', 'block_userquiz_monitor');
                    $stateicon = 'passed';
                } else {
                    $passingstr = get_string('examstatefailed', 'block_userquiz_monitor');
                    $stateicon = 'failed';
                }
                if (!$maxdisplay || ($used < $maxdisplay)) {
                    $attemptsstr = get_string('attempt', 'quiz', $usedix);
                    $usedurl = new moodle_url('/mod/quiz/review.php', array('q' => $quizid, 'attempt' => $usedattempt->id));
                    $attemptdate = '<a href="'.$usedurl.'">'.userdate($usedattempt->timefinish).'</a>';
                    $iconurl = $this->output->pix_url($stateicon, 'block_userquiz_monitor');
                    $str .= '<tr valign="middle">';
                    $str .= '<td class="exam-history-attempt">'.$attemptsstr.' '.$attemptdate.'<br/>'.$passingstr.'</td>';
                    $str .= '<td><img src="'.$iconurl.'" /></td>';
                    $str .= '</tr>';
                } else {
                    if (!$printedellipse) {
                        $iconurl = $this->output->pix_url('usedattempt', 'block_userquiz_monitor');
                        $str .= '<tr valign="top">';
                        $str .= '<td>...</td>';
                        $str .= '<td><img src="'.$iconurl.'" /></td>';
                        $str .= '</tr>';
                        $printedellipse = true;
                    }
                }
                $usedix--;
            }
        } else {
            $usedattempts = array();
            $str .= '<tr>';
            $str .= '<td>'.$nousedattemptsstr.'</td>';
            $str .= '</tr';
        }

        $limitsenabled = $DB->get_field('qa_usernumattempts', 'enabled', array('quizid' => $quizid));
        if (!$limitsenabled) {
            $iconurl = $this->output->pix_url('availableattempt', 'block_userquiz_monitor');
            $str .= '<tr valign="top">';
            $str .= '<td>'.$attemptstr.' '.$availablestr.'</td>';
            $str .= '<td><img src="'.$iconurl.'" /></td>';
            $str .= '</td>';

            $str .= '</table>'; // Table.

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
                    $str .= '<tr>';
                    $str .= '<td>'.$attemptstr.' '.$availablestr.'</td>';
                    $str .= '<td><img src="'.$iconurl.'" /></td>';
                    $str .= '</tr>';
                    $attemptsleft--;
                }
                if ($attemptsleft) {
                    // If we could not display all available.
                    $str .= '<tr valign="top">';
                    $str .= '<td>'.$attemptsleft.' '.$stillavailablestr.'</td>';
                    $str .= '</tr>';
                }
            } else {
                $str .= '<tr>';
                $str .= '<td align="center" style="color:#ff0000">';
                $str .= $noavailableattemptsstr;
                $str .= '</td>';
                $str .= '</td>';
            }
        }

        $str .= '</table>'; // Table.
        $str .= '</div>';

        return $str;
    }

    public function launch_widget($quizid, $remains, $total) {
        global $COURSE;

        $str = '<div class="exam-launch-panel">';

        $str .= '<div id="exam-remainings">';
        $str .= '<h3>'.get_string('remainingattempts', 'block_userquiz_monitor', "$remains / $total").'</h3>';
        $str .= '</div>';

        if (empty($this->theblock->config->examinstructions)) {
            $str .= get_string('examinstructions', 'block_userquiz_monitor', $this->theblock->config->trainingprogramname);
        } else {
            $str .= '<p>';
            $str .= format_string($this->theblock->config->examinstructions);
            $str .= '</p>';
        }

        $context = \context_system::instance();

        if ($remains || has_capability('moodle/site:config', $context)) {
            $formurl = new moodle_url('/blocks/userquiz_monitor/userpreset.php');
            $str .= '<form name="form" method="GET" action="'.$formurl.'">';
            $str .= '<input type="hidden" name="blockid" value="'.$this->theblock->instance->id.'">';
            $str .= $this->launch_button($quizid, 'examination');
            $str .= '</form>';
        } else {
            $str .= '<input type="submit" value="'.get_string('runexam', 'block_userquiz_monitor').'"/>';
        }

        $str .= '</div>';

        return $str;
    }

    public function results_widget() {
        global $DB, $USER;

        $total = '';

        $rootcategory = $this->theblock->config->rootcategory;
        $quizid = @$this->theblock->config->examquiz;
        $blockid = $this->theblock->instance->id;

        if (empty($quizid)) {
            return get_string('configwarningmonitor', 'block_userquiz_monitor');
        }

        // Init variables.
        $attemptsgraph = '';
        $errormsg = '';
        $overall = block_userquiz_monitor_init_overall();
        $total .= block_userquiz_monitor_init_rootcats($rootcategory, $rootcats);
        $userattempts = block_userquiz_monitor_get_exam_attempts($quizid);

        // Add time range limit.
        $userprefs = $DB->get_record('userquiz_monitor_prefs', array('userid' => $USER->id, 'blockid' => $blockid));

        $totalexams = 0;

        if (!empty($userattempts)) {

            if (@$userprefs->examsdepth > 0) {
                // Remove attempts so we keep the expected number.
                $totalexams = count($userattempts);
                while ($totalexams > $userprefs->examsdepth) {
                    array_shift($userattempts);
                    $totalexams--;
                }
            }
        }

        $errormsg = block_userquiz_monitor_compute_all_results($userattempts, $rootcategory, $rootcats, $attempts, $overall);

        $maxratio = block_userquiz_monitor_compute_ratios($rootcats);

        $graphwidth = ($overall->ratio * 100) / $maxratio;

        // Prepare results bargaphs.
        $graphparams = array (
            'boxheight' => 50,
            /* 'boxwidth' => 300, */
            'boxwidth' => '95%',
            'skin' => 'A',
            'type' => 'global',
            'graphwidth' => $graphwidth,
            'stop' => $this->theblock->config->rateAserie,
            'successrate' => $overall->ratioA,
        );
        $components['progressbarA'] = $this->progress_bar_html_jqw($rootcategory, $graphparams);

        if (!empty($this->theblock->config->dualserie)) {
            $graphparams = array (
                'boxheight' => 50,
                /* 'boxwidth' => 300, */
                'boxwidth' => '95%',
                'skin' => 'C',
                'type' => 'global',
                'graphwidth' => $graphwidth,
                'stop' => $this->theblock->config->rateCserie,
                'successrate' => $overall->ratioC,
            );
            $components['progressbarC'] = $this->progress_bar_html_jqw($rootcategory, $graphparams);
        }

        $data = array('dualserie' => $this->theblock->config->dualserie,
                      'goodA' => $overall->goodA,
                      'cptA' => $overall->cptA,
                      'goodC' => $overall->goodC,
                      'cptC' => $overall->cptC);

        $total = '<div id="divtotal" style="width:100%;">';
        $total .= $this->total($components, $data, null, 'exam');
        $total .= '</div>';

        return $total;
    }

    public function history_widget() {
        global $USER;

        $quizid = @$this->theblock->config->examquiz;
        return $this->available_attempts($USER->id, $quizid, 0);
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

        $str .= '<table width="100%">';

        $str .= $this->render_bar_head_row('');

        if (!empty($cat->questiontypes)) {
            ksort($cat->questiontypes);
            $keys = array_keys($cat->questiontypes);

            foreach ($keys as $questiontype) {
                if ($questiontype == 'A') {
                    $serieicon = $this->get_area_url('serie2icon', $this->output->pix_url('a', 'block_userquiz_monitor'));
                    $cat->skin = 'A';
                    $cat->progressbar = $this->progress_bar_html_jqw($cat->id, $cat->dataA);
                    $str .= $this->render_bar_range_row($cat->progressbar, $cat, $serieicon);
                }

                if ($this->theblock->config->dualserie && ($questiontype == 'C')) {
                    $serieicon = $this->get_area_url('serie2icon', $this->output->pix_url('c', 'block_userquiz_monitor'));
                    $cat->skin = 'C';
                    $cat->progressbar = $this->progress_bar_html_jqw($cat->id, $cat->dataC);
                    $str .= $this->render_bar_range_row($cat->progressbar, $cat, $serieicon);
                }
            }
        }
        $str .= '</table>';

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

        $str .= '<table width="100%">';

        $count = new StdClass();
        $count->good = $overall->goodA;
        $count->cpt = $overall->cptA;
        $serieicon = $this->get_area_url('serie1icon', $this->output->pix_url('a', 'block_userquiz_monitor'));
        $str .= $renderer->render_bar_range_row($progressbara, $count, $serieicon);

        if (!empty($this->theblock->config->dualserie)) {
            $count = new StdClass();
            $count->good = $overall->goodC;
            $count->cpt = $overall->cptC;
            $serieicon = $this->get_area_url('serie2icon', $this->output->pix_url('c', 'block_userquiz_monitor'));
            $$str .= $renderer->render_bar_range_row($progressbarc, $count, $serieicon);
        }

        $str .= '</table>';

        return $str;
    }
}