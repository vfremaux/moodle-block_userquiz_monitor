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

require_once($CFG->dirroot.'/blocks/userquiz_monitor/block_userquiz_monitor_lib.php');

class block_userquiz_monitor extends block_base {

    public function init() {
        $this->title = get_string('blockname', 'block_userquiz_monitor');
    }

    public function specialization() {
        if (empty($this->config)) {
            $this->config = new StdClass;
        }
        if (empty($this->config->rateAserie)) {
            $this->config->rateAserie = 85;
        }

        if (empty($this->config->rateCserie)){
            $this->config->rateCserie = 75;
        }

        if (!isset($this->config->dualserie)) {
            $this->config->dualserie = 1;
        }
    }

    public function applicable_formats() {
        return array('course' => true);
    }

    public function instance_allow_config() {
        return true;
    }
    
    public function get_content() {
        global $USER, $CFG;
        $wwwroot = '';
        $signup = '';

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new StdClass;

        $this->content->footer = '';
        $this->content->text = $this->get_report();

        return $this->content;
    }

    /**
     * Serialize and store config data
     */
    public function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links.
        $config->examinstructionsformat = $data->examinstructions['format'];
        $config->examinstructions = file_save_draft_area_files($data->examinstructions['itemid'], $this->context->id,
                                                               'block_userquiz_monitor', 'content', 0,
                                                               array('subdirs' => true), $data->examinstructions['text']);

        parent::instance_config_save($config, $nolongerused);
    }

    public function get_report() {
        global $DB, $COURSE, $CFG, $USER, $SESSION, $OUTPUT, $PAGE;

        include_once($CFG->dirroot.'/blocks/userquiz_monitor/trainingmonitor.php');
        include_once($CFG->dirroot.'/blocks/userquiz_monitor/exammonitor.php');
        include_once($CFG->dirroot.'/blocks/userquiz_monitor/block_userquiz_monitor_lib.php');
        include_once($CFG->dirroot.'/blocks/userquiz_monitor/schedulemonitor.php');

        $renderer = $PAGE->get_renderer('block_userquiz_monitor');

        // HTML response.
        $response = '';

        // Menu establishment.
        $response = $renderer->tabs($this);
        $defaultview = (!empty($SESSION->userquizview)) ? $SESSION->userquizview : 'training';
        $selectedview = optional_param('selectedview', $defaultview, PARAM_TEXT);

        // Display schedule.
        // Note : At the moment we do not really know what to do with this.
        if ($selectedview == 'schedule') {

            $title = get_string('reftitle', 'block_userquiz_monitor', $this->config->trainingprogramname);
            $schedule = $OUTPUT->heading( $title, 1);

            if (!empty($this->config->rootcategory)) {
                $schedule .= get_schedule($this);
            } else {
                $notif = get_string('warningchoosecategory', 'block_userquiz_monitor');
                $schedule .= $OUTPUT->notification($notif, 'notifyproblem');
            }

            $response .= $schedule;
        }

        // Display test.
        if ($selectedview == 'training') {

            $title = get_string('testtitle', 'block_userquiz_monitor');
            $training = '<table width="100%"></tr><td>';
            $training .= $OUTPUT->heading( $title, 1);
            $training .= '</td><td align="right">';
            $training .= $renderer->filter_state('training', $this->instance->id);
            $training .= '</td></tr></table>';

            // $training .= get_string('testinstructions', 'block_userquiz_monitor');

            if ((! empty($this->config->rootcategory)) && (! empty($this->config->trainingquizzes))) {
                get_monitortest($COURSE->id, $training, $this);
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
        if ($selectedview == 'examination') {

            if (!empty($this->config->alternateexamheading)) {
                $title = format_text($this->config->alternateexamheading);
            } else {
                $title = get_string('examtitle', 'block_userquiz_monitor', $this->config->trainingprogramname);
            }
            $examination = '<table width="100%"></tr><td>';
            $examination .= $OUTPUT->heading( $title, 1);
            $examination .= '</td><td align="right">';
            $examination .= $renderer->filter_state('exams', $this->instance->id);
            $examination .= '</td></tr></table>';

            if (empty($this->config->examinstructions)) {
                $examination .= get_string('examinstructions', 'block_userquiz_monitor', $this->config->trainingprogramname);
            } else {
                $examination .= '<p>';
                $examination .= format_string($this->config->examinstructions);
                $examination .= '</p>';
            }

            if ((!empty($this->config->rootcategory)) && (!empty($this->config->examquiz))) {
                get_monitorexam($COURSE->id, $examination, $this);
            } else {
                if (empty($this->config->rootcategory)) {
                    $examination .= get_string('warningchoosecategory', 'block_userquiz_monitor');
                    $examination .= '<br/>';
                }

                if (empty($this->config->examquiz)) {
                    $examination .= get_string('warningconfigexam', 'block_userquiz_monitor');
                }
            }

            $response.= $examination;
        }

        if ($selectedview == 'preferences') {
            include($CFG->dirroot.'/blocks/userquiz_monitor/preferenceForm.php');

            $preferenceform = new PreferenceForm($this->instance->id);
            $params = array('userid' => $USER->id, 'blockid' => $this->instance->id);
            if ($prefs = $DB->get_record('userquiz_monitor_prefs', $params)) {
                $data = clone($prefs);
                unset($data->id);
                $preferenceform->set_data($data);
            }

            if (!$preferenceform->is_cancelled()) {
                if ($data = $preferenceform->get_data()) {
                    $data->userid = $USER->id;
                    if (!empty($prefs)) {
                        $prefs->resultsdepth = 0 + @$data->resultsdepth;
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
            $response .= ob_get_clean();
        }

        return $response;
    }
}
