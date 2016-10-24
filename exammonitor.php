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
 * @package     block_userquiz_monitor
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Display examination's monitor, provides a dashboard
 */

require_once($CFG->dirroot.'/blocks/userquiz_monitor/js/scripts.php');

function get_monitorexam($courseid, &$response, &$block) {
    global $USER, $CFG, $DB, $OUTPUT, $PAGE;

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
    $attempts_graph = '';
    $errormsg = '';
    $overall = block_userquiz_monitor_init_overall();

    if (!$rootcats = $DB->get_records('question_categories', array('parent' => $rootcategory), 'sortorder, id', 'id,name')) {
        $response.= get_string('configwarningmonitor', 'block_userquiz_monitor');
        return;
    }

    foreach ($rootcats as $catid => $cat) {

        $rootcats[$catid]->cptA = 0; // Number of question type A.
        $rootcats[$catid]->cptC = 0; // Number of question type C.
        $rootcats[$catid]->cpt = 0; // Number of question type A or C.
        $rootcats[$catid]->goodA = 0; // Number of matched questions type A.
        $rootcats[$catid]->goodC = 0; // Number of matched questions type C.
        $rootcats[$catid]->good = 0; // Number of matched questions type A or C.
        $rootcats[$catid]->ratioA = 0; // Ratio type A.
        $rootcats[$catid]->ratioC = 0; // Ratio type C.
        $rootcats[$catid]->ratio = 0;
        $rootcats[$catid]->questiontypes = array();

        $catidarr = array($cat->id);
        $cattreelist = userquiz_monitor_get_cattreeids($cat->id, $catidarr);
        $cattreelist = implode("','", $catidarr);

        $select = " category IN ('$cattreelist') AND defaultmark = 1000 ";
        if ($DB->record_exists_select('question', $select, array())) {
            $rootcats[$catid]->questiontypes['C'] = 1;
        }
        $select = " category IN ('$cattreelist') AND defaultmark = 1 ";
        if ($DB->record_exists_select('question', $select, array())) {
            $rootcats[$catid]->questiontypes['A'] = 1;
        }
    }

    // Get user's attempts list.
    $sql  = "
        SELECT
            distinct(ua.id),
            ua.uniqueid,
            ua.timefinish
        FROM
            {quiz_attempts} ua
        WHERE
            ua.userid = ? AND
            ua.timefinish <> 0 AND
            ua.quiz = ?
        ORDER BY
            ua.timefinish ASC
    ";
    $userattempts = $DB->get_records_sql($sql, array($USER->id, $quizid));

    // Add time range limit.
    $userprefs = $DB->get_record('userquiz_monitor_prefs', array('userid' => $USER->id, 'blockid' => $blockid));
    $timerangefilterclause = '';

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

    if (!empty($userattempts)) {

        $i = 0;
        foreach ($userattempts as $userattempt) {
            if ($statesrs = get_all_user_records($userattempt->uniqueid, $USER->id, null, true)) {
                if ($statesrs->valid()) {
                    foreach ($staters as $state) {
                        $answeridstabtemp = explode(':', $state->answer);

                        if (!empty($answeridstabtemp[1])) {
                            if ($answer = $DB->get_record('question_answers', array('id' => $answeridstabtemp[1]))) {
                                // Get question informations.
                                $select = " qtype = 'multichoice' AND id = ? ";
                                $field = 'id, defaultmark, category';
                                $question = $DB->get_record_select('question', $select, array($answer->question), $fields);
                                $questioncategory = $question->category;
                                if ($parent = $DB->get_field('question_categories', 'parent', array('id' => $questioncategory))) {
                                    while (!in_array($parent, array_keys($rootcats))) {
                                        $parent = $DB->get_field('question_categories', 'parent', array('id' => $parent));
                                    }
                                }

                                $attempts[$state->uaid][$question->category]->timefinish = $userattempt->timefinish;
                                $attempts[$state->uaid][$parent]->timefinish = $userattempt->timefinish;
                                $attempts[$state->uaid][$rootcategory]->timefinish = $userattempt->timefinish;
    
                                $rootcats[$parent]->cpt++;
                                $attempts[$state->uaid][$question->category]->cpt = @$attempts[$state->uaid][$question->category]->cpt + 1;
                                $attempts[$state->uaid][$parent]->cpt = @$attempts[$state->uaid][$parent]->cpt + 1;
                                $attempts[$state->uaid][$rootcategory]->cpt = @$attempts[$state->uaid][$rootcategory]->cpt + 1;
                                $overall->cpt++;
                                if ($question->defaultmark == '1000') {
                                    $rootcats[$parent]->cptC++;
                                    $attempts[$state->uaid][$question->category]->cptC = @$attempts[$state->uaid][$question->category]->cptC + 1;
                                    $attempts[$state->uaid][$parent]->cptC = @$attempts[$state->uaid][$parent]->cptC + 1;
                                    $attempts[$state->uaid][$rootcategory]->cptC = @$attempts[$state->uaid][$rootcategory]->cptC + 1;
                                    $overall->cptC++;
                                    if ($state->grade == 1) {
                                        $rootcats[$parent]->goodC++;
                                        $attempts[$state->uaid][$question->category]->goodC = @$attempts[$state->uaid][$question->category]->goodC + 1;
                                        $attempts[$state->uaid][$parent]->goodC = @$attempts[$state->uaid][$parent]->goodC + 1;
                                        $attempts[$state->uaid][$rootcategory]->goodC = @$attempts[$state->uaid][$rootcategory]->goodC + 1;
                                        $overall->goodC++;
                                    }
                                } else {
                                    $rootcats[$parent]->cptA++;
                                    $attempts[$state->uaid][$question->category]->cptA = @$attempts[$state->uaid][$question->category]->cptA + 1;
                                    $attempts[$state->uaid][$parent]->cptA = @$attempts[$state->uaid][$parent]->cptA + 1;
                                    $attempts[$state->uaid][$rootcategory]->cptA = @$attempts[$state->uaid][$rootcategory]->cptA + 1;
                                    $overall->cptA++;
                                    if ($state->grade == 1) {
                                        $rootcats[$parent]->goodA++;
                                        $attempts[$state->uaid][$question->category]->goodA = @$attempts[$state->uaid][$question->category]->goodA + 1;
                                        $attempts[$state->uaid][$parent]->goodA = @$attempts[$state->uaid][$parent]->goodA + 1;
                                        $attempts[$state->uaid][$rootcategory]->goodA = @$attempts[$state->uaid][$rootcategory]->goodA + 1;
                                        $overall->goodA++;
                                    }
                                }
                                if ($state->grade == 1) {
                                    $rootcats[$parent]->good++;
                                    $attempts[$state->uaid][$question->category]->good = @$attempts[$state->uaid][$question->category]->good + 1;
                                    $attempts[$state->uaid][$parent]->good = @$attempts[$state->uaid][$parent]->good + 1;
                                    $attempts[$state->uaid][$rootcategory]->good = @$attempts[$state->uaid][$rootcategory]->good + 1;
                                    $overall->good++;
                                }
                            }
                        }
                        $i++;
                    }
                }
            }
            $statesrs->close();
        }
    } else {
        $errormsg = get_string('error2', 'block_userquiz_monitor');
    }

    // Build the stucture of the reporting.

    // Post compute ratios.

    $overall->ratioA = ($overall->cptA == 0) ? 0 : round(($overall->goodA / $overall->cptA ) * 100);
    $overall->ratioC = ($overall->cptC == 0) ? 0 : round(($overall->goodC / $overall->cptC ) * 100);
    $overall->ratio = ($overall->cpt == 0) ? 0 : round(($overall->good / $overall->cpt ) * 100);

    $maxratio = 0;
    foreach (array_keys($rootcats) as $catid) {
        $rootcats[$catid]->ratioC = ($rootcats[$catid]->cptC == 0) ? 0 : round(($rootcats[$catid]->goodC / $rootcats[$catid]->cptC ) * 100);
        $rootcats[$catid]->ratioA = ($rootcats[$catid]->cptA == 0) ? 0 : round(($rootcats[$catid]->goodA / $rootcats[$catid]->cptA ) * 100);
        $rootcats[$catid]->ratio = ($rootcats[$catid]->cpt == 0) ? 0 : round(($rootcats[$catid]->good / $rootcats[$catid]->cpt ) * 100);
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
                    $attemptdate =  date('j/m/Y', $userattempt->timefinish);
                    $results[] = array( "date" => $attemptdate, "success" => true);
                } else {
                    $attemptdate =  date('j/m/Y', $userattempt->timefinish);
                    $results[] = array( 'date' => $attemptdate, 'success' => false);
                }
            }
        }

        $params = array('userid' => $USER->id, 'quizid' => $quizid);
        $userattemptnum = 0 + $DB->get_field('qa_usernumattempts', 'maxattempts', $params);

        $data = array (
            'boxheight' => 65,
            'boxwidth' => 400,
            'maxattempts' => $userattemptnum,
            'results' => $results,
        ) ;

        $test_data = urlencode(json_encode($data));
        $attempts_graph = $renderer->attempts($data);
    }

    // Construct report tab.
    $formurl = new moodle_url('/blocks/userquiz_monitor/userpreset.php');
    $response .= '<form name="form" method="GET" action="'.$formurl.'">';
    $response .= '<input type="hidden" name="blockid" value="'.$block->instance->id.'">';

    $total = $renderer->total_progress($overall, $rootcategory);
    $totaldescstr = get_string('totaldescexam', 'block_userquiz_monitor');

    if (!empty($recordsgetuserattempts)) {
        // Format the data to construct the histogram.
        $histparams = calcul_hist($categories, $attempts);
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
            $pixurl = $OUTPUT->pix_url('graph', 'block_userquiz_monitor');
            $cat->buttons .= $this->action_link($popuplink, $title, $action, array(), $pixurl);
        } else {
            $title = get_string('hist', 'block_userquiz_monitor');
            $pixurl = $OUTPUT->pix_url('graph', 'block_userquiz_monitor');
            $cat->buttons .= '<span><img class="userquiz-cmd-icon" title="'.$title.'" src="'.$pixurl.'" /></span>';
        }

        if ($cpt == 0) {
            $catstr = get_string('categories', 'block_userquiz_monitor', @$block->config->trainingprogramname);
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

