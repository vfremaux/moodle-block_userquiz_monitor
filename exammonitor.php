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
 * Display examination's monitor, provides a dashboard
 *
 * @package     block_userquiz_monitor
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/userquiz_monitor/js/scripts.php');

/**
 * the dashboard builder.
 */
function get_monitorexam($courseid, &$response, &$block) {
    global $USER, $DB, $OUTPUT, $PAGE;

    $renderer = $PAGE->get_renderer('block_userquiz_monitor');
    $renderer->set_block($block);

    $rootcategory = @$block->config->rootcategory;
    $quizid = @$block->config->examquiz;
    $blockid = $block->instance->id;

    if (empty($quizid)) {
        $response .= get_string('configwarningmonitor', 'block_userquiz_monitor');
        return;
    }

    // Init variables.
    $attemptsgraph = '';
    $errormsg = '';
    $overall = block_userquiz_monitor_init_overall();
    $response .= block_userquiz_monitor_init_rootcats($rootcategory, $rootcats);
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

    $maxratio = 0;
    foreach (array_keys($rootcats) as $catid) {
        $ratioc = $rootcats[$catid]->goodC / $rootcats[$catid]->cptC;
        $rootcats[$catid]->ratioC = (@$rootcats[$catid]->cptC == 0) ? 0 : round($ratioc * 100);
        $ratioa = $rootcats[$catid]->goodA / $rootcats[$catid]->cptA;
        $rootcats[$catid]->ratioA = (@$rootcats[$catid]->cptA == 0) ? 0 : round($ratioa * 100);
        $ratio = $rootcats[$catid]->good / $rootcats[$catid]->cpt;
        $rootcats[$catid]->ratio = (@$rootcats[$catid]->cpt == 0) ? 0 : round($ratio * 100);
        if ($maxratio < $rootcats[$catid]->ratio) {
            $maxratio = $rootcats[$catid]->ratio;
        }
    }

    if ($maxratio == 0) {
        $maxratio = 1;
    }

    $graphwidth = ($overall->ratio * 100) / $maxratio;

    // Call javascript.
    $scripts = get_js_scripts(array_keys($rootcats));
    $response .= $scripts;

    if (!empty($userattempts)) {
        foreach ($userattempts as $userattempt) {
            $results = array();
            if (@$attempts[$userattempt->id][$rootcategory]->cptA != 0 &&
                    @$attempts[$userattempt->id][$rootcategory]->cptC != 0) {

                $scoreset = $attempts[$userattempt->id][$rootcategory];
                $percentscorea = round(($scoreset->goodA / $scoreset->cptA) * 100);
                $percentscorec = round(($scoreset->goodC / $scoreset->cptC) * 100);

                if ($percentscorea >= 85 && $percentscorec >= 75) {
                    $attemptdate = date('j/m/Y', $userattempt->timefinish);
                    $results[] = array( "date" => $attemptdate, "success" => true);
                } else {
                    $attemptdate = date('j/m/Y', $userattempt->timefinish);
                    $results[] = array( 'date' => $attemptdate, 'success' => false);
                }
            }
        }

        $params = array('userid' => $USER->id, 'quizid' => $quizid);
        $userattemptnum = 0 + $DB->get_field('qa_usernumattempts_limits', 'maxattempts', $params);

        $data = array (
            'boxheight' => 65,
            'boxwidth' => 400,
            'maxattempts' => $userattemptnum,
            'results' => $results,
        );

        $testdata = urlencode(json_encode($data));
        $attemptsgraph = $renderer->attempts($data);
    }

    // Construct report tab.
    $formurl = new moodle_url('/blocks/userquiz_monitor/userpreset.php');
    $response .= '<form name="form" method="GET" action="'.$formurl.'">';
    $response .= '<input type="hidden" name="blockid" value="'.$block->instance->id.'">';

    $total = $renderer->total_progress($overall, $rootcategory);

    if (!empty($userattempts)) {
        // Format the data to construct the histogram.
        $histparams = calcul_hist($rootcats, $attempts);
        $params = array('mode' => 'displayhist', 'datetype' => 'short', 'type' => 'categories', 'param' => $histparams);
        $popuplink = new moodle_url('/blocks/userquiz_monitor/popup.php', $params);
        $action = new popup_action('click', $popuplink, 'ratings', array('height' => 400, 'width' => 600));
        $label = get_string('hist', 'block_userquiz_monitor');
        $pixicon = new pix_icon('graph', $label, 'block_userquiz_monitor', array('class' => 'userquiz-cmd-icon'));
        $accessorieslink = $OUTPUT->action_link($popuplink, '', $action, array(), $pixicon);
    } else {
        $params = '';
        $title = get_string('hist', 'block_userquiz_monitor');
        $pixurl = $OUTPUT->pix_url('graph', 'block_userquiz_monitor');
        $accessorieslink = '<img class="userquiz-cmd-icon" title="'.$title.'" src="'.$pixurl.'"/>';
    }

    $runlaunchform = '<div>'.get_string('noavailableattemptsstr', 'block_userquiz_monitor').'</div>';
    if (userquizmonitor_count_available_attempts($USER->id, $quizid) > 0) {
        $runlaunchform = $renderer->launch_button($quizid, 'examination');
    }

    $totalexamstr = get_string('totalexam', 'block_userquiz_monitor');
    $response .= $renderer->exam_launch_gui($runlaunchform, $quizid, $accessorieslink, $totalexamstr, $total);

    if (!empty($block->config->examhidescoringinterface)) {
        return;
    }

    if (!empty($errormsg)) {
        $errormsg = $renderer->errorline($errormsg);
    }

    $response .= '  <div class="trainingcontener">
                        <table class="tablemonitorcategoriescontainer">
                            '.$errormsg.'
                            <tr valign="top">
                                <td style="width:45%; padding:5px;">';

    $cpt = 0;
    foreach ($rootcats as $catid => $cat) {
        // Init variables.

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

        $cat->buttons = '';

        if (!empty($userattempts)) {
            $params = array('mode' => 'displayhist', 'datetype' => 'long', 'type' => 'category', 'param' => $params);
            $popuplink = new moodle_url('/blocks/userquiz_monitor/popup.php', $params);
            $action = new popup_action('click', $popuplink, 'ratings', array('height' => 400, 'width' => 600));
            $title = get_string('hist', 'block_userquiz_monitor');
            $pixicon = new pix_icon('graph', $label, 'block_userquiz_monitor', array('class' => 'userquiz-cmd-icon'));
            $cat->buttons .= $OUTPUT->action_link($popuplink, $title, $action, array(), $pixicon);
        } else {
            $title = get_string('hist', 'block_userquiz_monitor');
            $pixurl = $OUTPUT->pix_url('graph', 'block_userquiz_monitor');
            $cat->buttons .= '<span><img class="userquiz-cmd-icon" title="'.$title.'" src="'.$pixurl.'" /></span>';
        }

        if ($cpt == 0) {
            $response .= $renderer->program_headline(@$block->config->trainingprogramname, null);
        }

        $jshandler = 'activedisplayexaminationsubcategories('.$courseid.', '.$catid;
        $jshandler .= ', idcategoriespl, '.$quizid.', '.$blockid.')';

        $cat->dataA = array (
            'boxheight' => 50,
            'boxwidth' => 160,
            'skin' => 'A',
            'type' => 'local',
            'graphwidth' => $graphwidth,
            'stop' => $block->config->rateAserie,
            'successrate' => $cat->ratioA,
        );

        if ($block->config->dualserie) {
            $cat->dataC = array (
                            'boxheight' => 50,
                            'boxwidth' => 160,
                            'skin' => 'C',
                            'type' => 'local',
                            'graphwidth' => $graphwidth,
                            'stop' => $block->config->rateCserie,
                            'successrate' => $cat->ratioC);
        }

        $response .= $renderer->exam_main_category($cat, $jshandler);
        $cpt++;
    }

    $notenum = 1;
    if ($block->config->dualserie) {
        $response .= '<span class="smallnotes">'.get_string('columnnotesdual', 'block_userquiz_monitor', $notenum).'</span>';
        $notenum++;
    }
    $response .= '<span class="smallnotes">'.get_string('columnnotesratio', 'block_userquiz_monitor', $notenum).'</span>';
    $response .= '</td>';
    $response .= '<td style="width:45%; padding:5px;">';
    $response .= $renderer->category_detail_container();
    $response .= '</td>';
    $response .= '</tr>';
    $response .= '</table>';
    $response .= '</center></div>';
    $response .= '</form>';
}

