/**
 * this adds anto-copy security to the quiz content.
 * This is not a complete security, but hides most of the immediate ways to
 * copy question content:
 * - disables contextmenu
 * - disables text selection
 * - disabled F12
 */
// jshint undef:false, unused:false
/* globals $ */

$(document).ready( function() {

    // Protects dynamically against immediate text copy.
    $('body').on('contextmenu', function() {return false;} );
    $('body').css('-webkit-touch-callout', 'none');
    $('body').css('-webkit-user-select', 'none');
    $('body').css('-khtml-user-select', 'none');
    $('body').css('-moz-user-select', 'none');
    $('body').css('-ms-user-select', 'none');
    $('body').css('user-select', 'none');
    $(document).on('keydown', function (evt) {
        if (evt.keyCode == '123')
            return false;
    });
});