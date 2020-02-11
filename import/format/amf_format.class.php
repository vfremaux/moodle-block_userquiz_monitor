<?php

namespace block_userquiz_monitor\import;

require_once($CFG->dirroot.'/blocks/userquiz_monitor/import/format/import_format.class.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/extralibs/PHPExcel-1.8/Classes/PHPExcel.php');

use \PHPExcel_IOFactory;
use StdClass;
use \stored_file;
use \context_course;
use \moodle_url;

class amf_format extends import_format {

    static protected $AMF_defaults;

    protected $filepath;

    protected $objExcel;

    public $resultfile;

    public function __construct(stored_file $file, $uqblockinstance, $courseid) {
        parent::__construct($file, $uqblockinstance, $courseid);
        self::$AMF_defaults = [
            1 => [
                'name' => 'Cadre institutionnel',
                'idnumber' => 'AMF_1',
                'subs' => [
                    '1.1' => [
                        'name' => '1.1',
                        'idnumber' => 'AMF_1.1',
                        'type' => 'C',
                    ],
                    '1.2.1' => [
                        'name' => '1.2.1',
                        'idnumber' => 'AMF_1.2.1',
                        'type' => 'C',
                    ],
                    '1.2.2' => [
                        'name' => '1.2.2',
                        'idnumber' => 'AMF_1.2.2',
                        'type' => 'C',
                    ],
                    '1.3' => [
                        'name' => '1.3',
                        'idnumber' => 'AMF_1.3',
                        'type' => 'C',
                    ],
/*
                    '1.4' => [
                        'name' => '1.4',
                        'idnumber' => 'AMF_1.4',
                        'type' => 'C',
                    ],
*/
                    '1.5.1' => [
                        'name' => '1.5.1',
                        'idnumber' => 'AMF_1.5.1',
                        'type' => 'C',
                    ],
                    '1.5.2' => [
                        'name' => '1.5.2',
                        'idnumber' => 'AMF_1.5.2',
                        'type' => 'C',
                    ],
/*                    '1.6' => [
                        'name' => '1.6',
                        'idnumber' => 'AMF_1.6',
                        'type' => 'C',
                    ],
                    '1.7' => [
                        'name' => '1.7',
                        'idnumber' => 'AMF_1.7',
                        'type' => 'C',
                    ],
*/
                    '1.8' => [
                        'name' => '1.8',
                        'idnumber' => 'AMF_1.8',
                        'type' => 'C',
                    ],
                ]
            ],
            2 => [
                'name' => 'Déontologie',
                'idnumber' => 'AMF_2',
                'subs' => [
                    '2.1' => [
                        'name' => '2.1',
                        'idnumber' => 'AMF_2.1',
                        'type' => 'X',
                    ],
                    '2.2' => [
                        'name' => '2.2',
                        'idnumber' => 'AMF_2.2',
                        'type' => 'C',
                    ],
                    '2.3' => [
                        'name' => '2.3',
                        'idnumber' => 'AMF_2.3',
                        'type' => 'C',
                    ],
                ]
            ],
            3 => [
                'name' => 'Blanchiment',
                'idnumber' => 'AMF_3',
                'subs' => [
                    '3.1' => [
                        'name' => '3.1',
                        'idnumber' => 'AMF_3.1',
                        'type' => 'A',
                    ],
                ]
            ],
            4 => [
                'name' => 'Abus de marché',
                'idnumber' => 'AMF_4',
                'subs' => [
                    '4.1' => [
                        'name' => '4.1',
                        'idnumber' => 'AMF_4.1',
                        'type' => 'A',
                    ]
                ]
            ],
            5 => [
                'name' => 'Démarchage',
                'idnumber' => 'AMF_5',
                'subs' => [
                    '5.1' => [
                        'name' => '5.1',
                        'idnumber' => 'AMF_5.1',
                        'type' => 'X',
                    ],
                    '5.2' => [
                        'name' => '5.2',
                        'idnumber' => 'AMF_5.2',
                        'type' => 'X',
                    ]
                ]
            ],
            6 => [
                'name' => 'Relations client',
                'idnumber' => 'AMF_6',
                'subs' => [
                    '6.1.1' => [
                        'name' => '6.1.1',
                        'idnumber' => 'AMF_6.1.1',
                        'type' => 'A',
                    ],
                    '6.1.2' => [
                        'name' => '6.1.2',
                        'idnumber' => 'AMF_6.1.2',
                        'type' => 'C',
                    ],
                    '6.2' => [
                        'name' => '6.2',
                        'idnumber' => 'AMF_6.2',
                        'type' => 'A',
                    ],
                    '6.3' => [
                        'name' => '6.3',
                        'idnumber' => 'AMF_6.3',
                        'type' => 'A',
                    ],
                    '6.4' => [
                        'name' => '6.4',
                        'idnumber' => 'AMF_6.4',
                        'type' => 'A',
                    ],
                    '6.5' => [
                        'name' => '6.5',
                        'idnumber' => 'AMF_6.5',
                        'type' => 'C',
                    ],
                    '6.6' => [
                        'name' => '6.6',
                        'idnumber' => 'AMF_6.6',
                        'type' => 'C',
                    ],
                    '6.7' => [
                        'name' => '6.7',
                        'idnumber' => 'AMF_6.7',
                        'type' => 'C',
                    ],
                    '6.8' => [
                        'name' => '6.8',
                        'idnumber' => 'AMF_6.8',
                        'type' => 'C',
                    ],
                    '6.9' => [
                        'name' => '6.9',
                        'idnumber' => 'AMF_6.9',
                        'type' => 'C',
                    ],
                ]
            ],
            7 => [
                'name' => 'Instruments financiers',
                'idnumber' => 'AMF_7',
                'subs' => [
                    '7.1' => [
                        'name' => '7.1',
                        'idnumber' => 'AMF_7.1',
                        'type' => 'C',
                    ],
                    '7.2' => [
                        'name' => '7.2',
                        'idnumber' => 'AMF_7.2',
                        'type' => 'C',
                    ],
                    '7.3' => [
                        'name' => '7.3',
                        'idnumber' => 'AMF_7.3',
                        'type' => 'C',
                    ],
                    '7.4' => [
                        'name' => '7.4',
                        'idnumber' => 'AMF_7.4',
                        'type' => 'C',
                    ],
                    '7.5' => [
                        'name' => '7.5',
                        'idnumber' => 'AMF_7.5',
                        'type' => 'C',
                    ],
                    '7.6' => [
                        'name' => '7.6',
                        'idnumber' => 'AMF_7.6',
                        'type' => 'C',
                    ],
                    '7.7' => [
                        'name' => '7.7',
                        'idnumber' => 'AMF_7.7',
                        'type' => 'C',
                    ],
                    '7.8' => [
                        'name' => '7.8',
                        'idnumber' => 'AMF_7.8',
                        'type' => 'C',
                    ],
                    '7.9' => [
                        'name' => '7.9',
                        'idnumber' => 'AMF_7.9',
                        'type' => 'X',
                    ],
                    '7.10' => [
                        'name' => '7.10',
                        'idnumber' => 'AMF_7.10',
                        'type' => 'X',
                    ],
                ]
            ],
            8 => [
                'name' => 'Gestion collective',
                'idnumber' => 'AMF_8',
                'subs' => [
                    '8.1' => [
                        'name' => '8.1',
                        'idnumber' => 'AMF_8.1',
                        'type' => 'C',
                    ],
                    '8.2.1' => [
                        'name' => '8.2.1',
                        'idnumber' => 'AMF_8.2.1',
                        'type' => 'C',
                    ],
                    '8.2.2' => [
                        'name' => '8.2.2',
                        'idnumber' => 'AMF_8.2.2',
                        'type' => 'C',
                    ],
/*
                    '8.3' => [
                        'name' => '8.3',
                        'idnumber' => 'AMF_8.3',
                        'type' => 'C',
                    ],
*/
                    '8.4' => [
                        'name' => '8.4',
                        'idnumber' => 'AMF_8.4',
                        'type' => 'C',
                    ],
                    '8.5' => [
                        'name' => '8.5',
                        'idnumber' => 'AMF_8.5',
                        'type' => 'C',
                    ],
                    '8.6' => [
                        'name' => '8.6',
                        'idnumber' => 'AMF_8.6',
                        'type' => 'C',
                    ],
                    '8.7' => [
                        'name' => '8.7',
                        'idnumber' => 'AMF_8.7',
                        'type' => 'C',
                    ],
                ]
            ],
            9 => [
                'name' => 'Organisation des marchés',
                'idnumber' => 'AMF_9',
                'subs' => [
                    '9.1' => [
                        'name' => '9.1',
                        'idnumber' => 'AMF_9.1',
                        'type' => 'C',
                    ],
                    '9.2' => [
                        'name' => '9.2',
                        'idnumber' => 'AMF_9.2',
                        'type' => 'A',
                    ],
                    '9.3' => [
                        'name' => '9.3',
                        'idnumber' => 'AMF_9.3',
                        'type' => 'C',
                    ],
                    '9.4' => [
                        'name' => '9.4',
                        'idnumber' => 'AMF_9.4',
                        'type' => 'C',
                    ],
                    '9.5' => [
                        'name' => '9.5',
                        'idnumber' => 'AMF_9.5',
                        'type' => 'C',
                    ],
                ]
            ],
            10 => [
                'name' => 'Back-office',
                'idnumber' => 'AMF_10',
                'subs' => [
                    '10.1' => [
                        'name' => '10.1',
                        'idnumber' => 'AMF_10.1',
                         'type' => 'C',
                   ],
                    '10.2' => [
                        'name' => '10.2',
                        'idnumber' => 'AMF_10.2',
                         'type' => 'C',
                   ]
                ]
            ],
            11 => [
                'name' => 'Emissions et OST',
                'idnumber' => 'AMF_11',
                'subs' => [
                    '11.1' => [
                        'name' => '11.1',
                        'idnumber' => 'AMF_11.1',
                        'type' => 'C',
                    ],
                    '11.2' => [
                        'name' => '11,2',
                        'idnumber' => 'AMF_11.2',
                        'type' => 'C',
                    ]
                ]
            ],
            12 => [
                'name' => 'Bases comptables financières',
                'idnumber' => 'AMF_12',
                'subs' => [
                    '12.1' => [
                        'name' => '12.1',
                        'idnumber' => 'AMF_12.1',
                        'type' => 'C',
                    ],
                    '12.2' => [
                        'name' => '12.2',
                        'idnumber' => 'AMF_12.2',
                        'type' => 'C',
                    ],
                    '12.3' => [
                        'name' => '12.3',
                        'idnumber' => 'AMF_12.3',
                        'type' => 'C',
                    ],
                    '12.4' => [
                        'name' => '12.4',
                        'idnumber' => 'AMF_12.4',
                        'type' => 'C',
                    ],
                ]
            ],
        ];
    }

    protected function parse() {
        global $CFG;

        $this->filepath = $this->file->copy_content_to_temp($dir = 'files', $fileprefix = 'amfexcel_');
        mtrace("Reading excel sheet in $filepath ");

        /**  Identify the type of $inputFileName  **/
        $inputType = PHPExcel_IOFactory::identify($this->filepath);
        /**  Create a new Reader of the type that has been identified  **/
        $objReader = PHPExcel_IOFactory::createReader($inputType);
        /**  Load $inputFileName to a PHPExcel Object  **/
        $this->objExcel = $objReader->load($this->filepath);
        $excelSheet = $this->objExcel->getActiveSheet();

        // $data = new SpreadsheetReader($filepath, true);

        // Find real data start.
        for ($i = 1; $i < $excelSheet->getHighestDataRow(); $i++) {
//            $val = $data->val($i, 'B');
            $val = $excelSheet->getCell("B$i")->getValue();
            if ($val == "Theme") {
                $i++;
                break;
            }
        }
        if ($i == $excelSheet->getHighestDataRow()) {
            throw new moodle_exception("Start of data was not found in file. Maybe a not correctly formated AMF file.");
        }

        // $inputencoding = 'ISO-8859-1';
        $inputencoding = 'UTF-8';

        // Continue getting data.
        for (;$i < $excelSheet->getHighestDataRow(); $i++) {
            $amfq = new StdClass;
            $amfq->row = $i;
            $amfq->idnumber = 'AMFQ_'.$excelSheet->getCell("A$i")->getValue();
            $amfq->cat = $excelSheet->getCell("B$i")->getValue(); // Is a category id.
            $amfq->subcat = $excelSheet->getCell("C$i")->getValue();
            $amfq->qtype = $excelSheet->getCell("D$i")->getValue();
            $amfq->qtext = mb_convert_encoding($excelSheet->getCell("E$i")->getValue(), 'UTF-8', $inputencoding);
            $amfq->qatextA = mb_convert_encoding($excelSheet->getCell("F$i")->getValue(), 'UTF-8', $inputencoding);
            $amfq->qatextB = mb_convert_encoding($excelSheet->getCell("G$i")->getValue(), 'UTF-8', $inputencoding);
            $amfq->qatextC = mb_convert_encoding($excelSheet->getCell("H$i")->getValue(), 'UTF-8', $inputencoding);
            $amfq->a = $excelSheet->getCell("I$i")->getValue();
            $amfq->ref = mb_convert_encoding($excelSheet->getCell("J$i")->getValue(), 'UTF-8', $inputencoding);
            $amfq->status = ''; // K col
            $amfq->timeupdated = ''; // L col
            $result[] = $amfq;
            mtrace("Parsed {$amfq->idnumber}");
        }

        mtrace('');
        return $result;
    }

    protected function get_local_categories($options) {
        global $DB;

        $courseid = $this->courseid;

        // All userquiz monitor questions should stand at course level.
        $context = context_course::instance($courseid);
        $returnurl = new moodle_url('/course/view.php', ['id' => $this->courseid]);

        $rootcatid = @$this->uqblockinstance->config->rootcategory;
        if (empty($rootcatid)) {
            print_error("UserQuiz Monitor not configured. Missing Root category", '', $returnurl);
        }

        if (!$DB->get_record('question_categories', ['id' => $rootcatid])) {
            print_error("UserQuiz Monitor Root cat has gone away.", '', $returnurl);
        }

        $cats = [];
        // Get root main cats and check we have AMF id numbers set.
        block_userquiz_monitor_get_cattree($rootcatid, $cats, 1);
        if (empty($cats) || !empty($options['forcecreatecategories'])) {
            $this->create_AMF_defaults($rootcatid, $options);
        } else {
            if ($this->check_AMF_idnumbers($cats)) {
                print_error("AMF categories are there but untagged (or mistagged). Idnumber them from AMF1 to AMF12.", '', $returnurl);
            }
        }

        // Get all levels.
        block_userquiz_monitor_get_cattree($rootcatid, $cats);

        // rearrange by AMF id
        $amfcats = [];
        foreach ($cats as $cat) {
            $amfcats[$cat->idnumber] = $cat;
        }

        return $amfcats;
    }

    protected function check_AMF_idnumbers($cats) {

        $AMFids = [];

        foreach ($cats as $cat) {
            if (!preg_match('/AMF_\d+/', $cat->idnumber)) {
                return 'Empty ID';
            }
            if (array_key_exists($cat->idnumber, $AMFids)) {
                return 'Duple';
            }
            $AMFids[] = $cat->idnumber;

            // Get immediate children only (1 level).
            $subs = [];
            block_userquiz_monitor_get_cattree($cat->id, $subs, 1);
            $this->check_AMF_subidnumbers($subs);
        }
        return false;
    }

    protected function check_AMF_subidnumbers($cats) {
        $AMFids = [];
        foreach ($cats as $cat) {
            if (!preg_match('/AMF_\d+\.\d+/', $cat->idnumber)) {
                return 'Empty ID';
            }
            if (array_key_exists($cat->idnumber, $AMFids)) {
                return 'Duple';
            }
            $AMFids[] = $cat->idnumber;
        }
        return false;
    }

    /**
     * Create a complete default AMF question set in the root category.
     * @param object $rootcat the root category
     * @param array $options some processing options.
     */
    protected function create_AMF_defaults($rootcatid, $options) {
        global $DB;

        $contextid = $DB->get_field('question_categories', 'contextid', ['id' => $rootcatid]);

        $i = 1;
        foreach (self::$AMF_defaults as $cat) {
            $cat = (object) $cat;
            $questioncat = new StdClass;
            $questioncat->name = $cat->name;
            $questioncat->idnumber = $cat->idnumber;
            $questioncat->stamp = make_unique_id_code(); // Set the unique code (not to be changed)
            $questioncat->info = get_string('amfinfo', 'block_userquiz_monitor');
            $questioncat->infoformat = FORMAT_MOODLE;
            $questioncat->parent = $rootcatid;
            $questioncat->sortorder = $i;
            $questioncat->contextid = $contextid;
            $i++;
            if (!empty($options['simulate']) && empty($options['forcecreatecategories'])) {
                mtrace("[SIMULATION] Creating level 1 AMF category {$questioncat->idnumber}");
            } else {
                $params = ['parent' => $rootcatid, 'idnumber' => $questioncat->idnumber];
                if ($oldrec = $DB->get_record('question_categories', $params)) {
                    $questioncat->id = $oldrec->id;
                    $DB->update_record('question_categories', $questioncat);
                    $catid = $oldrec->id; // For subs.
                    if (!empty($options['verbose'])) {
                        mtrace("Updating level 1 AMF category {$questioncat->idnumber}");
                    }
                } else {
                    $catid = $DB->insert_record('question_categories', $questioncat);
                    if (!empty($options['verbose'])) {
                        mtrace("Creating level 1 AMF category {$questioncat->idnumber}");
                    }
                }
            }

            $j = $i * 100;
            foreach ($cat->subs as $sub) {
                $sub = (object) $sub;
                $questioncat = new StdClass;
                $questioncat->name = $sub->name;
                $questioncat->stamp = make_unique_id_code(); // Set the unique code (not to be changed)
                $questioncat->idnumber = $sub->idnumber;
                $questioncat->info = get_string('amfinfo', 'block_userquiz_monitor');
                $questioncat->infoformat = FORMAT_MOODLE;
                $questioncat->parent = $catid;
                $questioncat->sortorder = $j;
                $questioncat->contextid = $contextid;
                $j++;
                if (!empty($options['simulate']) && empty($options['forcecreatecategories'])) {
                    mtrace("[SIMULATION] Creating level 2 AMF category {$questioncat->idnumber}");
                } else {
                    $params = ['parent' => $catid, 'idnumber' => $questioncat->idnumber];
                    if ($oldrec = $DB->get_record('question_categories', $params)) {
                        $questioncat->id = $oldrec->id;
                        if (!empty($options['verbose'])) {
                            mtrace("Updating level 2 AMF category {$questioncat->idnumber}");
                        }
                        $DB->update_record('question_categories', $questioncat);
                    } else {
                        if (!empty($options['verbose'])) {
                            mtrace("Creating level 2 AMF category {$questioncat->idnumber}");
                        }
                        $DB->insert_record('question_categories', $questioncat);
                    }
                }
            }
        }
    }

    /**
     * @param array $amfcats AMF categories (flat array) keyed by idnumber.
     * @param array $remotes Remote set of questions.
     */
    protected function update(array $amfcats, $remotes, array $options) {
        global $DB, $USER;

        if (!empty($options['simulate']) && empty($amfcats)) {
            mtrace("AMF Categories are not setup while in simulation mode.");
            return;
        }

        list($insql, $params) = $DB->get_in_or_equal(array_column($amfcats, 'id'), SQL_PARAMS_NAMED);
        $select = "
            category $insql
        ";

        $oldquestionsrecs = $DB->get_records_select('question', $select, $params, 'idnumber');
        $oldquestions = [];
        if (!empty($oldquestionsrecs)) {
            foreach ($oldquestionsrecs as $req) {
                // Reorder by idnumber.
                $oldquestions[$req->idnumber] = $req;
            }
        }
        unset($oldquestionsrecs); // Free some memory.

        foreach ($remotes as &$amfq) {

            // Get some old question in this questionset scope.
            if (!array_key_exists($amfq->idnumber, $oldquestions)) {
                $qrecord = new Stdclass;
                $new = true;
            } else {
                $qrecord = $oldquestions[$amfq->idnumber];
                $new = false;
            }

            $amfq->status = 'nochange';
            $catkey = 'AMF_'.$amfq->subcat;
            $qrecord->category = $amfcats[$catkey]->id;
            $qrecord->parent = 0;
            $qrecord->name = 'AMF '.$amfq->idnumber;
            $qrecord->idnumber = $amfq->idnumber;
            if (!$new && ($qrecord->questiontext != $amfq->qtext)) {
                $amfq->status = 'updated';
                $amfq->updatetime = time();
            }
            $qrecord->questiontext = $amfq->qtext;
            $qrecord->questiontextformat = FORMAT_HTML;
            $qrecord->generalfeedback = '';
            $qrecord->generalfeedbackformat = FORMAT_HTML;
            $qrecord->defaultmark = ($amfq->qtype == 'C') ? 1000.0 : 1.0;
            $qrecord->penalty = 0;
            $qrecord->qtype = 'multichoice';
            $qrecord->length = 1;
            $qrecord->hidden = 0;
            if (empty($qrecord->stamp)) {
                // First time created.
                $qrecord->stamp = make_unique_id_code();
            }
            if (empty($qrecord->timecreated)) {
                // First time created.
                $qrecord->timecreated = time();
                $qrecord->createdby = $USER->id;
            } else {
                $qrecord->timemodified = time();
                $qrecord->modifiedby = $USER->id;
            }
            $qrecord->version = make_unique_id_code();

            if (!empty($qrecord->id)) {
                if (!empty($options['simulate'])) {
                    mtrace("[SIMULATION] : Update question [{$qrecord->idnumber}]{$qrecord->id}");
                } else {
                    mtrace("Update question [{$qrecord->idnumber}]{$qrecord->id}");
                    $DB->update_record('question', $qrecord);
                }
                // Unmark for future deletions.
                unset($oldquestions[$amfq->idnumber]);
            } else {
                if (!empty($options['simulate'])) {
                    mtrace("[SIMULATION] : Create question [{$qrecord->idnumber}]");
                } else {
                    mtrace("Create question [{$qrecord->idnumber}]");
                    $qrecord->id = $DB->insert_record('question', $qrecord);
                    $amfq->status = 'created';
                    $amfq->updatetime = time();
               }
            }

            // Update/create answers.
            /*
             * We should take care NOT to alter the good answer.
             */
            $oldanswers = null;
            if (empty($options['simulate']) | !empty($qrecord->id)) {
                $oldanswers = $DB->get_records('question_answers', ['question' => $qrecord->id], 'id');
            }
            if ($oldanswers) {
                // First approach : consider anwers are given in same order than previous version, so id order matchs
                // A,B,C column order.
                $letters = ['A', 'B', 'C'];
                $aix = 0;
                foreach ($oldanswers as $oa) {
                    $textkey = 'qatext'.$letters[$aix];
                    if ($oa->answer != $amfq->$textkey) {
                        $amfq->status = 'updated';
                        $amfq->updatetime = time();
                    }
                    $oa->answer = $amfq->$textkey;
                    if ($aix == $amfq->a) {
                        $oa->fraction = 1.0;
                    } else {
                        $oa->fraction = 0.0;
                    }
                    $DB->update_record('question_answers', $oa);
                    $aix++;
                }

                // NOTE : We'll see if its enough.
            } else {
                // All are new answers. Just create them.
                $letters = ['A', 'B', 'C'];
                foreach ($letters as $aix) {
                    $answer = new Stdclass;
                    if (empty($options['simulate'])) {
                        $answer->question = $qrecord->id;
                    }
                    $textkey = 'qatext'.$aix;
                    $answer->answer = $amfq->$textkey;
                    $answer->answerformat = FORMAT_HTML;
                    if ($aix == $amfq->a) {
                        $answer->fraction = 1.0;
                    } else {
                        $answer->fraction = 0.0;
                    }
                    $answer->feedback = '';
                    $answer->feedbackformat = FORMAT_HTML;
                    if (!empty($options['simulate'])) {
                        mtrace("[SIMULATION] : Create answer [{$textkey}] : {$answer->answer} / {$answer->fraction}");
                    } else {
                        $DB->insert_record('question_answers', $answer);
                    }
                }
            }
        }

        // Here what remains in $oldquestions have disappeared from remote.
        foreach ($oldquestions as $tohide) {
            // We will hide questions here. Because some old attempts may use them.
            if (!empty($options['simulate'])) {
                mtrace("[SIMULATION] : Hiding deleted question {$tohide->id} [{$tohide->idnumber}]");
            } else {
                $tohide->hidden = true;
                $DB->update_record('question', $tohide);
            }
        }

        $this->writeback($remotes);
    }

    protected function writeback($remotes) {
        // Write back status an updated dates in the worksheet.

        $worksheet = $this->objExcel->getActiveSheet();

        foreach ($remotes as $amfq) {
            // Status cell
            $worksheet->setCellValue('K'.$amfq->row, $amfq->status);
            $worksheet->setCellValue('L'.$amfq->row, PHPExcel_Shared_Date::PHPToExcel($amfq->updatetime));
            $dateformat = PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH;
            $worksheet->getStyle('L'.$amfq->aix)->getNumberFormat()->setFormatCode($dateformat);
        }

        // Write a temp response.
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
        $outputpath = $this->filepath;
        $outputpath = str_replace('.xls', '_output.xls', $this->filepath);
        $objWriter->save($outputpath);

        // Get back temp response in a moodle filearea
        $filerecord = new StdClass;
        $filerecord->contextid = context_system::instance()->id;
        $filerecord->component = 'block_userquiz_monitor';
        $filerecord->filearea = 'importresult';
        $filerecord->itemid = 0;
        $filerecord->filepath = '/';
        $filerecord->filename = basename($outputpath);

        $fs = get_file_storage();
        $this->resultfile = $fs->create_file_from_pathname($filerecord, $outputpath);
    }
}