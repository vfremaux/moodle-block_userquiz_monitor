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
 *    Display examination's monitor , it provides a dashboard
 */

// Include files.

require("../../config.php");
require_once($CFG->dirroot.'/blocks/userquiz_monitor/block_userquiz_monitor_lib.php');

// Get display's mode.

$mode = required_param('mode', PARAM_TEXT);

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$renderer = $PAGE->get_renderer('block_userquiz_monitor');

if ($mode == 'displayhist') {

    // Define histogram's parameters.
    $height = 400; // Unit : px ; Define the graph's height.
    $width = 600; // Unit : px ; Define the graph's width.
    $stopA = 85; // Unit : %.
    $stopC = 75; // Unit : %.
    $response = '';
    $param = required_param('param', PARAM_TEXT); 
    $datetype = required_param('datetype', PARAM_TEXT); 

    $param = urldecode($param);
    $param= stripslashes($param);
    $attempts = json_decode($param, true) ;

    if (empty($attempts)) {
        echo(get_string('nohist', 'block_userquiz_monitor'));
    } else {
        $cpt = 0;
        foreach ($attempts as $attempt) {

            if (!empty($attempt['attempttimefinish'])) {

                if ($attempt['nbquestionsA'] != 0) {
                    $graphheightA = round(($attempt['cptgoodanswersA']/$attempt['nbquestionsA'])*100);
                } else {
                    $graphheightA = 0;
                }

                if ($attempt['nbquestionsC'] != 0) {
                    $graphheightC =  round(($attempt['cptgoodanswersC']/$attempt['nbquestionsC'])*100);
                } else {
                    $graphheightC = 0;
                }

                $day = date('j', $attempt['attempttimefinish']);

                switch ($day) {
                    case 1:
                        $day = "01";
                        break;

                    case 2:
                        $day = "02";
                        break;

                    case 3:
                        $day = "03";
                        break;

                    case 4:
                        $day = "04";
                        break;

                    case 5:
                        $day = "05";
                        break;

                    case 6:
                        $day = "06";
                        break;

                    case 7:
                        $day = "07";
                        break;

                    case 8:
                        $day = "08";
                        break;

                    case 9:
                        $day = "09";
                        break;
                }

                if ($datetype == "short") {
                        $testdate =  $day .
                            '/'.date('m', $attempt['attempttimefinish']) .
                            '/'.date('Y', $attempt['attempttimefinish']);
                } else {
                    $testdate =  $day.'/'.date('m/Y H:i', $attempt['attempttimefinish']).']';
                }

                $result = array( 'graphheightA' => $graphheightA,
                                 'graphheightC' => $graphheightC,
                                 'date' =>  $testdate);
                $results[] = $result;
            }
            $cpt++;
        }

        if ($cpt >= 9) {
            $i = 0;
            $progressbar = null;
            $resultsbis = null;
            $j = 0;
            while ($i < $cpt) {
                $resultsbis[] = $results[$i];
                $i++;
                $j++;

                if ($j == 10) {
                    $data = array (
                            'boxheight' => $height,
                            'boxwidth' => $width,
                            'stopA' => $stopA,
                            'stopC' => $stopC,
                            'results' => $resultsbis) ;
                    $progressbar[] = $renderer->histogram($data);
                    unset($resultsbis);
                    $j=0;
                }
            }

            if (!empty($resultsbis)) {
                $data = array (
                        'boxheight' => $height,
                        'boxwidth' => $width,
                        'stopA' => $stopA,
                        'stopC' => $stopC,
                        'results' => $resultsbis) ;
                $progressbar[] = $renderer->histogram($data);
            }

            if (!empty($resultsbis) && !empty($progressbar)) {
                foreach ($progressbar as $histgraph) {
                    $response .= '<div>'.$histgraph.'</div>'; 
                }
            }
            echo($response);
        } else {
            $data = array (
                'boxheight' => $height,
                'boxwidth' => $width,
                'stopA' => $stopA,
                'stopC' => $stopC,
                'results' => $results) ;
            $progressbar = $renderer->histogram($data);
            $response = $progressbar;
            echo($response);
        }
    }
}

if ($mode == 'displaysubcategories') {

    // Get quiz id.
    $response = '<head>';
    $response.=     '<link rel="stylesheet" type="text/css" href="'.$CFG->wwwroot.'/blocks/userquiz_monitor/styles.css"/>';
    $response.= '</head>';
    $response.= '<body>';
    $quizid = $_GET['quizid'];
    $id = required_param('categoryid', PARAM_TEXT); 
    $response.= get_subcategories($id, $quizid);
    $response.= '</body>';
    echo($response);
}