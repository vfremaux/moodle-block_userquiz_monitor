<?php

/**
*    Provides all the necessary javascript in order to manage the dashbord
*
*/
function get_js_scripts($categories) {
    // Get global variables
    global $USER, $CFG, $COURSE;

    // Init
    $script = '';
    $j = 0;

    $runteststr = get_string('runtest', 'block_userquiz_monitor');
    $title = '<h1>'.$runteststr.'</h1>';
    $button =        '<table class="tablemonitorcategorycontainer">';
    $button .=            '<tr>';
    $button .=                '<td>';
    $button .=                     $title;
    $button .=                    '<p>'.get_string('runtraininghelp', 'block_userquiz_monitor').'</p>';
    $button .=                '</td>';
    $button .=            '</tr>';
    $button .=            '<tr>';
    $button .=                '<td>';
    $button .=                    '<input type="hidden" name="mode" value="test"/>';
    $button .=                    '<input type="hidden" name="courseid" value="'.$COURSE->id.'"/>';
    $button .=                    '<input type="submit" id="submit"     value="'.$runteststr.'" disabled="true"/>';
    $button .=                '</td>';
    $button .=            '</tr>';
    $button .=        '</table>';

    // Include script which manages ajax
    $script.= '<script type="text/javascript" src="'.$CFG->wwwroot.'/blocks/userquiz_monitor/js/block_js.js"></script>';

    // Categories id container
    $script.=  "<script type=\"text/javascript\">\n";
    $script.=        "var idcategoriespl = new Array();\n";
    foreach($categories as $categoryid) {
        if ($categoryid == 0) continue; // weird bug on Credit à la consommation .. Root Category issue
        $script.=    "idcategoriespl[$j] = ".$categoryid.";\n" ;
        $j++;
    }
    $script.=    "</script>\n";

    return $script;
}
