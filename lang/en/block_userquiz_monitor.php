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
 * @package     blocks_userquiz_monitor
 * @category    blocks
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['userquiz_monitor:view'] = 'View the dashboard';
$string['userquiz_monitor:addinstance'] = 'Add a userquiz monitor board';
$string['userquiz_monitor:import'] = 'Import questions into the userquiz monitor';

// Privacy.
$string['privacy:metadata'] = "The User Quiz Monitor needs to be implemented to reflect user preferences.";

$string['amfxslx'] = 'AMF format';
$string['fdxslx'] = 'FD format (Sustainable financials)';
$string['fdenxslx'] = 'FD format (Sustainable financials - English version)';
$string['amfinfo'] = 'Base de questions AMF';
$string['fdinfo'] = 'Base de question AMF Finance durable';
$string['fdeninfo'] = 'Base de questions AMF Sustainable (EN)';
$string['adminresethist'] = '(Admin only, or impersonated : Reset training results : )';
$string['allexamsfilterinfo'] = 'Results are calculated over all attempts';
$string['attempt'] = 'Attempt';
$string['available'] = 'available';
$string['backtocourse'] = 'Back to course';
$string['blockname'] = 'Userquiz Progress Monitoring';
$string['categories'] = 'Results by {$a} categories ';
$string['categorydetail'] = 'Detail of a {$a} category ';
$string['categorydetaildesc'] = 'Choose a category from the left column to view the detailed results of your training here.';
$string['categoryname'] = 'Category\'s name : ';
$string['clear'] = 'Clear this image';
$string['close'] = 'close';
$string['closesubsicon'] = 'Close subcategories icon';
$string['columnnotes'] = '({$a}) Level of questions - A or C.<br/>(2) Corresponds to the ratio number of correct answers / number of questions<br/>';
$string['columnnotesdual'] = '({$a}) Level of questions - A or C.<br/>';
$string['columnnotesratio'] = '({$a}) Corresponds to the ratio number of correct answers / number of questions<br/>';
$string['commenthist'] = 'Results history:&ensp;';
$string['configcoloraserie'] = 'First serie gauge color';
$string['configcolorcserie'] = 'Second serie gauge color';
$string['configdirectreturn'] = 'Direct return to course after attempt';
$string['configdualserie'] = 'Enable second question serie';
$string['configdualseries'] = 'Dual type test type (A and C)';
$string['configexam'] = 'Add belonging quizzes examinations';
$string['configexamdefault'] = 'Default navigation landing';
$string['configtrainingdefault'] = 'Default navigation landing';
$string['configexamalternatecaption'] = 'Alternative title of the exam content page';
$string['configexamdeadend'] = 'Examination is dead end after submitting (direct return enabled)';
$string['configexamdirectreturn'] = 'Direct return to course';
$string['configexamenabled'] = 'Enable exam simulation ';
$string['configexamhidescoringinterface'] = 'Hide examination scoring interface.<br/>';
$string['configexaminstructions'] = 'Instructions for exams';
$string['configexamtab'] = 'Title of the examination';
$string['configgaugerenderer'] = 'Gauge renderer';
$string['configinformationpageid'] = 'information course page identifier.<br/>';
$string['confignameaserie'] = 'First serie name';
$string['confignamecserie'] = 'Second serie name';
$string['configprotectcopy'] = 'Protect against content copy';
$string['configquizforceanswer'] = 'Force answer';
$string['configquiznobackwards'] = 'Forbid going backwards on quiz';
$string['configrateaserie'] = 'First serie threshold (% on A type)';
$string['configratecserie'] = 'Second serie threshold (% on C type)';
$string['configrootcategory'] = 'Parent category for the whole training system';
$string['configshowhistory'] = 'Show results history';
$string['configinfopageid'] = 'Info page id';
$string['configinfopageid_help'] = 'On a "page" format, you may use a separate page to give instructions to user. Defining the page id here will add a tab to the training interface to that page.';
$string['confignameserie'] = 'Serie name';
$string['confignameserie_help'] = 'This label is used in case of dual serie to labelize each serie result.';
$string['configrateserie'] = 'Serie rate';
$string['configrateserie_help'] = 'This is the "pass" rate of this question serie.';
$string['configdualserie'] = 'Dual serie';
$string['configdualserie_help'] = 'You may split your training system in two question series, each having a distinct pass rate. When using dualserie mode, rate your questions 1.000 for the first serie, and 1000.000 for the second serie.';
$string['configshowdetailedresults'] = 'Show detailed results';
$string['configtest'] = 'Add belonging quizzes tests';
$string['configtrainingenabled'] = 'Enable training';
$string['configtrainingprogramname'] = 'Name of the training program (enters in titling and labels)';
$string['configwarning'] = 'Beware if a quiz is selected in the test, then it can only be found in the examination and vice versa.';
$string['configwarningemptycats'] = 'The root cat you have choose seems not having subcats.';
$string['configwarningmonitor'] = 'Warning, be sure to configure the block to separate the types of quizzes test and quiz-type examination.';
$string['detailsicon'] = 'Icon for subcategory button';
$string['emulatecommunity'] = 'Emulate community version';
$string['error1'] = 'Unable to retrieve information from the user questions. <br/>';
$string['error2'] = 'Actualy, you don\'t have finished test. <br/>';
$string['error3'] = 'Impossible to get categories. <br/>';
$string['error4'] = 'Actualy, you don\'t have finished examination. <br/>';
$string['errorquestionoutsidescope'] = 'Some questions used in exams seem being outside the training scope. This may denote an error of configuration of the quiz system. You may report this to your teacher.';
$string['erroruserquiznoquiz'] = 'Sorry, there is no quiz available providing this amount of questions. This is a misconfiguration that should be reported.';
$string['examend'] = 'Exam finish';
$string['examfinishmessage'] = 'Congratulations %%FIRSTNAME%%! You have finished you exam attempt on the %%PROGRAMNAME%% training engine. You will see the results in your training dashboard.';
$string['examination'] = 'Here is the examanination part';
$string['examinstructions'] = '<p>The examination review simulates almost exact {$a} examination.</p><p>When you launch an attempt, you <b>must complete the review by going through issues</b>, or attempted to be little signficative and may distort your readiness assessment. So plan enough time to respond to 100 questions on the exam without being disturbed.</p><p>You have by your subscription, a limited number of examinations. Train yourself so enough to enjoy each simulation.</p>';
$string['examsdepth'] = 'Exams depth: ';
$string['examisdefault'] = ' &nbsp;&nbsp;Exam activity as default';
$string['trainingisdefault'] = ' &nbsp;&nbsp;Training activity as default';
$string['examsettings'] = 'Examination settings';
$string['examsfilterinfo'] = 'Results are calculated over {$a} exam attempts';
$string['examstatefailed'] = 'State : FAILED';
$string['examstatepassed'] = 'State : PASSED';
$string['examtitle'] = 'Simulation {$a} examination';
$string['filterinfo'] = 'Results are calculated from {$a->from} to {$a->to}';
$string['filtering'] = 'Progress results filtering';
$string['flash'] = 'Flash';
$string['forcecreatecategories'] = 'Force create categories (even when simulating)';
$string['fullhtml'] = 'Full html';
$string['gd'] = 'Php GD Generator';
$string['generalsettings'] = 'General settings';
$string['graphicassets'] = 'Graphic assets';
$string['importquestions'] = 'Import question set';
$string['importformat'] = 'Import format';
$string['questionimport'] = 'Question import';
$string['hist'] = 'Histogram';
$string['info1'] = '* Be sure to select at least one category or subcategory before releasing training. <br/>';
$string['importcategory'] = 'Target question category for import';
$string['jqw'] = 'JQWidget';
$string['keyprefix'] = 'Question identifiers prefix';
$string['launch'] = 'Launch the test';
$string['level'] = '<b>Level<sup>{$a}</sup></b>';
$string['level1'] = 'LEVEL';
$string['localcss'] = 'Local CSS';
$string['looseattemptsignal'] = 'You will loose all your answers if you quit the quiz. Continue ?';
$string['meanscore'] = 'Mean Score';
$string['menuamfref'] = '{$a} Reference';
$string['menuexamhistories'] = 'History';
$string['menuexamination'] = 'Assessment';
$string['menuexamlaunch'] = 'Launch an exam';
$string['menuexamresults'] = 'Results';
$string['menuexamdetails'] = 'Detailed results';
$string['menuinformation'] = 'Information';
$string['menupreferences'] = 'Preferences';
$string['menutest'] = 'Training';
$string['more'] = 'View sub-categories';
$string['noavailableattemptsstr'] = 'No available attempts';
$string['nodefinerootcategory'] = 'Please select the parent caregory';
$string['nohist'] = 'No history data available.';
$string['nofile'] = 'No imported file';
$string['nousedattemptsstr'] = 'No attempts performed';
$string['numberquestions'] = 'Number of question';
$string['optfiveexams'] = '5 passed exams';
$string['optfiveweeks'] = '5 passed weeks';
$string['optfourexams'] = '4 passed exams';
$string['optfourweeks'] = '4 passed weeks';
$string['optnofilter'] = 'All results (no filtering)';
$string['optoneexam'] = 'Last passed exam';
$string['optoneweek'] = 'Last passed week';
$string['optthreeexams'] = '3 passed exams';
$string['optthreeweeks'] = '3 passed weeks';
$string['opttwoexams'] = '2 passed exams';
$string['opttwoweeks'] = '2 passed weeks';
$string['pluginname'] = 'Userquiz monitoring';
$string['questiontype'] = 'Questions\'s types:&ensp;';
$string['ratio'] = '<b>Ratio<sup>{$a}</sup></b>';
$string['ratio1'] = 'RATIO';
$string['reftitle'] = '{$a} Assessment Reference';
$string['remainingattempts'] = 'Remaining attempts: {$a}';
$string['replaceall'] = 'Replace all questions';
$string['importfile'] = 'Import file';
$string['reset'] = 'Reset';
$string['resetinfo1'] = 'Reset done';
$string['resetinfo2'] = 'Impossible to reset your training results';
$string['resetinfo3'] = 'No attempt to remove.';
$string['resultsdepth'] = 'Depth of view';
$string['importresult'] = 'Import Result';
$string['returntoquiz'] = 'Back to the quiz';
$string['returntotraining'] = 'Back to training board';
$string['runexam'] = 'Run an assessment';
$string['runtest'] = 'Run a training';
$string['runtraininghelp'] = 'Select categories or subcategories in the following table than choose the size of your quiz attempt:';
$string['schedule'] = 'Here is the {$a}\'s training program';
$string['simulate'] = 'Just simulate (do not write anything).';
$string['score'] = 'SCORE';
$string['seedetails'] = 'See details';
$string['selectallcb'] = 'Select all';
$string['selectschedule'] = 'Select a category to view program';
$string['serie1icon'] = 'Icon for question serie 1';
$string['serie2icon'] = 'Icon for question serie 2';
$string['showdiv'] = 'Show / Hide the total score';
$string['statsbuttonicon'] = 'Icon for stats button';
$string['stillavailable'] = ' You have {$a} still available attempts in your account.';
$string['subcategoryname'] = 'Subcategory\'s name:&ensp;';
$string['target'] = 'Target';
$string['testinstructions'] = '<p>To launch a training, please select categories or subcategories you wxant to focus on in the following table than choose the number of questions you want to get in your training.</p><p>The dashboard computes your success rate per category, accumulating your mean success level from the beginning of your trainig period.</p>';
$string['testtitle'] = 'Self-training to the {$a} assessment';
$string['thankyou'] = 'Thank you for having submitted to this examination';
$string['total'] = 'Total';
$string['totaldescexam'] = 'These results are calculated on all topics and show you your average success rate of the mock exams.';
$string['totaldesctraining'] = 'These results are calculated on all topics and show you your average success rate with the trainings throughout the review period since the last reset.';
$string['totalexam'] = 'Overall results for assessment';
$string['trainingsettings'] = 'Training settings';
$string['userquizmonitor'] = 'Dashboard';
$string['warningchoosecategory'] = 'Please choose a root category in the block administration<br/>';
$string['warningconfigexam'] = 'Please select which userquiz is requested to perform the assessment<br/>';
$string['warningconfigtest'] = 'Please select a set of userquizes to perform trainings<br/>';

$string['launch_help'] = '
* Launch a training

To launch a training, you will choose your training scope by selecting categories
(or subcategories) you want to focus on in the training space
you can then choose the amount of questions you want to answer.
';

$string['total_help'] = '
* Global progress

Results are compiled across all your attempts and give your mean score
in training in the selected period';

$string['totalexam_help'] = '
* Exam progress

Results are compiled across all your examination attemps on the selected period.
';

$string['configquizforceanswer_help'] = 'If enabled, the corresponding quiz will force the user to answer by changing asnwer
content. This only works in "single question per page forme"';

$string['resultsdepth_help'] = 'Choose how long in the past you want results to be considered in calculation';

$string['configrootcategory_help'] = 'The choice of this category is very important for the training system. When training,
it will be the start of the quiz area choice by the user. When making an exam, it will also be the root of the results analysis
questionning space.';

$string['importformat_help'] = '
AMF Format : An Excel (better Excel 5 file .xls) with 1 first line as column names : 

t	Thème	Sous-Thème	Catégorie	Libellé Question	Réponse A	Réponse B	Réponse C

Subtopic must be filled, at least with 1 as default. Check this in file.
Topic and subtopic node must be allowed in the imported format description. See __construct of the format class.
';

include(__DIR__.'/pro_additional_strings.php');
