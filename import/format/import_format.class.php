<?php

namespace block_userquiz_monitor\import;

use \stored_file;

abstract class import_format {

    protected $file;

    protected $uqblockinstance;

    protected $courseid;

    public function __construct(stored_file $file, $uqblockinstance, $courseid) {
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

    protected abstract function parse();

    protected abstract function update(array $amfcats, &$remotes, array $options);
}