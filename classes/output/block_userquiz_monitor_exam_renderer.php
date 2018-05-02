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
use \StdClass;

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

        $template = new StdClass;
        $template->examheading = $this->output->heading($title, 1);

        /*
        // The results filtering scale
        $template->filterstate = $this->filter_state('exams', $this->theblock->instance->id);
        */

        return $this->output->render_from_template('block_userquiz_monitor/exampanelheader', $template);
    }

    /**
     * Prints a summary line of all attempts of a user.
     */
    public function available_attempts($userid, $quizid, $maxdisplay = 0) {
        global $DB;

        $gaugerendererfunc = $this->gaugerendererfunc;

        $rootcategory = @$this->theblock->config->rootcategory;
        $overall = block_userquiz_monitor_init_overall();
        block_userquiz_monitor_init_rootcats($rootcategory, $rootcats);

        $template = new StdClass;

        $template->nousedattemptsstr = $this->output->notification(get_string('nousedattemptsstr', 'block_userquiz_monitor'), 'notifyproblem');
        $template->noavailableattemptsstr = $this->output->notification(get_string('noavailableattemptsstr', 'block_userquiz_monitor'), 'notifyproblem');
        $template->availablestr = get_string('available', 'block_userquiz_monitor');
        $template->attemptstr = get_string('attempt', 'block_userquiz_monitor');

        // Start printing used attempts.
        $select = "
            userid = ? AND
            quiz = ?
        ";

        if ($usedattempts = $DB->get_records_select('quiz_attempts', $select, array($userid, $quizid), 'timefinish DESC')) {

            $maxratio = block_userquiz_monitor_compute_ratios($rootcats);

            $used = count($usedattempts);
            $printedellipse = false;
            $usedix = $used;
            foreach ($usedattempts as $usedattempt) {

                $usedattempttpl = new StdClass;

                // We need this to pass a single attempt by ref to compile function.
                $usedattemptarr = array($usedattempt);

                $overall = block_userquiz_monitor_init_overall();

                $errors = block_userquiz_monitor_compute_all_results($usedattemptarr, $rootcategory, $rootcats,
                                                                                   $attempts, $overall, 'exam');

                $passed = block_userquiz_monitor_is_passing($this->theblock, $overall);
                if ($passed) {
                    $usedattempttpl->passingstr = get_string('examstatepassed', 'block_userquiz_monitor');
                    $stateicon = 'passed';
                } else {
                    $usedattempttpl->passingstr = get_string('examstatefailed', 'block_userquiz_monitor');
                    $stateicon = 'failed';
                }
                if (!$maxdisplay || ($used < $maxdisplay)) {
                    $usedattempttpl->attemptsstr = get_string('attempt', 'quiz', $usedix);
                    $usedattempttpl->finishdate = userdate($usedattempt->timefinish);
                    $usedattempttpl->iconurl = $this->output->image_url($stateicon, 'block_userquiz_monitor');
                    $params = array('q' => $quizid, 'attempt' => $usedattempt->id, 'showall' => 1);
                    $usedattempttpl->usedurl = new moodle_url('/mod/quiz/review.php', $params);

                    $seedetailsstr = get_string('seedetails', 'block_userquiz_monitor');
                    $pixurl = $this->get_area_url('detailsicon');
                    if ($pixurl) {
                        $usedattempttpl->detailbutton = '<img class="userquiz-monitor-cat-button"
                                      title="'.$seedetailsstr.'"
                                      src="'.$pixurl.'"/>';
                    } else {
                        // If no detail image loaded keep a single button.
                        $usedattempttpl->detailbutton = '<input type="button"
                                      class="userquiz-monitor-cat-button btn"
                                      title="'.$seedetailsstr.'"
                                      value="'.$seedetailsstr.'"/>';
                    }

                    $graphwidth = ($overall->ratio * 100) / $maxratio;

                    // Prepare results bargaphs.
                    $graphparams = array (
                        'boxheight' => 50,
                        'boxwidth' => '95%',
                        'skin' => 'A',
                        'type' => 'global',
                        'graphwidth' => $graphwidth,
                        'stop' => $this->theblock->config->rateAserie,
                        'successrate' => $overall->ratioA,
                    );
                    $components['progressbarA'] = $this->$gaugerendererfunc($rootcategory, $graphparams);

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
                        $components['progressbarC'] = $this->$gaugerendererfunc($rootcategory, $graphparams);
                    }

                    $data = array('dualserie' => $this->theblock->config->dualserie,
                                  'goodA' => $overall->goodA,
                                  'cptA' => $overall->cptA,
                                  'goodC' => $overall->goodC,
                                  'cptC' => $overall->cptC);

                    $usedattempttpl->totalgraph = $this->total($components, $data, $quizid);

                } else {
                    if (!$printedellipse) {
                        // Do not continue loop.
                        $template->printellipse = true;
                        break;
                    }
                }
                $template->usedattempts[] = $usedattempttpl;
                $template->hasusedattempts = true;
                $usedix--;
            }
        }

        $template->limitsenabled = $DB->get_field('qa_usernumattempts', 'enabled', array('quizid' => $quizid));
        $template->availableiconurl = $this->output->image_url('availableattempt', 'block_userquiz_monitor');

        if ($maxattempts = $DB->get_record('qa_usernumattempts_limits', array('userid' => $userid, 'quizid' => $quizid))) {
            if ($availableattempts = $maxattempts->maxattempts - count($usedattempts)) {
                $attemptsleft = $availableattempts;
                for ($i = 0; $i < min($maxdisplay, $availableattempts); $i++) {
                    // Display as many available as possible.
                    $availtpl = new StdClass;
                    // Empty tpl to iterate.
                    $template->availables[] = $availtpl;
                    $attemptsleft--;
                }
                if ($attemptsleft) {
                    // If we could not display all available, but there are some more left in the account.
                    $template->stillavailablestr = get_string('stillavailable', 'block_userquiz_monitor', $attemptsleft);
                }
            }
        }

        return $this->output->render_from_template('block_userquiz_monitor/availableattempts', $template);
    }

    public function launch_widget($quizid, $remains, $total) {
        global $COURSE;

        $template = new StdClass;

        $template->remainingattemptsstr = get_string('remainingattempts', 'block_userquiz_monitor', "$remains / $total");

        if (empty($this->theblock->config->examinstructions)) {
            $template->examinstructions = get_string('examinstructions', 'block_userquiz_monitor', $this->theblock->config->trainingprogramname);
        } else {
            $template->examinstructions = format_string($this->theblock->config->examinstructions);
        }

        $context = \context_system::instance();

        $template->canexam = $remains || has_capability('moodle/site:config', $context);
        $template->formurl = new moodle_url('/blocks/userquiz_monitor/userpreset.php');
        $template->blockid = $this->theblock->instance->id;
        $template->quizid = $quizid;
        $template->courseid = $COURSE->id;
        $template->runexamstr = get_string('runexam', 'block_userquiz_monitor');

        return $this->output->render_from_template('block_userquiz_monitor/examlaunchform', $template);
    }

    /**
     * Print examination results.
     */
    public function results_widget() {
        global $DB, $USER;

        $total = '';
        $gaugerendererfunc = $this->gaugerendererfunc;

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
            'boxwidth' => '95%',
            'skin' => 'A',
            'type' => 'global',
            'graphwidth' => $graphwidth,
            'stop' => $this->theblock->config->rateAserie,
            'successrate' => $overall->ratioA,
        );
        $components['progressbarA'] = $this->$gaugerendererfunc($rootcategory, $graphparams);

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
            $components['progressbarC'] = $this->$gaugerendererfunc($rootcategory, $graphparams);
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

    /**
     * prints exam attempts history.
     */
    public function history_widget() {
        global $USER;

        $quizid = @$this->theblock->config->examquiz;
        return $this->available_attempts($USER->id, $quizid, 0);
    }


    public function launch_gui($runlaunchform, $quizid, $totalexamstr, $total) {
        global $USER;

        $template = new StdClass;

        $template->commenthist = get_string('commenthist', 'block_userquiz_monitor');

        $template->runexamstr = get_string('runexam', 'block_userquiz_monitor');
        $template->runlaunchform = $runlaunchform;
        $template->availableattempts = $this->available_attempts($USER->id, $quizid, 3);

        $template->totalexamheaderstr = $totalexamstr.' '.$this->output->help_icon('totalexam', 'block_userquiz_monitor', false);
        $template->examhidescoringinterface = $this->config->examhidescoringinterface;
        $template->total = $total;

        return $this->output->render_from_template('block_userquiz_monitor/examlaunchpanel', $template);
    }

    /**
     * @param int $courseid the surrounding course
     * @param object ref $block the userquiz_monitor instance
     */
    function exam($courseid, &$block) {
        global $USER, $DB, $PAGE, $OUTPUT;

        $template = new StdClass;

        $renderer = $PAGE->get_renderer('block_userquiz_monitor', 'exam');

        $rootcategory = @$block->config->rootcategory;
        $quizid = @$block->config->examquiz;
        $blockid = $block->instance->id;
        $renderer->set_block($block);
        $gaugerendererfunc = $renderer->get_gauge_renderer();

        // Init variables.
        $overall = block_userquiz_monitor_init_overall();

        // Preconditions.
        if (empty($quizid)) {
            return $OUTPUT->notification(get_string('configwarningmonitor', 'block_userquiz_monitor'), 'notifyproblem');
        }

        $template->initerrorstr = block_userquiz_monitor_init_rootcats($rootcategory, $rootcats);

        $userattempts = block_userquiz_monitor_get_user_attempts($blockid, $quizid);

        $template->compileerrormsg = block_userquiz_monitor_compute_all_results($userattempts, $rootcategory, $rootcats, $attempts, $overall);
        $template->errors = !empty($template->initerrorstr) || !empty($template->compileerrorstr);
        if ($template->errors) {
            return $this->output->render_from_template('block_userquiz_monitor/exam', $template);
        }

        $maxratio = block_userquiz_monitor_compute_ratios($rootcats);

        $graphwidth = ($overall->ratio * 100) / $maxratio;

        // Call javascript.
        $template->formurl = new moodle_url('/blocks/userquiz_monitor/userpreset.php');
        $template->blockid = $block->instance->id;

        $components['accessorieslink'] = '';

        $graphparams = array (
            'boxheight' => 50,
            /* 'boxwidth' => 300, */
            'boxwidth' => '95%',
            'skin' => 'A',
            'type' => 'global',
            'graphwidth' => $graphwidth,
            'stop' => $block->config->rateAserie,
            'successrate' => $overall->ratioA,
        );
        $components['progressbarA'] = $renderer->$gaugerendererfunc($rootcategory, $graphparams);

        if (!empty($block->config->dualserie)) {
            $graphparams = array (
                'boxheight' => 50,
                /* 'boxwidth' => 300, */
                'boxwidth' => '95%',
                'skin' => 'C',
                'type' => 'global',
                'graphwidth' => $graphwidth,
                'stop' => $block->config->rateCserie,
                'successrate' => $overall->ratioC,
            );
            $components['progressbarC'] = $renderer->$gaugerendererfunc($rootcategory, $graphparams);
        }

        $data = array('dualserie' => $block->config->dualserie,
                      'goodA' => $overall->goodA,
                      'cptA' => $overall->cptA,
                      'goodC' => $overall->goodC,
                      'cptC' => $overall->cptC);

        $total = '<div id="divtotal" style="width:100%;">';
        $total .= $renderer->total($components, $data, $quizid, 'exam');
        $total .= '</div>';

        $template->globalmonitor = $this->total_progress($overall, $rootcategory);

        $cpt = 0;
        $lcpt = 0;
        $scale = '';
        $globalcount = count($rootcats);

        foreach ($rootcats as $catid => $cat) {

            if ($catid == 0) {
                $lcpt++;
                continue; // But why.
            }

            $graphwidth = ($cat->ratio * 100) / $maxratio;

            if ($graphwidth < 1) {
                $graphwidth = 1;
            }

            $params = '';

            if ($cpt == 0) {
                $template->programheadline = $renderer->program_headline(@$block->config->trainingprogramname, 'exam');
            }

            $cat->accessorieslink = '';

            $data = array (
                'boxheight' => 50,
                'boxwidth' => '95%',
                'type' => 'local',
                'skin' => 'A',
                'graphwidth' => $graphwidth,
                'stop' => $block->config->rateAserie,
                'successrate' => $cat->ratioA,
            );
            $cat->progressbarA = $renderer->$gaugerendererfunc($cat->id, $data);

            if ($block->config->dualserie) {
                $data = array (
                    'boxheight' => 50,
                    'boxwidth' => '95%',
                    'type' => 'local',
                    'skin' => 'C',
                    'graphwidth' => $graphwidth,
                    'stop' => $block->config->rateCserie,
                    'successrate' => $cat->ratioC,
                );
                $cat->progressbarC = $renderer->$gaugerendererfunc($cat->id, $data);
            }

            $cpt++;
            $lcpt++;

            $cattpl = new StdClass;
            $cattpl->result = $this->category_result($cat, $lcpt == $globalcount);
            $template->categoryresults[] = $cattpl;
        }

        $notenum = 1;
        if ($block->config->dualserie) {
            $template->note1 = '<span class="smallnotes">'.get_string('columnnotesdual', 'block_userquiz_monitor', $notenum).'</span>';
            $notenum++;
        }
        $template->note2 = '<span class="smallnotes">'.get_string('columnnotesratio', 'block_userquiz_monitor', $notenum).'</span></div>';

        $template->categorydetail = $renderer->category_detail_container();

        return $this->output->render_from_template('block_userquiz_monitor/exam', $template);
    }

    public function category_result($cat, $islast = false) {

        $template = new StdClass;

        $template->islastclass = ($islast) ? 'is-last' : '';

        $template->catid = $cat->id;
        $template->name = $cat->name;
        $template->hassubs = $cat->hassubs;
        $template->loadingurl = $this->output->image_url('i/ajaxloader');

        $template->pixurl = $this->get_area_url('detailsicon');
        $template->seesubsstr = get_string('more', 'block_userquiz_monitor');
        $template->accessorylink = $cat->accessorieslink;

        if (optional_param('qdebug', false, PARAM_BOOL)) {
            $qdebug = '';
            if (!empty($cat->questions['A'])) {
                $qdebug .= 'A questions'."\n";
                foreach ($cat->questions['A'] as $catid => $catqs) {
                    $qdebug .= $catid.' => '.implode(', ', $catqs)."\n";
                }
            }
            if (!empty($cat->questions['C'])) {
                $qdebug .= 'C questions'."\n";
                foreach ($cat->questions['C'] as $catid => $catqs) {
                    $qdebug .= $catid.' => '.implode(', ', $catqs)."\n";
                }
            }
            $template->qdebug = $qdebug;
        }

        $template->barheadrow = $this->render_bar_head_row('');

        if (!empty($cat->questiontypes)) {

            $keys = array_keys($cat->questiontypes);
            foreach ($keys as $questiontype) {

                if ($questiontype == 'A') {
                    $serieicon = $this->get_area_url('serie1icon', $this->output->image_url('a', 'block_userquiz_monitor'));
                    $catcounts = new  \StdClass;
                    $catcounts->good = $cat->goodA;
                    $catcounts->cpt = $cat->cptA;
                    $template->barrangerowA = $this->render_bar_range_row($cat->progressbarA, $catcounts, $serieicon);
                }

                if ($this->theblock->config->dualserie && ($questiontype == 'C')) {
                    $serieicon = $this->get_area_url('serie2icon', $this->output->image_url('c', 'block_userquiz_monitor'));
                    $catcounts = new \StdClass;
                    $catcounts->good = $cat->goodC;
                    $catcounts->cpt = $cat->cptC;
                    $template->barrangerowC = $this->render_bar_range_row($cat->progressbarC, $catcounts, $serieicon);
                }
            }
        }

        return $this->output->render_from_template('block_userquiz_monitor/examcategoryresult', $template);
    }

    public function total_progress($overall, $rootcategory) {

        $template = new StdClass;

        $gaugerendererfunc = $this->gaugerendererfunc;
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

        $progressbara = $this->$gaugerendererfunc($rootcategory, $data);

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
            $progressbarc = $this->$gaugerendererfunc($rootcategory, $data);
        }

        $template->barheadrow = $this->render_bar_head_row('');

        $notenum = 1;
        if (!empty($this->theblock->config->dualserie)) {
            $template->levelstr = get_string('level', 'block_userquiz_monitor', $notenum);
            $notenum++;
        }
        $template->ratiostr = get_string('ratio', 'block_userquiz_monitor', $notenum);

        $count = new StdClass();
        $count->good = $overall->goodA;
        $count->cpt = $overall->cptA;
        $serieicon = $this->get_area_url('serie1icon', $this->output->image_url('a', 'block_userquiz_monitor'));
        $template->barrangerowA = $this->render_bar_range_row($progressbara, $count, $serieicon);

        if (!empty($this->theblock->config->dualserie)) {
            $count = new StdClass();
            $count->good = $overall->goodC;
            $count->cpt = $overall->cptC;
            $serieicon = $this->get_area_url('serie2icon', $this->output->image_url('c', 'block_userquiz_monitor'));
            $template->barrangerowC = $this->render_bar_range_row($progressbarc, $count, $serieicon);
        }

        return $this->output->render_from_template('block_userquiz_monitor/globalprogress', $template);
    }
}