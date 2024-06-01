<?php

namespace block_userquiz_monitor\import;

require_once($CFG->dirroot.'/blocks/userquiz_monitor/import/format/import_format.class.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/extralibs/PHPExcel-1.8/Classes/PHPExcel.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/extralibs/PHPExcel-1.8/Classes/PHPExcel/Shared/Date.php');

use PHPExcel_IOFactory;
use StdClass;
use stored_file;
use moodle_exception;

class amf_format extends import_format {

    protected $filepath;

    protected $objExcel;

    public $resultfile;

    public function __construct(stored_file $file, $uqblockinstance, $courseid) {

        parent::__construct($file, $uqblockinstance, $courseid);

        $this->defaults = [
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

    public function get_id_prefix() {
        $classname = (new \ReflectionClass($this))->getShortName();
        $parts = explode('_', $classname);
        $idprefix = $parts[0];
        return $idprefix;
    }

    /**
     * Parse the ODS/XLS file.
     */
    protected function parse() {
        global $CFG;

        $this->filepath = $this->file->copy_content_to_temp($dir = 'files', $fileprefix = $this->get_id_prefix().'excel_');
        mtrace("Reading excel sheet in {$this->filepath} ");

        /**  Identify the type of $inputFileName  **/
        $inputType = PHPExcel_IOFactory::identify($this->filepath);
        // mtrace("Excel sheet identified as {$inputType} ");
        /**  Create a new Reader of the type that has been identified  **/
        $objReader = PHPExcel_IOFactory::createReader($inputType);
        /**  Load $inputFileName to a PHPExcel Object  **/
        $this->objExcel = $objReader->load($this->filepath);
        $excelSheet = $this->objExcel->getActiveSheet();

        // $data = new SpreadsheetReader($filepath, true);

        $lastrow = $excelSheet->getHighestDataRow();
        if ($lastrow <= 1) {
            debug_trace("Bad last row at row $lastrow ", TRACE_ERROR);
        }
        debug_trace("Found last row as $lastrow ", TRACE_DEBUG_FINE);

        // Find real data start.
        for ($i = 1; $i < $lastrow; $i++) {
//            $val = $data->val($i, 'B');
            $val = $excelSheet->getCell("B$i")->getValue();
            if (substr($val, 0,2) == "Th") {
                // Stronger test : allors Theme or other accent versions.
                $i++;
                break;
            }
        }
        if ($i == $excelSheet->getHighestDataRow()) {
            throw new moodle_exception("Start of data was not found in file. Maybe a not correctly formated FD (EN) file.");
        }

        // $inputencoding = 'ISO-8859-1';
        $inputencoding = 'UTF-8';

        // Continue getting data.
        for (;$i < $excelSheet->getHighestDataRow(); $i++) {
            $q = new StdClass;
            $q->row = $i;
            $q->idnumber = $this->get_name_prefix().'Q_'.$excelSheet->getCell("A$i")->getValue();
            $q->cat = $excelSheet->getCell("B$i")->getValue(); // Is a category id.
            $q->subcat = $excelSheet->getCell("C$i")->getValue();
            $q->qtype = $excelSheet->getCell("D$i")->getValue();
            $q->qtext = mb_convert_encoding($excelSheet->getCell("E$i")->getValue(), 'UTF-8', $inputencoding);
            $q->qatextA = mb_convert_encoding($excelSheet->getCell("F$i")->getValue(), 'UTF-8', $inputencoding);
            $q->qatextB = mb_convert_encoding($excelSheet->getCell("G$i")->getValue(), 'UTF-8', $inputencoding);
            $q->qatextC = mb_convert_encoding($excelSheet->getCell("H$i")->getValue(), 'UTF-8', $inputencoding);
            $q->a = $excelSheet->getCell("I$i")->getValue();
            $q->ref = mb_convert_encoding($excelSheet->getCell("J$i")->getValue(), 'UTF-8', $inputencoding);
            $q->status = ''; // K col
            $q->timeupdated = ''; // L col
            $result[] = $q;
            mtrace("Parsed {$q->idnumber}");
        }

        mtrace('');
        return $result;
    }

}