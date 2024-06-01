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
require('../../../config.php');

require_once($CFG->dirroot.'/blocks/userquiz_monitor/import/import_form.php');

$id = required_param('id', PARAM_INT); // Course id.
$blockid = required_param('blockid', PARAM_INT); // Block id.

$url = new moodle_url('/blocks/userquiz_monitor/import/import.php', ['id' => $id, 'blockid' => $blockid]);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
$instance = $DB->get_record('block_instances', ['id' => $blockid], '*', MUST_EXIST);

$context = context_course::instance($id);
require_login($course);
require_capability('moodle/course:manageactivities', $context);

$uqminstance = block_instance('userquiz_monitor', $instance);
if (empty($uqminstance->config->rootcategory)) {
    throw new moodle_exception("Root category has not been setup for this Userquiz Monitor block instance. We cannot import.");
}

$rootcategory = $DB->get_record('question_categories', ['id' => $uqminstance->config->rootcategory], '*', MUST_EXIST);

$PAGE->set_url($url);
$PAGE->set_heading(get_string('questionimport', 'block_userquiz_monitor'));

$mform = new ImportForm($url, ['rootcategory' => $rootcategory]);

$courseurl = new moodle_url('/course/view.php?', ['id' => $id]);

if ($mform->is_cancelled()) {
    redirect($courseurl);
}

if ($data = $mform->get_data()) {

    if (!in_array($data->importformat, ['fd', 'fden', 'amf'])) {
        throw new moodle_exception("Not acceptable import format");
    }

    // process data and imported file.
    $formatfile = $CFG->dirroot.'/blocks/userquiz_monitor/import/format/'.$data->importformat.'_format.class.php';

    if (!file_exists($formatfile)) {
        throw new coding_exception("Format file missing.");
    }

    include_once($formatfile);

    $formatclass = '\\block_userquiz_monitor\\import\\'.$data->importformat.'_format';
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    $receivedfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $data->importfile, 'itemid, filepath, filename', false);
    if (!empty($receivedfiles)) {
        $receivedfile = array_shift($receivedfiles); // Take first one
        $importer = new $formatclass($receivedfile, $uqminstance, $id);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('importquestions', 'block_userquiz_monitor'));
        echo "<pre>";

        $options = [
            'simulate' => $data->simulate,
            'forcecreatecategories' => $data->forcecreatecategories,
            'verbose' => 1,
            'replaceall' => $data->replaceall
        ];
        $importer->import($options);
        echo "</pre>";

        if (isset($importer->fileresult)) {
            echo $OUTPUT->heading(get_string('result', 'block_userquiz_monitor'));

            $contextid = context_system::instance()->id;
            $component = $importer->resultfile->get_component();
            $area = $importer->resultfile->get_filearea();
            $itemid = $importer->resultfile->get_itemid();
            $pathname = $importer->resultfile->get_filepath();
            $filename = $importer->resultfile->get_filename();
            $resulturl = moodle_url::make_pluginfile_url($contextid, $component, $area, $itemid, $pathname, $filename, true);
            $params = ['href' => $resulturl];
            echo $OUTPUT->tag('a', $importer->resultfile->get_filename(), $params);
        }

        echo $OUTPUT->continue_button($courseurl);
        echo $OUTPUT->footer();
        die;
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->notification('nofile', 'block_userquiz_monitor');
        echo $OUTPUT->continue_button($courseurl);
        echo $OUTPUT->footer();
        die;
    }
}

$formdata = new StdClass;
$formdata->blockid = $blockid;
$formdata->id = $id;
$mform->set_data($formdata);

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
