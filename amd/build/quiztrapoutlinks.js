/**
 * This will only work for quizzes having :
 * - being customscripted as required
 * - having single question per page
 * - being linnked to an enabling userquiz_monitor panel.
 */
// jshint undef:false, unused:false
/* globals $ */


define(['jquery', 'core/str'], function($, str) {

    var passed;

    var f = function(e) {

        if (passed) return;

        e.stopPropagation();
        str.get_string('looseattemptsignal', 'theme_essential_barchen').done(function(s) {
            check = confirm(s);
        });
        passed = true;
        if (!check) {
            return false;
        }
        return true;
    };

    return {
        init: function() {
            $('header a').click(f);
            $('.navbar a').click(f);
            $('header a[target="_blank"]').unbind('click', f);
            $('.navbar a[target="_blank"]').unbind('click', f);
        }
    };

});