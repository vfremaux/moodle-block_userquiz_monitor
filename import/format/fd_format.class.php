<?php

namespace block_userquiz_monitor\import;

require_once($CFG->dirroot.'/blocks/userquiz_monitor/import/format/import_format.class.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/extralibs/PHPExcel-1.8/Classes/PHPExcel.php');
require_once($CFG->dirroot.'/blocks/userquiz_monitor/extralibs/PHPExcel-1.8/Classes/PHPExcel/Shared/Date.php');

use PHPExcel_IOFactory;
use StdClass;
use stored_file;
use moodle_exception;

class fd_format extends import_format {

    protected $filepath;

    protected $objExcel;

    public $resultfile;

    public function __construct(stored_file $file, $uqblockinstance, $courseid) {

        parent::__construct($file, $uqblockinstance, $courseid);

        $this->defaults = [
            1 => [
                'name' => 'LA FINANCE DURABLE ET LES PRINCIPALES NOTIONS',
                'idnumber' => 'FD_1',
                'subs' => [
                    '1.1' => [
                        'name' => '1.1 Les notions',
                        'idnumber' => 'FD_1.1',
                        'type' => 'A',
                    ],
                ]
            ],
            2 => [
                'name' => 'LE CADRE RÉGLEMENTAIRE FRANÇAIS ET EUROPÉEN',
                'idnumber' => 'FD_2',
                'subs' => [
                    '2.1' => [
                        'name' => '2.1 L\'harmonisation et l\'accroissement de la transparence',
                        'idnumber' => 'FD_2.1',
                        'type' => 'A',
                    ],
                    '2.2' => [
                        'name' => '2.2 La durabilité des activités d’un point de vue environnemental',
                        'idnumber' => 'FD_2.2',
                        'type' => 'A',
                    ],
                    '2.3' => [
                        'name' => '2.3 L\'impact sur la réglementation européenne et française existante',
                        'idnumber' => 'FD_2.3',
                        'type' => 'A',
                    ],
                ]
            ],
            3 => [
                'name' => 'LES ENTREPRISES ET LES ACTEURS NON FINANCIERS : LES ENJEUX ENVIRONNEMENTAUX, SOCIAUX ET DE GOUVERNANCE',
                'idnumber' => 'FD_3',
                'subs' => [
                    '3.1' => [
                        'name' => '3.1 Les entreprises et les acteurs',
                        'idnumber' => 'FD_3.1',
                        'type' => 'A',
                    ],
                ]
            ],
            4 => [
                'name' => 'LES APPROCHES EXTRA-FINANCIÈRES DANS LE DOMAINE DE LA GESTION D\'ACTIFS',
                'idnumber' => 'FD_4',
                'subs' => [
                    '4.1' => [
                        'name' => '4.1 Les approches extra-financières',
                        'idnumber' => 'FD_4.1',
                        'type' => 'A',
                    ]
                ]
            ],
            5 => [
                'name' => 'LA COMMERCIALISATION DES PRODUITS LIÉS À LA FINANCE DURABLE',
                'idnumber' => 'FD_5',
                'subs' => [
                    '5.1' => [
                        'name' => '5.1 La commercilaisation',
                        'idnumber' => 'FD_5.1',
                        'type' => 'A',
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