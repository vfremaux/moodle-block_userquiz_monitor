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

function get_schedule($thebock) {
    global $CFG, $COURSE, $PAGE;

    if (!empty($theblocks->config->rootcategory)) {
        $PAGE->requires->js('/blocks/userquiz_monitor/js/block_js.js');

        $response =         '<table class="trans100" style="margin-bottom:20px; width:100%;">
                                <tr>
                                    <td>
                                        <div style="margin-bottom:20px; margin-left:5px; width:100%;">
                                            <p>'.get_string('selectschedule', 'block_userquiz_monitor', $theblock->config->trainingprogramname).'</p>';

        for ($i = 0; $i < 12; $i++) {
            $class = ($i == 0) ? 'active' : 'inactive';
            $response .= '<a id="amfcat'.$i.'" onClick="refreshcontent('.$COURSE->id.', '.$theblock->config->rootcategory.', '.$i.')" '.$class.' >&nbsp '.($i+1).' &nbsp</a>';
        }

        $response .=                     '</div>
                                        <div id="divschedule" style="margin-top:20px; width:100%;">
                                        </div>
                                    </td>
                                </tr>
                            </table>';
        $response .=         '<script type="text/javascript">refreshcontent('.$COURSE->id.', '.$theblock->config->rootcategory.', 0);</script>';

        return($response);
    }
}

