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
defined('MOODLE_INTERNAL') || die();

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
        $str .= $progressbara;
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
            $str .= $progressbarc;
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

    public function category_results($cat) {
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

    public function subcat_container() {

        $catdetailstr = get_string('categorydetail', 'block_userquiz_monitor', $this->theblock->config->trainingprogramname);

        $str = '';

        $str .= '<div>';
        $str .= '<table class="tablemonitorcategorycontainer">';
        $str .= '<tr height="17">';
        $str .= '<td><h1>'.$catdetailstr.'</h1></td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</div>';
        $str .= '<div id="partright"></div>';

        return $str;
    }

    public function exam_launch_gui($runlaunchform, $quizid, $accessorieslink, $totalexamstr, $total) {
        global $USER, $OUTPUT;

        $commenthist = get_string('commenthist', 'block_userquiz_monitor');

        $str = '<div id="divtotal"><center>';

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
        $str .= '<h1>'.$totalexamstr.' '.$OUTPUT->help_icon('totalexam', 'block_userquiz_monitor', false).'</h1>';
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

    public function category_detail_container() {

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
     */
    public function progress_bar_html($id, $data) {

        $testdata = urlencode(json_encode($data));
        $data['id'] = $id;
        $progressbargraph = call_progress_bar_html($testdata, $data);
        return($progressbargraph);
    }

    public function progress_bar_html_gd($id, $data) {

        $progressbarid = 'progress_bar'.$id;
        $progressbarname = 'progress_bar'.$id;

        $test_data = urlencode(json_encode($data));
        $data['id'] = $id;
        if ($data['type'] == 'local') {
            $params = array('barwidth' => $data['successrate'], 'stop' => $data['stop'], 'skin' => $data['skin']);
            $barurl = new moodle_url('/blocks/userquiz_monitor/generators/gd/gd_local_dyn.php', $params);
            $progressbarimg = '<img id="'.$progressbarid.'" name="'.$progressbarname.'" src="'.$barurl.'" class="progress-bar-end" />';
        } else {
            $params = array('barwidth' => $data['successrate'], 'stop' => $data['stop'], 'skin' => $data['skin']);
            $barurl = new moodle_url('/blocks/userquiz_monitor/generators/gd/gd_total_dyn.php', $params);
            $progressbarimg = '<img id="'.$progressbarid.'" name="'.$progressbarname.'" src="'.$barurl.'" class="progress-bar-full" />';
        }
        return $progressbarimg;
    }

    public function progress_bar_html_jqw($id, $data) {
        global $PAGE;

        $jqrenderer = $PAGE->get_renderer('local_vflibs');

        /*
         * @param array $properties array with ('width', 'height', 'desc', 'barsize', 'tooltip') keys
         * @param array $ranges an array of range objects having ('start', 'end', 'color', 'opacity') keys
         * @param object $pointer an object with ('value', 'label', 'size', 'color') keys
         * @param object $target an object with ('value', 'label', 'size', 'color') keys
         * @param object $ticks an object with ('position', 'interval', 'size') keys
         */

        $properties['id'] = $id.$data['skin'];
        $properties['ticklabelformat'] = 'd';
        if ($data['type'] == 'local') {
            $properties['height'] = 60;
            $properties['width'] = 270;
            $properties['barsize'] = 50;
        } else {
            $properties['height'] = 150;
            $properties['width'] = 340;
            $properties['barsize'] = 40;
        }

        $pointer = new StdClass;
        $pointer->value = $data['successrate'];
        $pointer->color = ($data['skin'] == 'A') ? '#800000' : '#0000C0'; // TODO Parametrize.
        $pointer->label = get_string('meanscore', 'block_userquiz_monitor');
        $pointer->size = 30;
 
        $target = new StdClass();
        $target->value = $data['stop'];
        $target->color = ($data['skin'] == 'A') ? '#800000' : '#0000C0'; // TODO Parametrize.
        $target->size = '4';
        $target->label = get_string('target', 'block_userquiz_monitor');

        $ticks = new StdClass;
        $ticks->position = 'both';
        $ticks->interval = 10;
        $ticks->size = 10;

        $name = '';
        $ranges = null;

        return $jqrenderer->jqw_bulletchart($name, $properties, $ranges, $pointer, $target, $ticks);
    }

    /**
     * Display the histogram
     */
    public function histogram($data) {
        $testdata = urlencode(json_encode($data));
        $histgraph = call_hist_chart($testdata, $data);
        return($histgraph);
    }

    public function attempts($data) {
        $testdata = urlencode(json_encode($data));
        $attemptsgraph = call_attempts($testdata, $data);
        return($attemptsgraph);
    }

    public function category_monitor_container($options, $quizzeslist) {
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

    public function empty_category_monitor_container() {
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
     */
    public function subcategories($courseid, $rootcategory, $categoryid, $quizzeslist, $positionheight, $mode) {
        global $USER, $DB, $OUTPUT;

        $blockid = $this->theblock->instance->id;

        // Init variables.
        $str = '';

        $quizzeslist = stripslashes($quizzeslist);
        $quizzeslist = str_replace(',', "','", $quizzeslist);

        $fields = 'id, name, parent';
        if ($subcats = $DB->get_records('question_categories', array('parent' => $categoryid), 'sortorder, id', $fields )) {

            // Prepare aggregators.
            foreach ($subcats as $subcatid => $subcat) {
                $subcats[$subcatid]->cptA = 0; // Number of question type A.
                $subcats[$subcatid]->cptC = 0; // Number of question type C.
                $subcats[$subcatid]->cpt = 0; // Number of question type A or C.
                $subcats[$subcatid]->goodA = 0; // Number of matched questions type A.
                $subcats[$subcatid]->goodC = 0; // Number of matched questions type C.
                $subcats[$subcatid]->good = 0; // Number of matched questions type A or C.
                $subcats[$subcatid]->ratioA = 0; // Ratio type A.
                $subcats[$subcatid]->ratioC = 0; // Ratio type C.
                $subcats[$subcatid]->ratio = 0; //
                $subcats[$subcatid]->questiontypes = array();

                $select = '
                    category = ? AND
                    defaultmark = 1000 AND
                    qtype != "random" AND
                    qtype != "randomconstrained"
                ';
                if ($DB->record_exists_select('question', $select,  array($subcat->id))) {
                    $subcats[$subcatid]->questiontypes['C'] = 1;
                }

                $select = '
                    category = ? AND
                    defaultmark = 1 AND
                    qtype != "random" AND
                    qtype != "randomconstrained"
                ';
                if ($DB->record_exists_select('question', $select, array($subcat->id))) {
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

                // Get states for user.
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

                $catstates = $DB->get_records_sql($sql, array($USER->id));
            }

            if (!empty($catstates)) {

                // Get answer for each questions.
                $maxratio = 0;
                $i = 0;
                foreach ($catstates as $state) {

                    $answeridstabtemp = explode(':', $state->answer);

                    if (!empty($answeridstabtemp[1])) {
                        if ($answer = $DB->get_record('question_answers', array('id' => $answeridstabtemp[1]))) {
                            // Get question informations.
                            $select = '
                                qtype != "random" AND
                                qtype != "randomconstrained" AND
                                id = ?
                            ';
                            $fields = 'id, parent, defaultmark, category';
                            $question = $DB->get_record_select('question', $select, array($answer->question), $fields);
                            if (!in_array($question->category, array_keys($subcats))) {
                                continue;
                            }
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

            // Post compute ratios.

            $maxratio = 0;
            foreach (array_keys($subcats) as $subcatid) {
                $ratioc = $subcats[$subcatid]->goodC / $subcats[$subcatid]->cptC;
                $subcats[$subcatid]->ratioC = ($subcats[$subcatid]->cptC == 0) ? 0 : round($ratioc * 100);
                $ratioa = $subcats[$subcatid]->goodA / $subcats[$subcatid]->cptA;
                $subcats[$subcatid]->ratioA = ($subcats[$subcatid]->cptA == 0) ? 0 : round($ratioa * 100);
                $ratio = $subcats[$subcatid]->good / $subcats[$subcatid]->cpt;
                $subcats[$subcatid]->ratio = ($subcats[$subcatid]->cpt == 0) ? 0 : round($ratio * 100) ;
                if ($maxratio < $subcats[$subcatid]->ratio) {
                    $maxratio = $subcats[$subcatid]->ratio;
                }
            }

            if ($maxratio == 0) {
                $maxratio = 1;
            }

            // Generate output.

            $cpt = 0;

            foreach ($subcats as $subcat) {

                if ($cpt == 0) {
                    // Define height position of the first block on the left part monitor.
                    if ($positionheight != 0) {
                        $str .= '<div id="divpr" style="height:'.$positionheight.'px;"></div>';
                    }
                    $cancel = '';
                    if ($mode == 'training') {
                        $pixurl = $OUTPUT->pix_url('cancel', 'block_userquiz_monitor');
                        $cancel .= '<img class="userquiz-icon" src="'.$pixurl.'" onclick="closepr()" />';
                    } else {
                        $pixurl = $OUTPUT->pix_url('cancel', 'block_userquiz_monitor');
                        $cancel .= '<img class="userquiz-icon" src="'.$pixurl.'" onclick="closeprexam()" />';
                    }

                    $cb = '';
                    $quizzesliststring = $quizzeslist;

                    if ($mode == "training") {
                        $jshandler = 'updateselectorpr('.$courseid.','.$rootcategory.', \''.$subcategoriesids.'\', \'all\'';
                        $jshandler .= ', \''.$quizzesliststring.'\');';
                        $cb = '<input type="checkbox"
                               name="checkall_pr"
                               id="checkall_pr"
                               onclick="'.$jshandler.'"
                               style="padding-left:2px;" /> '.get_string('selectallcb', 'block_userquiz_monitor');
                    }

                    $str .= '
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
    
                $str .= '
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
                if (!empty($this->theblock->config->dualserie)) {
                    $str .= '<td style="width : 15%; background-color:#D9E1D6; text-align:center;font-size:0.8em; color:#658EA0;">
                                    '.get_string('level1', 'block_userquiz_monitor').'
                                 </td>';
                }
                $str .= '<td style="width : 15%; background-color:#D9E1D6; text-align:center;font-size:0.8em; color:#658EA0;">
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
                        $progressbar = $this->progress_bar_html_jqw($subcat->id, $data);

                        $str.= '<tr>';
                        $str .= '<td class="userquiz-cat-progress">';
                        $str .= $progressbar;
                        $str .= '</td>';
                        if (!empty($this->theblock->config->dualserie)) {
                            $serieicon = $this->get_area_url('serie1icon', $OUTPUT->pix_url('a', 'block_userquiz_monitor'));
                            $str .= '<td class="userquiz-cat-total">';
                            $str .= '<img class="userquiz-cat-image" src="'.$serieicon.'" />';
                            $str .= '</td>';
                        }
                        $str .= '<td class="userquiz-cat-total">';
                        $str .= '<h4>'.$subcat->goodA.'/'.$subcat->cptA.'</h4>';
                        $str .= '</td>';
                        $str .= '</tr>';
                   }
                    if ($this->theblock->config->dualserie && ($questiontype == 'C')) {
                        $data = array ( 
                            'boxheight' => 50,
                            'boxwidth' => 160,
                            'skin' => 'C',
                            'type' => 'local',
                            'graphwidth' => $graphwidth,
                            'stop' => $block->config->rateCserie,
                            'successrate' => $subcat->ratioC,
                        );
                        $progressbar = $this->progress_bar_html_jqw($subcat->id, $data);
    
                        $str .= '<tr>';
                        $str .= '<td class="userquiz-cat-progress">';
                        $str .= $progressbar;
                        $str .= '</td>';
                        $str .= '<td class="userquiz-cat-total">';
                        $serieicon = $this->get_area_url('serie2icon', $OUTPUT->pix_url('c', 'block_userquiz_monitor'));
                        $str .= '<img class="userquiz-cat-image" src="'.$serieicon.'" />';
                        $str .= '</td>';
                        $str .= '<td style="userquiz-cat-total">';
                        $str .= '<h4>'.$subcat->goodC.'/'.$subcat->cptC.'</h4>';
                        $str .= '</td>';
                        $str .= '</tr>';
                    }
                }
                $str .= '</table></div>';
                $cpt++;
            }
            return $str;
        }
    }

    public function global_monitor($total, $selector) {
        global $OUTPUT;

        $totalstr = get_string('total', 'block_userquiz_monitor');

        $str = '';
        $str .= '<table class="userquiz-global-monitor">';
        $str .= '<tr>';
        $str .= '<td>';
        $helpicon = $OUTPUT->help_icon('launch', 'block_userquiz_monitor', false);
        $str .= '<h1>'.get_string('runtest', 'block_userquiz_monitor').' '.$helpicon.'</h1>';
        $str .= '</td>';
        $str .= '<td>';
        $str .= '<h1>'.$totalstr.' '.$OUTPUT->help_icon('total', 'block_userquiz_monitor', false).'</h1>';
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

    public function filter_state($domain, $blockid) {
        global $USER, $COURSE, $DB, $OUTPUT;

        $lang = substr(current_language(), 0, 2);

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
                if ($prefs->examsdepth > 0) {
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
                if (@$prefs->resultsdepth == 0) {
                    $dates->from = userdate($absolutestart);
                } else {
                    $dates->from = userdate(max(time() - @$prefs->resultsdepth * 7 * DAYSECS, $absolutestart));
                }
                $dates->to = userdate(time());
                $filterinfo = get_string('filterinfo', 'block_userquiz_monitor', $dates);
                $pixurl = $OUTPUT->pix_url('filter_'.$prefs->resultsdepth.'_'.$lang, 'block_userquiz_monitor');
                $pix = '<img class="userquiz-monitor-exam-pix" src="'.$pixurl.'" title="'.$filterinfo.'" />';
                return get_string('filtering', 'block_userquiz_monitor').': '.$pix;
            }
            $dates->from = userdate($absolutestart);
            $dates->to = userdate(time());
            $filterinfo = get_string('filterinfo', 'block_userquiz_monitor', $dates);
            $pixurl = $OUTPUT->pix_url('filter_0_'.$lang, 'block_userquiz_monitor');
            $pix = '<img class="userquiz-monitor-exam-pix" src="'.$pixurl.'" title="'.$filterinfo.'" />';
            return get_string('filtering', 'block_userquiz_monitor').': '.$pix;
        }
    }

    public function tabs() {
        global $SESSION, $COURSE;

        $conf = @$this->theblock->config;

        // Ensures context conservation in userquiz_monitor.
        $selectedview = optional_param('selectedview', @$SESSION->userquizview, PARAM_TEXT);
        if (empty($SESSION->userquizview) ||
                (!@$conf->trainingenabled && $SESSION->userquizview == 'training') ||
                        (!@$conf->examenabled && $SESSION->userquizview == 'examination')) {
            if (!empty($conf->trainingenabled)) {
                $SESSION->userquizview = 'training';
            } else if (!empty($conf->examenabled)) {
                $SESSION->userquizview = 'examination';
            } else if ($selectedview != 'preferences') {
                if (!empty($conf->informationpageid) && !isediting()) {
                    $params = array('id' => $COURSE->id, 'page' => $conf->informationpageid);
                    redirect(new moodle_url('/course/view.php', $params));
                }
            }
        }
        $selectedview = $SESSION->userquizview = $selectedview;

        if (!empty($conf->informationpageid)) {
            // page deals with the page format
            $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'page' => $conf->informationpageid));
            $rows[0][] = new tabobject('information', $taburl, get_string('menuinformation', 'block_userquiz_monitor'));
        }

        // $rows[0][] = new tabobject('schedule', "view.php?id=".$COURSE->id."&selectedview=schedule", get_string('menuamfref', 'block_userquiz_monitor', $conf->trainingprogramname));
        if (!empty($conf->trainingenabled)) {
            $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'selectedview' => 'training'));
            $rows[0][] = new tabobject('training', $taburl, get_string('menutest', 'block_userquiz_monitor'));
        }
        if (!empty($conf->examenabled)) {
            $examtab = get_string('menuexamination', 'block_userquiz_monitor');
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
    public function total($components, $data, $quizzeslist) {
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
            $label = get_string('reset', 'block_userquiz_monitor');
            $str .= '<input type="button" value="'.$label.'" id="" onclick="'.$jshandler.'" /></p>';
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

        $serie1iconurl = $this->get_area_url('serie1icon', $OUTPUT->pix_url('a', 'block_userquiz_monitor'));

        if (!empty($data['dualserie'])) {
            $str .= '<td class="progressbarlabel">';
            $str .= '<img class="userquiz-cat-image" src="'.$serie1iconurl.'" />';
            $str .= '</td>';
        }

        $str .= '<td class="progressbarlabel">';
        $str .= '<h4>';
        $str .= $data['goodA'].'/'.$data['cptA'];
        $str .= '</h4>';
        $str .= '</td>';
        $str .= '</tr>';

        if (!empty($data['dualserie'])) {

            $serie2iconurl = $this->get_area_url('serie2icon', $OUTPUT->pix_url('c', 'block_userquiz_monitor'));

            $str .= '<tr>';
            $str .= '<td class="userquiz-cat-progress">';
            $str .= $components['progressbarC'];
            $str .= '</td>';
            $str .= '<td class="progressbarlabel">';
            $str .= '<img class="userquiz-cat-image" src="'.$serie2iconurl.' "/>';
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
        $str .= '<img class="userquiz-monitor-cat-detail"
                      title="'.$seesubsstr.'"
                      src="'.$pixurl.'"
                      onclick="'.$cat->jshandler2.'"/>';
        $str .= '</td>';
        $str .= '</tr>';

        // Ensure cat types are presented in sorted order.
        ksort($cat->questiontypes);
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
                        $pixurl = $this->get_area_url('serie1icon', $OUTPUT->pix_url('a', 'block_userquiz_monitor'));
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
                    $pixurl = $this->get_area_url('serie2icon', $OUTPUT->pix_url('c', 'block_userquiz_monitor'));
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
            $ksort($cat->questiontypes);
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

        $str .= '</table></div>';

        return $str;
    }

    /**
     * Provides the url of an eventual stored asset from a filearea. 
     * If none exist, returns default url.
     * @param string $filearea the filearea name
     * @param string|moodle_url $defaulturl
     */
    protected function get_area_url($filearea, $defaulturl = '') {

        $fs = get_file_storage();

        $context = context_block::instance($this->theblock->instance->id);

        if (!$fs->is_area_empty($context->id, 'block_userquiz_monitor', $filearea, 0)) {
            $files = $fs->get_area_files($context->id, 'block_userquiz_monitor', $filearea, false);
            $f = array_pop($file);
            return moodle_url::make_pluginfile_url($f->contextid, $f->component, $f->filearea, 0,
                                                   $f->filepath, $f->filename);
        } else {
            return $defaulturl;
        }
    }
}