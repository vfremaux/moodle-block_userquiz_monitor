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

$test = optional_param('test', 0, PARAM_INT);
if (!$test) {
    header("Content-type: image/png");
}

// Searching for font files.

// Output special situations messages.

$imageWidth = 199;
$imageHeight = 44;

$stop = required_param('stop', PARAM_TEXT);
$skin = required_param('skin', PARAM_TEXT);
$background = $CFG->dirroot.'/blocks/userquiz_monitor/generators/gd/generic_bg_local.png';

$im = imagecreatefrompng($background);

$threshold = $CFG->dirroot.'/blocks/userquiz_monitor/generators/gd/generic_threshold_local.png';
$im2 = imagecreatefrompng($threshold);

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
$xbarorigin = 12;
$ybarorigin = 15;

$absbarwidth = ceil(($fullbarwidth * $barwidth) / 100);

$absthresholdx = $xbarorigin + ceil((($fullbarwidth * $stop) / 100) - imagesx($im2) / 2);
$absthresholdy = 10;

$font = $CFG->dirroot.'/blocks/userquiz_monitor/generators/gd/arial.ttf';

imagefilledrectangle($im, $xbarorigin, $ybarorigin, $absbarwidth + $xbarorigin, $ybarorigin + $barheight, $skincolor);
imagefttext($im , 9, 0, 10, 37, $colors['darkgray'], $font , $barwidth.' %');

// Add threshold.
imagecopy($im, $im2, $absthresholdx, $absthresholdy, 0, 0, imagesx($im2), imagesy($im2));
imagefttext($im , 9, 0, $absthresholdx + 7, 36, $colors['darkgray'], $font , $stop.' %');

// Delivering image.
imagepng($im);
imagedestroy($im);
