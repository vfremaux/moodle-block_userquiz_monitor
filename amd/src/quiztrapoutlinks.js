/**
 * This will only work for quizzes having :
 * - being customscripted as required
 * - having single question per page
 * - being linnked to an enabling userquiz_monitor panel.
 */
// jshint undef:false, unused:false
/* globals $ */


define(['jquery', 'core/str', 'core/log'], function($, str, log) {

    var f = function(e) {

        e.stopPropagation();
        str.get_string('looseattemptsignal', 'theme_essential_barchen').done(function(s) {
            outcheck = confirm(s);
        });
        if (!outcheck) {
            return false;
        }
        return true;
    };

    return {
        init: function() {
            $('header a, .navbar a, .breadcrumb a').click(f);
            $('header a[target="_blank"]').unbind('click', f);
            $('.navbar a[target="_blank"]').unbind('click', f);
            $('.navbar a.dropdown-toggle').unbind('click', f);
            $('.navbar a[data-toggle="collapse"]').unbind('click', f);

            log.debug('AMD Block_userquiz_monnitor quiztrapoutlinks initialized');
        }
    };

});