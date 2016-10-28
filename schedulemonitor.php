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

function get_schedule($theblock) {
    global $COURSE, $PAGE;

    if (!empty($theblock->config->rootcategory)) {
        $PAGE->requires->js('/blocks/userquiz_monitor/js/block_js.js');

        $response = '<table class="trans100" style="margin-bottom:20px; width:100%;">';
        $response .= '<tr>';
        $response .= '<td>';
        $response .= '<div style="margin-bottom:20px; margin-left:5px; width:100%;">';
        $response .= '<p>'.get_string('selectschedule', 'block_userquiz_monitor', $theblock->config->trainingprogramname).'</p>';

        for ($i = 0; $i < 12; $i++) {
            $class = ($i == 0) ? 'active' : 'inactive';
            $jshandler = 'refreshcontent('.$COURSE->id.', '.$theblock->config->rootcategory.', '.$i.')';
            $response .= '<a id="amfcat'.$i.'" onClick="'.$jshandler.'" '.$class.' >&nbsp '.($i + 1).' &nbsp</a>';
        }

        $response .= '</div>';
        $response .= '<div id="divschedule" style="margin-top:20px; width:100%;">';
        $response .= '</div>';
        $response .= '</td>';
        $response .= '</tr>';
        $response .= '</table>';
        $js = 'refreshcontent('.$COURSE->id.', '.$theblock->config->rootcategory.', 0);';
        $response .= '<script type="text/javascript">'.$js.'</script>';

        return $response;
    }
}

