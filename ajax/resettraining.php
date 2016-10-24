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
require('../../../config.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/block_userquiz_monitor_lib.php');

header('ContentType: text/html; charset=UTF-8');

// Init variable.

$userid = optional_param('userid', 0, PARAM_INT);
$quizzeslist = optional_param('quizzeslist', '', PARAM_RAW);

if ($userid && !empty($quizzeslist)) {
    $getrecords = null;
    $bexecdelete1 = true;
    $bexecdelete2 = false;
    $quizzeslist = urldecode($quizzeslist);
    $quizzeslistarr = explode(',', $quizzeslist);

    list($insql, $params) = $DB->get_in_or_equal($quizzeslistarr, SQL_PARAMS_QM);

    $sqldeletequestionstates = "
        DELETE
        FROM
          {question_attempt_steps}
        WHERE
          questionattemptid = ?
    ";

    $sqldeleteuserquizattempts = "
        DELETE
        FROM
          {quiz_attempts}
        WHERE
            userid = :userid AND
            quiz $insql
    ";

    $sqluserattempts = "
        SELECT
            id
        FROM
            {quiz_attempts}
        WHERE
            userid = :userid AND
            quiz $insql
    ";

    $params['userid'] = $userid;
    $getrecords = $DB->get_records_sql($sqluserattempts, $params);

    if (!empty($getrecords)) {
        foreach ($getrecords as $record) {
            $bexecdelete = $DB->execute($sqldeletequestionstates, array($record->id));
            if ($bexecdelete == false) {
                $bexecdelete1 = false;
            }
        }

        if ($bexecdelete1 == true) {
            $bexecdelete2 = $DB->execute($sqldeleteuserquizattempts, $params);
        }

        if ($bexecdelete1 == true && $bexecdelete2 == true) {
            $response = get_string('resetinfo1', 'block_userquiz_monitor');
        } else {
            $response = get_string('resetinfo2', 'block_userquiz_monitor');
        }
    } else {
        $response = get_string('resetinfo3', 'block_userquiz_monitor');
    }

    echo($response);
} else {
    echo "missing parameters";
}
