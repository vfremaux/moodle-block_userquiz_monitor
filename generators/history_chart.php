<?php
function call_hist_chart($test_data, $data) {

    global $CFG;

    $response = '<script src="'.$CFG->wwwroot.'/blocks/userquiz_monitor/generators/AC_OETags.js" language="javascript"></script>';
    $response.= '<script src="'.$CFG->wwwroot.'/blocks/userquiz_monitor/generators/history/history.js" language="javascript"></script>';

    $response.= '<style>';
    $response.= '</style>';

    $response.= '<script language="JavaScript" type="text/javascript">';
    $response.=     'var requiredMajorVersion = 9;';
    $response.=     'var requiredMinorVersion = 0;';
    $response.=     'var requiredRevision = 24;';
    $response.= '</script>';
    
    $response.= '<script language="JavaScript" type="text/javascript">';
    $response.=     'var hasProductInstall = DetectFlashVer(6, 0, 65);';
    $response.=     'var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);';
    $response.=     'if (hasProductInstall && !hasRequestedVersion ) {';
    $response.=         'var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";';
    $response.=         'var MMredirectURL = window.location;';
    $response.=         'document.title = document.title.slice(0, 47) + " - Flash Player Installation";';
    $response.=         'var MMdoctitle = document.title;';
    
    $response.=         'AC_FL_RunContent(';
    $response.=             '"src", "playerProductInstall",';
    $response.=             '"FlashVars", "MMredirectURL="+MMredirectURL+\'&MMplayerType=\'+MMPlayerType+\'&MMdoctitle=\'+MMdoctitle+"",';
    $response.=             '"width", "100%",';
    $response.=             '"height", "100%",';
    $response.=             '"align", "middle",';
    $response.=             '"id", "history_chart",';
    $response.=             '"quality", "high",';
    $response.=             '"bgcolor", "#ffffff",';
    $response.=             '"name", "history_chart",';
    $response.=                 '"allowScriptAccess","sameDomain",';
    $response.=             '"type", "application/x-shockwave-flash",';
    $response.=             '"pluginspage", "http://www.adobe.com/go/getflashplayer"';
    $response.=         ');';
    $response.=     '} else if (hasRequestedVersion) {';
    $response.=         'AC_FL_RunContent(';
    $response.=             '"src", "'.$CFG->wwwroot.'/blocks/userquiz_monitor/generators/history_chart",';
    $response.=             '"width", "'.($data['boxwidth']+100).'",';
    $response.=             '"height", "'.($data['boxheight']+100).'",';
    $response.=             '"align", "middle",';
    $response.=             '"id", "history_chart",';
    $response.=             '"quality", "high",';
    $response.=             '"bgcolor", "#ffffff",';
    $response.=             '"name", "history_chart",';
    $response.=             '"FlashVars","data='.$test_data.'",';
    $response.=             '"allowScriptAccess","sameDomain",';
    $response.=             '"type", "application/x-shockwave-flash",';
    $response.=             '"pluginspage", "http://www.adobe.com/go/getflashplayer"';
    $response.=         ');';
    $response.=     '} else {';
    $response.=         'var alternateContent = \'Alternate HTML content should be placed here. \'';
    $response.=         '+ \'This content requires the Adobe Flash Player. \'';
    $response.=         '+ \'<a href=http://www.adobe.com/go/getflash/>Get Flash</a>\';';
    $response.=         'document.write(alternateContent)';
    $response.=     ' }';
    $response.= '</script>';
    $response.= '<noscript>';
    $response.=     '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"';
    $response.=         'id="history_chart"'; 
    $response.=         'codebase="http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab">';
    $response.=         '<param name="movie" value="'.$CFG->wwwroot.'/blocks/userquiz_monitor/generators/history_chart.swf" />';
    $response.=         '<param name="quality" value="high" />';
    $response.=         '<param name="bgcolor" value="#ffffff" />';
    $response.=         '<param name="allowScriptAccess" value="sameDomain" />';
    $response.=         '<embed src="'.$CFG->wwwroot.'/blocks/userquiz_monitor/generators/history_chart.swf" quality="high" bgcolor="#ffffff"';
    $response.=             'name="history_chart" align="middle"';
    $response.=             'play="true"';
    $response.=             'loop="false"';
    $response.=             'quality="high"';
    $response.=             'allowScriptAccess="sameDomain"';
    $response.=             'type="application/x-shockwave-flash"';
    $response.=             'pluginspage="http://www.adobe.com/go/getflashplayer">';
    $response.=         '</embed>';
    $response.=     '</object>';
    $response.= '</noscript>';
    
    return ($response);
}
