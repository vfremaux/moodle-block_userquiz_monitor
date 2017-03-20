/**
 * This will only work for quizzes having :
 * - being customscripted as required
 * - having single question per page
 * - being linnked to an enabling userquiz_monitor panel.
 */
// jshint undef:false unused:false
/* globals $ */
/* globals jQuery */

$(document).ready( function() {

    // Disables end button.
    $('.mod_quiz-next-nav').attr('disabled', 'disabled');

    // Add onclic observer on all question options
    $('#responseform input').on('change', function() {
        $('.mod_quiz-next-nav').attr('disabled', null);
    });
});