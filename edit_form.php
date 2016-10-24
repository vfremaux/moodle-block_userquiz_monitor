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

    protected function specific_definition($mform) {
        global $DB, $COURSE;

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('generalsettings', 'block_userquiz_monitor'));

        $mform->addElement('text', 'config_trainingprogramname', get_string('configtrainingprogramname', 'block_userquiz_monitor'));
        $mform->setType('config_trainingprogramname', PARAM_CLEANHTML);

        $quizzes = $DB->get_records('quiz', array('course' => $COURSE->id), 'id', 'id,name');
        if (!empty($quizzes)) {
            /*
             * Get all categories in course context or higher : There is no sense to dig down in a specific quiz context as
             * question banks may not ne shared between quiz instances.
             */
            $categorymenu = block_userquiz_monitor_get_categories_for_root();

        }

        $label = get_string('configinformationpageid', 'block_userquiz_monitor');
        $mform->addElement('text', 'config_informationpageid', $label);
        $mform->setType('config_informationpageid', PARAM_INT);

        $label = get_string('configrateAserie', 'block_userquiz_monitor');
        $mform->addElement('text', 'config_rateAserie', $label);
        $mform->setType('config_rateAserie', PARAM_INT);

        $label = get_string('configdualserie', 'block_userquiz_monitor');
        $mform->addElement('advcheckbox', 'config_dualserie', $label);
        $mform->setType('config_dualserie', PARAM_BOOL);

        $label = get_string('configrateCserie', 'block_userquiz_monitor');
        $mform->addElement('text', 'config_rateCserie', $label);
        $mform->disabledIf('config_rateCserie', 'config_dualserie', 0);
        $mform->setType('config_rateCserie', PARAM_INT);

        // Configuration for training system.

        $mform->addElement('header', 'configheader1', get_string('trainingsettings', 'block_userquiz_monitor'));

        $label = get_string('configtrainingenabled', 'block_userquiz_monitor');
        $mform->addElement('advcheckbox', 'config_trainingenabled', $label);

        // Get quizzes list.
        $quizzeslist = $DB->get_records_menu('quiz', array('course' => $COURSE->id), 'name', 'id,name');

        $label = get_string('configtest', 'block_userquiz_monitor');
        $select = $mform->addElement('select', 'config_trainingquizzes', $label, $quizzeslist);
        $select->setMultiple(true);

        $label = get_string('configrootcategory', 'block_userquiz_monitor');
        $mform->addElement('select', 'config_rootcategory', $label, $categorymenu);
        $mform->setType('config_rootcategory', PARAM_INT);

        // Configuration for exam system.

        $mform->addElement('header', 'configheader2', get_string('examsettings', 'block_userquiz_monitor'));

        $label = get_string('configexamenabled', 'block_userquiz_monitor');
        $mform->addElement('advcheckbox', 'config_examenabled', $label);

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

        $label = get_string('configdirectreturn', 'block_userquiz_monitor');
        $mform->addElement('advcheckbox', 'config_directreturn', $label);

        $label = get_string('configexamhidescoringinterface', 'block_userquiz_monitor');
        $mform->addElement('advcheckbox', 'config_examhidescoringinterface', $label);

        $label = get_string('configexamdeadend', 'block_userquiz_monitor');
        $mform->addElement('advcheckbox', 'config_examdeadend', $label);

    }

    function set_data($defaults, &$files = null) {

        if (!empty($this->block->config) && is_object($this->block->config)) {
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
            $defaults->config_examinstructions['text'] = file_prepare_draft_area($draftideditor, $this->block->context->id,
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

        // Have to delete text here, otherwise parent::set_data will empty content of editor.
        unset($this->block->config->examinstructions);
        parent::set_data($defaults, $files);
        // Restore text.
        if (!isset($this->block->config)){
            $this->block->config = new StdClass();
        }
        $this->block->config->examinstructions = $text;

        if (isset($title)) {
            // Reset the preserved title.
            $this->block->config->title = $title;
        }
    }
}
