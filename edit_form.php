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
 * Form for editing HTML block instances.
 *
 * @package   blocks_userquiz_monitor
 * @category blocks
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright  Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/blocks/userquiz_monitor/locallib.php');

/**
 * Form for editing Random glossary entry block instances.
 */
class block_userquiz_monitor_edit_form extends block_edit_form {

    public $imgfilepickerattrs;

    public function __construct($url, $block, $page) {
        global $COURSE;

        $this->imgfilepickerattrs = array('maxfiles' => 1,
                                       'subdirs' => false,
                                       'maxbytes' => $COURSE->maxbytes,
                                       'accepted_types' => array('.jpg', '.gif', '.png'));

        parent::__construct($url, $block, $page);
    }

    protected function specific_definition($mform) {
        global $DB, $COURSE, $CFG;

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('generalsettings', 'block_userquiz_monitor'));

        $label = get_string('configtrainingprogramname', 'block_userquiz_monitor');
        $mform->addElement('text', 'config_trainingprogramname', $label);
        $mform->setType('config_trainingprogramname', PARAM_CLEANHTML);

        $quizzes = $DB->get_records('quiz', array('course' => $COURSE->id), 'id', 'id,name');

        $categorymenu = array();
        /*
         * Get all categories in course context or higher : There is no sense to dig down in a specific quiz context as
         * question banks may not ne shared between quiz instances.
         */
        $categorymenu = block_userquiz_monitor_get_categories_for_root();
        asort($categorymenu);

        $label = get_string('configrootcategory', 'block_userquiz_monitor');
        $mform->addElement('select', 'config_rootcategory', $label, $categorymenu);
        $mform->setType('config_rootcategory', PARAM_INT);
        $mform->addHelpButton('config_rootcategory', 'configrootcategory', 'block_userquiz_monitor');

        $label = get_string('configinformationpageid', 'block_userquiz_monitor');
        $mform->addElement('text', 'config_informationpageid', $label);
        $mform->setType('config_informationpageid', PARAM_INT);

        $label = get_string('configrateaserie', 'block_userquiz_monitor');
        $mform->addElement('text', 'config_rateAserie', $label);
        $mform->setType('config_rateAserie', PARAM_INT);

        $label = get_string('configcoloraserie', 'block_userquiz_monitor');
        $mform->addElement('text', 'config_colorAserie', $label);
        $mform->setType('config_colorAserie', PARAM_TEXT);
        $mform->setDefault('config_colorAserie', '#C00000');

        if (block_userquiz_monitor_supports_feature('series/dualseries')) {
            include_once($CFG->dirroot.'/blocks/userquiz_monitor/pro/edit_form.php');
            \block_userquiz_monitor\edit_form_pro_extensions::add_dualseries_settings(&$mform);
        }
        if (block_userquiz_monitor_supports_feature('question/protection')) {
            include_once($CFG->dirroot.'/blocks/userquiz_monitor/pro/edit_form.php');
            \block_userquiz_monitor\edit_form_pro_extensions::add_question_protection_settings(&$mform)
        }

        // Configuration for training system.
        $mform->addElement('header', 'configheader1', get_string('trainingsettings', 'block_userquiz_monitor'));

        $label = get_string('configtrainingenabled', 'block_userquiz_monitor');
        $mform->addElement('advcheckbox', 'config_trainingenabled', $label);

        $label = get_string('configtrainingdefault', 'block_userquiz_monitor');
        $option = get_string('trainingisdefault', 'block_userquiz_monitor');
        $mform->addElement('radio', 'config_examdefault', $label, $option, 0);

        // Get quizzes list.
        $quizzeslist = $DB->get_records_menu('quiz', array('course' => $COURSE->id), 'name', 'id,name');

        $label = get_string('configtest', 'block_userquiz_monitor');
        $select = $mform->addElement('select', 'config_trainingquizzes', $label, $quizzeslist);
        $select->setMultiple(true);

        if (block_userquiz_monitor_supports_feature('monitor/histories')) {
            include_once($CFG->dirroot.'/blocks/userquiz_monitor/pro/edit_form.php');
            \block_userquiz_monitor\edit_form_pro_extensions::add_monitor_history_settings(&$mform, 'training');
        }

        // Configuration for exam system.

        $mform->addElement('header', 'configheader2', get_string('examsettings', 'block_userquiz_monitor'));

        $label = get_string('configexamenabled', 'block_userquiz_monitor');
        $mform->addElement('advcheckbox', 'config_examenabled', $label);

        $label = get_string('configexamdefault', 'block_userquiz_monitor');
        $option = get_string('examisdefault', 'block_userquiz_monitor');
        $mform->addElement('radio', 'config_examdefault', $label, $option, 1);

        $label = get_string('configexamtab', 'block_userquiz_monitor');
        $mform->addElement('text', 'config_examtab', $label);
        $mform->setType('config_examtab', PARAM_TEXT);

        $label = get_string('configexamalternatecaption', 'block_userquiz_monitor');
        $mform->addElement('text', 'config_examalternatecaption', $label);
        $mform->setType('config_examalternatecaption', PARAM_TEXT);

        $label = get_string('configexam', 'block_userquiz_monitor');
        $select = $mform->addElement('select', 'config_examquiz', $label, $quizzeslist);

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $label = get_string('configexaminstructions', 'block_userquiz_monitor');
        $mform->addElement('editor', 'config_examinstructions', $label, null, $editoroptions);
        $mform->setType('config_examinstructions', PARAM_CLEANHTML);

        $label = get_string('configshowdetailedresults', 'block_userquiz_monitor');
        $mform->addElement('advcheckbox', 'config_examshowdetails', $label, '', 1);

        if (block_userquiz_monitor_supports_feature('monitor/histories')) {
            include_once($CFG->dirroot.'/blocks/userquiz_monitor/pro/edit_form.php');
            \block_userquiz_monitor\edit_form_pro_extensions::add_monitor_history_settings(&$mform, 'exam');
        }

        // Other configurations.

        $mform->addElement('header', 'configheader3', get_string('graphicassets', 'block_userquiz_monitor'));

        $rendereroptions = array('html' => get_string('fullhtml', 'block_userquiz_monitor'),
                            'gd' => get_string('gd', 'block_userquiz_monitor'),
                            'flash' => get_string('flash', 'block_userquiz_monitor'));
        if (is_dir($CFG->dirroot.'/local/vflibs')) {
            $rendereroptions['jqw'] = get_string('jqw', 'block_userquiz_monitor');
        }
        $label = get_string('configgaugerenderer', 'block_userquiz_monitor');
        $select = $mform->addElement('select', 'config_gaugerenderer', $label, $rendereroptions);
        $mform->setDefault('config_gaugerenderer', 'jqw');

        if (block_userquiz_monitor_supports_feature('icon/customisation')) {
            include_once($CFG->dirroot.'/blocks/userquiz_monitor/pro/edit_form.php');
            \block_userquiz_monitor\edit_form_pro_extensions::add_icon_customisation_settings($mform);
        }

        $label = get_string('localcss', 'block_userquiz_monitor');
        $mform->addElement('textarea', 'config_localcss', $label, array('rows' => 10, 'cols' => 60));
        $mform->setType('config_localcss', PARAM_TEXT);
    }

    public function set_data($defaults) {
        global $COURSE;

        if (!empty($this->block->config) && is_object($this->block->config)) {

            /*
             * As being in a block configuration standard editor preparation cannot be implemented
             * as usual because of the expected name of the configuration attribute.
             */
            $text = '';
            if (!empty($this->block->config->examinstructions)) {
                if (is_array($this->block->config->examinstructions)) {
                    $text = $this->block->config->examinstructions['text'];
                } else {
                    $text = $this->block->config->examinstructions;
                }
                $draftideditor = file_get_submitted_draft_itemid('config_examinstructions');
            }
            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }
            $text = $defaults->config_examinstructions['text'] = file_prepare_draft_area($draftideditor, $this->block->context->id,
                                                                                 'block_userquiz_monitor', 'examinstructions',
                                                                                 0, array('subdirs' => true), $currenttext);
            $defaults->config_examinstructions['itemid'] = $draftideditor;
            $defaults->config_examinstructions['format'] = FORMAT_HTML;
        } else {
            $text = '';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely.
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        // Have to delete examinstruction here, otherwise parent::set_data will empty content of editor.
        unset($this->block->config->examinstructions);

        $context = context_block::instance($this->block->instance->id);

        if (block_userquiz_monitor_supports_feature('icon/customisation')) {
            include_once($CFG->dirroot.'/blocks/userquiz_monitor/pro/edit_form.php');
            \block_userquiz_monitor\edit_form_pro_extensions::set_data($defaults, $context);
        }

        parent::set_data($defaults);

        // Restore text.
        if (!isset($this->block->config)) {
            $this->block->config = new StdClass();
        }
        $this->block->config->examinstructions = $text;

        if (isset($title)) {
            // Reset the preserved title.
            $this->block->config->title = $title;
        }
    }
}
