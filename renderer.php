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

class block_userquiz_monitor_renderer extends plugin_renderer_base {

    protected $theblock;

    public function set_block($bi) {
        $this->theblock = $bi;
    }

    public function available_attempts($userid, $quizid, $maxdisplay = 0) {
        global $DB, $OUTPUT;

        $nousedattemptsstr = get_string('nousedattemptsstr', 'block_userquiz_monitor');
        $noavailableattemptsstr = get_string('noavailableattemptsstr', 'block_userquiz_monitor');
        $availablestr = get_string('available', 'block_userquiz_monitor');
        $attemptstr = get_string('attempt', 'block_userquiz_monitor');
        $stillavailablestr = get_string('stillavailable', 'block_userquiz_monitor');

        $str = '<div style="margin-top:5px" class="trans100" >';
        $str .= '<table width="100%" style="font-size:0.8em">';

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
                    $iconurl = $OUTPUT->pix_url('usedattempt', 'block_userquiz_monitor');
                    $str .= '<tr valign="top">';
                    $str .= '<td>'.$attemptsstr.'</td>';
                    $str .= '<td>'.$attemptdate.'</td>';
                    $str .= '<td><img src="'.$iconurl.'" /></td>';
                    $str .= '</tr>';
                } else {
                    if (!$printedellipse) {
                        $iconurl = $OUTPUT->pix_url('usedattempt', 'block_userquiz_monitor');
                        $str .= '<tr valign="top">';
                        $str .= '<td>...</td>';
                        $str .= '<td></td>';
                        $str .= '<td><img src="'.$iconurl.'" /></td>';
                        $str .= '</tr>';
                        $printedellipse = true;
                    }
                }
                $usedix--;
            } 
        } else {
            $usedattempts = array();
            $str .= '<tr valign="top">';
            $str .= '<td colspan="3" align="center" style="color:#ff0000">'.$nousedattemptsstr.'</td>';
            $str .= '</tr>';
        }

        if ($maxattempts = $DB->get_record('qa_usernumattempts_limits', array('userid' => $userid, 'quizid' => $quizid))) {
            if ($availableattempts = $maxattempts->maxattempts - count($usedattempts)) {
                $iconurl = $OUTPUT->pix_url('availableattempt', 'block_userquiz_monitor');
                $attemptsleft = $availableattempts;
                for ($i = 0; $i < min($maxdisplay, $availableattempts); $i++) {
                    // Display as many available as possible.
                    $iconurl = $OUTPUT->pix_url('availableattempt', 'block_userquiz_monitor');
                    $str .= '<tr valign="top">';
                    $str .= '<td>'.$attemptstr.'</td>';
                    $str .= '<td>'.$availablestr.'</td>';
                    $str .= '<td><img src="'.$iconurl.'" /></td>';
                    $str .= '</tr>';
                    $attemptsleft--;
                }
                if ($attemptsleft) {
                    // If we could not display all available.
                    $str .= '<tr valign="top">';
                    $str .= '<td colspan="2">'.$attemptsleft.' '.$stillavailablestr.'</td>';
                    $str .= '<td></td>';
                    $str .= '</tr>';
                }
            } else {
                $str .= '<tr valign="top">';
                $str .= '<td colspan="3" align="center" style="color:#ff0000\">'.$noavailableattemptsstr.'</td>';
                $str .= '</tr>';
            }
        }
        $str .= '</table>';
        $str .= '</div>';

        return $str;
    }

    public function errorline($msg) {
        global $OUTPUT;

        $str = '';

        $str .= '<tr>';
        $str .= '<td>';
        $str .= $OUTPUT->notification($msg);
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    public function total_progress($overall, $rootcategory) {
        global $OUTPUT;

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

        $progressbarA = $this->progress_bar_html_gd($rootcategory, $data);

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
            $progressbarC = $this->progress_bar_html_gd($rootcategory, $data);
        }

        $str = '<table class="tablemonitortotalprogress">';

        $str .= '<tr>';
        $str .= '<td style="width:67%;">';
        $str .= '</td>';
        $str .= '<td>';
        $str .= get_string('level', 'block_userquiz_monitor');
        $str .= '</td>';
        $str .= '<td>';
        $str .= get_string('ratio', 'block_userquiz_monitor');
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr>';
        $str .= '<td style="width:70%;">';
        $str .= '<div>';
        $str .= $progressbarA;
        $str .= '</div>';
        $str .= '</td>';
        $str .= '<td style="width:15%;">';
        $pixurl = $OUTPUT->pix_url('a', 'block_userquiz_monitor');
        $str .= '<img class="userquiz-monitor-total-icon" src="'.$pixurl.'" />';
        $str .= '</td>';
        $str .= '<td style="width:15%;">';
        $str .= '<h4>'.$overall->goodA.'/'.$overall->cptA.'</h4>';
        $str .= '</td>';
        $str .= '</tr>';

        if (!empty($this->theblock->config->dualserie)) {
            $str .= '<tr>';
            $str .= '<td style="width:70%;">';
            $str .= '<div>';
            $str .= $progressbarC;
            $str .= '</div>';
            $str .= '</td>';
            $str .= '<td style="width:15%;">';
            $pixurl = $OUTPUT->pix_url('c', 'block_userquiz_monitor');
            $str .= '<img class="userquiz-monitor-total-icon" src="'.$pixurl.' "/>';
            $str .= '</td>';
            $str .= '<td style="width:15%;">';
            $str .= '<h4>'.$overall->goodC.'/'.$overall->cptC.'</h4>';
            $str .= '</td>';
            $str .= '</tr>';
        }

        $str .= '</table>';
        return $str;
    }

    function category_results($cat) {
        global $OUTPUT;

        $str = '';

        $str .= '<tr>';
        $str .= '<td style="width:40%; text-align:left;">';
        $str .= '<div id="progressbarcontainer'.$cat->skin.$cat->id.'">';
        $str .= $cat->progressbar;
        $str .= '</div>';
        $str .= '</td>';
        $str .= '<td style="width:15%; text-align:center;">';
        $pixurl = $OUTPUT->pix_url(core_text::strtolower($cat->skin), 'block_userquiz_monitor');
        $str .= '<img class="userquiz-monitor-questiontype" src="'.$pixurl.'" />';
        $str .= '</td>';
        $str .= '<td style="width:15%; text-align:center;">';
        $good = 'good'.$cat->skin;
        $cpt = 'cpt'.$cat->skin;
        $str .= '<h4>'.$cat->$good.'/'.$cat->$cpt.'</h4>';
        $str .= '</td>';
        $str .= '</tr>';

        return $str;
    }

    function launch_button($quizid, $mode) {
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

    function exam_launch_gui($runlaunchform, $quizid, $accessorieslink, $totalexamstr, $total) {
        global $USER, $OUTPUT;

        $commenthist = get_string('commenthist', 'block_userquiz_monitor');

        $str = ' <div id="divtotal"><center>';

        $str .= '<table class="globalmonitor">';
        $str .= '<tr>';

        $str .= '<td valign="top" style="padding:5px;">';
        $str .= '<h1>'.get_string('runexam', 'block_userquiz_monitor').'</h1>';
        $str .= '<div class="trans100" style="text-align:center;">';
        $str .= $runlaunchform;
        $str .= '</div>';
        $str .= $this->available_attempts($USER->id, $quizid, 3);
        $str .= '</td>';

        if (!empty($this->theblock->config->examhidescoringinterface)) {
            $str .= '</tr>';
            $str .= '</table>';
            $str .= '</div>';
            return $str;
        }

        $str .= '<td valign="top" style="width:70%; padding:5px;">';
        $str .= '<h1>'.$totalexamstr.' '.$OUTPUT->help_icon('totalexam', 'block_userquiz_monitor', true).'</h1>';
        $str .= '<div class="trans100">';
        $str .= '<p>'.$commenthist.' '.$accessorieslink.'<p>';
        $str .= '<p>'.$total.'<p>'; 
        $str .= '</div>';
        $str .= '</td>';

        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</div>';

        return $str;
    }

    function category_detail_container() {

        $catdetailstr = get_string('categorydetail', 'block_userquiz_monitor', $this->theblock->config->trainingprogramname);

        $str = '<div class="tablemonitorcategorycontainer">';
        $str .= '<h1>'.$catdetailstr.'</h1>';
        $str .= '</div>';
        $str .= '<div id="displaysubcategories">';
        $str .= '</div>';

        return $str;
    }

    /**
     * Display the progress bar
     *
     */

    function progress_bar_html($id, $data) {

        $test_data = urlencode(json_encode($data));
        $data['id'] = $id;
        $progress_bar_graph = call_progress_bar_html($test_data, $data);
        return($progress_bar_graph);
    }

    function progress_bar_html_gd($id, $data) {

        $progress_bar_id = 'progress_bar'.$id;
        $progress_bar_name = 'progress_bar'.$id;

        $test_data = urlencode(json_encode($data));
        $data['id'] = $id;
        if ($data['type'] == 'local') {
            $params = array('barwidth' => $data['successrate'], 'stop' => $data['stop'], 'skin' => $data['skin']);
            $barurl = new moodle_url('/blocks/userquiz_monitor/generators/gd/gd_local_dyn.php', $params);
            $progress_bar_img = '<img id="'.$progress_bar_id.'" name="'.$progress_bar_name.'" src="'.$barurl.'" class="progress-bar-end" />';
        } else {
            $params = array('barwidth' => $data['successrate'], 'stop' => $data['stop'], 'skin' => $data['skin']);
            $barurl = new moodle_url('/blocks/userquiz_monitor/generators/gd/gd_total_dyn.php', $params);
            $progress_bar_img = '<img id="'.$progress_bar_id.'" name="'.$progress_bar_name.'" src="'.$barurl.'" class="progress-bar-full" />';
        }
        return $progress_bar_img;
    }

    /**
     * Display the histogram
     *
     */

    function histogram($data) {
        $test_data = urlencode(json_encode($data));
        $hist_graph = call_hist_chart($test_data, $data);
        return($hist_graph);
    }

    function attempts($data) {
        $test_data = urlencode(json_encode($data));
        $attempts_graph = call_attempts($test_data, $data);
        return($attempts_graph);
    }

    function category_monitor_container($options, $quizzeslist) {
        global $COURSE;

        $numberofquestionsstr = get_string('numberquestions', 'block_userquiz_monitor');
        $runteststr = get_string('runtest', 'block_userquiz_monitor');
        $runtraininghelpstr = get_string('runtraininghelp', 'block_userquiz_monitor');

        $str = '<table class="tablemonitorcategorycontainer">
                    <tr>
                        <td>
                            <p>'.$runtraininghelpstr.'</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                             '.$numberofquestionsstr.'
                            <select id="selectornbquestions" name="selectornbquestions" size="1">
                                '.$options.'
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                             <input type="hidden" name="mode" value="test"/>
                             <input type="hidden" name="courseid" value="'.$COURSE->id.'"/> 
                             <input type="hidden" name="quizzeslist" value="'.$quizzeslist.'"/>
                             <input type="submit" value="'.$runteststr.'" id="submit"/>
                         </td>
                     </tr>
                </table>';

        return $str;
    }

    function empty_category_monitor_container() {
        global $COURSE;

        $runteststr = get_string('runtest', 'block_userquiz_monitor');
        $runtraininghelpstr = get_string('runtraininghelp', 'block_userquiz_monitor');

        $str = '<table class="tablemonitorcategorycontainer" >
                    <tr>
                        <td>
                            <p>'.$runtraininghelpstr.'</p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="hidden" name="mode" value="test"/> 
                            <input type="hidden" name="courseid" value="'.$COURSE->id.'"/> 
                            <input type="submit" value="'.$runteststr.'" id="submit" disabled />
                        </td>
                    </tr>
                </table>';

        return $str;
    }

    /**
     * Displaying the subcategories of a category
     *
     */
    function subcategories($courseid, $rootcategory, $categoryid, $quizzeslist, $positionheight, $mode, &$block) {
        global $USER, $CFG, $DB, $OUTPUT;

        $blockid = $block->instance->id;

        // Init variables
        $response = '';

        $quizzeslist = stripslashes($quizzeslist);
        $quizzeslist = str_replace(',', "','", $quizzeslist);

        if ($subcats = $DB->get_records('question_categories', array('parent' => $categoryid), 'sortorder, id', 'id, name,parent' )) {

            // prepare aggregators
            foreach ($subcats as $subcatid => $subcat) {
                $subcats[$subcatid]->cptA = 0; // Number of question type A
                $subcats[$subcatid]->cptC = 0; // Number of question type C
                $subcats[$subcatid]->cpt = 0; // Number of question type A or C
                $subcats[$subcatid]->goodA = 0; // Number of matched questions type A
                $subcats[$subcatid]->goodC = 0; // Number of matched questions type C
                $subcats[$subcatid]->good = 0; // Number of matched questions type A or C
                $subcats[$subcatid]->ratioA = 0; // Ratio type A
                $subcats[$subcatid]->ratioC = 0; // Ratio type C
                $subcats[$subcatid]->ratio = 0; // 
                $subcats[$subcatid]->questiontypes = array();

                if ($DB->record_exists_select('question', " category = ? AND defaultmark = 1000 AND qtype != 'random' AND qtype != 'randomconstrained' ",  array($subcat->id))) {
                    $subcats[$subcatid]->questiontypes['C'] = 1;
                }
                if ($DB->record_exists_select('question', " category = ? AND defaultmark = 1 AND qtype != 'random' AND qtype != 'randomconstrained' ", array($subcat->id))) {
                    $subcats[$subcatid]->questiontypes['A'] = 1;
                }
            }

            $subcatids = array_keys($subcats);
            $subcatlist = implode(",", $subcatids);
            $subcategoriesids = implode(",", $subcatids);

            // Add time range limit.
            $userprefs = $DB->get_record('userquiz_monitor_prefs', array('userid' => $USER->id), 'blockid', $blockid);
            $timerangefilterclause = '';
            if (@$userprefs->resultsdepth > 0) {
                $limit = time() - ($userprefs->resultsdepth * 7 * DAYSECS);
                $timerangefilterclause = " AND timestart >= $limit ";
            }

            $catstates = null;
            if (!empty($categoryid) && !empty($quizzeslist)) {

                // Get states for user
                $sql = "
                    SELECT 
                        qasd.id,
                        qasd.value as answer,
                        qas.fraction as grade
                    FROM
                        {question_attempt_step_data} qasd,
                        {question_attempt_steps} qas,
                        {question_attempts} qa,
                        {quiz_attempts} qua,
                        {quiz_slots} qs,
                        {question} q
                    WHERE
                        qasd.name = 'answer' AND
                        qas.userid = ? AND
                        qasd.attemptstepid = qas.id AND
                        qas.questionattemptid = qa.id AND
                        qa.questionusageid = qua.uniqueid AND
                        qa.slot = qs.id AND
                        qs.questionid = q.id AND
                        qs.quizid IN ('$quizzeslist') AND
                        qas.timecreated <> 0
                        $timerangefilterclause
                ";
                // echo $sql;
                $catstates = $DB->get_records_sql($sql, array($USER->id));
            }

            if (!empty($catstates)) {

                // Get answer for each questions
                $maxratio = 0;
                $i = 0;
                foreach ($catstates as $state) {

                    $answeridstabtemp = explode(':', $state->answer);

                    if (!empty($answeridstabtemp[1])) {
                        if ($answer = $DB->get_record('question_answers', array('id' => $answeridstabtemp[1]))) {
                            // Get question informations
                            $question = $DB->get_record_select('question', " qtype != 'random' AND qtype != 'randomconstrained' AND id = ? ", array($answer->question), 'id, parent, defaultmark, category');
                            if (!in_array($question->category, array_keys($subcats))) {
                                continue;
                            }
                            // echo "$i($state->id, $state->uaid > $question->id,$question->category)=$question->defaultmark<br/>";
                            $subcats[$question->category]->cpt++;
                            if (round($question->defaultmark) == 1000) {
                                $subcats[$question->category]->cptC++;
                                if ($state->grade == 1) {
                                    $subcats[$question->category]->goodC++;
                                }
                            } else {
                                $subcats[$question->category]->cptA++;
                                if ($state->grade == 1) {
                                    $subcats[$question->category]->goodA++;
                                }
                            }
                            if ($state->grade == 1) {
                                $subcats[$question->category]->good++;
                            }
                        }
                    }
                    $i++;
                }
            }

            // post compute ratios

            $maxratio = 0;
            foreach (array_keys($subcats) as $subcatid) {
                $subcats[$subcatid]->ratioC = ($subcats[$subcatid]->cptC == 0) ? 0 : round(($subcats[$subcatid]->goodC / $subcats[$subcatid]->cptC )*100) ;
                $subcats[$subcatid]->ratioA = ($subcats[$subcatid]->cptA == 0) ? 0 : round(($subcats[$subcatid]->goodA / $subcats[$subcatid]->cptA )*100) ;
                $subcats[$subcatid]->ratio = ($subcats[$subcatid]->cpt == 0) ? 0 : round(($subcats[$subcatid]->good / $subcats[$subcatid]->cpt )*100) ;
                if ($maxratio < $subcats[$subcatid]->ratio) {
                    $maxratio = $subcats[$subcatid]->ratio;
                }
            }
    
            if($maxratio == 0) {
                $maxratio = 1;
            }

            // generate output

            $cpt = 0;

            foreach ($subcats as $subcat) {

                if ($cpt == 0) {
                    // Define height position of the first block on the left part monitor
                    if ($positionheight != 0) {
                        $response .= '<div id="divpr" style="height:'.$positionheight.'px;"></div>'; 
                    }
                    $cancel = '';
                    if ($mode == 'training') {
                        $cancel .= '<img class="userquiz-icon" src="'.$OUTPUT->pix_url('cancel', 'block_userquiz_monitor').'" onclick="closepr()" />';
                    } else {
                        $cancel .= '<img class="userquiz-icon" src="'.$OUTPUT->pix_url('cancel', 'block_userquiz_monitor').'" onclick="closeprexam()" />';
                    }

                    $cb = '';
                    $quizzesliststring = $quizzeslist;

                    if ($mode == "training") {
                        $cb = "
                            <input type=\"checkbox\" 
                               name=\"checkall_pr\" 
                               id=\"checkall_pr\" 
                               onclick=\"updateselectorpr('{$courseid}','{$rootcategory}', '{$subcategoriesids}', 'all', '{$quizzesliststring}') \" 
                               style=\"padding-left:2px;\" /> ".get_string('selectallcb', 'block_userquiz_monitor');
                    }

                    $response.=    '
                        <div class="trans100" id="divpr" >
                            <table class="tablemonitorcategorycontainer">
                                <tr>
                                    <td style="width:70%;" colspan="2">
                                        '.$cb.' <span style="float:right;">'.$cancel.'</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    ';
                }

                $cb = '';
                if ($mode == 'training') {
                    $cb = "
                        <input type=\"checkbox\" 
                          name=\"cbpr{$subcat->id}\" 
                          id=\"cbpr{$subcat->id}\" 
                          onclick=\"updateselectorpr('{$courseid}', '{$rootcategory}', '{$subcategoriesids}', 'none', '{$quizzesliststring}')\" 
                          style=\"padding-left:2px;\" />
                    ";
                }
    
                $response.= '
                    <div class="trans100" id="divpr'.$subcat->id.'" >
                    <table class="tablemonitorcategorycontainer">
                        <tr>
                            <td  colspan="3">
                                <div class="categoryname" style="width:100%;">
                                    '.$subcat->name.'
                                </div>
                            </td>
                        </tr>
                        <tr>
                             <td style="width:70%; background-color:#D9E1D6;">
                                '.$cb.'
                             </td>
                        ';
                if (!empty($block->config->dualserie)) {
                    $response .= '<td style="width : 15%; background-color:#D9E1D6; text-align:center;font-size:0.8em; color:#658EA0;">
                                    '.get_string('level1', 'block_userquiz_monitor').'
                                 </td>';
                }
                $response .= '<td style="width : 15%; background-color:#D9E1D6; text-align:center;font-size:0.8em; color:#658EA0;">
                                '.get_string('ratio1', 'block_userquiz_monitor').'
                             </td>
                        </tr>
                ';
    
                $graphwidth = round(($subcat->ratio * 100)/$maxratio);
    
                if ($graphwidth < 1) {
                    $graphwidth = 1;
                }

                ksort($subcat->questiontypes);
                foreach (array_keys($subcat->questiontypes) as $questiontype) {

                    if ($questiontype == 'A') {
                        $data = array ( 
                            'boxheight' => 50,
                            'boxwidth' => 160,
                            'skin' => 'A',
                            'type' => 'local',
                            'graphwidth' => $graphwidth,
                            'stop' => $block->config->rateAserie,
                            'successrate' => $subcat->ratioA,
                        );
                        $progressbar = $this->progress_bar_html_gd($subcat->id, $data);

                        $response.=  '
                            <tr>
                                <td class="userquiz-cat-progress">
                                         '.$progressbar.'
                                 </td>';
                        if (!empty($block->config->dualserie)) {
                             $response .= '<td class="userquiz-cat-total">
                                    <img class="userquiz-cat-image" src="'.$OUTPUT->pix_url('a', 'block_userquiz_monitor').'" />
                                </td>';
                        }
                        $response .= '<td class="userquiz-cat-total">
                                    <h4>'.$subcat->goodA.'/'.$subcat->cptA.'</h4>
                                </td>
                             </tr>
                         ';
                    }
                    if ($block->config->dualserie && ($questiontype == 'C')) {
                        $data = array ( 
                            'boxheight' => 50,
                            'boxwidth' => 160,
                            'skin' => 'C',
                            'type' => 'local',
                            'graphwidth' => $graphwidth,
                            'stop' => $block->config->rateCserie,
                            'successrate' => $subcat->ratioC,
                        );
                        $progressbar = $this->progress_bar_html_gd($subcat->id, $data);
    
                        $response .= '
                            <tr>
                                <td class="userquiz-cat-progress">
                                    '.$progressbar.'
                                </td>
                                <td class="userquiz-cat-total">
                                    <img class="userquiz-cat-image" src="'.$OUTPUT->pix_url('c', 'block_userquiz_monitor').'" />
                                </td>
                                <td style="userquiz-cat-total">
                                    <h4>'.$subcat->goodC.'/'.$subcat->cptC.'</h4>
                                </td>
                            </tr>
                        ';
                    }
                }
                $response .= '</table></div>';
                $cpt++;
            }
            echo($response);
        }
    }

    function global_monitor($total, $selector) {
        global $OUTPUT;

        $totalstr = get_string('total', 'block_userquiz_monitor');

        $str = '';
        $str .= '<table class="userquiz-global-monitor">';
        $str .= '<tr>';
        $str .= '<td>';
        $str .= '<h1>'.get_string('runtest', 'block_userquiz_monitor').' '.$OUTPUT->help_icon('launch', 'block_userquiz_monitor', true).'</h1>';
        $str .= '</td>';
        $str .= '<td>';
        $str .= '<h1>'.$totalstr.' '.$OUTPUT->help_icon('total', 'block_userquiz_monitor', true).'</h1>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '<tr valign="top">';
        $str .= '<td>';
        $str .= '<div class="trans100">';
        $str .= '<div id="selectorcontainer" style="width:100%; font-size : 120%;">';
        $str .= $selector;
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</td>';
        $str .= '<td  valign="top" class="userquiz-cat-progress">';
        $str .= '<div class="trans100">';
        $str .= $total;
        $str .= '</div>';
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';

        return $str;
    }

    function filter_state($domain, $blockid) {
        global $USER, $CFG, $COURSE, $DB, $OUTPUT;

        $lang = substr(current_language(), 0, 2);

        $context = context_course::instance($COURSE->id);

        $sql = "
            SELECT
                MIN(timestart) as 'firstenrol'
            FROM
                {user_enrolments} ue,
                {enrol} e
            WHERE
                ue.enrolid = e.id AND
                e.courseid = ? AND
                ue.userid = ? AND
                e.status = 0 AND
                ((timeend IS NULL) OR (timeend > ?))
        ";
        $firstactiveenrol = $DB->get_field_sql($sql, array($COURSE->id, $USER->id, time()));

        if ($firstactiveenrol) {
            $absolutestart = max($COURSE->startdate, $firstactiveenrol);
        } else {
            $absolutestart = $COURSE->startdate;
        }
    
        if ($domain == 'exams') {
            if ($prefs = $DB->get_record('userquiz_monitor_prefs', array('userid' => $USER->id, 'blockid' => $blockid))) {
                if ($prefs->examsdepth> 0) {
                    $filterinfo = get_string('examsfilterinfo', 'block_userquiz_monitor', $prefs->examsdepth);
                    $pixurl = $OUTPUT->pix_url('examfilter_'.$prefs->examsdepth.'_'.$lang, 'block_userquiz_monitor');
                    $pix = '<img class="userquiz-monitor-exam-pix" src="'.$pixurl.'" title="'.$filterinfo.'" />';
                    return get_string('filtering', 'block_userquiz_monitor').': '.$pix;
                }
            }
            $filterinfo = get_string('allexamsfilterinfo', 'block_userquiz_monitor');
            $pixurl = $OUTPUT->pix_url('examfilter_0_'.$lang, 'block_userquiz_monitor');
            $pix = '<img class="userquiz-monitor-exam-pix" src="'.$pixurl.'" title="'.$filterinfo.'" />';
            return get_string('filtering', 'block_userquiz_monitor').': '.$pix;
        } else {
            $dates = new StdClass;
            if ($prefs = $DB->get_record('userquiz_monitor_prefs', array('userid' => $USER->id, 'blockid' => $blockid))) {
                $dates->from = userdate((@$prefs->resultsdepth == 0) ? $absolutestart : max(time() - @$prefs->resultsdepth * 7 * DAYSECS, $absolutestart));
                $dates->to = userdate(time());
                $filterinfo = get_string('filterinfo', 'block_userquiz_monitor', $dates);
                $pixurl = $OUTPUT->pix_url('filter_'.$prefs->resultsdepth.'_'.$lang, 'block_userquiz_monitor');
                $pix = '<img class="userquiz-monitor-exam-pix" src="'.$pixurl.'" title="'.$filterinfo.'" />';
                return get_string('filtering', 'block_userquiz_monitor').': '.$pix;
            }
            $dates->from = userdate($absolutestart) ;
            $dates->to = userdate(time());
            $filterinfo = get_string('filterinfo', 'block_userquiz_monitor', $dates);
            $pixurl = $OUTPUT->pix_url('filter_0_'.$lang, 'block_userquiz_monitor');
            $pix = '<img class="userquiz-monitor-exam-pix" src="'.$pixurl.'" title="'.$filterinfo.'" />';
            return get_string('filtering', 'block_userquiz_monitor').': '.$pix;
        }
    }

    function tabs($theblock) {
        global $SESSION, $COURSE;

        // Ensures context conservation in userquiz_monitor.
        $selectedview = optional_param('selectedview', @$SESSION->userquizview, PARAM_TEXT);
        if (empty($SESSION->userquizview) ||
                (!@$theblock->config->trainingenabled && $SESSION->userquizview == 'training') ||
                        (!@$theblock->config->examenabled && $SESSION->userquizview == 'examination')  ) {
            if (!empty($theblock->config->trainingenabled)) {
                $SESSION->userquizview = 'training';
            } else if (!empty($theblock->config->examenabled)) {
                $SESSION->userquizview = 'examination';
            } else if($selectedview != 'preferences') {
                if (!empty($theblock->config->informationpageid) && !isediting()){
                    $params = array('id' => $COURSE->id, 'page' => $theblock->config->informationpageid);
                    redirect(new moodle_url('/course/view.php', $params));
                }
            }
        }
        $selectedview = $SESSION->userquizview = $selectedview;

        if (!empty($theblock->config->informationpageid)) {
            // page deals with the page format
            $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'page' => $theblock->config->informationpageid));
            $rows[0][] = new tabobject('information', $taburl, get_string('menuinformation', 'block_userquiz_monitor'));
        }

        // $rows[0][] = new tabobject('schedule', "view.php?id=".$COURSE->id."&selectedview=schedule", get_string('menuamfref', 'block_userquiz_monitor', $this->config->trainingprogramname));
        if (!empty($theblock->config->trainingenabled)) {
            $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'selectedview' => 'training'));
            $rows[0][] = new tabobject('training', $taburl, get_string('menutest', 'block_userquiz_monitor'));
        }
        if (!empty($theblock->config->examenabled)) {
            $examtab = get_string('menuexamination', 'block_userquiz_monitor');
            if (!empty($theblock->config->examtab)) {
                $examtab = $theblock->config->examtab;
            }
            $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'selectedview' => 'examination'));
            $rows[0][] = new tabobject('examination', $taburl, $examtab);
        }
        $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'selectedview' => 'preferences'));
        $rows[0][] = new tabobject('preferences', $taburl, get_string('menupreferences', 'block_userquiz_monitor'));

        return print_tabs($rows, $selectedview, null, null, true);
    }

    /**
     * Renders the full total block. 
     * @param array $components an array of $rendered subcomponents as strings
     * @param array $data scalar data to render as valriable inputs
     * @param int $rootcategory
     * @param string $list of involved quizzes
     */
    public function total($components, $data, $rootcategory, $quizzeslist) {
        global $USER, $OUTPUT, $COURSE;

        $commenthist = get_string('commenthist', 'block_userquiz_monitor');
        $totaldescstr = get_string('totaldesc', 'block_userquiz_monitor');

        $str = '';

        $str .= '<table style="padding:5px;">';
        $str .= '<tr>';
        $str .= '<td colspan="3">';
        $str .= '<p>'.$totaldescstr.'</p>';
        $str .= '<p>'.$commenthist.''.$components['accessorieslink'].'</p>';

        if (has_capability('moodle/site:config', context_system::instance(), @$USER->realuser)) {
            $str .= '<p>'.get_string('adminresethist', 'block_userquiz_monitor');
            $jshandler = 'resettraining(\''.$COURSE->id.'\', \''.$USER->id.'\', \''.urlencode($quizzeslist).'\')';
            $str .= '<input type="button" value="'.get_string('reset', 'block_userquiz_monitor').'" id="" onclick="'.$jshandler.'" /></p>';
        }

        $str .= '</td></tr>';

        $str .= '<tr>';
        $str .= '<td style="width:67%;"></td>'; // Blanck cell.
        $notenum = 1;
        if (!empty($data['dualserie'])) {
            $str .= '<td valign="bottom" class="progressbarcaption progressbarlabel">';
            $str .= get_string('level', 'block_userquiz_monitor', $notenum);
            $str .= '</td>';
            $notenum++;
        }
        $str .= '<td valign="bottom" class="progressbarcaption progressbarlabel">';
        $str .= get_string('ratio', 'block_userquiz_monitor', $notenum);
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr>';
        $str .= '<td class="userquiz-cat-progress">';
        $str .= $components['progressbarA'];
        $str .= '</td>';

        if (!empty($data->dualserie)) {
            $str .= '<td class="progressbarlabel">';
            $str .= '<img class="userquiz-cat-image" src="'.$OUTPUT->pix_url('a', 'block_userquiz_monitor').'" />';
            $str .= '</td>';
        }

        $str .= '<td class="progressbarlabel">';
        $str .= '<h4>';
        $str .= $data['goodA'].'/'.$data['cptA'];
        $str .= '</h4>';
        $str .= '</td>';
        $str .= '</tr>';

        if (!empty($data->dualserie)) {
            $str .= '<tr>';
            $str .= '<td class="userquiz-cat-progress">';
            $str .= $components['progressbarC'];
            $str .= '</td>';
            $str .= '<td class="progressbarlabel">';
            $str .= '<img class="userquiz-cat-image" src="'.$OUTPUT->pix_url('c', 'block_userquiz_monitor').' "/>';
            $str .= '</td>';
            $str .= '<td class="progressbarlabel">';
            $str .= '<h4>';
            $str .= $data['goodC'].'/'.$data['cptC'];
            $str .= '</h4>';
            $str .= '</td>';
            $str .= '</tr>';
        }

        $str .= '</table>';

        return $str;
    }

    public function program_headline($programname, $jshandler) {

        $catstr = get_string('categories', 'block_userquiz_monitor', $programname);
        $selectallcbstr = get_string('selectallcb', 'block_userquiz_monitor');

        $str = '';

        $str .= '<div id="userquiz-monitor-program-headline">';
        $str .= '<table class="tablemonitorcategorycontainer">';
        $str .= '<tr height="17">';
        $str .= '<td><h1>'.$catstr.'</h1></td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</div>';

        if (empty($jshandler)) {
            return $str;
        }

        $str .= '<div class="trans100">';
        $str .= '<table class="tablemonitorcategorycontainer">';
        $str .= '<tr>';
        $str .= '<td style="width:59%;">';
        $str .= '<input type="checkbox"
                        name="checkall_pl"
                        id="checkall_pl"
                        style="padding-left:2px;"
                        onclick="'.$jshandler.'" />';
        $str .= $selectallcbstr;
        $str .= '</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</div>';

        return $str;
    }

    public function category_result($cat) {
        global $OUTPUT;

        $seesubsstr = get_string('more', 'block_userquiz_monitor');

        $str = '';

        $str .= '<div class="trans100" id="divpl'.$cat->id.'">';
        $str .= '<table class="tablemonitorcategorycontainer">';
 
        $str .= '<tr>';
        $str .= '<td colspan="5">';
        $str .= '<div class="categoryname" style="width:100%;">';
        $str .= $cat->name;
        $str .= '</div>';
        $str .= '</td>';
        $str .= '</tr>';
 
        $str .= '<tr>';
        $str .= '<td class="userquiz-monitor-category">';
        $str .= '<input type="checkbox" 
                        name="cb_pl'.$cat->id.'" 
                        id="cbpl'.$cat->id.'" 
                        onclick="'.$cat->jshandler1.'" 
                        style="padding-left:2px;" />';
        $str .= $cat->accessorieslink;
        $str .= '<input type="hidden" name="h_cb_pl'.$cat->id.'" value="h_cb_pl'.$cat->id.'"/>';
        $str .= '</td>';

        if (!empty($this->theblock->config->dualserie)) {
            $level1str = get_string('level1', 'block_userquiz_monitor');
            $str .= '<td class="userquiz-monitor-category level">'.$level1str.'</td>';
        }
        $str .= '<td class="userquiz-monitor-category ratio">';
        $str .= get_string('ratio1', 'block_userquiz_monitor');
        $str .= '</td>';
        $str .= '<td style="width:25%; background-color:#D9E1D6;">';
        $pixurl = $OUTPUT->pix_url('detail', 'block_userquiz_monitor');
        $str .= '<img class="userquiz-monitor-cat-detail" title="'.$seesubsstr.'" src="'.$pixurl.'" onclick="'.$cat->jshandler2.'"/>';
        $str .= '</td>';
        $str .= '</tr>';

        if (!empty($cat->questiontypes)) {

            $keys = array_keys($cat->questiontypes);
            foreach ($keys as $questiontype) {

                if ($questiontype == 'A') {

                    $str .= '<tr>';

                    $str .= '<td class="progressbar">';
                    $str .= '<div id="progressbarcontainerC'.$cat->id.'">';
                    $str .= $cat->progressbarA;
                    $str .= '</div>';
                    $str .= '</td>';
                    if (!empty($this->theblock->config->dualserie)) {
                        $str .= '<td class="progressbarlabel">';
                        $pixurl = $OUTPUT->pix_url('a', 'block_userquiz_monitor');
                        $str .= '<img class="userquiz-monitor->questiontype" src="'.$pixurl.'"/>';
                        $str .= '</td>';
                    }
                    $str .= '<td class="progressbarlabel">';
                    $str .= '<h4>'.$cat->goodA.'/'.$cat->cptA.'</h4>';
                    $str .= '</td>';

                    $str .= '</tr>';
                }

                if ($this->theblock->config->dualserie && ($questiontype == 'C')) {

                    $str .= '<tr>';
                    $str .= '<td class="progressbar">';
                    $str .= '<div id="progressbarcontainerC'.$cat->id.'">';
                    $str .= $cat->progressbarC;
                    $str .= '</div>';
                    $str .= '</td>';

                    $str .= '<td class="progressbarlabel">';
                    $pixurl = $OUTPUT->pix_url('c', 'block_userquiz_monitor');
                    $str .= '<img class="userquiz-monitor->questiontype" src="'.$pixurl.'" />';
                    $str .= '</td>';
                    $str .= '<td class="progressbarlabel">';
                    $str .= '<h4>'.$cat->goodC.'/'.$cat->cptC.'</h4>';
                    $str .= '</td>';
                    $str .= '</tr>';
                }
            }
        }

        $str .= '</table></div>';

        return $str;
    }

    public function exam_main_category($cat, $jshandler) {
        global $OUTPUT;

        $seesubsstr = get_string('more', 'block_userquiz_monitor', $cat->name);

        $str = '';

        $str .= '<div class="trans100" id="divpl'.$cat->id.'">';
        $str .= '<table class="tablemonitorcategorycontainer">';

        $str .= '<tr>';
        $str .= '<td colspan="4" style="width:100%;">';
        $str .= '<div class="categoryname" style="width:100%;">';
        $str .= $cat->name;
        $str .= '</div>';
        $str .= '</td>';
        $str .= '</tr>';

        $str .= '<tr>';
        $str .= '<td class="userquiz-monitor-bg">';
        $str .= $cat->buttons;
        $str .= '</td>';
        $str .= '<td class="userquiz-monitor-bg" style="font-size:0.8em;text-align:center">';
        $str .= get_string('level1', 'block_userquiz_monitor');
        $str .= '</td>';
        $str .= '<td class="userquiz-monitor-bg" style="font-size:0.8em; text-align:center">';
        $str .= get_string('ratio1', 'block_userquiz_monitor');
        $str .= '</td>';
        $str .= '<td class="userquiz-monitor-bg" style="text-align:center;">';
        $str .= '<span style="float:right">';
        $pixurl = $OUTPUT->pix_url('detail', 'block_userquiz_monitor');
        $str .= '<img title="'.$seesubsstr.'" src="'.$pixurl.'" onclick="'.$jshandler.'" />';
        $str .= '</span>';
        $str .= '</td>';
        $str .= '</tr>';

        if (!empty($cat->questiontypes)) {
            $keys = array_keys($cat->questiontypes);

            foreach ($keys as $questiontype) {
                if ($questiontype == 'A') {
                    $cat->skin = 'A';
                    $cat->progressbar = $this->progress_bar_html_gd($cat->id, $cat->dataA);
                    $str .= $this->category_results($cat);
                }

                if ($this->theblock->config->dualserie && ($questiontype == 'C')) {
                    $cat->skin = 'C';
                    $cat->progressbar = $this->progress_bar_html_gd($cat->id, $cat->dataC);
                    $str .= $this->category_results($cat);
                }
            }
        }

        $str .= '</table></div>';

        return $str;
    }
}