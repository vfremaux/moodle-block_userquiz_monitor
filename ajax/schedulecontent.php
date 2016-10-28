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

header('ContentType: text/html; charset=UTF-8');

// Init variables.
$response = '';
$cpt = 0;

$rootcategory = optional_param('rootcategory', null, PARAM_INT);
$id = optional_param('id', null, PARAM_INT);

if (is_null($rootcategory) || is_null($id)) {
    echo $response;
    die;
}

// Get id, name of the categories.
$sqlselectcategories = "
    SELECT
        id,
        name
    FROM
        {question_categories}
    WHERE
        parent = ?
    ORDER BY
        sortorder,id
";

// Get questions types from subcategories.
$questiontypesubsql = "
    SELECT DISTINCT
        (defaultgrade)
    FROM
        {question}
    WHERE
        category = ?
    ORDER BY
        defaultgrade
";

// Build the stucture of the reporting.
$recordsgetcategories = $DB->get_records_sql($sqlselectcategories, array($rootcategory));

if (!empty($recordsgetcategories)) {
    foreach ($recordsgetcategories as $recordsgetcategory) {

        if ($cpt == $id) {
            $response .= '<table class="test_report">';
            $response .= '<tr>';
            $response .= '<td>';
            $response .= '<div>';
            $response .= '<table class="test_report">';
            $response .= '<tr>';
            $response .= '<td>';
            $response .= '<p>';
            $response .= '<b><u>'.$recordsgetcategory->name.'</u></b>';
            $response .= '</p>';
            $response .= '</td>';
            $response .= '</tr>';
            $response .= '</table>';
            $response .= '</div>';

            $subcategories = $DB->get_records_sql($sqlselectcategories, array($recordsgetcategory->id));

            if (!empty($subcategories)) {
                foreach ($subcategories as $recordgetsubcategory) {
                    $recordsquestionstypesubcategory = $DB->get_records_sql($questiontypesubsql, array($recordgetsubcategory->id));

                    $type = '';
                    foreach ($recordsquestionstypesubcategory as $questiontype) {
                        if ($questiontype->defaultgrade == 1000) {
                            $type = 'C';
                        } else {
                            $type = 'A';
                        }
                    }

                    $response .= '<div style="width:100%;">';
                    $response .= '<table style="margin-left:10px;">';
                    $response .= '<tr>';
                    $response .= '<td>';
                    $response .= '<p>';
                    $response .= '<b>';
                    $response .= $recordgetsubcategory->name;
                    $response .= '</b>';
                    $response .= '</p>';
                    $response .= '</td>';
                    $response .= '</tr>';
                    $response .= '</table>';
                    $response .= '<table style="margin-left:20px;width:95%;">';
                    $response .= '<tr>';
                    $response .= '<td>';
                    $response .= '<p>';
                    $response .= $recordgetsubcategory->info;
                    $response .= '</p>';
                    $response .= '</td>';
                    $response .= '</tr>';
                    $response .= '</table>';
                    $response .= '</div>';
                }
            }
            $response .= '</td>';
            $response .= '</tr>';
            $response .= '</table>';
        }
        $cpt++;
    }
}

echo($response);