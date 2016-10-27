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

function call_progress_bar($testdata, $data) {
    global $CFG;

    $data['boxwidth'] = $data['boxwidth'] + 40; // Just to add some inner margin.
    $data['boxheight'] = $data['boxheight'] + 5;

    $progressbarid = 'progress_bar'.$data['id'];
    $progressbarname = 'progress_bar'.$data['id'];

    $str = '';
    $str .= '<script type="text/javascript" id="js_'.$progressbarid.'">';
    $str .= 'setupProgressBar('.$data['boxwidth'].', '.$data['boxheight'].', \''.$progressbarid.'\', \''.$progressbarname.'\', \''.$testdata.'\')';
    $str .= '</script>';

    $str .= '<noscript>';
    $swfurl = $CFG->wwwroot.'/blocks/userquiz_monitor/generators/progress_bar.swf';
    $str .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
            id="'.$progressbarid.'"
            width="'.$data['boxwidth'].'"
            height="'.$data['boxheight'].'"
            codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">
            <param name="movie" value="'.$swfurl.'" />
            <param name="quality" value="high" />
            <param name="wmode" value="transparent"/>
            <param name="bgcolor" value="#ffffff" />
            <param name="scale" value="exactfit" />
            <param name="allowScriptAccess" value="sameDomain" />';
    $str .= '<embed src="'.$swfurl.'"
            quality="high"
            bgcolor="#ffffff"
            width="'.$data['boxwidth'].'"
            height="'.$data['boxheight'].'"
            name="'.$progressbarname.'"
            align="middle"
            play="true"
            loop="false"
            quality="high"
            scale="exactfit"
            allowScriptAccess="sameDomain"
            type="application/x-shockwave-flash"
            pluginspage="http://www.adobe.com/go/getflashplayer"></embed>';
    $str .= '</object>';
    $str .= '</noscript>';

    return $str;
}


function call_progress_bar_html($testdata, $data) {
    global $CFG;

    $data['boxwidth'] = $data['boxwidth'] + 40; // Just to add some inner margin.
    $data['boxheight'] = $data['boxheight'] + 5;
    $str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
                id="progress_bar'.$data['id'].'"
                width="'.$data['boxwidth'].'"
                height="'.$data['boxheight'].'"
                codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">';
    $swfurl = $CFG->wwwroot.'/blocks/userquiz_monitor/generators/progress_bar.swf';
    $str .= '<param name="movie" value="'.$swfurl.'" />';
    $str .= '<param name="quality" value="high" />';
    $str .= '<param name="swliveconnect" value="true" />';
    $str .= '<param name="bgcolor" value="#ffffff" />';
    $str .= '<param name="wmode" value="transparent"/>';
    $str .= '<param name="flashvars" value="data={$testdata}" />';
    $str .= '<param name="allowScriptAccess" value="sameDomain" />';
    $str .= '<param name="scale" value="noborder" />';
    $str .= '<embed src="'.$swfurl.'"
               width="'.$data['boxwidth'].'"
               height="'.$data['boxheight'].'"
               align="middle"
               wmode="transparent"
               id="progress_bar'.$data['id'].'"
               quality="high"
               bgcolor="#ffffff"
               name="progress_bar'.$data['id'].'"
               flashvars="data='.$testdata.'"
               swliveconnect="true"
               allowScriptAccess="sameDomain"
               pluginspage="http://www.adobe.com/go/getflashplayer"
               type="application/x-shockwave-flash" >';
    $str .= '</embed>';
    $str .= '</object>';

    return $str;
}
