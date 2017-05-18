/**
 * This will only work for quizzes having :
 * - being customscripted as required
 * - having single question per page
 * - being linnked to an enabling userquiz_monitor panel.
 */
// jshint undef:false, unused:false
/* globals $ */


define(['jquery', 'core/str'], function($, str) {

    var f = function(e) {
        str.get_string('looseattemptsignal', 'theme_essential_barchen').done(function(s) {
            check = confirm(s);
        });
        if (!check) {
            e.stopPropagation();
            return false;
        }
    };

    return {
        init: function() {
            $('header a').click(f);
            $('#page-navbar a').click(f);
        }
    };

});