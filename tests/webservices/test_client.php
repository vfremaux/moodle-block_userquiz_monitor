<?php

require_once('test_client_base.php');

class test_client extends test_client_base {

    public function test_get_attempt_review($attemptid, $page) {

        if (empty($this->t->baseurl)) {
            echo "Test target not configured\n";
            return;
        }

        if (empty($this->t->wstoken)) {
            echo "No token to proceed\n";
            return;
        }

        $params = array('wstoken' => $this->t->wstoken,
                        'wsfunction' => 'block_userquiz_monitor_get_attempt_review',
                        'moodlewsrestformat' => 'json',
                        'attemptid' => $attemptid,
                        'page' => $page);

        $serviceurl = $this->t->baseurl.$this->t->service;

        return $this->send($serviceurl, $params);
    }

}

// Effective test scenario.

echo "STARTING:\n";
$client = new test_client();

echo "TEST GET ATTEMPT REVIEW ALL PAGES:\n";
$client->test_get_attempt_review(8, '-1');

echo "TEST GET ATTEMPT REVIEW INE PAGE:\n";
$client->test_get_attempt_review(8, 1);

