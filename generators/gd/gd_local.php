<?php

	include "../../../../config.php";

    header("Content-type: image/png");
  
    // Searching for font files

    // output special situations messages 

    $imageWidth = 199;
    $imageHeight = 44;
    
    $stop = required_param('stop', PARAM_TEXT);
    $skin = required_param('skin', PARAM_TEXT);
    $background = $CFG->dirroot.'/blocks/userquiz_monitor/generators/gd/localbackground'.$stop.'.png';

    $im = imagecreatefrompng($background);
    // imageantialias($im, FALSE);

    // drawing bar
    $colors['blue'] = imagecolorallocate($im, 0, 0, 180);
    $colors['red'] = imagecolorallocate($im, 180, 0, 0);
    $colors['darkgray'] = imagecolorallocate($im, 40, 40, 40);
    
    switch($skin){
    	case 'A':{
    		$skincolor = $colors['red'];
    		break;
    	}
    	case 'C':{
    		$skincolor = $colors['blue'];
    		break;
    	}
    }
    
    $barwidth = required_param('barwidth', PARAM_INT);
    
    $fullbarwidth = 160;
    $barheight = 3;
    $xbarorigin = 14;
    $ybarorigin = 15;
    
    $absbarwidth = ceil(($fullbarwidth * $barwidth) / 100);
    // $barwidth = $fullbarwidth; // full range test

	$font = $CFG->dirroot.'/blocks/userquiz_monitor/generators/gd/arial.ttf';
    
    imagefilledrectangle($im, $xbarorigin, $ybarorigin, $absbarwidth + $xbarorigin, $ybarorigin + $barheight, $skincolor);
	imagefttext($im , 9, 0, 10, 38, $colors['darkgray'], $font , $barwidth.' %');

  	// delivering image
  	imagepng($im);
  	imagedestroy($im);
?>