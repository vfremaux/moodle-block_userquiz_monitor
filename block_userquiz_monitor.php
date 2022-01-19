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
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/blocks/userquiz_monitor/lib.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/locallib.php');

class block_userquiz_monitor extends block_base {

    protected $extensions;

    public function init() {
        $this->title = get_string('blockname', 'block_userquiz_monitor');
    }

    public function has_config() {
        return true;
    }

    public function specialization() {
        global $CFG;

        if (empty($this->config)) {
            $this->config = new StdClass;
        }
        if (empty($this->config->rateAserie)) {
            $this->config->rateAserie = 85;
        }

        if (empty($this->config->colorAserie)) {
            $this->config->colorAserie = '#C00000';
        }

        if ('pro' == block_userquiz_monitor_supports_feature()) {
            include_once($CFG->dirroot.'/blocks/userquiz_monitor/pro/block_userquiz_monitor.php');
            $this->extensions = new block_userquiz_monitor\pro_extensions($this);
            $this->extensions->specialization();
        }
    }

    public function applicable_formats() {
        return array('course' => true);
    }

    public function instance_allow_config() {
        return true;
    }

    /**
     * Serialize and store config data
     */
    public function instance_config_save($data, $nolongerused = false) {
        global $CFG;

        $fs = get_file_storage();

        if ('pro' == block_userquiz_monitor_supports_feature()) {
            include_once($CFG->dirroot.'/blocks/userquiz_monitor/pro/block_userquiz_monitor.php');
            $this->extensions = new block_userquiz_monitor\pro_extensions($this);
            $this->extensions->instance_config_save($data);
        }

        // Move embedded files into a proper filearea and adjust HTML links.
        $data->examinstructionsformat = $data->examinstructions['format'];
        $data->examinstructions = file_save_draft_area_files($data->examinstructions['itemid'], $this->context->id,
                                                               'block_userquiz_monitor', 'content', 0,
                                                               array('subdirs' => true), $data->examinstructions['text']);

        parent::instance_config_save($data);
    }

    public function get_content() {
        global $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new StdClass;

        $blockcontext = context_block::instance($this->instance->id);

        if (has_capability('block/userquiz_monitor:import', $blockcontext)) {
            $params = ['id' => $COURSE->id, 'blockid' => $this->instance->id];
            $importurl = new moodle_url('/blocks/userquiz_monitor/import/import.php', $params);
            $this->content->footer = '<a href="'.$importurl.'">'.get_string('importquestions', 'block_userquiz_monitor').'</a>';
        } else {
            $this->content->footer = '';
        }

        $this->content->text = '';
        if (!empty($this->config->localcss)) {
            $this->content->text .= '<style>'.$this->config->localcss.'</style>';
        }
        $this->content->text .= $this->get_report();

        return $this->content;
    }

    /**
     * Draws the GUI
     */
    public function get_report() {
        global $DB, $COURSE, $CFG, $USER, $SESSION, $OUTPUT, $PAGE;

        // HTML response.
        $response = '';

        // Menu establishment.
        $selectedview = $this->get_active_view();

        // Display schedule.
        // Note : At the moment we do not really know what to do with this.
        if ($selectedview == 'schedule') {

            $renderer = $PAGE->get_renderer('block_userquiz_monitor', 'schedule');
            $renderer->set_block($this);
            $response = $renderer->tabs($selectedview);

            $title = get_string('reftitle', 'block_userquiz_monitor', $this->config->trainingprogramname);
            $schedule = $OUTPUT->heading($title, 1);

            if (!empty($this->config->rootcategory)) {
                $schedule .= $renderer->schedule($this);
            } else {
                $notif = get_string('warningchoosecategory', 'block_userquiz_monitor');
                $schedule .= $OUTPUT->notification($notif, 'notifyproblem');
            }

            $response .= $schedule;
        }

        // Display test.
        if ($selectedview == 'training') {

            require_once($CFG->dirroot.'/blocks/userquiz_monitor/classes/output/block_userquiz_monitor_training_renderer.php');
            $renderer = $PAGE->get_renderer('block_userquiz_monitor', 'training');
            $renderer->set_block($this);

            $response = $renderer->tabs($selectedview);

            $training = $renderer->heading();

            if ((! empty($this->config->rootcategory)) && (! empty($this->config->trainingquizzes))) {
                $training .= $renderer->training($COURSE->id, $this);
            } else {
                if (empty($this->config->rootcategory)) {
                    $notif = get_string('warningchoosecategory', 'block_userquiz_monitor');
                    $training .= $OUTPUT->notification($notif, 'notifyproblem');
                    $training .= '<br/>';
                }

                if (empty($this->config->trainingquizzes)) {
                    $notif = get_string('warningconfigtest', 'block_userquiz_monitor');
                    $training .= $OUTPUT->notification($notif, 'notifyproblem');
                }
            }
            $response .= $training;
        }

        // Display examination.
        if (in_array($selectedview, array('examination', 'examlaunch', 'examresults', 'examdetails', 'examhistory'))) {

            require_once($CFG->dirroot.'/blocks/userquiz_monitor/classes/output/block_userquiz_monitor_exam_renderer.php');
            $renderer = $PAGE->get_renderer('block_userquiz_monitor', 'exam');
            $renderer->set_block($this);

            $response = $renderer->tabs($selectedview);

            $examination = $renderer->heading();

            if ((!empty($this->config->rootcategory)) && (!empty($this->config->examquiz))) {
                switch ($selectedview) {

                    case 'examlaunch': {
                        $quizid = @$this->config->examquiz;
                        $available = block_userquiz_monitor_count_available_attempts($USER->id, $quizid);
                        $maxattempts = $this->get_max_attempts($quizid);
                        $examination .= $renderer->launch_widget($quizid, 0 + $available, max(0 + $available, 0 + $maxattempts));
                        break;
                    }

                    case 'examresults': {

                        include($CFG->dirroot.'/blocks/userquiz_monitor/preferenceForm.php');

                        $preferenceform = new PreferenceForm(null, array('mode' => 'examination', 'blockconfig' => $this->config));
                        $params = array('userid' => $USER->id, 'blockid' => $this->instance->id);
                        if ($prefs = $DB->get_record('userquiz_monitor_prefs', $params)) {
                            $data = clone($prefs);
                            unset($data->id);
                        } else {
                            $data = new StdClass;
                        }
                        $data->blockid = $this->instance->id;
                        $data->selectedview = 'examresults';
                        $preferenceform->set_data($data);

                        if (!$preferenceform->is_cancelled()) {
                            if ($data = $preferenceform->get_data()) {
                                $data->userid = $USER->id;
                                if (!empty($prefs)) {
                                    $prefs->examsdepth = 0 + @$data->examsdepth;
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
                        $examination .= ob_get_clean();

                        $examination .= $renderer->results_widget();
                        break;
                    }

                    case 'examdetails': {

                        include($CFG->dirroot.'/blocks/userquiz_monitor/preferenceForm.php');

                        $preferenceform = new PreferenceForm(null, array('mode' => 'examination', 'blockconfig' => $this->config));
                        $params = array('userid' => $USER->id, 'blockid' => $this->instance->id);
                        if ($prefs = $DB->get_record('userquiz_monitor_prefs', $params)) {
                            $data = clone($prefs);
                            unset($data->id);
                        } else {
                            $data = new StdClass;
                        }
                        $data->blockid = $this->instance->id;
                        $data->selectedview = 'examdetails';
                        $preferenceform->set_data($data);

                        if (!$preferenceform->is_cancelled()) {
                            if ($data = $preferenceform->get_data()) {
                                $data->userid = $USER->id;
                                if (!empty($prefs)) {
                                    $prefs->examsdepth = 0 + @$data->examsdepth;
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
                        $examination .= ob_get_clean();

                        $examination .= $renderer->exam($COURSE->id, $this);
                        break;
                    }

                    case 'examhistory': {
                        $examination .= $renderer->history_widget();
                        break;
                    }
                }
            } else {
                // Setup signals for authors.
                if (empty($this->config->rootcategory)) {
                    $examination .= get_string('warningchoosecategory', 'block_userquiz_monitor');
                    $examination .= '<br/>';
                }

                if (empty($this->config->examquiz)) {
                    $examination .= get_string('warningconfigexam', 'block_userquiz_monitor');
                }
            }

            $response .= $examination;
        }

        return $response;
    }

    public function get_required_javascript() {
        global $PAGE, $COURSE;

        parent::get_required_javascript();

        $PAGE->requires->jquery_plugin('jqwidgets-bulletchart', 'local_vflibs');
        if (!empty($this->config->trainingquizzes)) {
            $trainquizlist = implode(',', $this->config->trainingquizzes);
            $args = array($COURSE->id, $this->instance->id, $this->config->rootcategory, $trainquizlist, @$this->config->examquiz);
            $PAGE->requires->js_call_amd('block_userquiz_monitor/training', 'init', $args);
        }
    }

    protected function get_active_view() {
        global $SESSION, $USER, $COURSE;

        if (!in_array(@$SESSION->userquizview[$COURSE->id], array('training', 'examination'))) {
            if (!empty($this->config->examdefault) && !empty($this->config->examenabled)) {
                $SESSION->userquizview[$COURSE->id] = 'examination';
            } else {
                $SESSION->userquizview[$COURSE->id] = 'training';
            }
        }

        $selectedview = optional_param('selectedview', $SESSION->userquizview[$COURSE->id], PARAM_TEXT);

        // Ensures context conservation in userquiz_monitor.
        if (!@$this->config->examenabled && (empty($selectedview) || $selectedview == 'examination')) {
            $selectedview = 'training';
        }

        if (!@$this->config->trainingenabled && (empty($selectedview) || $selectedview == 'training')) {
            $selectedview = 'examination';
        }

        /*
        if (!empty($this->config->informationpageid)) {
            $params = array('id' => $COURSE->id, 'page' => $this->config->informationpageid);
            redirect(new moodle_url('/course/view.php', $params));
        }
        */

        $SESSION->userquizview[$COURSE->id] = $selectedview;
        return $selectedview;
    }

    public function get_max_attempts($quizid) {
        global $USER, $DB;

        $params = array('userid' => $USER->id, 'quizid' => $quizid);
        // Check if the quiz has usernumattempts or standard attempts.
        $userlimitsenabled = $DB->get_field('qa_usernumattempts', 'enabled', array('quizid' => $quizid));
        if ($userlimitsenabled) {
            $maxattempts = $DB->get_field('qa_usernumattempts_limits', 'maxattempts', $params);
        } else {
            $maxattempts =  $DB->get_field('quiz', 'attempts', array('id' => $quizid));
        }

        return $maxattempts;
    }
}
