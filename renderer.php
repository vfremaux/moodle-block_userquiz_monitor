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

    protected $gaugerendererfunc;

    /**
     * Loads the block instance into the controller.
     */
    public function set_block($bi) {
        $this->theblock = $bi;

        switch (@$this->theblock->config->gaugerenderer) {
            case 'html': {
                $this->gaugerendererfunc = 'progress_bar_html';
                break;
            }

            case 'flash': {
                $this->gaugerendererfunc = 'progress_bar_flash';
                break;
            }

            case 'gd': {
                $this->gaugerendererfunc = 'progress_bar_html_gd';
                break;
            }

            default:
                $this->gaugerendererfunc = 'progress_bar_html_jqw';
        }

    }

    public function get_gauge_renderer() {
        return $this->gaugerendererfunc;
    }

    public function category_detail_container() {

        $template = new Stdclass;
        $template->catdetailstr = get_string('categorydetail', 'block_userquiz_monitor', $this->theblock->config->trainingprogramname);

        return $this->output->render_from_template('block_userquiz_monitor/catdetailcontainer', $template);
    }

    /**
     * Display the progress bar
     */
    public function progress_bar_html($id, $data) {
        global $OUTPUT;

        $template = (object) $data;

        if ($template->skin == 'A') {
            $template->barcolor = $this->theblock->config->colorAserie;
        } else {
            $template->barcolor = $this->theblock->config->colorCserie;
        }

        // $valuebartop = $data['boxheight'] / 2 - 2; 

        $template->id = $id;

        $template->remains = 100 - $template->successrate;

        return $OUTPUT->render_from_template('block_userquiz_monitor/progressbarhtml', $template);
    }

    /**
     * Display the progress bar
     */
    public function progress_bar_flash($id, $data) {

        $testdata = urlencode(json_encode($data));
        $data['id'] = $id;
        $progressbargraph = call_progress_bar_html($testdata, $data);
        return $progressbargraph;
    }

    public function progress_bar_html_gd($id, $data) {

        $progressbarid = 'progress_bar'.$id;
        $progressbarname = 'progress_bar'.$id;

        $testdata = urlencode(json_encode($data));
        $data['id'] = $id;
        if ($data['type'] == 'local') {
            $params = array('barwidth' => $data['successrate'], 'stop' => $data['stop'], 'skin' => $data['skin']);
            $barurl = new moodle_url('/blocks/userquiz_monitor/generators/gd/gd_local_dyn.php', $params);
            $progressbarimg = '<img id="'.$progressbarid.'"
                                    name="'.$progressbarname.'"
                                    src="'.$barurl.'"
                                    class="progress-bar-end" />';
        } else {
            $params = array('barwidth' => $data['successrate'], 'stop' => $data['stop'], 'skin' => $data['skin']);
            $barurl = new moodle_url('/blocks/userquiz_monitor/generators/gd/gd_total_dyn.php', $params);
            $progressbarimg = '<img id="'.$progressbarid.'"
                                    name="'.$progressbarname.'"
                                    src="'.$barurl.'"
                                    class="progress-bar-full" />';
        }
        return $progressbarimg;
    }

    public function progress_bar_html_jqw($id, $data) {
        global $PAGE;

        $jqrenderer = $PAGE->get_renderer('local_vflibs');

        /*
         * @param array $properties array with ('width', 'height', 'desc', 'barsize', 'tooltip', 'color') keys
         * @param array $ranges an array of range objects having ('start', 'end', 'color', 'opacity') keys
         * @param object $pointer an object with ('value', 'label', 'size', 'color') keys
         * @param object $target an object with ('value', 'label', 'size', 'color') keys
         * @param object $ticks an object with ('position', 'interval', 'size') keys
         */

        if ($data['skin'] == 'A') {
            $color = $this->theblock->config->colorAserie;
        } else {
            $color = $this->theblock->config->colorCserie;
        }

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

        if (!empty($data['boxheight'])) {
            $properties['height'] = $data['boxheight'];
            $properties['width'] = max($data['boxwidth'], 240);
        }

        // Respect relative width if given.
        if (strstr($data['boxwidth'], '%') !== false) {
            $properties['width'] = $data['boxwidth'];
        } else {
            $properties['width'] = max($data['boxwidth'], 240);
        }

        $pointer = new StdClass;
        $pointer->value = $data['successrate'];
        $pointer->color = $color;
        $pointer->label = get_string('meanscore', 'block_userquiz_monitor');
        $pointer->size = 30;

        $target = new StdClass();
        $target->value = $data['stop'];
        $target->color = $color;
        $target->size = '4';
        $target->label = get_string('target', 'block_userquiz_monitor');

        $ticks = new StdClass;
        $ticks->position = 'near';
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

    /**
     * Displaying the subcategories of a category
     * @see Called by ajax/subcategorycontent.php
     * @param int $courseid
     * @param int $rootcategory
     * @param int $categoryid
     * @param array $quizzeslist
     * @param int $mode
     */
    public function subcategories($courseid, $rootcategory, $categoryid, $quizzeslist, $mode) {
        global $USER, $DB;

        $template = new StdClass;
        $template->catid = $categoryid;

        $blockid = $this->theblock->instance->id;
        $gaugerenderfunc = $this->gaugerendererfunc;
        if (empty($gaugerenderfunc)) {
            throw new coding_exception('Renderers functions were called before renderer has block being setup');
        }

        $quizzeslist = stripslashes($quizzeslist);
        $quizzesarr = explode(',', $quizzeslist);
        list($insql, $inparams) = $DB->get_in_or_equal($quizzesarr);

        $fields = 'id, name, parent';
        if ($subcats = $DB->get_records('question_categories', ['parent' => $categoryid], 'sortorder', $fields )) {

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

                $sql = "
                    SELECT
                        q.*,
                        qbe.questioncategoryid as category
                    FROM
                        {question} q,
                        {question_versions} qv,
                        {question_bank_entries} qbe
                    WHERE
                        q.id = qv.questionid AND
                        qbe.id = qv.questionbankentryid AND
                        qbe.questioncategoryid = ? AND
                        q.defaultmark = ? AND
                        q.qtype NOT LIKE ?
                ";
                if ($DB->record_exists_sql($sql, [$subcat->id, 1000, 'random%'])) {
                    $subcats[$subcatid]->questiontypes['C'] = 1;
                }

                if ($DB->record_exists_sql($sql, [$subcat->id, 1, 'random%'])) {
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

                $sql = "
                    SELECT
                        qas.questionattemptid as qattemptid,
                        qa.questionid as qid,
                        MAX(qas.fraction) as grade,
                        qua.quiz as quizid,
                        qbe.questioncategoryid as qcat,
                        qua.id as uaid
                    FROM
                        {question_attempt_steps} qas,
                        {question_attempts} qa,
                        {quiz_attempts} qua,
                        {question} q,
                        {question_versions} qv,
                        {question_bank_entries} qbe,
                        {question_categories} qc
                    WHERE
                        qas.questionattemptid = qa.id AND
                        qa.questionusageid = qua.uniqueid AND
                        qa.questionid = q.id AND
                        qas.state != 'todo' AND
                        qv.questionid = q.id AND
                        qv.questionbankentryid = qbe.id AND
                        qbe.questioncategoryid = qc.id AND
                        qas.userid = ? AND
                        qua.quiz $insql AND
                        qa.questionid = q.id AND
                        qc.parent = ?
                    GROUP BY
                        qas.questionattemptid
                ";

                $params = array($USER->id);
                $params = array_merge($params, $inparams);
                $params[] = $categoryid;
                $catstates = $DB->get_records_sql($sql, $params);
            }

            if (!empty($catstates)) {

                // Get answer for each questions.
                $maxratio = 0;
                $i = 0;
                foreach ($catstates as $state) {

                    $select = '
                        qtype NOT LIKE ? AND
                        id = ?
                    ';
                    if ($defaultmark = $DB->get_field_select('question', 'defaultmark', $select, ['random%', $state->qid])) {

                        $subcats[$state->qcat]->cpt++;
                        if (round($defaultmark) == 1000) {
                            $subcats[$state->qcat]->cptC++;
                            if ($state->grade == 1) {
                                $subcats[$state->qcat]->goodC++;
                            }
                        } else {
                            $subcats[$state->qcat]->cptA++;
                            if ($state->grade == 1) {
                                $subcats[$state->qcat]->goodA++;
                            }
                        }
                        if ($state->grade == 1) {
                            $subcats[$state->qcat]->good++;
                        }
                    }
                    $i++;
                }
            } else {
                if function_exists('debug_trace') debug_trace("No substates", TRACE_DEBUG);
            }

            // Post compute ratios.

            $maxratio = 0;
            foreach (array_keys($subcats) as $subcatid) {
                if ($subcats[$subcatid]->cptC) {
                    $ratioc = $subcats[$subcatid]->goodC / $subcats[$subcatid]->cptC;
                } else {
                    $ratioc = 0;
                }
                $subcats[$subcatid]->ratioC = round($ratioc * 100);
                if ($subcats[$subcatid]->cptA) {
                    $ratioa = $subcats[$subcatid]->goodA / $subcats[$subcatid]->cptA;
                } else {
                    $ratioa = 0;
                }
                $subcats[$subcatid]->ratioA = round($ratioa * 100);
                if ($subcats[$subcatid]->cpt) {
                    $ratio = $subcats[$subcatid]->good / $subcats[$subcatid]->cpt;
                } else {
                    $ratio = 0;
                }
                $subcats[$subcatid]->ratio = round($ratio * 100);
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

                    $cb = '';
                    $quizzesliststring = $quizzeslist;

                    if ($mode == 'training') {
                        $boxtpl = new StdClass;
                        $boxtpl->selectall = true;
                        $boxtpl->selectallcbstr = get_string('selectallcb', 'block_userquiz_monitor');
                        $boxtpl->name = 'checkall_detail';
                        $boxtpl->id = 'id-checkall-detail-'.$categoryid;
                        $boxtpl->class = 'cb-detail-'.$categoryid;
                        $cb = $this->output->render_from_template('block_userquiz_monitor/catcheckbox', $boxtpl);
                    }

                    $canceltpl = new StdClass;
                    $canceltpl->canceliconurl = $this->get_area_url('closesubsicon', $this->output->image_url('cancel', 'block_userquiz_monitor'));
                    $canceltpl->parentcatid = $categoryid;
                    $canceltpl->cb = $cb;
                    $template->cancelrow = $this->output->render_from_template('block_userquiz_monitor/cancelrow', $canceltpl);
                }

                $cb = '';
                if ($mode == 'training') {
                    $boxtpl = new StdClass;
                    $boxtpl->name = 'cbpr'.$subcat->id;
                    $boxtpl->id = 'id-cb-detail-'.$subcat->id;
                    $boxtpl->class = 'cb-detail cb-detail-'.$categoryid;
                    $cb = $this->output->render_from_template('block_userquiz_monitor/catcheckbox', $boxtpl);
                }

                $subcattpl = new StdClass;
                $subcattpl->id = $subcat->id;
                $subcattpl->cb = $cb;
                $subcattpl->name = $subcat->name;
                $subcattpl->radioid = 'id-cb-detail-'.$subcat->id;

                $subcattpl->barheadrow = $this->render_bar_head_row();

                $graphwidth = round(($subcat->ratio * 100) / $maxratio);

                if ($graphwidth < 1) {
                    $graphwidth = 1;
                }

                ksort($subcat->questiontypes);
                foreach (array_keys($subcat->questiontypes) as $questiontype) {

                    if ($questiontype == 'A') {
                        $data = array (
                            'boxheight' => 50,
                            'boxwidth' => '95%',
                            'skin' => 'A',
                            'type' => 'local',
                            'graphwidth' => $graphwidth,
                            'stop' => $this->theblock->config->rateAserie,
                            'successrate' => $subcat->ratioA,
                        );
                        $progressbar = $this->$gaugerenderfunc($subcat->id, $data);

                        $serieicon = $this->get_area_url('serie1icon', $this->output->image_url('a', 'block_userquiz_monitor'));
                        $catcounts = new StdClass;
                        $catcounts->good = $subcat->goodA;
                        $catcounts->cpt = $subcat->cptA;
                        $subcattpl->barrangerowA = $this->render_bar_range_row($progressbar, $catcounts, $serieicon);
                    }
                    if ($this->theblock->config->dualserie && ($questiontype == 'C')) {
                        $data = array (
                            'boxheight' => 50,
                            'boxwidth' => '95%',
                            'skin' => 'C',
                            'type' => 'local',
                            'graphwidth' => $graphwidth,
                            'stop' => $this->theblock->config->rateCserie,
                            'successrate' => $subcat->ratioC,
                        );
                        $progressbar = $this->$gaugerenderfunc($subcat->id, $data);

                        $serieicon = $this->get_area_url('serie2icon', $this->output->image_url('c', 'block_userquiz_monitor'));
                        $catcounts = new StdClass;
                        $catcounts->good = $subcat->goodC;
                        $catcounts->cpt = $subcat->cptC;
                        $subcattpl->barrangerowC = $this->render_bar_range_row($progressbar, $catcounts, $serieicon);
                    }
                }

                $template->subcategories[] = $subcattpl;

                $cpt++;
            }

            return $this->output->render_from_template('block_userquiz_monitor/subcategories', $template);
        }
    }

    public function render_bar_head_row() {

        $template = new Stdclass;
        $template->dualserie = !empty($this->theblock->config->dualserie);
        $template->level1str = get_string('level1', 'block_userquiz_monitor');
        $template->scorestr = get_string('score', 'block_userquiz_monitor');
        $template->ratio1str = get_string('ratio1', 'block_userquiz_monitor');

        return $this->output->render_from_template('block_userquiz_monitor/headrow', $template);
    }

    public function render_bar_range_row($progressbar, $catcounts, $serieicon) {

        $template = new StdClass;

        $template->dualserie = !empty($this->theblock->config->dualserie);
        $template->serieiconurl = $serieicon;
        $template->progressbar = $progressbar;
        $template->catcounter = $catcounts->good.'/'.$catcounts->cpt;

        return $this->output->render_from_template('block_userquiz_monitor/rangebarrow', $template);
    }

    public function filter_state($domain, $blockid) {
        global $USER, $COURSE, $DB;

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

        $attrs = array('class' => 'userquiz-monitor-exam-pix');
        if ($domain == 'exams') {
            if ($prefs = $DB->get_record('userquiz_monitor_prefs', array('userid' => $USER->id, 'blockid' => $blockid))) {
                if ($prefs->examsdepth > 0) {
                    $filterinfo = get_string('examsfilterinfo', 'block_userquiz_monitor', $prefs->examsdepth);
                    $pix = $this->output->pix_icon('examfilter_'.$prefs->examsdepth.'_'.$lang, $filterinfo, 'block_userquiz_monitor', $attrs);
                    return get_string('filtering', 'block_userquiz_monitor').': '.$pix;
                }
            }
            $filterinfo = get_string('allexamsfilterinfo', 'block_userquiz_monitor');
            $pix = $this->output->pix_icon('examfilter_0_'.$lang, $filterinfo, 'block_userquiz_monitor', $attrs);
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
                $pix = $this->output->pix_icon('filter_'.$prefs->resultsdepth.'_'.$lang, $filterinfo, 'block_userquiz_monitor', $attrs);
                return get_string('filtering', 'block_userquiz_monitor').': '.$pix;
            }
            $dates->from = userdate($absolutestart);
            $dates->to = userdate(time());
            $filterinfo = get_string('filterinfo', 'block_userquiz_monitor', $dates);
            $pixurl = $this->output->pix_icon('filter_0_'.$lang, $filterinfo, 'block_userquiz_monitor', $attrs);
            return get_string('filtering', 'block_userquiz_monitor').': '.$pix;
        }
    }

    /**
     * Prints scrren navigation tabs. May change the active selectedview by submenus.
     * @param intref &$selectedview
     */
    public function tabs(&$selectedview) {
        global $COURSE;

        $conf = @$this->theblock->config;

        if (!empty($conf->informationpageid)) {
            // Page deals with the page format.
            $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'page' => $conf->informationpageid));
            $rows[0][] = new tabobject('information', $taburl, get_string('menuinformation', 'block_userquiz_monitor'));
        }

        /*
         * $label = get_string('menuamfref', 'block_userquiz_monitor', $conf->trainingprogramname);
         * $rows[0][] = new tabobject('schedule', "view.php?id=".$COURSE->id."&selectedview=schedule", $label);
         */
        $activated = null;
        $inactives = null;
        $hasexamtabs = false;
        if (!empty($conf->trainingenabled)) {

            if (empty($conf->examenabled)) {
                // No tabs at all if only training.
                return;
            }

            $trainingtab = get_string('menutest', 'block_userquiz_monitor');
            $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'selectedview' => 'training'));
            $rows[0][] = new tabobject('training', $taburl, $trainingtab);

            $examtab = get_string('menuexamination', 'block_userquiz_monitor');
            $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'selectedview' => 'examination'));
            $rows[0][] = new tabobject('examination', $taburl, $examtab);

            $hasexamtabs = true;
            $examrow = 1;
            if (in_array($selectedview, ['examination', 'examlaunch', 'examresults', 'examdetails', 'examhistory'])) {
                $activated = ['examination'];
            }
            if ($selectedview == 'training') {
                // $inactives = ['examlaunch', 'examresults', 'examdetails', 'examhistory'];
                $hasexamtabs = false;
            }
        } else {
            // If only exam enabled, print exam tabs at first level.
            $hasexamtabs = true;
            $examrow = 0;
        }

        if ($selectedview == 'examination') {
            $selectedview = 'examlaunch'; // The default.
        }

        if ($hasexamtabs) {
            $examtab = get_string('menuexamlaunch', 'block_userquiz_monitor');
            $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'selectedview' => 'examlaunch'));
            $rows[$examrow][] = new tabobject('examlaunch', $taburl, $examtab);

            $examtab = get_string('menuexamresults', 'block_userquiz_monitor');
            $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'selectedview' => 'examresults'));
            $rows[$examrow][] = new tabobject('examresults', $taburl, $examtab);

            if (!empty($conf->examshowdetails)) {
                $examtab = get_string('menuexamdetails', 'block_userquiz_monitor');
                $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'selectedview' => 'examdetails'));
                $rows[$examrow][] = new tabobject('examdetails', $taburl, $examtab);
            }

            if (!empty($conf->examshowhistory)) {
                $examtab = get_string('menuexamhistories', 'block_userquiz_monitor');
                $taburl = new moodle_url('/course/view.php', array('id' => $COURSE->id, 'selectedview' => 'examhistory'));
                $rows[$examrow][] = new tabobject('examhistory', $taburl, $examtab);
            }
        }

        return print_tabs($rows, $selectedview, $inactives, $activated, true);
    }

    /**
     * Renders the full total block.
     * @param array $components an array of $rendered subcomponents as strings
     * @param array $data scalar data to render as valriable inputs
     * @param int $rootcategory
     * @param string $list of involved quizzes
     */
    public function total($components, $data, $quizzeslist, $mode = 'training') {
        global $USER, $COURSE;

        $template = new StdClass;

        $template->mode = $mode;
        $template->commenthist = get_string('commenthist', 'block_userquiz_monitor');
        $template->totaldescstr = get_string('totaldesc'.$mode, 'block_userquiz_monitor');

        $template->admintraining = ($mode == 'training') &&
            has_capability('moodle/site:config', context_system::instance(), @$USER->realuser);
        $template->adminresethiststr = get_string('adminresethist', 'block_userquiz_monitor');
        $template->jshandler = 'resettraining(\''.$COURSE->id.'\', \''.$USER->id.'\', \''.urlencode($quizzeslist).'\')';
        $template->resetlabel = get_string('reset', 'block_userquiz_monitor');

        $template->barheadrow = $this->render_bar_head_row('');

        $serieicon = $this->get_area_url('serie1icon', $this->output->image_url('a', 'block_userquiz_monitor'));
        $catcounts = new \StdClass;
        $catcounts->good = $data['goodA'];
        $catcounts->cpt = $data['cptA'];
        $template->barrangerowA = $this->render_bar_range_row($components['progressbarA'], $catcounts, $serieicon);

        if (!empty($data['dualserie'])) {
            $serie2iconurl = $this->get_area_url('serie2icon', $this->output->image_url('c', 'block_userquiz_monitor'));
            $serieicon = $this->get_area_url('serie2icon', $this->output->image_url('c', 'block_userquiz_monitor'));
            $catcounts = new \StdClass;
            $catcounts->good = $data['goodC'];
            $catcounts->cpt = $data['cptC'];
            $template->barrangerowC = $this->render_bar_range_row($components['progressbarC'], $catcounts, $serieicon);
        }

        return $this->output->render_from_template('block_userquiz_monitor/total', $template);
    }

    public function program_headline($programname, $mode = 'training') {

        $template = new StdClass;

        $template->catstr = get_string('categories', 'block_userquiz_monitor', $programname);
        if ($mode == 'training') {
            $template->selectall = true;
            $template->selectallcbstr = get_string('selectallcb', 'block_userquiz_monitor');
        }

        return $this->output->render_from_template('block_userquiz_monitor/programheadline', $template);
    }

    /**
     * Provides the url of an eventual stored asset from a filearea.
     * If none exist, returns default url.
     * @param string $filearea the filearea name
     * @param string|moodle_url $defaulturl
     */
    public function get_area_url($filearea, $defaulturl = '') {

        $fs = get_file_storage();

        $context = context_block::instance($this->theblock->instance->id);

        if (!$fs->is_area_empty($context->id, 'block_userquiz_monitor', $filearea, 0)) {
            if ($files = $fs->get_area_files($context->id, 'block_userquiz_monitor', $filearea, false)) {
                $f = array_pop($files);
                return moodle_url::make_pluginfile_url($f->get_contextid(), $f->get_component(), $f->get_filearea(), 0,
                                                       $f->get_filepath(), $f->get_filename());
            }
        }
        return $defaulturl;
    }

    /**
     * This is an override of the standard function in core renderer, to admit a derivated url from
     * moodle files.
     */
    public function render_pix_icon(pix_icon $icon, $alternateurl = '') {
        $attributes = $icon->attributes;
        if (!empty($alternateurl)) {
            $attributes['src'] = $alternateurl;
        } else {
            $attributes['src'] = $this->image_url($icon->pix, $icon->component);
        }
        return html_writer::empty_tag('img', $attributes);
    }

    /**
     * Renders an action_link object.
     *
     * The provided link is renderer and the HTML returned. At the same time the
     * associated actions are setup in JS by {@link core_renderer::add_action_handler()}
     *
     * @param action_link $link
     * @return string HTML fragment
     */
    public function render_action_link(action_link $link, $alternateiconurl = '') {
        global $CFG;

        $text = '';
        if ($link->icon) {
            $text .= $this->render_pix_icon($link->icon, $alternateiconurl);
        }

        if ($link->text instanceof renderable) {
            $text .= $this->output->render($link->text);
        } else {
            $text .= $link->text;
        }

        // A disabled link is rendered as formatted text.
        if (!empty($link->attributes['disabled'])) {
            // Do not use div here due to nesting restriction in xhtml strict.
            return html_writer::tag('span', $text, array('class' => 'currentlink'));
        }

        $attributes = $link->attributes;
        unset($link->attributes['disabled']);
        $attributes['href'] = $link->url;

        if ($link->actions) {
            if (empty($attributes['id'])) {
                $id = html_writer::random_id('action_link');
                $attributes['id'] = $id;
            } else {
                $id = $attributes['id'];
            }
            foreach ($link->actions as $action) {
                $this->output->add_action_handler($action, $id);
            }
        }

        return html_writer::tag('a', $text, $attributes);
    }
}