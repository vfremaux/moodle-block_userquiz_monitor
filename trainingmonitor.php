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
 * Displays training's monitor, it provides a dashboard
 *
 * @package     block_userquiz_monitor
 * @category    blocks
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux <valery.fremaux@gmail.com> (MyLearningFactory.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->dirroot.'/blocks/userquiz_monitor/js/scripts.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/locallib.php');

/**
 * @param int $courseid the surrounding course
 * @param ref $response
 * @param object ref $block the userquiz_monitor instance
 */
function get_monitortest($courseid, &$response, &$block) {
    global $USER, $DB, $PAGE, $OUTPUT;

    $renderer = $PAGE->get_renderer('block_userquiz_monitor');

    $rootcategory = @$block->config->rootcategory;
    $quizzesids = @$block->config->trainingquizzes;
    $blockid = $block->instance->id;
    $renderer->set_block($block);

    // Init variables.
    $quizzeslist = '';
    $errormsg = '';
    $overall = block_userquiz_monitor_init_overall();

    // Preconditions.
    if (empty($quizzesids)) {
        $response.= $OUTPUT->notification(get_string('configwarningmonitor', 'block_userquiz_monitor'), 'notifyproblem');
        return;
    }

    $response .= block_userquiz_monitor_init_rootcats($rootcategory, $rootcats);

    foreach ($quizzesids as $quiz) {
        $quizzeslist[] = $quiz;
    }
    $quizzesliststring = implode(",", $quizzeslist);
    $quizzeslist = implode("','", $quizzeslist);
    $quizzeslist = '\''.$quizzeslist.'\'';

    $userattempts = block_userquiz_monitor_get_user_attempts($blockid, $quizzesids);

    $errormsg = block_userquiz_monitor_compute_all_results($userattempts, $rootcategory, $rootcats, $attempts, $overall);

    $maxratio = 0;
    foreach (array_keys($rootcats) as $catid) {
        $rootcats[$catid]->ratioC = (@$rootcats[$catid]->cptC == 0) ? 0 : round(($rootcats[$catid]->goodC / $rootcats[$catid]->cptC ) * 100);
        $rootcats[$catid]->ratioA = (@$rootcats[$catid]->cptA == 0) ? 0 : round(($rootcats[$catid]->goodA / $rootcats[$catid]->cptA ) * 100);
        $rootcats[$catid]->ratio = (@$rootcats[$catid]->cpt == 0) ? 0 : round(($rootcats[$catid]->good / $rootcats[$catid]->cpt ) * 100);
        if ($maxratio < $rootcats[$catid]->ratio) {
            $maxratio = $rootcats[$catid]->ratio;
        }
    }

    if ($maxratio == 0) {
        $maxratio = 1;
    }

    $graphwidth = ($overall->ratio * 100)/$maxratio;

    // Call javascript.
    $scripts = get_js_scripts(array_keys($rootcats));
    $response .= $scripts;
    $formurl = new moodle_url('/blocks/userquiz_monitor/userpreset.php');
    $response .= '<form name="form" method="GET" action="'.$formurl.'">';
    $response .= '<input type="hidden" name="blockid" value="'.$block->instance->id.'">';

    if (!empty($userattempts)) {
        $params = calcul_hist($rootcategory, $attempts);
        $params = array('mode' => 'displayhist', 'datetype' => 'short', 'action' => 'Get_Stats', 'type' => 'category', 'param' => $params);
        $popuplink = new moodle_url('/blocks/userquiz_monitor/popup.php', $params);
        $action = new popup_action('click', $popuplink, 'ratings', array('height' => 400, 'width' => 600));
        $label = get_string('hist', 'block_userquiz_monitor');
        $pixicon = new pix_icon('graph', $label, 'block_userquiz_monitor', array('class' => 'userquiz-cmd-icon'));
        $components['accessorieslink'] = $OUTPUT->action_link($popuplink, '', $action, array(), $pixicon);
    } else {
        $title = get_string('hist', 'block_userquiz_monitor');
        $pixurl = $OUTPUT->pix_url('graph', 'block_userquiz_monitor');
        $components['accessorieslink'] = '<img width="38" height="20" title="'.$title.'" src="'.$pixurl.'"/>';
    }

    $graphparams = array (
        'boxheight' => 50,
        'boxwidth' => 300,
        'skin' => 'A',
        'type' => 'global',
        'graphwidth' => $graphwidth,
        'stop' => $block->config->rateAserie,
        'successrate' => $overall->ratioA,
    );
    $components['progressbarA'] = $renderer->progress_bar_html_gd($rootcategory, $graphparams);

    if (!empty($block->config->dualserie)) {
        $graphparams = array ( 
            'boxheight' => 50,
            'boxwidth' => 300,
            'skin' => 'C',
            'type' => 'global',
            'graphwidth' => $graphwidth,
            'stop' => $block->config->rateCserie,
            'successrate' => $overall->ratioC,
        );
        $components['progressbarC'] = $renderer->progress_bar_html_gd($rootcategory, $graphparams);
    }

    $data = array('dualserie' => $block->config->dualserie,
                  'goodA' => $overall->goodA,
                  'cptA' => $overall->cptA,
                  'goodC' => $overall->goodC,
                  'cptC' => $overall->cptC);

    $total = '<div id="divtotal" style="width:100%;">';
    $total .= $renderer->total($components, $data, $quizzeslist);
    $total .= '</div>';

    $selector = update_selector($courseid, null, 'mode0', $rootcategory, $quizzeslist);

    $response .= $renderer->global_monitor($total, $selector);

    $response .= '<table style="width:100%;" class="trainingcontener">';

    if (!empty($errormsg)) {
        $response .= $renderer->errorline($errormsg);
    }

    $response .= '<tr valign="top"><td style="width:50%; padding:5px;">';

    $cpt = 0;
    $scale = '';
    $quizzeslist = urlencode($quizzeslist);

    foreach ($rootcats as $catid => $cat) {

        if  ($catid == 0) {
            continue; // But why.
        }

        $graphwidth = ($cat->ratio * 100) / $maxratio;

        if ($graphwidth < 1) {
            $graphwidth = 1;
        }

        if (!empty($userattempts)) {
            // Format the data to construct the histogram.
            $params = calcul_hist($catid, $attempts);
        } else {
            $params = '';
        }

        if ($cpt == 0) {

            $jshandler = 'updateselectorpl('.$courseid.', \''.$rootcategory;
            $jshandler .= '\', idcategoriespl , \'cbpl\', \'all\', \''.$quizzesliststring.'\')';
            $response .= $renderer->program_headline(@$block->config->trainingprogramname, $jshandler);
        }

        if (!empty($userattempts)) {
            $params = array('mode' => 'displayhist', 'datetype' => 'long', 'action' => 'Get_Stats', 'type' => 'category', 'param' => $params);
            $popuplink = new moodle_url('/blocks/userquiz_monitor/popup.php', $params);
            $params = array('height' => 400, 'width' => 600);
            $action = new popup_action('click', $popuplink, 'ratings', $params);
            $label = get_string('hist', 'block_userquiz_monitor');
            $pixurl = new pix_icon('graph', $label, 'block_userquiz_monitor', array('class' => 'userquiz-cmd-icon'));
            $cat->accessorieslink = $OUTPUT->action_link($popuplink, '', $action, array(), $pixurl);
        } else {
            $cat->accessorieslink = '<img width="38" height="20" title="'.get_string('hist', 'block_userquiz_monitor').'" src="'.$OUTPUT->pix_url('graph', 'block_userquiz_monitor').'"/>';
        }

        $data = array ( 
            'boxheight' => 50,
            'boxwidth' => 160,
            'type' => 'local',
            'skin' => 'A',
            'graphwidth' => $graphwidth,
            'stop' => $block->config->rateAserie,
            'successrate' => $cat->ratioA,
        );
        $cat->progressbarA = $renderer->progress_bar_html_gd($cat->id, $data);

        if ($block->config->dualserie) {
            $data = array ( 
                'boxheight' => 50,
                'boxwidth' => 160,
                'type' => 'local',
                'skin' => 'C',
                'graphwidth' => $graphwidth,
                'stop' => $block->config->rateCserie,
                'successrate' => $cat->ratioC,
            );
            $cat->progressbarC = $renderer->progress_bar_html_gd($cat->id, $data);
        }

        $cat->jshandler1 = 'updateselectorpl(\''.$courseid.'\',\''.$rootcategory.'\', idcategoriespl , \'cbpl\', \'none\', \''.$quizzesliststring.'\')';
        $cat->jshandler2 = 'activedisplaytrainingsubcategories('.$courseid.', '.$rootcategory.', '.$catid;
        $cat->jshandler2 .= ', idcategoriespl , \''.$quizzesliststring.'\' , \''.$scale.'\', '.$blockid.')';
        $response .= $renderer->category_result($cat);
        $cpt++;
    }

    $catdetailstr = get_string('categorydetail', 'block_userquiz_monitor', @$block->config->trainingprogramname);
    $notenum = 1;
    if ($block->config->dualserie) {
        $response .= '<span class="smallnotes">'.get_string('columnnotesdual', 'block_userquiz_monitor', $notenum).'</span>';
        $notenum++;
    }
    $response .= '<span class="smallnotes">'.get_string('columnnotesratio', 'block_userquiz_monitor', $notenum).'</span>';
    $response.= '</td>
                 <td style="width:50%; padding:5px;">
                    <div>
                        <table class="tablemonitorcategorycontainer">
                            <tr height="17">
                                <td><h1>'.$catdetailstr.'</h1></td>
                            </tr>
                        </table>
                    </div>
                    <div id="partright"></div>
                 </td>
            </tr>
        </table>
        </form>
   ';

    // Init elements on the page
    $response.= '<script type="text/javascript"> initelements();</script>';
}

