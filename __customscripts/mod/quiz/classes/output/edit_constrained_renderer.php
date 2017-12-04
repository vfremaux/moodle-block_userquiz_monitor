<?php

namespace mod_quiz\output;

class edit_constrained_renderer extends edit_renderer {

    /**
     * Override adding randomconstrained menu item
     */
    public function edit_menu_actions(\mod_quiz\structure $structure, $page, \moodle_url $pageurl, array $pagevars) {

        $actions = parent::edit_menu_actions($structure, $page, $pageurl, $pagevars);

        $str = get_string('addrandomconstrainedquestion', 'quizaccess_chooseconstraints');
        $returnurl = new \moodle_url('/mod/quiz/edit.php', array('cmid' => $structure->get_cmid(), 'data-addonpage' => $page));
        $params = array('returnurl' => urlencode($returnurl), 'cmid' => $structure->get_cmid(), 'appendqnumstring' => 'addarandomconstrainedquestion');
        $url = new \moodle_url('/mod/quiz/accessrule/chooseconstraints/addrandomconstrained.php', $params);
        $icon = new \pix_icon('t/add', $str, 'moodle', array('class' => 'iconsmall', 'title' => ''));
        $attributes = array('class' => 'cm-edit-action addarandomconstrainedquestion', 'data-action' => 'addarandomconstrainedquestion');
        if ($page) {
            $title = get_string('addrandomconstrainedquestiontopage', 'quizaccess_chooseconstraints', $page);
        } else {
            $title = get_string('addrandomconstrainedquestionatend', 'quizaccess_chooseconstraints');
        }
        $attributes = array_merge(array('data-header' => $title, 'data-addonpage' => $page), $attributes);
        $actions['addarandomconstrainedquestion'] = new \action_menu_link_secondary($url, $icon, $str, $attributes);

        $str = get_string('addtenrandomconstrainedquestion', 'quizaccess_chooseconstraints');
        $returnurl = new \moodle_url('/mod/quiz/edit.php', array('cmid' => $structure->get_cmid(), 'data-addonpage' => $page));
        $params = array('returnurl' => urlencode($returnurl), 'cmid' => $structure->get_cmid(), 'appendqnumstring' => 'addtenarandomconstrainedquestion', 'randomcount' => 10);
        $url = new \moodle_url('/mod/quiz/accessrule/chooseconstraints/addrandomconstrained.php', $params);
        $icon = new \pix_icon('t/add', $str, 'moodle', array('class' => 'iconsmall', 'title' => ''));
        $attributes = array('class' => 'cm-edit-action addarandomconstrainedquestion', 'data-action' => 'addarandomconstrainedquestion');
        if ($page) {
            $title = get_string('addrandomconstrainedquestiontopage', 'quizaccess_chooseconstraints', $page);
        } else {
            $title = get_string('addrandomconstrainedquestionatend', 'quizaccess_chooseconstraints');
        }
        $attributes = array_merge(array('data-header' => $title, 'data-addonpage' => $page), $attributes);
        $actions['addarandomconstrainedquestion'] = new \action_menu_link_secondary($url, $icon, $str, $attributes);

        return $actions;
    }

    /**
     * Return random question form.
     * @param \moodle_url $thispageurl the canonical URL of this page.
     * @param \question_edit_contexts $contexts the relevant question bank contexts.
     * @param array $pagevars the variables from {@link \question_edit_setup()}.
     * @return string HTML to output.
     */
    protected function randomconstrained_question_form(\moodle_url $thispageurl, \question_edit_contexts $contexts, array $pagevars) {

        if (!$contexts->have_cap('moodle/question:useall')) {
            return '';
        }
        $randomform = new \quiz_add_randomconstrained_form(new \moodle_url('/mod/quiz/accessrule/chooseconstraints/addrandomconstrained.php'),
                                 array('contexts' => $contexts, 'cat' => $pagevars['cat']));
        $randomform->set_data(array(
            'returnurl' => $thispageurl->out_as_local_url(true),
            'randomnumber' => 1,
            'cmid' => $thispageurl->param('cmid'),
        ));
        return html_writer::div($randomform->render(), 'randomquestionformforpopup');
    }
}