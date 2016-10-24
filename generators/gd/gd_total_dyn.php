<?php

	include "../../../../config.php";

    $test = optional_param('test', 0, PARAM_INT);
    if (!$test)
	    header("Content-type: image/png");
  
    // Searching for font files

    // output special situations messages 

    $imageWidth = 340;
    $imageHeight = 51;
    
    $stop = required_param('stop', PARAM_TEXT);
    $skin = required_param('skin', PARAM_TEXT);
    $background = $CFG->dirroot.'/blocks/userquiz_monitor/generators/gd/generic_bg_total.png';
    $im = imagecreatefrompng($background);

    $threshold = $CFG->dirroot.'/blocks/userquiz_monitor/generators/gd/generic_threshold_total.png';
    $im2 = imagecreatefrompng($threshold);

    // imageantialias($im, TRUE);

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
    
    $fullbarwidth = 298;
    $barheight = 4;
    $xbarorigin = 19;
    $ybarorigin = 20;
    
    $absbarwidth = ceil(($fullbarwidth * $barwidth) / 100);
    // $barwidth = $fullbarwidth; // full range test
    
    $absthresholdx = $xbarorigin + ceil((($fullbarwidth * $stop) / 100) - imagesx($im2) / 2);
    $absthresholdy = 12;

	$font = $CFG->dirroot.'/blocks/userquiz_monitor/generators/gd/arial.ttf';
        
    imagefilledrectangle($im, $xbarorigin, $ybarorigin, $absbarwidth + $xbarorigin, $ybarorigin + $barheight, $skincolor);
	imagefttext($im , 9, 0, 10, 43, $colors['darkgray'], $font , $barwidth.' %');
	
	// add threshold
	imagecopy($im, $im2, $absthresholdx, $absthresholdy, 0, 0, imagesx($im2), imagesy($im2));
	imagefttext($im , 9, 0, $absthresholdx + 14, 43, $colors['darkgray'], $font , $stop.' %');

  	// delivering image
  	imagepng($im);
  	imagedestroy($im);
?>