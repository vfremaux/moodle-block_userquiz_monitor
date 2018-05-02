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
use \StdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/userquiz_monitor/renderer.php');

class training_renderer extends \block_userquiz_monitor_renderer {

    protected $course;

    protected $settings;

    public function training_second_button($selector) {

        $template = new StdClass;

        $template->selector = $selector;

        return $this->output->render_from_template('block_userquiz_monitor/trainingsecondlaunchbutton', $template);
    }

    public function heading() {

        $title = get_string('testtitle', 'block_userquiz_monitor', $this->theblock->config->trainingprogramname);
        $template = new StdClass;

        $template->trainingheading = $this->output->heading($title, 1);

        /*
        $template->filterstate = $this->filter_state('training', $this->theblock->instance->id);
        */
        $template->filterform = $this->training_filter_form($this->theblock);

        if (!empty($template->trainingheading) && !empty($template->filterform)) {
            $template->hascontent = true;
        }

        return $this->output->render_from_template('block_userquiz_monitor/trainingheader', $template);
    }

    public function global_monitor($total, $selector) {

        $template = new StdClass;
        $template->totalstr = get_string('total', 'block_userquiz_monitor');
        $template->totalhelpicon = $this->output->help_icon('total', 'block_userquiz_monitor', false);

        $template->runtesthelpicon = $this->output->help_icon('launch', 'block_userquiz_monitor', false);
        $template->runteststr = get_string('runtest', 'block_userquiz_monitor');
        $template->total = $total;

        $template->selector = $selector;

        return $this->output->render_from_template('block_userquiz_monitor/trainingglobalmonitor', $template);
    }

    public function category_result($cat, $islast = false) {

        $template = new StdClass;

        $template->islastclass = ($islast) ? 'is-last' : '';

        $template->catid = $cat->id;
        $template->name = $cat->name;
        $template->hassubs = $cat->hassubs;
        $template->loadingurl = $this->output->pix_url('i/ajaxloader');

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
                    $serieicon = $this->get_area_url('serie1icon', $this->output->pix_url('a', 'block_userquiz_monitor'));
                    $catcounts = new  \StdClass;
                    $catcounts->good = $cat->goodA;
                    $catcounts->cpt = $cat->cptA;
                    $template->barrangerowA = $this->render_bar_range_row($cat->progressbarA, $catcounts, $serieicon);
                }

                if ($this->theblock->config->dualserie && ($questiontype == 'C')) {
                    $serieicon = $this->get_area_url('serie2icon', $this->output->pix_url('c', 'block_userquiz_monitor'));
                    $catcounts = new \StdClass;
                    $catcounts->good = $cat->goodC;
                    $catcounts->cpt = $cat->cptC;
                    $template->barrangerowC = $this->render_bar_range_row($cat->progressbarC, $catcounts, $serieicon);
                }
            }
        }

        return $this->output->render_from_template('block_userquiz_monitor/trainingcategoryresult', $template);
    }

    public function launch_gui($options, $quizzeslist) {
        global $COURSE;

        $template = new StdClass;

        $template->numberofquestionsstr = get_string('numberquestions', 'block_userquiz_monitor');
        $template->runteststr = get_string('runtest', 'block_userquiz_monitor');
        $template->runtraininghelpstr = get_string('runtraininghelp', 'block_userquiz_monitor');
        $template->jshandler = 'sync_training_selectors(this)';
        $template->quizlist = $quizzeslist;
        $template->options = $options;
        $template->courseid = $COURSE->id;
        $template->disabled = 'disabled="disabled"';

        return $this->output->render_from_template('block_userquiz_monitor/traininglaunchgui', $template);
    }

    public function empty_launch_gui() {
        return $this->launch_gui(null, null);
    }

    /**
     * @param int $courseid the surrounding course
     * @param object ref $block the userquiz_monitor instance
     */
    function training($courseid, &$block) {
        global $USER, $DB, $PAGE, $OUTPUT;

        $template = new StdClass;

        $renderer = $PAGE->get_renderer('block_userquiz_monitor', 'training');

        $rootcategory = @$block->config->rootcategory;
        $quizzesids = @$block->config->trainingquizzes;
        $blockid = $block->instance->id;
        $renderer->set_block($block);
        $gaugerendererfunc = $renderer->get_gauge_renderer();

        // Init variables.
        $quizzeslist = '';
        $overall = block_userquiz_monitor_init_overall();

        // Preconditions.
        if (empty($quizzesids)) {
            return $OUTPUT->notification(get_string('configwarningmonitor', 'block_userquiz_monitor'), 'notifyproblem');
        }

        $template->initerrorstr = block_userquiz_monitor_init_rootcats($rootcategory, $rootcats);

        foreach ($quizzesids as $quiz) {
            $quizlist[] = $quiz;
        }
        $quizzesliststring = implode(",", $quizlist);
        $quizlist = implode("','", $quizlist);
        $quizlist = '\''.$quizlist.'\'';

        $userattempts = block_userquiz_monitor_get_user_attempts($blockid, $quizzesids);

        $template->compileerrormsg = block_userquiz_monitor_compute_all_results($userattempts, $rootcategory, $rootcats, $attempts, $overall);
        $template->errors = !empty($template->initerrorstr) || !empty($template->compileerrorstr);
        if ($template->errors) {
            return $this->output->render_from_template('block_userquiz_monitor/training', $template);
        }

        $maxratio = block_userquiz_monitor_compute_ratios($rootcats);

        $graphwidth = ($overall->ratio * 100) / $maxratio;

        // Call javascript.
        $template->formurl = new moodle_url('/blocks/userquiz_monitor/userpreset.php');
        $template->blockid = $block->instance->id;

        if (!empty($block->config->trainingshowhistory)) {
            if (!empty($userattempts)) {
                $params = calcul_hist($rootcategory, $attempts);
                $params = array('mode' => 'displayhist',
                                'datetype' => 'short',
                                'action' => 'Get_Stats',
                                'type' => 'category',
                                'param' => $params);
                $popuplink = new moodle_url('/blocks/userquiz_monitor/popup.php', $params);
                $action = new popup_action('click', $popuplink, 'ratings', array('height' => 400, 'width' => 600));
                $label = get_string('hist', 'block_userquiz_monitor');
                $pixicon = new pix_icon('graph', $label, 'block_userquiz_monitor', array('class' => 'userquiz-monitor-cat-button'));
                $link = new action_link($popuplink, '', $action, array(), $pixicon);
                $alternateurl = $renderer->get_area_url('statsbuttonicon', '');
                $components['accessorieslink'] = $renderer->render_action_link($link, $alternateurl);
            } else {
                $title = get_string('hist', 'block_userquiz_monitor');
                $pixurl = $renderer->get_area_url('statsbuttonicon', $OUTPUT->pix_url('graph', 'block_userquiz_monitor'));
                $components['accessorieslink'] = '<img class="userquiz-monitor-cat-button"  title="'.$title.'" src="'.$pixurl.'"/>';
            }
        } else {
            $components['accessorieslink'] = '';
        }

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
        $total .= $renderer->total($components, $data, $quizlist, 'training');
        $total .= '</div>';

        $selector = block_userquiz_monitor_update_selector($courseid, null, 'mode0', $rootcategory, $quizlist);

        $template->globalmonitor = $renderer->global_monitor($total, $selector);

        $cpt = 0;
        $lcpt = 0;
        $scale = '';
        $quizlist = urlencode($quizlist);
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

            if (!empty($userattempts)) {
                // Format the data to construct the histogram.
                $params = calcul_hist($catid, $attempts);
            } else {
                $params = '';
            }

            if ($cpt == 0) {
                $template->programheadline = $renderer->program_headline(@$block->config->trainingprogramname);
            }

            if (!empty($block->config->trainingshowhistory)) {
                if (!empty($userattempts)) {
                    $params = array('mode' => 'displayhist',
                                    'datetype' => 'long',
                                    'action' => 'Get_Stats',
                                    'type' => 'category',
                                    'param' => $params);
                    $popuplink = new moodle_url('/blocks/userquiz_monitor/popup.php', $params);
                    $params = array('height' => 400, 'width' => 600);
                    $action = new popup_action('click', $popuplink, 'ratings', $params);
                    $label = get_string('hist', 'block_userquiz_monitor');
                    $pixicon = new pix_icon('graph', $label, 'block_userquiz_monitor', array('class' => 'userquiz-monitor-cat-button'));
                    $link = new action_link($popuplink, '', $action, array(), $pixicon);
                    $alternateurl = $renderer->get_area_url('statsbuttonicon', '');
                    $cat->accessorieslink = $renderer->render_action_link($link, $alternateurl);
                } else {
                    $title = get_string('hist', 'block_userquiz_monitor');
                    $pixurl = $renderer->get_area_url('statsbuttonicon', $OUTPUT->pix_url('graph', 'block_userquiz_monitor'));
                    $cat->accessorieslink = '<img class="userquiz-monitor-cat-button shadow" title="'.$title.'" src="'.$pixurl.'" />';
                }
            } else {
                $cat->accessorieslink = '';
            }

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
            $cattpl->result = $renderer->category_result($cat, $lcpt == $globalcount);
            $template->categoryresults[] = $cattpl;
        }

        $notenum = 1;
        if ($block->config->dualserie) {
            $template->note1 = '<span class="smallnotes">'.get_string('columnnotesdual', 'block_userquiz_monitor', $notenum).'</span>';
            $notenum++;
        }
        $template->note2 = '<span class="smallnotes">'.get_string('columnnotesratio', 'block_userquiz_monitor', $notenum).'</span></div>';

        $template->categorydetail = $renderer->category_detail_container();

        // Will display only for small screens.
        $template->secondbutton = $renderer->training_second_button($selector);

        return $this->output->render_from_template('block_userquiz_monitor/training', $template);
    }

    function training_filter_form(&$block) {
        global $DB, $CFG, $USER;

        include($CFG->dirroot.'/blocks/userquiz_monitor/preferenceForm.php');

        $preferenceform = new \PreferenceForm(null, array('mode' => 'training', 'blockconfig' => $block->config));
        $params = array('userid' => $USER->id, 'blockid' => $block->instance->id);
        if ($prefs = $DB->get_record('userquiz_monitor_prefs', $params)) {
            $data = clone($prefs);
            unset($data->id);
        } else {
            $data = new StdClass;
        }
        $data->blockid = $block->instance->id;
        $data->selectedview = 'training';
        $preferenceform->set_data($data);

        if (!$preferenceform->is_cancelled()) {
            if ($data = $preferenceform->get_data()) {
                $data->userid = $USER->id;
                if (!empty($prefs)) {
                    if (!is_null($data->resultsdepth)) {
                        $prefs->resultsdepth = 0 + @$data->resultsdepth;
                    }
                    $DB->update_record('userquiz_monitor_prefs', $prefs);
                } else {
                    unset($data->id);
                    $DB->insert_record('userquiz_monitor_prefs', $data);
                }
            }
        }

        @ob_flush();
        ob_start();
        $preferenceform->display();
        $str = ob_get_clean();

        return $str;
    }
}