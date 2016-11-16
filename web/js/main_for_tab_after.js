var current_grid;
var executeTiming=0;
var disableItems
$(function() {
    $('.filter_div_showhide_btn.showhide_btn_left').click(moveBot);
    $('.afilter_div_showhide_btn.showhide_btn_left').click(forceBot);

    $('.filter_div_showhide_btn.showhide_btn_left').click(NewclearSearchFields);
    $('.afilter_div_showhide_btn.showhide_btn_left').click(clearSearchFields);

    //выключение дейтпикеров с атрибутом readonly
     disableItems=$('.datetimewithbutton[readonly]');
    setTimeout(function(){ for(var i=0;i<disableItems.length;i++)
        $(disableItems[i]).off();
    },0);

    //Вот это валиция
    $('#update_entity_btn').click(regexInputCheck);
    $('#update_entity_btn').click(regexTextareaCheck);

    $('.save-btn').click(regexInputCheck);
    $('.save-btn').click(regexTextareaCheck);

    $('.measuredValue').blur(checkNumberField);


    //Вот это конец валидации



$('#btn_clear_modal').click(clearModalInputs);
    $('.modal-dialog .select-counterparty-filters-input').change(checkModalInputs);
  //  $('.modal-dialog .select-counterparty-filters-input').keypress(checkModalInputs);

    if($(".manifest-form").prop('tagName')!=undefined)
    executeTiming=200;
    else
    executeTiming=0;
   // $(".nav.nav-tabs.custom-tab-styling>li").on('click',function(){      setTimeout(function(){ updateGridOnActiveTab();},0); });

    $(document).on('click','.grid_erase',eraseElement);
    $(document).on('click','.erase_clone',eraseRow);
    //ресайз гридов
    $(window).on('resize',updateGridOnActiveTab);

    $(".nav.nav-tabs.custom-tab-styling>li").not($('#w1').parent()).on('click',function(){ setTimeout(updateGridOnActiveTab,executeTiming); });

    //обновление полей при транслитерации приватных особ
    $('#privat_pers_transliterate_button').on('click',transliterateFix);
    //обновление полей при транслитерации не приватных особ
    $('#legal_entity_transliterate_button').on('click',transliterateFix);

    $('.filter_clear_btn').on('click',clearSearchFields);
    $('.filter2_clear_btn').on('click',NewclearSearchFields);

    updateGridOnActiveTab();
    // добавление класса кликабельности в ifarme для иконки "карандашь"

    $('.glyphicon.glyphicon-pencil').click(function()
    {
        var ifr_url=$(this).parent('a').attr('href');
        var tab_title=$(this).parent('a').attr('title');
        parent.application_create_new_tab(tab_title,ifr_url,false,false,false);
        return false;

    });


    $('.href_newtab').click(function()
        {
            var ifr_url=$(this).attr('href');
            var tab_title=$(this).attr('title');
            parent.application_create_new_tab(tab_title,ifr_url,false,false,false);
            return false;
        }
    );



    //нажатие на кнопку "обновить"
    $('.refresh_btn').click(function(){
        location.reload();
    });

    /**
     * Функция добавления\удаления класса к объекту
     */
    function tog(v){
        return v?'addClass':'removeClass';
    }




    /**
     * Очищаемое поле и очищаемый выпадающий список
     * Классы clearable и clearablecombo
     * При наведении на крестик изменение курсора. При нажатии - очистка
     */


    //вначале добавляем ко всем элементам этот класс (для элементов которых это будет срабатывать)
    $('input:not([readonly])').not("[type='checkbox']").not("[type='button']").not("[disabled]")
        .not('#sender_counterparty_id').not('#receiver_counterparty_id')
        .not('.datetimewithbutton').addClass('clearable');
    $('textarea:not([readonly])').addClass('clearable');


    $('.datetimewithbutton').not("[disabled]").not("[readonly]").addClass('clearablecombo');
    $('.filter_div_data select').addClass('clearablecombo');

    $('.clearablecombo:not([readonly]):not([disabled])').addClass('xc');
    $('.clearablecombo').change(function(){ if(this.value!="") $(this).addClass("xc");});

    $('.clearable').not('[readonly]').each(function(){$(this)[tog(this.value)]('x')});

    $(document).on('input', '.clearable', function(){
        $(this)[tog(this.value)]('x');
    }).on('mousemove', '.x', function( e ){
        $(this)[tog(this.offsetWidth-18 < e.clientX-this.getBoundingClientRect().left)]('onX');
    }).on('mousemove', '.scroll.x', function(e){
        $(this)[tog(this.offsetWidth-35 < e.clientX-this.getBoundingClientRect().left)]('onX');
    }).on('click', '.onX', function(){
        $(this).removeClass('x onX').val('').change();
    });

    $('.clearablecombo').each(function(){$(this)[tog(this.value)]('xc')});
    $(document).on('select', '.clearablecombo', function(){
        $(this)[tog(this.value)]('xc');
    }).on('mousemove', '.xc', function( e ){
        $(this)[tog(this.offsetWidth-35 < e.clientX-this.getBoundingClientRect().left&&this.offsetWidth-35 > e.clientX-this.getBoundingClientRect().left-18)]('onXC');
    }).on('click', '.onXC', function(){

        $(this).removeClass('xc onXC').val('').change();

        $(this).blur();
        $(this).css('box-shadow','rgba(0, 0, 0, 0.0745098) 0px 1px 1px 0px inset');
    });




    $(".tab_div.active .tab_iframe",window.parent.document).contents().find(".datetimewithbutton").on('focus',function(event) {
        if(this.value=="")
        $(this).trigger('click');
        setTimeout(function(){setInputCursor(event.target);},0);
    });
    function setInputCursor(el){
        $(el)[0].selectionStart = 0;
        $(el)[0].selectionEnd = 0;
    }

    $(".tab_div.active .tab_iframe",window.parent.document).contents().find(".datetimewithbutton").on('click',function(event){
   var mask=$(this).attr('mask');
    mask=getFinalMask(mask);
        if(this.value=="")
          this.value=mask;
        this.selectionStart = 0;
        this.selectionEnd = 0;
    });





var grids1 =$(".webix_grid_conteiner_w_margin");
var events=["onAfterAdd","onAfterDelete","onAfterSelect","onHeaderClick","onRowResize","onAfterEditStop"];
var afterEvents=["onDataUpdate"];
    var globTimeout=new Array(grids1.length);
    var evalTMP;
    for(var i=0;i<grids1.length;i++){
evalTMP=$(grids1[i]).attr('id').substring(11,this.lenth)+".attachEvent('onItemClick',function(id){setTheGrid(id,"+$(grids1[i]).attr('id').substring(11,this.lenth)+");});";
eval(evalTMP);
            for(var j=0;j<events.length;j++){
evalTMP=$(grids1[i]).attr('id').substring(11,this.lenth)+".attachEvent('"+events[j]+"',function(){setTimeout(function(){ addCrossToGrid("+$(grids1[i]).attr('id')+")},0);});";
eval(evalTMP);
            }
        for(var z=0;z<afterEvents.length;z++){
evalTMP=$(grids1[i]).attr('id').substring(11,this.lenth)+".attachEvent('"+afterEvents[z]+"',function(){setTimeout(function(){ addCrossToGrid("+$(grids1[i]).attr('id')+")},0);});";
eval(evalTMP);
        }
evalTMP=$(grids1[i]).attr('id').substring(11,this.lenth)+".attachEvent('onAfterLoad',function(){$(this.$view).parent().parent().find('.gridUN').trigger('tryUpdate');});";
eval(evalTMP);


    }


  //  $(".express-waybill-form  .dropdown-toggle").off();
//$(".express-waybill-form  .dropdown-toggle").on('mouseenter',function(){$("#w1").find(".dropdown-toggle").click(); });


    //$("#w1").click(function(){$("#w1 a:eq(0)").click(); });

    if($('#operation_selector').val()==50){
    $('.open_on_close').css('display','block');
        $('.close_on_close').css('display','none');
    }
    else{
        $('.open_on_close').css('display','none');
        $('.close_on_close').css('display','block');
    }


});


// Добавить кнопки для очистки ячеек и строк webix.DataTable
function addCrossToGrid(el) {
    if ($(".tab-pane.active .grid_delbtn.grid_delbtn.btn.btn-default").attr('class')!=undefined) {
        if (el != undefined) {
            elmtS = $(".tab-pane.active #" + $(el).attr('id')).find(".webix_cell").not(".webix_first .webix_cell").not(".webix_last .webix_cell");
            for (var i = 0; i < elmtS.length; i++) {
                elmt = elmtS[i];
                if ($(elmt).find('.grid_erase').attr('class') == undefined && $(elmt).text().trim() != "")
                    $(elmt).not('#webix_grid_grid_places div[column="1"] .webix_cell').prepend("<div class='grid_erase'> </div>");

                elmt = $(".tab-pane.active #" + $(el).attr('id')).find(".webix_last .webix_cell");
                if ($(elmt).find('.btn.btn-default.erase_clone').attr('class') == undefined)
                    $(elmt).append("<input type='button' class='btn btn-default erase_clone' value='x'/>");
            }

        }
        else {
            elmtS = $(".tab-pane.active").find(".webix_cell").not(".webix_first .webix_cell").not(".webix_last .webix_cell");
            for (var i = 0; i < elmtS.length; i++) {
                elmt = elmtS[i];
                if ($(elmt).find('.grid_erase').attr('class') == undefined && $(elmt).text().trim() != "")
                    $(elmt).not('#webix_grid_grid_places div[column="1"] .webix_cell').prepend("<div class='grid_erase'> </div>");
                if (current_grid != undefined) {
                    elmt = $(".tab-pane.active #" + $(current_grid.$view).attr('id')).find(".webix_last .webix_cell");
                    if ($(elmt).find('.btn.btn-default.erase_clone').attr('class') == undefined)
                        $(elmt).append("<input type='button' class='btn btn-default erase_clone' value='x'/>");
                }
                else {
                    elmt = $(".tab-pane.active").find(".webix_last .webix_cell")
                    if ($(elmt).find('.btn.btn-default.erase_clone').attr('class') == undefined)
                        $(elmt).append("<input type='button' class='btn btn-default erase_clone' value='x'/>");
                }
            }

        }
   }
    //  console.log('cwa_1.1');
}


function getFinalMask(input){
    return input.replace(/[0-9]/g,'_');
         }

function updateGridOnActiveTab(){
    var el=$('.tab-pane.active');

    if($(this).prop('tagName')==undefined){
   var grids =$(el).find('.webix_grid_conteiner_w_margin');

    if(grids[0]!=undefined){
        for(var i=0;i<grids.length;i++){
    var webixId=$(grids[i]).attr('id').substring(11,this.lenth);
    webixId=webixId +".refresh();";
    eval(webixId);
        }
    }

    }
    addCrossToGrid();
}
var current_column;
function setTheGrid(id,elmz){
    current_grid=elmz;
    current_column=id;
}
var globalObj;

// очистить ячейку webix.DataTable
function eraseElement() {
    var immuneEls=['place_bc'];
    if (immuneEls.indexOf(current_column.column)==-1){
        var columnName = current_column.column;
        var item = current_grid.getItem(current_column);
        item[columnName] = '';
        current_grid.unselect();
        addCrossToGrid();
        current_grid.callEvent('onAfterEraseElement', [columnName, item]);
    }
}

// очисчить строку webix.DataTable
function eraseRow() {
    current_grid.editRow(current_grid.getSelectedId().row);
        $('.webix_dt_editor input').not('#webix_grid_grid_places .webix_dt_editor input:eq(0)').val('');
        $('.webix_dt_editor select').prepend('<option value="0"></option>').val('');
        $('.webix_view.webix_calendar').find('.webix_cal_icon_clear.webix_cal_icon').click();
    current_grid.editStop();
    var item = current_grid.getSelectedItem(); // строка webix (данные)
    current_grid.callEvent('onAfterEraseRow', [item]);
    current_grid.unselect();
}

function transliterateFix(){
    console.log('working fix tr');
    privatePerson=($(this).attr("id")=='privat_pers_transliterate_button')?true:false;

    if(privatePerson)
    {
        var el=$("#counterpartySecondnameRu").parent().parent().parent();
        var els=$(el).find("input.clearable");
        for(var i=0;i<els.length;i++)
            if($(els[i]).hasClass('x')==false&&$(els[i]).val()!="")
                $(els[i]).addClass('x');
    }
    else{
        var el=$("#legal_entity_transliterate_button").parent().parent();
        var els=$(el).find("input.clearable");
        for(var i=0;i<els.length;i++)
            if($(els[i]).hasClass('x')==false&&$(els[i]).val()!="")
                $(els[i]).addClass('x');
    }
}

function clearSearchFields(){
    var parent=$(".filter_clear_btn").parent();
    var textInputs=$(parent).find('input');
    var selectInputs=$(parent).find('select');
    var select1Inputs=$(parent).find('.select2-selection__rendered');

    for(var i=0;i<textInputs.length;i++)
        $(textInputs[i]).val('').removeClass('x').removeClass('xc');
    for(var i=0;i<selectInputs.length;i++)
    if($(selectInputs[i]).val()!='uk'&&$(selectInputs[i]).val()!='en'&&$(selectInputs[i]).val()!='ru')
        $(selectInputs[i]).val('').removeClass('x');
    for(var i=0;i<select1Inputs.length;i++)
        $(select1Inputs[i]).html('');
}

function showSearchDiv(){
    $('.filter_div_showhide_btn.showhide_btn_left').click();
    event.preventDefault();
    event.stopPropagation();
}

function insertCharacter(fieldObj,insertValue){
    var focused=$(fieldObj).find('input:focus');
if($(focused).prop('type')=='text'&&$(focused).css('display')!='none'&&$(focused).attr('readonly')!='readonly'&&$(focused).attr('disabled')!='disabled')
{
    var text=$(focused).val();
    var select= $(focused)[0].selectionStart;
    $(focused).val(text.substring(0,select)+insertValue+text.substring(select,text.length) );
    $(focused)[0].selectionStart=select+1;
    $(focused)[0].selectionEnd=select+1;
}
    clearTimeout(specialTimeout);
    specialTimes=0;
    clearKeys([222,17]);
}

function clearModalInputs(){
var textInps=$('.modal-dialog input[type="text"]');
    var selectInps=$('.modal-dialog select');
    for(var i=0;i<textInps.length;i++)
    textInps[i].value="";
    for(var i=0;i<selectInps.length;i++)
        selectInps[i].value="";
}

function checkModalInputs(){
var res=false;
    var textInps=$('.modal-dialog .select-counterparty-filters-input[type="text"]');
    for(var i=0;i<textInps.length;i++)
        if(textInps[i].value!="")
        {res=true; break;}

    if(res)
       $("#btn_find_modal").removeAttr("disabled");
    else
        $("#btn_find_modal").attr("disabled","disabled");
}

function specialDelete(event){
    var dic = $(event.target).attr('data-partial_delete');
    if(dic!='*')
    $('input[data-partial_delete="'+dic+'"]').val("");
    else
    $('input[data-partial_delete]').val("");
}

function moveBot(){

    if($('.filter_div_showhide_btn.showhide_btn_left').hasClass('open_filter_form'))
        $('.afilter_div_showhide_btn.showhide_btn_left').css('margin-top',(parseInt($('.filter_form_div.open_filter_form').css('height'))-30));
    else
        $('.afilter_div_showhide_btn.showhide_btn_left').css('margin-top','0px');

}

function forceBot(){
    $('.afilter_div_showhide_btn.showhide_btn_left').css('margin-top','0px');
}

/*
function NewclearSearchFields() {
    var parent=$(".filter2_clear_btn").parent();
    var Inputs=$(parent).find('.form-control').not("#af_ew_lang").not('#af_counterparty_lang');
    var select1Inputs=$(parent).find('.select2-selection__rendered');
    var checboxInputs=$(parent).find('input:checked');

    for(var i=0;i<Inputs.length;i++)
        $(Inputs[i]).val('').change().removeClass('x').removeClass('xc');

    for(var i=0;i<select1Inputs.length;i++)
        $(select1Inputs[i]).html('');
    
    for(var i=0;i<checboxInputs.length;i++)
        $(checboxInputs[i]).prop('checked', false);
    $('.clearablecheckbox_hida').hide();
    
    $('.dropdown p').html('');
}
*/

function NewclearSearchFields() {
    var parent_div = $(this).parent();
    parent_div.find('.form-control').not('[disabled]').not("#af_ew_lang").not('#af_counterparty_lang').removeClass('x').removeClass('xc').css('border-color',' #ccc').val('');
    parent_div.find('.form-control:checkbox').not('[disabled]').attr('checked',false);
    parent_div.find('.select2-selection__rendered').html('');
    parent_div.find('.clearablecheckbox_hida').click();
}

function regexInputCheck(){
var CAfix=$('#legal_entity_form'); // fix with change() event

    var item=$('input[type="text"],input[type="password"]').not('.datetimewithbutton,.select-counterparty-filters-input,[hidden],[readonly],[disabled]');
    for(var i=0;i<item.length;i++)
        $(item[i]).val($(item[i]).val().replace(/\s/g,' ').trim());

    for(var i=0;i<CAfix.length;i++)
        $(CAfix[i]).find('input[type="text"],input[type="password"]').not('.datetimewithbutton,.select-counterparty-filters-input,[hidden],[readonly],[disabled]').change();
}

function regexTextareaCheck(){
    var item=$('textarea').not('.select-counterparty-filters-input,[hidden],[readonly],[disabled]');
    for(var i=0;i<item.length;i++)
        $(item[i])
        .val($(item[i]).val().replace(/\n+/g, '-%n%-'))
        .val($(item[i]).val().replace(/\s\s+/g,' '))
        .val($(item[i]).val().replace(/(-%n%-)+/g,'-%n%-'))
        .val($(item[i]).val().replace(/-%n%- |-%n%-?/g, '\n').trim())

        .val($(item[i]).val().replace(/\n+/g, '-%n%-'))
        .val($(item[i]).val().replace(/\s\s+/g,' '))
        .val($(item[i]).val().replace(/(-%n%-)+/g,'-%n%-'))
        .val($(item[i]).val().replace(/-%n%- |-%n%-?/g, '\n').trim());


}

function checkNumberField(){
    var integerValsId=[];
    $(this).val(getNumberValue($(this).val())).change();
    if(integerValsId.indexOf($(this).attr('id'))!=-1)
        $(this).val($(this).val().replace(/[.].*/,''));
    //.replace(/^[.]|[.]$|[^0-9.]/g,'');
}

/**
 * Форматирует числовые значения
 * @param {array} data Данные (row)
 * @param {array} mas Колонки, которые проверять
 * @param {string} gridName Имя грида
 */
function checkGridValues(data,mas,gridName){
    try {
        // существующий элемент
        if (parseInt(data.id)>0) {
            for(var i=0;i<mas.length;i++) {
                var tempData=data[mas[i]];
                tempData=getNumberValue(tempData); // отформатировать число
                data[mas[i]]=tempData;
            }
        } else {
            for(var i=0;i<mas.length;i++) {
                if (eval(gridName+".getItem("+data.id+")."+mas[i])!=undefined) {
                    var tempData=eval(gridName+".getItem("+data.id+")."+mas[i]).toString();
                    if (tempData!=undefined) {
                        tempData= getNumberValue(tempData); // отформатировать число
                        eval(gridName+".getItem("+data.id+")."+mas[i]+"="+tempData+";");
                    }
                }
            }
        }
    }
    catch(ex) {
        console.log(ex);
    }
}

function checkGridTextVal(data,mas,gridName){
    try{
        if(parseInt(data.id)>0){
            for(var i=0;i<mas.length;i++){
                var tempData=data[mas[i]];
                tempData= tempData
                   .replace(/\n+/g, '-%n%-')
                    .replace(/\s\s+/g,' ')
                    .replace(/(-%n%-)+/g,'-%n%-')
                   .replace(/-%n%- |-%n%-?/g, '\n').trim()

                   .replace(/\n+/g, '-%n%-')
                    .replace(/\s\s+/g,' ')
                    .replace(/(-%n%-)+/g,'-%n%-')
                    .replace(/-%n%- |-%n%-?/g, '\n').trim();
                data[mas[i]]=tempData;
            }}
        else
        {
            for(var i=0;i<mas.length;i++){
                var tempData=eval(gridName+".getItem("+data.id+")."+mas[i]);
                if(tempData!=undefined){
                    tempData= tempData
                        .replace(/\n+/g, '-%n%-')
                        .replace(/\s\s+/g,' ')
                        .replace(/(-%n%-)+/g,'-%n%-')
                        .replace(/-%n%- |-%n%-?/g, '\n').trim()

                        .replace(/\n+/g, '-%n%-')
                        .replace(/\s\s+/g,' ')
                        .replace(/(-%n%-)+/g,'-%n%-')
                        .replace(/-%n%- |-%n%-?/g, '\n').trim();
                    eval(gridName+".getItem("+data.id+")."+mas[i]+"='"+tempData+"';");
                }
            }
        }

    }
    catch(ex) {
        console.log(ex);
    }
}

/**
 * Удаляет все символы кроме [.,0-9]
 * Преобразует символ ',' в символ '.' и удаляет все символы '.' после первого.
 */
function getNumberValue(val, isInt) {
    val+='';
    val=val.replace(/[^0-9,.]/g,'')
        .replace(/[,]/g,'.')
        .replace( /^([^\.]*\.)|\./g, '$1' );;
    return val;
}

$(".dropdown dt a").on('click', function () {
    $(".dropdown dd ul").hide();
    var id = ".dropdown dd ul."+this.id;
    $(id).slideToggle('fast');
});

$(".dropdown dd ul li a").on('click', function () {
    $(".dropdown dd ul").hide();
});

function getSelectedValue(id) {
    return $("#" + id).find("dt a span.value").html();
}

$(document).bind('click', function (e) {
    var $clicked = $(e.target);
    if (!$clicked.parents().hasClass("dropdown"))
        $(".dropdown dd ul").hide();
});

$('.mutliSelect input[type="checkbox"]').on('click', function () {
    clickMultiSelect(this);
});

$('.clearablecheckbox_hida').on('click', function () {
    $('.check_' + this.id).attr('checked', false);
    $('.multiSel_check_' + this.id).html('');
    $(".dropdown dd ul").hide();
    $(this).hide();
});

function clickMultiSelect(what){
    var html = $(what).parent().html().replace(/(<([^>]+)>)/ig, "") + ",";
    var id = '.multiSel_' + what.id;
    if ($(what).is(':checked')) {
        var html = '<span title="' + html + '">' + html + '</span>';
        $(id).append(html);
        $('.hida_' + what.id).show();
    }
    else {
        $('span[title="' + html + '"]').remove();
        if ($('.multiSel_' + what.id).text() === '')
            $('.hida_' + what.id).hide();
    }
}

/* __________ improved navigation ______________  */

function initializeAdvancedGridNav(destinationBlock){

    var stringToInsert='<div class="gridUN">'+
        '<div class="gridUNitem gridUN_vavImg gridUN_l2" onclick="sendCommandToNav(this,\'first\')">&nbsp;'+
        '</div><div class="gridUNitem gridUN_vavImg gridUN_l1" onclick="sendCommandToNav(this,\'prev\')">&nbsp;'+
        '</div><div class="gridUNitem gridUN_navText gridUN_Page"> Page'+
        '</div><div class="gridUNitem gridUN_Inp"> <input type="text" value="1" onchange="changePageInNav(this)"/>'+
        '</div><div class="gridUNitem gridUN_navText gridUN_Num"> of <span></span>'+
        '</div><div class="gridUNitem gridUN_vavImg gridUN_r1" onclick="sendCommandToNav(this,\'next\')">&nbsp;'+
        '</div><div class="gridUNitem gridUN_vavImg gridUN_r2" onclick="sendCommandToNav(this,\'last\')">&nbsp;'+
        '</div><div class="gridUNitem gridUN_navText gridUN_Desc"> items <span></span> of <span></span> </div>'+
        '</div>';

    $(destinationBlock).find('div,input').css('display','none');
    $(destinationBlock).css({'height':'32px','overflow':'hidden'});
    $(destinationBlock).prepend(stringToInsert);
    $(destinationBlock).find('.gridUN').on('tryUpdate',function (){
        updateGridNavData($(this).parent());
    });
    updateGridNavData(destinationBlock);
}

function updateGridNavData(destinationBlock){

    var pagesTotal=$(destinationBlock).find('.gridwidget-count-pages').text().replace(/[^0-9]/g,'');
    var itemsOnPage=$(destinationBlock).find('.gridwidget-count-onpage').text().replace(/[^0-9]/g,'');
    var itemsTotal=$(destinationBlock).find('.gridwidget-count').text().replace(/[^0-9]/g,'');

    $(destinationBlock).find('.gridUN_Inp input').val($('.webix_pager_item_selected').text());
    $(destinationBlock).find('.gridUN_Num span').text(pagesTotal);
    $(destinationBlock).find('.gridUN_Desc span:eq(0)').text(itemsOnPage);
    $(destinationBlock).find('.gridUN_Desc span:eq(1)').text(itemsTotal);

    //check left
    if($('.webix_pager_item_selected').text()==1||$('.webix_pager_item_selected').text()==0){
        $(destinationBlock).find('.gridUN_l1').attr('data-disabled','');
        $(destinationBlock).find('.gridUN_l2').attr('data-disabled','');
    }
    else{
        $(destinationBlock).find('.gridUN_l1').removeAttr('data-disabled');
        $(destinationBlock).find('.gridUN_l2').removeAttr('data-disabled');
    }
    //check right
    if($('.webix_pager_item_selected').text()==pagesTotal||$('.webix_pager_item_selected').text()==0||pagesTotal==1){
        $(destinationBlock).find('.gridUN_r1').attr('data-disabled','');
        $(destinationBlock).find('.gridUN_r2').attr('data-disabled','');
    }
    else{
        $(destinationBlock).find('.gridUN_r1').removeAttr('data-disabled');
        $(destinationBlock).find('.gridUN_r2').removeAttr('data-disabled');
    }
}

function sendCommandToNav(el,webix_p_id){
    $(el).parent().parent().find('.webix_view.webix_pager button[webix_p_id="'+webix_p_id+'"]').click();
    updateGridNavData( $(el).parent().parent());
}

function changePageInNav(el){
    $(el).parent().parent().parent().find('input.gridwidget-goto-page').val($(el).val()).trigger({ type : 'keypress', which :13 });
    updateGridNavData( $(el).parent().parent().parent());
}

/* __________ improved navigation ______________  */