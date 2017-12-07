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

            // Disables end button.
            questions = $('.que.notyetanswered');
            feedbacks = $('.immediatefeedback');
            if (questions.length > 0) {
                // If we do not have all answered, disable the next button.
                $('.mod_quiz-next-nav').attr('disabled', 'disabled');
                $('.mod_quiz-next-nav').css('visibility', 'visible');
                $('.im-controls').css('visibility', 'hidden');
                $('.is-userquiz #responseform input[type=radio]').css('visibility', 'visible');
                $('.is-userquiz #responseform input[type=radio] + label').css('pointer-events', 'auto');
            } else {
                // Show the nav button back.
                $('.mod_quiz-next-nav').css('visibility', 'visible');
                $('.is-userquiz #responseform input[type=radio]').css('visibility', 'visible');
                $('.is-userquiz #responseform input[type=radio] + label').css('pointer-events', 'auto');
            }

            // Add onchange observer on all question options.
            $('#responseform input').on('change', function() {
                $('.mod_quiz-next-nav').attr('disabled', null);
                $('.im-controls').css('visibility', 'visible');
            });

            log.debug('AMD Block_userquiz_monnitor quizforceanswer initialized');
        }
    };
});