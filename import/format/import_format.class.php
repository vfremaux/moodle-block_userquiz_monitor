<?php

namespace block_userquiz_monitor\import;

use stored_file;
use context_course;
use StdClass;
use moodle_url;
use moodle_exception;

abstract class import_format {

    protected $defaults;

    protected $file;

    protected $uqblockinstance;

    protected $courseid;

    public function __construct(stored_file $file, $uqblockinstance, $courseid) {
        $this->defaults = [];
        $this->file = $file;
        $this->courseid = $courseid;
        $this->uqblockinstance = $uqblockinstance;
    }

    /**
     * Get the internal question file content, parse and update the uq_monitor instance question bank
     */
    public function import(array $options = []) {

        // make a temp table with local ids.
        $localcats = $this->get_local_categories($options);
        $remotes = $this->parse();

        // Import.
        $this->update($localcats, $remotes, $options);
    }

    protected abstract function parse(); // parse can be subject to variations depending on the Excel sheet layout.

    /**
     * Create a complete default question set in the root category.
     * @param object $rootcat the root category
     * @param array $options some processing options.
     */
    protected function create_defaults($rootcatid, $options) {
        global $DB;

        $contextid = $DB->get_field('question_categories', 'contextid', ['id' => $rootcatid]);

        $i = 1;
        foreach ($this->defaults as $cat) {
            $cat = (object) $cat;
            $questioncat = new StdClass;
            $questioncat->name = $cat->name;
            $questioncat->idnumber = $cat->idnumber;
            $questioncat->stamp = make_unique_id_code(); // Set the unique code (not to be changed)
            $questioncat->info = get_string($this->get_id_prefix().'info', 'block_userquiz_monitor');
            $questioncat->infoformat = FORMAT_MOODLE;
            $questioncat->parent = $rootcatid;
            $questioncat->sortorder = $i;
            $questioncat->contextid = $contextid;
            $i++;
            if (!empty($options['simulate']) && empty($options['forcecreatecategories'])) {
                mtrace("[SIMULATION] Creating level 1 ".$this->get_name_prefix()." category {$questioncat->idnumber}");
            } else {
                $params = ['parent' => $rootcatid, 'idnumber' => $questioncat->idnumber];
                if ($oldrec = $DB->get_record('question_categories', $params)) {
                    $questioncat->id = $oldrec->id;
                    $DB->update_record('question_categories', $questioncat);
                    $catid = $oldrec->id; // For subs.
                    if (!empty($options['verbose'])) {
                        mtrace("Updating level 1 ".$this->get_name_prefix()." category {$questioncat->idnumber}");
                    }
                } else {
                    $catid = $DB->insert_record('question_categories', $questioncat);
                    if (!empty($options['verbose'])) {
                        mtrace("Creating level 1 ".$this->get_name_prefix()." category {$questioncat->idnumber}");
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
                $questioncat->info = get_string($this->get_id_prefix().'info', 'block_userquiz_monitor');
                $questioncat->infoformat = FORMAT_MOODLE;
                $questioncat->parent = $catid;
                $questioncat->sortorder = $j;
                $questioncat->contextid = $contextid;
                $j++;
                if (!empty($options['simulate']) && empty($options['forcecreatecategories'])) {
                    mtrace("[SIMULATION] Creating level 2 ".$this->get_name_prefix()." category {$questioncat->idnumber}");
                } else {
                    $params = ['parent' => $catid, 'idnumber' => $questioncat->idnumber];
                    if ($oldrec = $DB->get_record('question_categories', $params)) {
                        $questioncat->id = $oldrec->id;
                        if (!empty($options['verbose'])) {
                            mtrace("Updating level 2 ".$this->get_name_prefix()." category {$questioncat->idnumber}");
                        }
                        $DB->update_record('question_categories', $questioncat);
                    } else {
                        if (!empty($options['verbose'])) {
                            mtrace("Creating level 2 ".$this->get_name_prefix()." category {$questioncat->idnumber}");
                        }
                        $DB->insert_record('question_categories', $questioncat);
                    }
                }
            }
        }
    }

    protected function get_local_categories($options) {
        global $DB;

        $courseid = $this->courseid;

        // All userquiz monitor questions should stand at course level.
        $context = context_course::instance($courseid);
        $returnurl = new moodle_url('/course/view.php', ['id' => $this->courseid]);

        $rootcatid = @$this->uqblockinstance->config->rootcategory;
        if (empty($rootcatid)) {
            throw new moodle_exception("UserQuiz Monitor not configured. Missing Root category");
        }

        if (!$DB->get_record('question_categories', ['id' => $rootcatid])) {
            throw new moodle_exception("UserQuiz Monitor Root cat has gone away.");
        }

        $cats = [];
        // Get root main cats and check we have FDEN id numbers set.
        block_userquiz_monitor_get_cattree($rootcatid, $cats, 1);
        if (empty($cats) || !empty($options['forcecreatecategories'])) {
            $this->create_defaults($rootcatid, $options);
        } else {
            if ($this->check_idnumbers($cats)) {
                $prf = $this->get_name_prefix();
                $message = $prf." categories are there but untagged (or mistagged). Idnumber them from {$prf}1 to {$prf}5.";
                throw new moodle_exception();
            }
        }

        // Get all levels.
        block_userquiz_monitor_get_cattree($rootcatid, $cats);

        // rearrange by id
        $outcats = [];
        foreach ($cats as $cat) {
            $outcats[$cat->idnumber] = $cat;
        }

        return $outcats;
    }

    abstract public function get_id_prefix();

    protected function get_name_prefix() {
        return strtoupper($this->get_id_prefix());
    }

    /**
     * @param array $trainingcats categories (flat array) keyed by idnumber.
     * @param array $remotes Remote set of questions.
     */
    protected function update(array $trainingcats, &$remotes, array $options) {
        global $DB, $USER;

        if (!empty($options['simulate']) && empty($trainingcats)) {
            mtrace($this->get_name_prefix()." Categories are not setup while in simulation mode.");
            return;
        }

        list($insql, $params) = $DB->get_in_or_equal(array_column($trainingcats, 'id'), SQL_PARAMS_NAMED);
        $sql = "
            SELECT
                q.*,
                qbe.questioncategoryid as category,
                qbe.idnumber as idnumber
            FROM
                {question} q,
                {question_versions} qv,
                {question_bank_entries} qbe
            WHERE
                q.id = qv.questionid AND
                qv.questionbankentryid = qbe.id AND
                qbe.questioncategoryid $insql
            ORDER BY
                qbe.idnumber
        ";

        $oldquestionsrecs = $DB->get_records_sql($sql, $params);

        $oldquestions = [];
        if (!empty($oldquestionsrecs)) {
            foreach ($oldquestionsrecs as $req) {
                // Reorder by idnumber.
                $oldquestions[$req->idnumber] = $req;
            }
        }
        unset($oldquestionsrecs); // Free some memory.

        foreach ($remotes as $q) {

            // Get some old question in this questionset scope.
            if (!array_key_exists($q->idnumber, $oldquestions)) {
                $qrecord = new Stdclass;
                $new = true;
            } else {
                $qrecord = $oldquestions[$q->idnumber];
                $new = false;
            }

            $q->status = 'nochange';
            $q->updatetime = 0;
            $catkey = $this->get_name_prefix().'_'.$q->cat.'.'.$q->subcat;
            $qrecord->category = $trainingcats[$catkey]->id;
            $qrecord->parent = 0;
            $qrecord->name = $this->get_name_prefix().' '.$q->idnumber;
            $qrecord->idnumber = $q->idnumber;
            if (!$new && ($qrecord->questiontext != $q->qtext)) {
                $q->status = 'updated';
                $q->updatetime = time();
            }
            $qrecord->questiontext = $q->qtext;
            $qrecord->questiontextformat = FORMAT_HTML;
            $qrecord->generalfeedback = '';
            $qrecord->generalfeedbackformat = FORMAT_HTML;
            $qrecord->defaultmark = ($q->qtype == 'C') ? 1000.0 : 1.0;
            $qrecord->penalty = 0;
            $qrecord->qtype = 'multichoice';
            $qrecord->length = 1;
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
                    mtrace("[SIMULATION] : Update question [{$qrecord->idnumber}]{$qrecord->id} in {$catkey}");
                } else {
                    mtrace("Update question [{$qrecord->idnumber}]{$qrecord->id} in {$catkey}");
                    $category = $qrecord->category;
                    unset($qrecord->category);
                    $idnumber = $qrecord->idnumber;
                    unset($qrecord->idnumber);

                    $DB->update_record('question', $qrecord);

                    // Find category mappings
                    // Update question bank reference
                    $qv = $DB->get_record('question_versions', ['questionid' => $qrecord->id, 'status' => 'ready']);
                    $qbe = $DB->get_record('question_bank_entries', ['id' => $qv->questionbankentryid]);

                    // Discard other records in the way.
                    $select = ' questioncategoryid = ? AND idnumber = ? AND id != ? ';
                    $params = [$category, $idnumber, $qv->questionbankentryid];
                    $DB->delete_records_select('question_bank_entries', $select, $params);

                    $qbe->questioncategoryid = $category;
                    $qbe->idnumber = $idnumber;
                    $DB->update_record('question_bank_entries', $qbe);
                }
                // Unmark for future deletions.
                unset($oldquestions[$q->idnumber]);
            } else {
                if (!empty($options['simulate'])) {
                    mtrace("[SIMULATION] : Create question [{$qrecord->idnumber}] in {$catkey}");
                } else {
                    mtrace("Create question [{$qrecord->idnumber}] in {$catkey}");
                    $category = $qrecord->category;
                    unset($qrecord->category);
                    $idnumber = $qrecord->idnumber;
                    unset($qrecord->idnumber);
                    $qrecord->id = $DB->insert_record('question', $qrecord);

                    // Map category in question bank
                    if ($oldqbe = $DB->get_record('question_bank_entries', ['questioncategoryid' => $category, 'idnumber' => $idnumber])) {
                        $qbe = $oldqbe;
                    } else {
                        $qbe = new StdClass;
                        $qbe->questioncategoryid = $category;
                        $qbe->idnumber = $idnumber;
                        $qbe->ownerid = null;
                        $qbe->id = $DB->insert_record('question_bank_entries', $qbe);
                    }

                    // Map qbe to question by version
                    $qv = new StdClass;
                    $qv->questionbankentryid = $qbe->id;
                    $qv->questionid = $qrecord->id;
                    $qv->version = 1;
                    $qv->status = 'ready';
                    $DB->insert_record('question_versions', $qv);

                    $q->status = 'created';
                    $q->updatetime = time();
               }
            }

            if (!empty($qrecord->id)) {
                // Supposes NOT being in simulation.
                // Create multichoice qtype record if missing.
                $params = ['questionid' => $qrecord->id];
                if (!$oldrec = $DB->get_record('qtype_multichoice_options', $params)) {
                    $qtyperec = new Stdclass;
                    $qtyperec->questionid = $qrecord->id;
                    $qtyperec->layout = 0;
                    $qtyperec->single = 1;
                    $qtyperec->shuffleanswers = 1;
                    $qtyperec->correctfeedback = '';
                    $qtyperec->correctfeedbackformat = 1;
                    $qtyperec->partiallycorrectfeedback = '';
                    $qtyperec->partiallycorrectfeedbackformat = 1;
                    $qtyperec->incorrectfeedback = '';
                    $qtyperec->incorrectfeedbackformat = 1;
                    $qtyperec->answernumbering = '123';
                    $qtyperec->shownumcorrect = 0;

                    $DB->insert_record('qtype_multichoice_options', $qtyperec);
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
                    if ($oa->answer != $q->$textkey) {
                        $q->status = 'updated';
                        $q->updatetime = time();
                    }
                    $oa->answer = $q->$textkey;
                    if ($letters[$aix] == $q->a) {
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
                foreach ($letters as $let) {
                    if (!empty($options['simulate'])) {
                        mtrace("[SIMULATION] : Create answer for letter [{$let}] : ");
                        continue;
                    }
                    $answer = new Stdclass;
                    $answer->question = $qrecord->id;
                    $textkey = 'qatext'.$let;
                    $answer->answer = $q->$textkey;
                    $answer->answerformat = FORMAT_HTML;
                    if ($let == $q->a) {
                        $answer->fraction = 1.0;
                    } else {
                        $answer->fraction = 0.0;
                    }
                    $answer->feedback = '';
                    $answer->feedbackformat = FORMAT_HTML;
                    $DB->insert_record('question_answers', $answer);
                }
            }
        }

        // Here what remains in $oldquestions have disappeared from remote.
        foreach ($oldquestions as $tohide) {
            // We will hide questions here. Because some old attempts may use them.
            if (!empty($options['simulate'])) {
                mtrace("[SIMULATION] : Hiding deleted question {$tohide->id} [{$tohide->idnumber}]");
            } else {
                $DB->set_field('question_versions', 'status', 'hidden', ['questionid' => $tohide->id]);
            }
        }

        $this->writeback($remotes);
    }

    /**
     * @param array $remotes the array of updated input question data.
     */
    protected function writeback($remotes) {
        // Write back status an updated dates in the worksheet.

        $worksheet = $this->objExcel->getActiveSheet();

        foreach ($remotes as $q) {
            // Status cell
            $worksheet->setCellValue('K'.$q->row, $q->status);
            $worksheet->setCellValue('L'.$q->row, \PHPExcel_Shared_Date::PHPToExcel($q->updatetime));
            $dateformat = \PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDDSLASH;
            $worksheet->getStyle('L'.$q->row)->getNumberFormat()->setFormatCode($dateformat);
        }

        // Write a temp response.
        $objWriter = \PHPExcel_IOFactory::createWriter($this->objExcel, "Excel2007");
        $outputpath = $this->filepath;
        $outputpath = str_replace('.xls', '_output.xls', $this->filepath);
        $objWriter->save($outputpath);

        // Get back temp response in a moodle filearea
        $filerecord = new StdClass;
        $filerecord->contextid = \context_system::instance()->id;
        $filerecord->component = 'block_userquiz_monitor';
        $filerecord->filearea = 'importresult';
        $filerecord->itemid = 0;
        $filerecord->filepath = '/';
        $filerecord->filename = basename($outputpath);

        $fs = get_file_storage();
        $this->resultfile = $fs->create_file_from_pathname($filerecord, $outputpath);
    }

    /**
     * Checks the integrity of first level question categories.
     */
    protected function check_idnumbers($cats) {

        $ids = [];

        foreach ($cats as $cat) {
            if (!preg_match('/'.$this->get_name_prefix().'_\d+/', $cat->idnumber)) {
                return 'Empty ID';
            }
            if (array_key_exists($cat->idnumber, $ids)) {
                return 'Duple';
            }
            $ids[] = $cat->idnumber;

            // Get immediate children only (1 level).
            $subs = [];
            block_userquiz_monitor_get_cattree($cat->id, $subs, 1);
            $this->check_subidnumbers($subs);
        }
        return false;
    }

    /**
     * Checks the integrity of second level question categories.
     */
    protected function check_subidnumbers($cats) {

        $ids = [];
        foreach ($cats as $cat) {
            if (!preg_match('/'.$this->get_name_prefix().'_\d+\.\d+/', $cat->idnumber)) {
                return 'Empty ID';
            }
            if (array_key_exists($cat->idnumber, $ids)) {
                return 'Duple';
            }
            $ids[] = $cat->idnumber;
        }
        return false;
    }
}