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
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die();

require_once($CFG->dirroot.'/lib/formslib.php');

class ImportForm extends moodleform {

    public $fileoptions;

    public function __construct() {
        parent::__construct();
        $this->fileoptions = [];
    }

    public function definition() {
        global $COURSE;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        $options = ['amf' => get_string('amfxslx', 'block_userquiz_monitor')];
        $mform->addElement('select', 'importformat', get_string('importformat', 'block_userquiz_monitor'), $options);

        $mform->addElement('filepicker', 'importfile', get_string('importfile', 'block_userquiz_monitor'), $this->fileoptions);

        $mform->addElement('advcheckbox', 'replaceall', get_string('replaceall', 'block_userquiz_monitor'));
        $mform->setDefault('replaceall', 0);

        $mform->addElement('advcheckbox', 'simulate', get_string('simulate', 'block_userquiz_monitor'));
        $mform->setDefault('simulate', 0);

        $mform->addElement('advcheckbox', 'forcecreatecategories', get_string('forcecreatecategories', 'block_userquiz_monitor'));
        $mform->setDefault('forcecreatecategories', 0);

        $this->add_action_buttons(true);
    }

}