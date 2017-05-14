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
$string['close'] = 'close';
$string['closesubsicon'] = 'Close subcategories icon';
$string['columnnotes'] = '({$a}) Level of questions - A or C.<br/>(2) Corresponds to the ratio number of correct answers / number of questions<br/>';
$string['columnnotesdual'] = '({$a}) Level of questions - A or C.<br/>';
$string['columnnotesratio'] = '({$a}) Corresponds to the ratio number of correct answers / number of questions<br/>';
$string['commenthist'] = 'Results history:&ensp;';
$string['configdirectreturn'] = 'Direct return to course after attempt';
$string['configdualserie'] = 'Enable second question serie';
$string['configdualseries'] = 'Dual type test type (A and C)';
$string['configgaugerenderer'] = 'Gauge renderer';
$string['fullhtml'] = 'Full html';
$string['gd'] = 'Php GD Generator';
$string['jqw'] = 'JQWidget';
$string['flash'] = 'Flash';
$string['configexam'] = 'Add belonging quizzes examinations';
$string['configexamalternatecaption'] = 'Alternative title of the exam content page';
$string['configexamdeadend'] = 'Examination is dead end after submitting (direct return enabled)';
$string['configexamdirectreturn'] = 'Direct return to course';
$string['configexamenabled'] = 'Enable exam simulation ';
$string['configexamhidescoringinterface'] = 'Hide examination scoring interface.<br/>';
$string['configexaminstructions'] = 'Instructions for exams';
$string['configexamtab'] = 'Title of the examination';
$string['configinformationpageid'] = 'information course page identifier.<br/>';
$string['configcoloraserie'] = 'First serie gauge color';
$string['configcolorcserie'] = 'Second serie gauge color';
$string['configquiznobackwards'] = 'Forbid going backwards on quiz';
$string['configprotectcopy'] = 'Protect against content copy';
$string['configquizforceanswer'] = 'Force answer';
$string['configrateaserie'] = 'First serie threshold (% on A type)';
$string['configratecserie'] = 'Second serie threshold (% on C type)';
$string['configrootcategory'] = 'Parent category for the whole training system';
$string['configtest'] = 'Add belonging quizzes tests';
$string['configtrainingenabled'] = 'Enable training';
$string['configshowhistory'] = 'Show results history';
$string['configtrainingprogramname'] = 'Name of the training program (enters in titling and labels)';
$string['configwarning'] = 'Beware if a quiz is selected in the test, then it can only be found in the examination and vice versa.';
$string['configwarningemptycats'] = 'The root cat you have choose seems not having subcats.';
$string['configwarningmonitor'] = 'Warning, be sure to configure the block to separate the types of quizzes test and quiz-type examination.';
$string['error1'] = 'Unable to retrieve information from the user questions. <br/>';
$string['error2'] = 'Actualy, you don\'t have finished test. <br/>';
$string['error3'] = 'Impossible to get categories. <br/>';
$string['error4'] = 'Actualy, you don\'t have finished examination. <br/>';
$string['errorquestionoutsidescope'] = 'Some questions used in exams seem being outside the training scope. This may denote an error of configuration of the quiz system. You may report this to your teacher.';
$string['erroruserquiznoquiz'] = 'Sorry, there is no quiz available providing this amount of questions. This is a misconfiguration that should be reported.';
$string['examination'] = 'Here is the examanination part';
$string['examinstructions'] = '<p>The examination review simulates almost exact {$a} examination.</p><p>When you launch an attempt, you <b>must complete the review by going through issues</b>, or attempted to be little signficative and may distort your readiness assessment. So plan enough time to respond to 100 questions on the exam without being disturbed.</p><p>You have by your subscription, a limited number of examinations. Train yourself so enough to enjoy each simulation.</p>';
$string['examsdepth'] = 'Exams depth: ';
$string['examsettings'] = 'Examination settings';
$string['examend'] = 'Exam finish';
$string['examfinishmessage'] = 'Congratulations %%FIRSTNAME%%! You have finished you exam attempt on the %%PROGRAMNAME%% training engine. You will see the results in your training dashboard.';
$string['examsfilterinfo'] = 'Results are calculated over {$a} exam attempts';
$string['examtitle'] = 'Simulation {$a} examination';
$string['examstatefailed'] = 'State : FAILED';
$string['examstatepassed'] = 'State : PASSED';
$string['filterinfo'] = 'Results are calculated from {$a->from} to {$a->to}';
$string['filtering'] = 'Progress results filtering';
$string['graphicassets'] = 'Graphic assets';
$string['statsbuttonicon'] = 'Icon for stats button';
$string['detailsicon'] = 'Icon for subcategory button';
$string['clear'] = 'Clear this image';
$string['serie1icon'] = 'Icon for question serie 1';
$string['serie2icon'] = 'Icon for question serie 2';
$string['generalsettings'] = 'General settings';
$string['hist'] = 'Histogram';
$string['info1'] = '* Be sure to select at least one category or subcategory before releasing training. <br/>';
$string['launch'] = 'Launch the test';
$string['level'] = '<b>Level<sup>{$a}</sup></b>';
$string['level1'] = 'LEVEL';
$string['localcss'] = 'Local CSS';
$string['menuamfref'] = '{$a} Reference';
$string['menuexamination'] = 'Assessment';
$string['menuinformation'] = 'Information';
$string['menupreferences'] = 'Preferences';
$string['menuexamlaunch'] = 'Launch an exam';
$string['menuexamresults'] = 'Results';
$string['menuexamhistories'] = 'History';
$string['menutest'] = 'Training';
$string['more'] = 'View sub-categories';
$string['noavailableattemptsstr'] = 'No available attempts';
$string['nodefinerootcategory'] = 'Please select the parent caregory';
$string['nohist'] = 'No history data available.';
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
$string['reset'] = 'Reset';
$string['resetinfo1'] = 'Reset done';
$string['resetinfo2'] = 'Impossible to reset your training results';
$string['resetinfo3'] = 'No attempt to remove.';
$string['resultsdepth'] = 'Depth of view';
$string['runexam'] = 'Run an assessment';
$string['runtest'] = 'Run a training';
$string['runtraininghelp'] = 'Select categories or subcategories in the following table than choose the size of your quiz attempt:';
$string['schedule'] = 'Here is the {$a}\'s training program';
$string['selectallcb'] = 'Select all';
$string['selectschedule'] = 'Select a category to view program';
$string['showdiv'] = 'Show / Hide the total score';
$string['stillavailable'] = ' still available';
$string['score'] = 'Score';
$string['subcategoryname'] = 'Subcategory\'s name:&ensp;';
$string['target'] = 'Target';
$string['meanscore'] = 'Mean Score';
$string['seedetails'] = 'See details';
$string['testinstructions'] = '<p>To launch a training, please select categories or subcategories you wxant to focus on in the following table than choose the number of questions you want to get in your training.</p><p>The dashboard computes your success rate per category, accumulating your mean success level from the beginning of your trainig period.</p>';
$string['testtitle'] = 'Self-training to the {$a} assessment';
$string['thankyou'] = 'Thank you for having submitted to this examination';
$string['total'] = 'Total';
$string['totaldesctraining'] = 'These results are calculated on all topics and show you your average success rate with the trainings throughout the review period since the last reset.';
$string['totaldescexam'] = 'These results are calculated on all topics and show you your average success rate of the mock exams.';
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
