/**
 * This will only work for quizzes having :
 * - being customscripted as required
 * - having single question per page
 * - being linnked to an enabling userquiz_monitor panel.
 */
// jshint undef:false, unused:false
/* globals $ */

define(['jquery', 'core/log'], function($, log) {

    var courseid;

    var blockid;

    var quizlist;

    var rootcategory;

    var training = {
        init: function(cid, bid, rc, ql) {

            courseid = cid;
            blockid = bid;
            rootcategory = rc;
            quizlist = ql;

            $('.cb-master').prop('disabled', false); // Disabled while checking we have enough questions to process.
            $('.cb-master').prop('checked', false); // Unchecked at start.
            $('#checkall-master').prop('disabled', false); // Disabled while checking we have enough questions to process.
            $('#checkall-master').prop('checked', false); // Unchecked at start.
            $('#checkall-master').bind('change', this.select_all_master_categories);

            // this is a call stack in order.
            $('.cb-master').bind('click', this.update_selector_master_ajax);
            $('.userquiz-monitor-cat-button').bind('click', this.fetch_training_subcategories);
            $('.userquiz-monitor-cat-button').addClass('active');

            log.debug('AMD Block_userquiz_monnitor quizforceanswer initialized');
        },

        /*
         * this will be called later each time loading some subcategory content
         * this usually should be triggered on an ajax loaded content, after ajax has been completed
         * loading.
         * Use this function by proxying on a main category related element
         */
        init_detail: function() {

            that = $(this);
            regexp  = /[^0-9]*([0-9]+)$/;
            matches = that.attr('id').match(regexp);
            categoryid = matches[1];

            $('#id-checkall-detail-' + categoryid).prop('checked', false);
            $('#id-checkall-detail-' + categoryid).bind('change', this.select_all_detail_categories);
            $('.cb-detail-' + categoryid).prop('checked', false);
            $('.cb-detail-' + categoryid).bind('click', this.update_selector_detail_ajax);
            log.debug('AMD Block_userquiz_monitor detail ' + categoryid + ' initialized');
        },

        /**
         * Updates the selector following the user's choice selecting or unselecting
         * a category.
         */
        update_selector_master_ajax: function() {

            var that = $(this);
            // try first from a selector checkbox.
            categoryid = parseInt(that.attr('id').replace('id-cb-master-', ''));
            if (!categoryid) {
                // try if even not comes from the subcategory button.
                categoryid = parseInt(that.attr('id').replace('details-button-', ''));
            }

            var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&categoryid=" + categoryid;
            params += "&location=mode0&quizlist=" + quizlist;
            var url = M.cfg.wwwroot + '/blocks/userquiz_monitor/ajax/updateselector.php?' + params;

            $.get(url, function(data) {
                $('.selectorcontainers').html(data);
            }, 'html');
        },

        /**
         * Updates the selector following the user's choice.
         */
        update_selector_master_global: function(mode) {

            var categorieslist = '';
            var cpt = 0;

            if (mode === 'all') {
                this.select_all_master_cb();
            }

            $('.cb-master').each(function(index) {
                if ($(this).prop('checked')) {
                    catid = $(this).attr('id').replace('id-cb-master-', '');
                    if (categorieslist === '') {
                        categorieslist = catid;
                    } else {
                        categorieslist = categorieslist + "," + catid;
                    }
                }
            });

            if (categorieslist === '') {
                categorieslist = 'null';
            }

            var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&categoryid=" + categorieslist;
            params += "&location=mode0&quizlist=" + quizlist;
            var url = M.cfg.wwwroot + '/blocks/userquiz_monitor/ajax/updateselector.php?' + params;

            $.get(url, function(data) {
                $('.selectorcontainers').html(data);
            }, 'html');
        },

        update_selector_detail: function(list, display) {

            var number = '';
            var idsubcategories = [];
            var cpt = 0;

            if (display === 'all') {
                select_all_cb_detail(list);
            }
            for (i = 0; i < list.length; i++) {
                if (list[i] === ',') {
                    idsubcategories[cpt] = number;
                    number = '';
                    cpt++;
                } else {
                    number = number + list[i];
                }
            }
            idsubcategories[cpt] = number;
            this.update_selector_detail_ajax(idsubcategories);
        },

        /**
         * Updates the selector following the user's choice.
         */
        update_selector_detail_ajax: function () {

            var categorieslist = '';
            var cpt = 0;

            $('.cb-detail').each(function(index) {

                var that = $(this);
                categoryid = that.attr('id').replace('id-cb-detail-', '');

                if ($(this).prop('checked')) {
                    if (categorieslist === '') {
                        categorieslist = categoryid;
                    } else {
                        categorieslist = categorieslist + "," + categoryid;
                    }
                }
            });

            if (categorieslist === '') {
                categorieslist = "null";
            }

            var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&categoryid=" + categorieslist;
            params += "&location=mode1&quizlist=" + quizlist;
            var url = M.cfg.wwwroot + '/blocks/userquiz_monitor/ajax/updateselector.php?' + params;

            $.get(url, function(data) {
                $('.selectorcontainers').html(data);
            }, 'html');
        },

        /**
         * Refresh the number of questions on the selector of the training dashbord
         */
        refresh_selector: function () {

            var that = $(this);
            categoryid = that.attr('id').replace();

            var params = "rootcategory=" + rootcategory + "&categoryid=" + categoryid + "&quizzeslist=" + quizlist;
            var url = M.cfg.wwwroot + "/blocks/userquiz_monitor/ajax/refreshselector.php?" + params;

            $.get(url, function(data) {
                $('.selectorcontainers').html(data);
            }, 'html');
        },

        /*
         * This will be attached to the single "select all" checkbox of a main group.
         */
        select_all_master_categories: function () {
            if ($('#checkall-master').prop('checked')) {
                $('.cb-master').prop('checked', true);
            } else {
                $('.cb-master').prop('checked', false);
            }

            training.update_selector_master_global();
        },

        /*
         * This will be attached to each "select all" checkbox of a detail subcategory group.
         */
        select_all_detail_categories: function () {

            that = $(this);
            categoryid = that.attr('id').replace('id-checkall-detail-', '');

            if (that.prop('checked')) {
                $('.cb-detail-' + categoryid).prop('checked', true);
            } else {
                $('.cb-detail-' + categoryid).prop('checked', false);
            }
        },

        reset_training: function(userid) {

            var params = "id=" + courseid + "&userid=" + userid + "&quizzeslist=" + quizlist;
            var url = M.cfg.wwwroot + "/blocks/userquiz_monitor/ajax/resettraining.php?" + params;

            $.get(url, function (data, status) {
                alert(data);
                window.location = M.cfg.wwwroot + "/course/view.php?id=" + courseid;
            }, 'html');
        },

        /**
         * Updates the program following the user's choice.
         * Deprecated ?
         */
        refresh_content: function () {

            that = $(this);
            categoryid = that.attr('id').replace('', '');

            var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&id=" + categoryid;

            var url = M.cfg.wwwroot + '/blocks/userquiz_monitor/ajax/schedulecontent.php?' + params;

            $.get(url, function(data) {
                $('#divschedule').html(data);
                this.highlight_amf_cat( categoryid);
            });
        },

        highlight_training_cat: function(categoryid) {
            $('trainingcat').removeClass('active');
            $('trainingcat').addClass('inactive');
            for (i = 0; i < 12; i++) {
                if (i === categoryid) {
                    $('#trainingcat' + i).removeClass('inactive');
                    $('#trainingcat' + i).addClass('active');
                }
            }
        },

        close_detail: function() {
            $('#checkall-master').prop('checked', false);
            $('#checkall-master').prop('disabled', false);
            $('.cb-master').prop('checked', false);
            $('.cb-master').prop('disabled', false);
            $('.cb-master').addClass('trans100');
            $('.cb-master').removeClass('trans50');
            $('#id-detail-button-div-' + categoryid).addClass('active');
            $('.progressbar-container').css('visibility', 'visible');
        },

        /**
         * Display subcategories on the right part of the training dashbord. On narrow screens,
         * will route the content to the special container under the category main block.
         */
        fetch_training_subcategories: function(e) {

            that = $(this);
            categoryid = that.attr('id').replace('details-button-div-', '');

            var params = "blockid=" + blockid + "&courseid=" + courseid + "&rootcategory=" + rootcategory;
            params += "&categoryid=" + categoryid + "&quizzeslist=" + quizlist + "&mode=training";
            var url = M.cfg.wwwroot + "/blocks/userquiz_monitor/ajax/subcategoriescontent.php?" + params;

            $.post(url, '', function(data) {
                var localcatid;
                if (isNaN(categoryid)) {
                    localcatid = categoryid.replace('details-button-', '');
                } else {
                    localcatid = categoryid;
                }
                $('#category-subcatpod-' + localcatid).html(data);
                log.debug('AMD Block_userquiz_monitor detail ' + localcatid + ' loaded');

                $('#id-checkall-detail-' + categoryid).prop('checked', false);
                $('#id-checkall-detail-' + categoryid).bind('change', training.select_all_detail_categories);
                if (!$('#id-cb-master-' + categoryid).prop('checked')) {
                    $('#id-checkall-detail-' + categoryid).prop('checked', false);
                    $('.cb-detail-' + categoryid).prop('checked', false);
                } else {
                    $('#id-checkall-detail-' + categoryid).prop('checked', true);
                    $('.cb-detail-' + categoryid).prop('checked', true);
                }
                $('.cb-detail-' + categoryid).bind('click', training.update_selector_detail_ajax);
                $('#id-cancel-detail-' + categoryid).bind('click', training.close_detail);
                $('#id-detail-button-div-' + categoryid).removeClass('active');

            }, 'html');
        }


    };

    return training;
});


