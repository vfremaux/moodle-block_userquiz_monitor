<?php

include_once('../../../config.php');

header('Content-Type:text/javascript');

?>

// Setup common variables

// Php to JS passing
var wwwroot = '<?php echo $CFG->wwwroot ?>';

var requiredMajorVersion = 9;
var requiredMinorVersion = 0;
var requiredRevision = 24;

var hasProductInstall = DetectFlashVer(6, 0, 65);
var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);

function setupProgressBar(boxwidth, boxheight, barId, barName, barData){
    if ( hasProductInstall && !hasRequestedVersion ) {
        var MMPlayerType = (isIE == true) ? "ActiveX" : "PlugIn";
        var MMredirectURL = window.location;
        document.title = document.title.slice(0, 47) + " - Flash Player Installation";
        var MMdoctitle = document.title;
        AC_FL_RunContent(
            "src", "playerProductInstall",
            "FlashVars", "MMredirectURL="+MMredirectURL+"&MMplayerType="+MMPlayerType+"&MMdoctitle="+MMdoctitle,
            "width", "100%",
            "height", "100%",
            "align", "middle",
            "id", barId,
            "quality", "high",
            "bgcolor", "#ffffff",
            "name", barName,
            "allowScriptAccess","sameDomain",
            "type", "application/x-shockwave-flash",
            "pluginspage", "http://www.adobe.com/go/getflashplayer");
    } else if (hasRequestedVersion) {
        AC_FL_RunContent(
            "src", wwwroot + "/blocks/userquiz_monitor/generators/progress_bar",
            "width", boxwidth + 10,
            "height", boxheight + 10,
            "align", "middle",
            "id", barId,
            "quality", "high",
            "bgcolor", "#ffffff",
            "name", barName,
            "FlashVars", "data=" + barData,
            "allowScriptAccess", "sameDomain",
            "type", "application/x-shockwave-flash",
            "pluginspage", "http://www.adobe.com/go/getflashplayer");
    } else {
        var alternateContent = "Alternate HTML content should be placed here. " +
        "This content requires the Adobe Flash Player." +
        "<a href=http://www.adobe.com/go/getflash/>Get Flash</a>";
        document.write(alternateContent); 
    }
}
