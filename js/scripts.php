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
defined('MOODLE_INTERNAL') || die();

/**
 * Provides all the necessary javascript in order to manage the dashbord.
 */
function get_js_scripts($categories) {
    global $CFG, $COURSE;

    // Init.
    $script = '';
    $j = 0;

    $runteststr = get_string('runtest', 'block_userquiz_monitor');
    $title = '<h1>'.$runteststr.'</h1>';
    $button = '<table class="tablemonitorcategorycontainer">';
    $button .= '<tr>';
    $button .= '<td>';
    $button .= $title;
    $button .= '<p>'.get_string('runtraininghelp', 'block_userquiz_monitor').'</p>';
    $button .= '</td>';
    $button .= '</tr>';
    $button .= '<tr>';
    $button .= '<td>';
    $button .= '<input type="hidden" name="mode" value="test"/>';
    $button .= '<input type="hidden" name="courseid" value="'.$COURSE->id.'"/>';
    $button .= '<input type="submit" id="submit"     value="'.$runteststr.'" disabled="true"/>';
    $button .= '</td>';
    $button .= '</tr>';
    $button .= '</table>';

    // Include script which manages ajax.
    $script .= '<script type="text/javascript" src="'.$CFG->wwwroot.'/blocks/userquiz_monitor/js/block_js.js"></script>';

    // Categories id container.
    $script.= "<script type=\"text/javascript\">\n";
    $script.= "var idcategoriespl = new Array();\n";
    foreach ($categories as $categoryid) {
        if ($categoryid == 0) {
            continue; // Weird bug on Credit à la consommation .. Root Category issue.
        }
        $script .= "idcategoriespl[$j] = ".$categoryid.";\n";
        $j++;
    }
    $script .= "</script>\n";

    return $script;
}
