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
 * @package   blocks_userquiz_monitor
 * @category blocks
 * @author     Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright  Valery Fremaux (valery.fremaux@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

header('Content-Type:text/javascript');

?>

// Setup common variables.

var requiredMajorVersion = 9;
var requiredMinorVersion = 0;
var requiredRevision = 24;

var hasProductInstall = DetectFlashVer(6, 0, 65);
var hasRequestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);

function setupProgressBar(boxwidth, boxheight, barId, barName, barData) {
    if ( hasProductInstall && !hasRequestedVersion ) {
        var MMPlayerType = (isIE === true) ? "ActiveX" : "PlugIn";
        var MMredirectURL = window.location;
        document.title = document.title.slice(0, 47) + " - Flash Player Installation";
        var MMdoctitle = document.title;
        AC_FL_RunContent(
            "src", "playerProductInstall",
            "FlashVars", "MMredirectURL=" + MMredirectURL + "&MMplayerType=" + MMPlayerType + "&MMdoctitle=" + MMdoctitle,
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
            "src", M.cfg.wwwroot + "/blocks/userquiz_monitor/generators/progress_bar",
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
        var alternateContent = "Alternate HTML content should be placed here. ";
        alternateContent += "This content requires the Adobe Flash Player.";
        alternateContent += "<a href=http://www.adobe.com/go/getflash/>Get Flash</a>";
        document.write(alternateContent); 
    }
}
