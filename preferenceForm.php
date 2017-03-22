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

require_once($CFG->libdir.'/formslib.php');

class PreferenceForm extends moodleform {

    public function definition() {
        global $COURSE, $DB;

        $mform =& $this->_form;

        $mform->disable_form_change_checker();

        $mform->addElement('hidden', 'id', $COURSE->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);

        $mform->addElement('hidden', 'selectedview');
        $mform->setType('selectedview', PARAM_TEXT);

        $mform->addElement('html', get_string('resultsdepthdesc', 'block_userquiz_monitor'));
        if (!empty($this->_customdata['blockconfig']->trainingenabled) && ($this->_customdata['mode'] == 'training')) {
            $options = array('0' => get_string('optnofilter', 'block_userquiz_monitor'),
                '1' => get_string('optoneweek', 'block_userquiz_monitor'),
                '2' => get_string('opttwoweeks', 'block_userquiz_monitor'),
                '3' => get_string('optthreeweeks', 'block_userquiz_monitor'),
                '4' => get_string('optfourweeks', 'block_userquiz_monitor'),
                '5' => get_string('optfiveweeks', 'block_userquiz_monitor'),
            );
            $attrs = array('onchange' => 'this.form.submit();');
            $mform->addElement('select', 'resultsdepth', get_string('resultsdepth', 'block_userquiz_monitor'), $options, $attrs);
            $mform->addHelpButton('resultsdepth', 'resultsdepth', 'block_userquiz_monitor');
        }

        if (!empty($this->_customdata['blockconfig']->examenabled) && ($this->_customdata['mode'] == 'examination')) {
            $examoptions = array('0' => get_string('optnofilter', 'block_userquiz_monitor'),
                '1' => get_string('optoneexam', 'block_userquiz_monitor'),
                '2' => get_string('opttwoexams', 'block_userquiz_monitor'),
                '3' => get_string('optthreeexams', 'block_userquiz_monitor'),
                '4' => get_string('optfourexams', 'block_userquiz_monitor'),
                '5' => get_string('optfiveexams', 'block_userquiz_monitor'),
            );
            $attrs = array('onchange' => 'this.form.submit();');
            $mform->addElement('select', 'examsdepth', get_string('examsdepth', 'block_userquiz_monitor'), $examoptions, $attrs);
        } else {
            $mform->addElement('hidden', 'examsdepth', 0);
            $mform->setType('examsdepth', PARAM_INT);
        }
    }
}
