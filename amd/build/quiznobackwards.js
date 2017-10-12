/**
 * This will only work for quizzes having :
 * - being customscripted as required
 * - having single question per page
 * - being linnked to an enabling userquiz_monitor panel.
 */
// jshint undef:false, unused:false
/* globals $ */

define(['jquery', 'core/log'], function($, log) {

    return {
        init: function() {
            // Disables prev button.
            $('.mod_quiz-prev-nav').css('display', 'none');

            log.debug('AMD Block_userquiz_monnitor quiznobackwards initialized');
        }
    };
});