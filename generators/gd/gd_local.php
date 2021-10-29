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
require("../../../../config.php");

header("Content-type: image/png");

// Searching for font files.

// Output special situations messages.

$stop = required_param('stop', PARAM_TEXT);
$skin = required_param('skin', PARAM_TEXT);
$background = $CFG->dirroot.'/blocks/userquiz_monitor/generators/gd/localbackground'.$stop.'.png';

$im = imagecreatefrompng($background);

// Drawing bar.
$colors['blue'] = imagecolorallocate($im, 0, 0, 180);
$colors['red'] = imagecolorallocate($im, 180, 0, 0);
$colors['darkgray'] = imagecolorallocate($im, 40, 40, 40);

switch ($skin) {
    case 'A':
        $skincolor = $colors['red'];
        break;

    case 'C':
        $skincolor = $colors['blue'];
        break;
}

$barwidth = required_param('barwidth', PARAM_INT);

$fullbarwidth = 160;
$barheight = 3;
$xbarorigin = 14;
$ybarorigin = 15;

$absbarwidth = ceil(($fullbarwidth * $barwidth) / 100);

$font = $CFG->dirroot.'/blocks/userquiz_monitor/generators/gd/arial.ttf';

imagefilledrectangle($im, $xbarorigin, $ybarorigin, $absbarwidth + $xbarorigin, $ybarorigin + $barheight, $skincolor);
imagefttext($im , 9, 0, 10, 38, $colors['darkgray'], $font , $barwidth.' %');

// Delivering image.
imagepng($im);
imagedestroy($im);
