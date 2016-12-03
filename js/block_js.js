/**
 * Reset training
 */
// jshint unused:false, undef:false

function getpositionelement(id) {
    var divHeight = 0;

    if ($('#divpl' + id).attr('offsetHeight')) {
        divHeight = $('#divpl' + id).attr('offsetHeight');
    } else if ($('#divpl' + id).css('pixelHeight')) {
        divHeight = $('#divpl' + id).css('pixelHeight');
    }
    return(divHeight);
}

/**
 * Updates the selector following the user's choice.
 */
function updateselectorplajax(courseid, rootcategory, categoryid, quizzeslist) {

    var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&categoryid=" + categoryid;
    params += "&location=mode0&quizzeslist=" + quizzeslist;
    var url = M.cfg.wwwroot + '/blocks/userquiz_monitor/updateselector.php?' + params;

    $.get(url, function(data) {
        $('.selectorcontainers').html(data);
    }, 'html');
}

function resettraining(courseid, userid, quizzeslist) {

    var params = "id=" + courseid + "&userid=" + userid + "&quizzeslist=" + quizzeslist;
    var url = M.cfg.wwwroot + "/blocks/userquiz_monitor/ajax/resettraining.php?" + params;

    $.get(url, function (data, status) {
        alert(data);
        window.location = M.cfg.wwwroot + "/course/view.php?id=" + courseid;
    }, 'html');
}

/**
 * Refresh the number of questions on the selector of the training dashbord
 */
function refresh_selector(categoryid, quizzeslist, rootcategory) {

    var params = "rootcategory=" + rootcategory + "&categoryid=" + categoryid + "&quizzeslist=" + quizzeslist;
    var url = M.cfg.wwwroot + "/blocks/userquiz_monitor/ajax/refreshselector.php?" + params;

    $.get(url, function(data) {
        $('.selectorcontainers').html(data);
    }, 'html');
}

function selectallcbpl() {
    for (var j = 0; j < idcategoriespl.length; j++) {
        if ($('#checkall_pl').prop('checked')) {
            $('#cbpl' + idcategoriespl[j]).prop('checked', true);
        } else {
            $('#cbpl' + idcategoriespl[j]).prop('checked', false);
        }
    }
}

/**
 * Updates the selector following the user's choice.
 */
function updateselectorpl(courseid, rootcategory, list, location, mode, quizzeslist) {

    var categorieslist = '';
    var cpt = 0;

    if (mode === 'all') {
        selectallcbpl();
    }

    for (var i = 0; i < list.length; i++) {
        if ($('#cbpl' + list[i]).prop('checked')) {
            if (categorieslist === '') {
                categorieslist = list[i];
            } else {
                categorieslist = categorieslist + "," + list[i];
            }
        }
        cpt++;
    }

    if (categorieslist === '') {
        categorieslist = 'null';
    }

    var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&categoryid=" + categorieslist;
    params += "&location=mode0&quizzeslist=" + quizzeslist;
    var url = M.cfg.wwwroot + '/blocks/userquiz_monitor/updateselector.php?' + params;

    $.get(url, function(data) {
        $('.selectorcontainers').html(data);
    }, 'html');
}

/**
 * Display subcategories on the right part of the training dashbord
 */
function displaytrainingsubcategories(courseid, rootcategory, categoryid, list, quizzeslist, scale, positionheight, blockid){

    for (var i = 0; i < list.length; i++) {
        if (list[i] === categoryid) {
            $('#divpl' + list[i]).addClass('trans100');
            $('#divpl' + list[i]).removeClass('trans50');
            $('#cbpl' + list[i]).prop('checked', true);
            $('#cbpl' + list[i]).prop('disabled', true);
        } else {
            $('#divpl' + list[i]).addClass('trans50');
            $('#divpl' + list[i]).removeClass('trans100');
            $('#cbpl' + list[i]).prop('checked', false);
            $('#cbpl' + list[i]).prop('disabled', true);
            $('#progressbarcontainerA' + list[i]).css('visibility', 'hidden');
            $('#progressbarcontainerC' + list[i]).css('visibility', 'hidden');
        }
    }

    var params = "blockid=" + blockid + "&courseid=" + courseid + "&rootcategory=" + rootcategory;
    params += "&categoryid=" + categoryid + "&quizzeslist=" + quizzeslist + "&scale=" + scale;
    params += "&positionheight=" + positionheight + "&mode=training&blockid=" + blockid;
    var url = M.cfg.wwwroot + "/blocks/userquiz_monitor/ajax/subcategoriescontent.php?" + params;

    $.post(url, '', function(data) {
        $('#partright').html(data);
    }, 'html');

    updateselectorplajax(courseid, rootcategory, categoryid, quizzeslist);
}

// Refresh right's part of dashbord training.
function activedisplaytrainingsubcategories(courseid, rootcategory, categoryid, list, quizzeslist, scale, blockid) {
    var sizetemp = 0;
    var positionheight = 0;
    var isdetected = false;
    var cpt = 0;

    $('#progressbarcontainerA' + categoryid).css('visibility', 'visible');
    $('#progressbarcontainerC' + categoryid).css('visibility', 'visible');

    for (var i = 0; i < list.length; i++) {

        if (list[i] === categoryid) {
            isdetected = true;
        }

        if (isdetected === false) {
            sizetemp = getpositionelement(list[i]);
            positionheight = positionheight + sizetemp + 5.3;
            cpt++;
        }
    }

    if (positionheight !== 0) {
        positionheight = positionheight;
    }

    displaytrainingsubcategories(courseid, rootcategory, categoryid, list, quizzeslist, scale, positionheight, blockid);
}

/**
 * Display subcategories on the right part of the examination dashbord
 */
function activedisplayexaminationsubcategories(courseid, categoryid, list, quizid, blockid) {

    var sizetempt = 0;
    var positionheight = 0;
    var isdetected = false;
    var cpt = 0;

    for (var i = 0; i < list.length; i++) {
        if (list[i] === categoryid) {
            $('#divpl' + list[i]).addClass('trans100');
            $('#divpl' + list[i]).removeClass('trans50');
        } else {
            $('#divpl' + list[i]).addClass('trans50');
            $('#divpl' + list[i]).removeClass('trans100');
            $('#progressbarcontainerA' + list[i]).css('visibility', 'hidden');
            $('#progressbarcontainerC' + list[i]).css('visibility', 'hidden');
        }
    }

    for (i = 0; i < list.length; i++) {

        if (list[i] === categoryid) {
            isdetected = true;
        }

        if (isdetected === false) {
            sizetemp = getpositionelement(list[i]);
            positionheight = positionheight + sizetemp + 4.8;
            cpt++;
        }
    }

    if (positionheight !== 0) {
        positionheight = positionheight;
    }

    var params = "blockid=" + blockid + "&courseid=" + courseid + "&categoryid=" + categoryid + "&quizzeslist=" + quizid;
    params += "&positionheight=" + positionheight + "&mode=examination&blockid=" + blockid;
    var url = M.cfg.wwwroot + "/blocks/userquiz_monitor/ajax/subcategoriescontent.php?" + params;

    $.get(url, function(data) {
        $('#displaysubcategories').html(data);
    }, 'html');
}

/**
 * Updates the program following the user's choice.
 */
function refreshcontent(courseid, rootcategory, id){

    var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&id=" + id;

    var url = M.cfg.wwwroot + '/blocks/userquiz_monitor/ajax/schedulecontent.php?' + params;

    $.get(url, function(data) {
        $('#divschedule').html(data);
        highlightamfcat(id);
    });
}

function highlighttrainingcat(categoryid) {
    $('trainingcat').removeClass('active');
    $('trainingcat').addClass('inactive');
    for (i = 0; i < 12; i++) {
        if (i === categoryid) {
            $('#trainingcat' + i).removeClass('inactive');
            $('#trainingcat' + i).addClass('active');
        }
    }
}

/**
 * Updates the selector following the user's choice.
 */
function updateselectorprajax(courseid, rootcategory, cats, quizzeslist) {

    var categorieslist = '';
    var cpt = 0;

    for (var i = 0; i < cats.length; i++) {
        if ($('#cbpr' + cats[i]).prop('checked')) {
            if (categorieslist === '') {
                categorieslist = cats[i];
            } else {
                categorieslist = categorieslist + "," + cats[i];
            }
        }
        cpt++;
    }

    if (categorieslist === '') {
        categorieslist = "null";
    }

    var params = "courseid=" + courseid + "&rootcategory=" + rootcategory + "&categoryid=" + categorieslist;
    params += "&location=mode1&quizzeslist=" + quizzeslist;
    var url = M.cfg.wwwroot + '/blocks/userquiz_monitor/updateselector.php?' + params;

    $.get(url, function(data) {
        $('.selectorcontainers').html(data);
    }, 'html');
}

function selectallcbpr(categoriesidlist) {
    var tab = categoriesidlist.split(',');
    for (var t = 0; t < tab.length; t++) {
        if ($('#checkall_pr').prop('checked')) {
            $('#cbpr' + tab[t]).prop('checked', true);
        } else {
            $('#cbpr' + tab[t]).prop('checked', false);
        }
    }
}

function initelements() {
    for (var j = 0; j < idcategoriespl.length; j++) {
        $('#cbpl' + idcategoriespl[j]).prop('disabled', false);
        $('#cbpl' + idcategoriespl[j]).prop('checked', false);
    }
    $('#checkall_pl').prop('disabled', false);
    $('#checkall_pl').prop('checked', false);
    $('#cbpl' + idcategoriespl[0]).prop('disabled', false);
    $('#checkall_pr').prop('checked', false);
}

function show(id) {
    if ($('#' + id).css('display') === 'none') {
        $('#' + id).css('display', 'block');
    } else {
        $('#' + id).css('display', 'none');
    }
}

function closepr(button) {
    $('#checkall_pl').prop('checked', false);
    $('#checkall_pl').prop('disabled', false);
    $('#partright').html('');
    $('.selectorcontainers').html(button);
    for (var j = 0; j < idcategoriespl.length; j++) {
        $('#cbpl' + idcategoriespl[j]).prop('checked', false);
        $('#cbpl' + idcategoriespl[j]).prop('disabled', false);
        $('#divpl' + idcategoriespl[j]).addClass('trans100');
        $('#progressbarcontainerA' + idcategoriespl[j]).css('visibility', 'visible');
        $('#progressbarcontainerC' + idcategoriespl[j]).css('visibility', 'visible');
    }
}

function closeprexam() {
    $('#displaysubcategories').html('');

    for (var j = 0; j < idcategoriespl.length; j++) {
        $('#divpl' + idcategoriespl[j]).addClass('trans100');
        $('#progressbarcontainerA' + idcategoriespl[j]).css('visibility', 'visible');
        $('#progressbarcontainerC' + idcategoriespl[j]).css('visibility', 'visible');
    }
}

function updateselectorpr(courseid, rootcategory, list, display, quizzeslist) {
    var number = '';
    var idsubcategories = [];
    var cpt = 0;

    if (display === 'all') {
        selectallcbpr(list);
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
    updateselectorprajax(courseid, rootcategory, idsubcategories, quizzeslist);
}

function urlencodeurlencode(str) {
    return escape(str.replace(/%/g, '%25').replace(/\+/g, '%2B')).replace(/%25/g, '%');
}

function sync_training_selectors(selectobj) {
    $('.selectorsnbquestions').val(selectobj.options[selectobj.selectedIndex].value);
}