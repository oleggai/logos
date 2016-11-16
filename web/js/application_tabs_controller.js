


function return_data_to_parent_tab(par_wid,par_el_id,par_el_val,this_tid,trigger_el_id)
{
    var element = $('#'+par_wid,window.parent.document).contents().find('#'+par_el_id);
    // если элемент div - установка значения text, иначе установка val
    if (element.is("div"))
        element.text(par_el_val);
    else
    {
        element.val(par_el_val);
    }

    //$('#'+par_wid,window.parent.document).trigger("find_complete");
    $('#'+this_tid,window.parent.document).find('.close_this_tab').click();

    if (typeof trigger_el_id !== typeof undefined && trigger_el_id !== false)
        $('#'+par_wid,window.parent.document).contents().find('#'+trigger_el_id).click();

    return true;
}


/**
 * Выдает код для следующего внось создаваемого таба
 * @param obj elem элемент на который было выполнено нажатие
 * @param bool from_iframe будет true если нажатие на элемент который в iframe
 * @return str
 */
function get_new_tab_number (unique_tab_id)
{

    if (typeof unique_tab_id !== typeof undefined && unique_tab_id !== false&&unique_tab_id!='false') {
        return unique_tab_id;
    }
    else
    {
        var tab_num_counter=$('#main_form_tab_counter',window.parent.document);
        var tab_num=parseInt(tab_num_counter.val(),10);

        tab_num_counter.val(tab_num+1);
        return tab_num;
    }

}



/**
 * События которые происходят при нажатие на кнопку закрытие таба
 * @param obj elem таб на который было выполнено нажатие закрытия
 */
function close_this_tab_click_(elem)
{

    if (!before_exit_tabs(elem))
        return;

    // при закрытии необходимо передать данные в другую закладку
    if (elem.parent().attr('model_id') && elem.parent().attr('par_wid')){
        var id = elem.parent().attr('model_id');
        var par_wid = elem.parent().attr('par_wid');
        var par_el_id = elem.parent().attr('par_el_id');
        var par_trigger_el_id = elem.parent().attr('par_trigger_el_id');
        var this_id = elem.parent().attr('this_tid');

        elem.parent().removeAttr('par_wid');

        return_data_to_parent_tab(par_wid,par_el_id,id,this_id,par_trigger_el_id);
    }

    var id = elem.parent().attr('tbb');

    //если в кнопке закрытие таба есть атрибут с кодом таба который нужно открыть после его закрытия - открываем его иначе открываем первый в списке
    var onclosereturn=$(elem).attr('onclosereturn');

    $('#tab_'+id,window.parent.document).remove();
    $('#tabheader_'+id,window.parent.document).remove();


    if (typeof onclosereturn !== typeof undefined && onclosereturn !== false)
    {$('#'+onclosereturn, window.parent.document).click();}
    else {
        $('.tab_btn', window.parent.document).last().click();
    }



}

/**
 * Метод перез выходом или закрытием таба. Если есть несохраненные данные - пользователю показывается уведомление
 * @param elem Закрываемые таб, либо null если идет выход\закрытие браузера
 */
function before_exit_tabs(elem) {

    var tab = elem ? $(elem.parent()) : null;
    var message = 'Присутствуют несохраненные данные, желаете продолжить?';

    if (tab){

        var need_save = tab.attr('need_save');
        if (need_save == "true"){
            return show_app_alert_with_close_return('Warning',message,'Yes','No');
        }
        return true;
    }

    var allTabs = $("#all_tabs", window.parent.document);
    var lastTab = null;
    var lastDate = 0;

    for (var i = 0; i < allTabs.children().length; i++){

        var tab = $(allTabs.children()[i]);
        if (tab.attr('need_save') == "true"){

            var update_date = tab.attr('update_date');
            if (update_date > lastDate){
                lastDate = update_date;
                lastTab = tab;
            }
        }
    }

    if (lastTab){
        tab_click_(lastTab);
        return message;
    }

    return true;

}


/**
 * События которые происходят при нажатие на заголовок таба
 * @param obj elem таб на который было выполнено нажатие
 */
function tab_click_(elem) {


    var id = elem.attr('tbb');
    // сделать неактивными все содержимое табов и сами табы
    $(".tab_div",window.parent.document).removeClass('active');
    $('.tab_btn',window.parent.document).removeClass('active');

    // текущий таб сделать активным и его содержимое
    $("#tab_" + id,window.parent.document).addClass('active');
    //$("#tab_" + id,window.parent.document).find('iframe').contents().find('#webix_grid_grida').css('border','1px solid red;');

    elem.addClass('active');

    $(".tab_div.active .tab_iframe").contents().find("body").focus();

}


/**
 * Возвращает ид текущего таба
 */
function get_current_tab_id() {
    var allTabs = $(".tab_btn", window.parent.document);
    var currentTab = allTabs.filter('.active')

    return currentTab.length > 0 ? currentTab.attr('tbb') : null;
}

/**
 * Переименовывает текущий таб. Переименование только последнего слова
 */
function rename_current_tab(new_name) {

    var allTabs = $(".tab_btn", window.parent.document);
    var title = allTabs.filter('.active').find(".tab_title");


    var last_word_index = title.text().lastIndexOf(" ")+1;
    title.text(title.text().substring(0, last_word_index)+ new_name);
}

/**
 * Изменение текущего таба. Меняется название, дата последнего изменения, необходимость сохранения
 * @param new_name Новое имя таба (последнее слово)
 * @param date Дата создания/обновления
 * @param needSave Есть ли не сохраненные данные
 */
function change_current_tab(new_name, date, needSave) {

    if (!this.frameElement)
        return;

    var frame_div = $(this.frameElement.parentNode);
    var uniq_name = frame_div.attr('f_tbb');
    var tab_div = $("#tabheader_"+uniq_name, window.parent.document);
    var title = tab_div.find(".tab_title");

    if (new_name) {
        var last_word_index = title.text().lastIndexOf(" ") + 1;
        title.text(title.text().substring(0, last_word_index) + new_name);
    }

    tab_div.attr('update_date', date);
    tab_div.attr('need_save', needSave);
}

/**
 * Установка атрибутов текущего таба
 */
function setattr_current_tab(attributes) {

    if (!this.frameElement)
        return;

    var frame_div = $(this.frameElement.parentNode);
    var uniq_name = frame_div.attr('f_tbb');
    var tab_div = $("#tabheader_"+uniq_name, window.parent.document);

    $.each(attributes, function(key, value) {
        tab_div.attr(key,value);
    });
}

function close_current_tab(){
    if (!this.frameElement)
        return;

    var frame_div = $(this.frameElement.parentNode);
    var uniq_name = frame_div.attr('f_tbb');
    var tab_div = $("#tabheader_"+uniq_name, window.parent.document);

    tab_div.attr('need_save', false);
    tab_div.find('.close_this_tab').click();
}

function refresh_current_tab(){

    var allTabs = $(".tab_btn", window.parent.document);

    allTabs.filter('.active').find('.refresh_this_tab').click();
}

function goback_current_tab(){

    window.history.back();
}

// tab_title - название новой вкладки
// ifr_url - урль для загрузки в фрейм
// unique_tab_id - уникальный идентификатор для вкладки
// return_data_to -
// par_win - уникальный идентификатор родительской вкладки
// par_trigger_el_id -
function application_create_new_tab(tab_title,ifr_url,unique_tab_id,return_data_to,par_wid,par_trigger_el_id,par_with_create, linked_frame)
{

    var tab_num=get_new_tab_number(unique_tab_id);

    // ищем есть ли такой таб
    var find_tab=$("#tabheader_"+tab_num,window.parent.document);
    // если есть уже такой то переходим на него
    if( find_tab.length )
    {
        find_tab.click();
        return false;
    }



    //если задан return_data_to это значит что данные из вновь создаваемого iframe должны будут потом перейти в родительский iframe в элемент с id=return_data_to
    //добавляем в новый таб в кнопку закрытия атрибут onclosereturn=id заголовка родительского таба
    //а в урл нового iframe передаем доп параметры this_tid (id заголовка нового таба-что потом его автозакрыть), par_el_id (id элта родительского iframe), par_wid (id iframe родителя)
    var parentparam='';var onclosereturn='';
    if (typeof return_data_to !== typeof undefined && return_data_to !== false && return_data_to !== null)
    {

        var this_tid = linked_frame ? linked_frame : 'tabheader_'+tab_num;

        parentparam='&this_tid='+this_tid +'&par_el_id='+return_data_to+'&par_wid='+par_wid+
            '&par_trigger_el_id='+par_trigger_el_id+"&par_with_create="+par_with_create;
        onclosereturn='onclosereturn="'+par_wid.replace('ifarme_','tabheader_')+'"';

    };

    var content_tab=' <div id="tabheader_'+tab_num+'" tbb="'+tab_num+'" class="tab_btn">'
        +'<span class="refresh_this_tab" onclick="' +
        'var view_url_elem =document.getElementById(\'ifarme_'+tab_num+'\').contentDocument.getElementById(\'view_entity_url\'); ' +
        'if (view_url_elem) document.getElementById(\'ifarme_'+tab_num+'\').contentDocument.location.href = view_url_elem.value; ' +
        'else document.getElementById(\'ifarme_'+tab_num+'\').contentDocument.location.reload(true)"></span>'
        +'<span class="tab_title">' + tab_title + '</span>'
        +'<span class="close_this_tab" '+onclosereturn+'></span></div>';
    var content_iframe='<div id="tab_'+tab_num+'" class="tab_div" f_tbb="'+tab_num+'"><iframe id="ifarme_'+tab_num+'" class="tab_iframe'+'" src="'+ifr_url+parentparam+'"  ></iframe></div>';


    $('#all_tabs',window.parent.document).append(content_tab);
    $('#all_frames',window.parent.document).append(content_iframe);

    $('#tabheader_'+tab_num,window.parent.document).click(function () {tab_click_($(this));});
    $('#tabheader_'+tab_num+'>.close_this_tab',window.parent.document).click(function (event) {event.stopPropagation();close_this_tab_click_($(this));});
    $('#tabheader_'+tab_num,window.parent.document).click();



    return false;

    setTimeout(function(){$(".tab_div.active .tab_iframe").contents().find("body").focus();},250);
}

function app_add_new_tab_from_iframe(element){

    var this_ifr_id = '';
    if (window.frameElement)
        this_ifr_id=window.frameElement.getAttribute("id");
    else
        webix.modalbox.hide(current_modal); // test

    parent.application_create_new_tab(element.getAttribute("tab_title"),element.getAttribute("ifr_url"),element.getAttribute("unique_tab_id"),element.getAttribute("return_data_to"),this_ifr_id,element.getAttribute("trigger_el_id"));
};

function checkLines(values){
    var isNotEmpty=false;
    for(var i=0;i<values.length;i++){
        if($(values[i]).val()!=undefined&&$(values[i]).val()!="")
            isNotEmpty=true;
    }
    return isNotEmpty;
}

$(function() {


    var elemi = $(".btn.btn-default.btn-xs.glyph-btn.glyphicon");


    if ($('.grid_addBtn_upper.grid_addbtn.grid-addbtn.btn.btn-default').css('display') == undefined) {
        $(elemi).attr('disabled', 'disabled').css('display', 'none');
    }

    $(elemi).click(checkInvoiceTab);


    var elems = [
        $('#invoice_cp_id')
    ];



    $(".nav.nav-tabs.custom-tab-styling .next-ul-dropdown-menu ").find("ul").addClass("dropdown-menu dropdown-enchanced-left");
    //    ourElem=;
        
    
    var isFirstTariff= 0;
    // окно редактирование тарифа
    if ($(".tariff_help_class").css("display") != undefined) {
        isFirstTariff = 1;
    }
//    else if ($(".tariff_zone_help_class").css("display") != undefined)
//        isFirstTariff = 2;
/*
    if (isFirstTariff == 1) { // перенести кнопку добавления строки внутрь грида
        if ($(".view-option-line .grid_addbtn").css("display") != undefined) {
            $(".view-option-line .grid_addbtn").css('display', 'none');
            $(".webix_hcell:eq(2)").append('<input type="button" class="grid_addBtn_upper grid_addbtn grid-addbtn btn btn-default" value="+" >');
            $(".webix_hcell:eq(5)").append('<input type="button" class="grid_addBtn_upper grid_addbtn grid-addbtn btn btn-default" value="+" >');
            $(".webix_hcell:eq(8)").append('<input type="button" class="grid_addBtn_upper grid_addbtn grid-addbtn btn btn-default" value="+"">');
        }
    }*/
//    else if (isFirstTariff == 2) {
//        $(".view-option-line .grid_addbtn").css('display', 'none');
//        if ($(".view-option-line .grid_addbtn").css("display") != undefined) {
//            $(".webix_hcell:eq(5)").append('<input type="button" class="grid_addBtn_upper grid_addbtn grid-addbtn btn btn-default" value="+" >');
//        }
//    }

});


function checkInvoiceTab() {
    var elemi = $(".btn.btn-default.btn-xs.glyph-btn.glyphicon");
    $(".toggle-wrapper:eq(0)").slideToggle("slow");
    if ($(elemi).hasClass('glyphicon-menu-down'))
        $(elemi).removeClass("glyphicon-menu-down").addClass("glyphicon-menu-up");
    else
        $(elemi).removeClass("glyphicon-menu-up").addClass("glyphicon-menu-down");
}



