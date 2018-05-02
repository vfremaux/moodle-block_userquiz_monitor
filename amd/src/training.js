/**
 * This will only work for quizzes having :
 * - being customscripted as required
 * - having single question per page
 * - being linnked to an enabling userquiz_monitor panel.
 */
// jshint undef:false, unused:false
/* globals $ */

define(['jquery', 'core/config', 'core/log'], function($, cfg, log) {

    var courseid;

    var blockid;

    var quizlist;

    var examquiz;

    var rootcategory;

    var training = {
        init: function(cid, bid, rc, ql, exq) {

            courseid = cid;
            blockid = bid;
            rootcategory = rc;
            quizlist = ql;
            examquiz = exq;

            $('.cb-master').prop('disabled', false); // Disabled while checking we have enough questions to process.
            $('.cb-master').prop('checked', false); // Unchecked at start.
            $('#checkall-master').prop('disabled', false); // Disabled while checking we have enough questions to process.
            $('#checkall-master').prop('checked', false); // Unchecked at start.
            $('#checkall-master').bind('change', this.select_all_master_categories);

            // this is a call stack in order.
            $('.cb-master').bind('click', this.update_selector_master_global);
            $('.userquiz-monitor-cat-button').bind('click', this.fetch_training_subcategories);
            $('.userquiz-monitor-cat-button').addClass('active');
            $('.userquiz-monitor-exam-cat-button').bind('click', this.fetch_exam_subcategories);
            $('.userquiz-monitor-exam-cat-button').addClass('active');

            log.debug('AMD Block_userquiz_monitor quizforceanswer initialized');
        },

        /*
         * this will be called later each time loading some subcategory content
         * this usually should be triggered on an ajax loaded content, after ajax has been completed
         * loading.
         * Use this function by proxying on a main category related element
         */
        init_detail: function() {

            var that = $(this);
            var regexp  = /[^0-9]*([0-9]+)$/;
            var matches = that.attr('id').match(regexp);
            var categoryid = matches[1];

            $('#id-checkall-detail-' + categoryid).prop('checked', false);
            $('#id-checkall-detail-' + categoryid).bind('change', this.select_all_detail_categories);
            $('.cb-detail-' + categoryid).prop('checked', false);
            $('.cb-detail-' + categoryid).bind('click', this.update_selector_detail);
            log.debug('AMD Block_userquiz_monitor detail ' + categoryid + ' initialized');
        },

        /**
         * Updates the selector following the user's choice selecting or unselecting
         * a category.
         */
        update_selector_master_ajax: function() {

            var that = $(this);

            // Try first from a selector checkbox.
            var categoryid = parseInt(that.attr('id').replace('id-cb-master-', ''));
            if (!categoryid) {
                // Try if even not comes from the subcategory button.
                categoryid = parseInt(that.attr('id').replace('details-button-', ''));
            }

            var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&categoryid=" + categoryid;
            params += "&location=mode0&quizlist=" + quizlist;
            var url = M.cfg.wwwroot + '/blocks/userquiz_monitor/ajax/updateselector.php?' + params;

            // If is checked, should check also all subcategories if visible.
            if (that.prop('checked')) {
                $('.cb-detail-' + categoryid).prop('checked', true);
            }

            $.get(url, function(data) {
                $('.selectorcontainers').html(data);

                // Enable Go Btn if there are question numbers.
                if ($('#id-selector-nb-questions').length) {
                    $('#id-training-go-button').prop('disabled', false);
                } else {
                    $('#id-training-go-button').prop('disabled', true);
                }
            }, 'html');
        },

        /**
         * Updates the selector following the user's choice.
         */
        update_selector_master_global: function(mode) {

            var categorieslist = '';

            if (mode === 'all') {
                this.select_all_master_cb();
            }

            $('.cb-master').each(function() {
                if ($(this).prop('checked')) {
                    var catid = $(this).attr('id').replace('id-cb-master-', '');
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

                // Enable Go Btn if there are question numbers.
                if ($('#id-selector-nb-questions').length) {
                    $('#id-training-go-button').prop('disabled', false);
                } else {
                    $('#id-training-go-button').prop('disabled', true);
                }
            }, 'html');
        },

        /**
         * Updates the selector following the user's choice.
         */
        update_selector_detail: function () {

            log.debug("update_selector_detail");

            var categorieslist = '';
            var cpt = 0;
            var allchecked = true;
            var parentid = 0;

            var currentclasses = this.className.split(' ');

            for (var i in currentclasses) {
                // Find the parent related class
                if (currentclasses[i].match(/^cb-detail-/)) {
                    parentid = currentclasses[i].replace('cb-detail-', '');
                }
            }

            $('.cb-detail').each(function() {

                var that = $(this);
                var categoryid = that.attr('id').replace('id-cb-detail-', '');

                if ($(this).prop('checked')) {
                    if (categorieslist === '') {
                        categorieslist = categoryid;
                    } else {
                        categorieslist = categorieslist + "," + categoryid;
                    }
                } else {
                    allchecked = false;
                }
            });

            // If not checked, uncheck the master category.
            if (!allchecked && parentid) {
                $('#id-cb-master-' + parentid).prop('checked', false);
            }

            if (categorieslist === '') {
                categorieslist = "null";
            }

            var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&categoryid=" + categorieslist;
            params += "&location=mode1&quizlist=" + quizlist;
            var url = cfg.wwwroot + '/blocks/userquiz_monitor/ajax/updateselector.php?' + params;

            $.get(url, function(data) {
                $('.selectorcontainers').html(data);

                // Enable Go Btn if there are question numbers.
                if ($('#id-selector-nb-questions').length) {
                    $('#id-training-go-button').prop('disabled', false);
                } else {
                    $('#id-training-go-button').prop('disabled', true);
                }
            }, 'html');
        },

        /**
         * Refresh the number of questions on the selector of the training dashbord
         */
        refresh_selector: function () {

            var that = $(this);
            var categoryid = that.attr('id').replace();

            var params = "rootcategory=" + rootcategory + "&categoryid=" + categoryid + "&quizzeslist=" + quizlist;
            var url = cfg.wwwroot + "/blocks/userquiz_monitor/ajax/refreshselector.php?" + params;

            $.get(url, function(data) {
                $('.selectorcontainers').html(data);

                // Enable Go Btn if there   are question numbers.
                if ($('#id-selector-nb-questions').length) {
                    $('#id-training-go-button').prop('disabled', false);
                } else {
                    $('#id-training-go-button').prop('disabled', true);
                }
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

            var that = $(this);
            var categoryid = that.attr('id').replace('id-checkall-detail-', '');

            if (that.prop('checked')) {
                $('.cb-detail-' + categoryid).prop('checked', true);
            } else {
                $('.cb-detail-' + categoryid).prop('checked', false);
            }

            var callback = $.proxy(training.update_selector_detail, this);
            callback();
        },

        reset_training: function(userid) {

            var params = "id=" + courseid + "&userid=" + userid + "&quizzeslist=" + quizlist;
            var url = cfg.wwwroot + "/blocks/userquiz_monitor/ajax/resettraining.php?" + params;

            $.get(url, function (data) {
                alert(data);
                window.location = cfg.wwwroot + "/course/view.php?id=" + courseid;
            }, 'html');
        },

        /**
         * Updates the program following the user's choice.
         * Deprecated ?
         */
        refresh_content: function () {

            var that = $(this);
            var categoryid = that.attr('id').replace('', '');

            var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&id=" + categoryid;

            var url = cfg.wwwroot + '/blocks/userquiz_monitor/ajax/schedulecontent.php?' + params;

            $.get(url, function(data) {
                $('#divschedule').html(data);
                this.highlight_amf_cat(categoryid);
            });
        },

        highlight_training_cat: function(categoryid) {
            $('trainingcat').removeClass('active');
            $('trainingcat').addClass('inactive');
            for (var i = 0; i < 12; i++) {
                if (i === categoryid) {
                    $('#trainingcat' + i).removeClass('inactive');
                    $('#trainingcat' + i).addClass('active');
                }
            }
        },

        close_detail: function() {

            var that = $(this);

            var categoryid = that.attr('id').replace('id-cancel-detail-', '');

            $('#checkall-master').prop('checked', false);
            $('#checkall-master').prop('disabled', false);
            $('.cb-master').prop('checked', false);
            $('.cb-master').prop('disabled', false);
            $('.cb-master').addClass('trans100');
            $('.cb-master').removeClass('trans50');
            $('#id-detail-button-div-' + categoryid).addClass('active');
            $('.progressbar-container').css('visibility', 'visible');
            $('.category-subpod').css('display', 'none');
            $('#category-subcatpod-' + categoryid).html('');

            // Desinhibits all other master cats.
            $('.cb-master').prop('disabled', false);
            $('.div-main').removeClass('trans50');
            $('.div-main').addClass('trans100');

            var callback = $.proxy(training.update_selector_detail, this);
            callback();
        },

        /**
         * Display subcategories on the right part of the training dashbord. On narrow screens,
         * will route the content to the special container under the category main block.
         */
        fetch_training_subcategories: function() {

            var that = $(this);
            var categoryid = that.attr('id').replace('details-button-div-', '');

            var params = "blockid=" + blockid + "&courseid=" + courseid + "&rootcategory=" + rootcategory;
            params += "&categoryid=" + categoryid + "&quizzeslist=" + quizlist + "&mode=training";
            var url = cfg.wwwroot + "/blocks/userquiz_monitor/ajax/subcategoriescontent.php?" + params;

            $.post(url, '', function(data) {
                var localcatid;
                if (isNaN(categoryid)) {
                    localcatid = categoryid.replace('details-button-', '');
                } else {
                    localcatid = categoryid;
                }

                // Empty all subcategories.
                $('.category-subpod').css('display', 'none');
                $('#category-subcatpod-' + categoryid).html('');

                // Setup category content.
                $('#category-subcatpod-' + localcatid).html(data);
                $('#category-subcatpod-' + localcatid).css('display', 'inline-block');
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
                $('.cb-detail-' + categoryid).bind('click', training.update_selector_detail);
                $('#id-cancel-detail-' + categoryid).bind('click', training.close_detail);
                $('#id-detail-button-div-' + categoryid).removeClass('active');

                // Inhibits all other master cats.
                $('.cb-master').prop('disabled', true);
                $('.cb-master').prop('checked', false);
                $('.div-main').addClass('trans50');
                $('.div-main').removeClass('trans100');
                $('#cb-master-' + categoryid).prop('disabled', false);
                $('#id-div-main-' + categoryid).removeClass('trans50');
                $('#id-div-main-' + categoryid).addClass('trans100');

                $('html,body').animate({scrollTop: $('#id-cat-' + categoryid).offset().top},'slow');

                var callback = $.proxy(training.update_selector_detail, this);
                callback();

            }, 'html');
        },

        /**
         * Display subcategories on the right part of the training dashbord. On narrow screens,
         * will route the content to the special container under the category main block.
         */
        fetch_exam_subcategories: function() {

            var that = $(this);
            var categoryid = that.attr('id').replace('details-button-div-', '');

            var params = "blockid=" + blockid + "&courseid=" + courseid + "&rootcategory=" + rootcategory;
            params += "&categoryid=" + categoryid + "&quizzeslist=" + examquiz + "&mode=exam";
            var url = cfg.wwwroot + "/blocks/userquiz_monitor/ajax/subcategoriescontent.php?" + params;

            $.post(url, '', function(data) {
                var localcatid;
                if (isNaN(categoryid)) {
                    localcatid = categoryid.replace('details-button-', '');
                } else {
                    localcatid = categoryid;
                }

                // Empty all subcategories.
                $('.category-subpod').css('display', 'none');
                $('#category-subcatpod-' + categoryid).html('');

                // Setup category content.
                $('#category-subcatpod-' + localcatid).html(data);
                $('#category-subcatpod-' + localcatid).css('display', 'inline-block');
                log.debug('AMD Block_userquiz_monitor detail ' + localcatid + ' loaded');

                $('#id-cancel-detail-' + categoryid).bind('click', training.close_detail);
                $('#id-detail-button-div-' + categoryid).removeClass('active');

                // Inhibits all other master cats.
                $('.div-main').addClass('trans50');
                $('.div-main').removeClass('trans100');
                $('#id-div-main-' + categoryid).removeClass('trans50');
                $('#id-div-main-' + categoryid).addClass('trans100');

                $('html,body').animate({scrollTop: $('#id-cat-' + categoryid).offset().top},'slow');

            }, 'html');
        }

    };

    return training;
});


