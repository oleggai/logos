/** * Created by ypalaguta on 07.08.2015. */
    //захваты событий ГК
$(document).on('keydown',function(event){registerKeyDown(event,this);});
$(document).on('keyup',registerKeyUp);
$("#w2 .next-ul-dropdown-menu.dropdown-submenu:eq(0) a:eq(0)").keydown(function(event){ navInPrint(event.keyCode,event.target); });
$("#w2 .next-ul-dropdown-menu.dropdown-submenu:eq(1) a:eq(0)").keydown(function(event){ navInPrint(event.keyCode,event.target); });
$("#w2 .next-ul-dropdown-menu.dropdown-submenu:eq(1)").next().find('a').keydown(function(event){ navInPrint(event.keyCode,event.target); });
$(".filter_div_data").on('keydown',function(event){ searchTrigger=true; registerKeyDown(event,this); event.stopPropagation();});
// $(".filter_div_data input[type='text']").on('keydown',function(event){registerKeyDown(event,this);});
var itemsToClose=new Array();

$("#w1").mouseleave(closeAllItems);
$("#w1").mouseleave(function(){ $('#w1 .dropdown>a').blur();});

$("#w2").click(function(event){ navInPrint(event.keyCode,event.targe); event.preventDefault(); event.stopPropagation();});

$("#w1").click(function(){openCurrentEl($(this).find("#w2"));});
var itemsz=document.getElementsByClassName('datetimewithbutton');

for(var i=0;i<itemsz.length;i++)
    itemsz[i].addEventListener('keydown',setCursorToEnd);

$('#w2 .next-ul-dropdown-menu.dropdown-submenu:eq(0)').mousemove(function(){
    openCurrentEl($(this).find('ul'));
    $('#w2 .next-ul-dropdown-menu.dropdown-submenu:eq(1)').find('ul').css('display','none');
});
$('#w2 .next-ul-dropdown-menu.dropdown-submenu:eq(1)').mousemove(function(){
    openCurrentEl($(this).find('ul'));
    $('#w2 .next-ul-dropdown-menu.dropdown-submenu:eq(0)').find('ul').css('display','none');
});
if(document.getElementById("w1")!=null)
if(document.getElementById("w1").getElementsByTagName("a")[0]!=undefined)
document.getElementById("w1").getElementsByTagName("a")[0].addEventListener('keydown',function(){
    if(event.keyCode==40)
        openCurrentEl("#w2");
},true);




//навигация боковыми клавишами

$(".next-ul-dropdown-menu.dropdown-submenu:eq(0) a:eq(0)").keypress(function(){tryToOpen(false)});


$(setNoneToLists);

function createTab(){
    $('.tab_div.active .tab_iframe',window.parent.document).contents().find('#create_button').click();
    clearKeys();
    return false;
}
function copyTab(){
    if($(".webix_ss_body .webix_row_select")[0]!=undefined)
        $('li[operation="200"]').click();
}
function openTab(){
    if($(".webix_ss_body .webix_row_select")[0]!=undefined){
        $($(".webix_ss_body .webix_row_select")[0]).click();
        $($(".webix_ss_body .webix_row_select")[0]).click();
    }
}
function editTab(){
    if($(".webix_ss_body .webix_row_select")[0]!=undefined&&$('li[operation="2"]').css("display")!='none')
        $('li[operation="2"]').click();
    else if($(".webix_ss_body .webix_row_select")[0]==undefined){
        $("#operation_selector").val(2).change();
    }
}

function editStatus() {
    if ($(".webix_ss_body .webix_row_select")[0] != undefined && $('li[operation="206"]').css("display") != 'none') {
        $('li[operation="206"]').click();
    }
    else if ($(".webix_ss_body .webisx_row_select")[0] == undefined) {
        $("#operation_selector").val(206).change();
    }
}

function editNondelivery() {
    if ($(".webix_ss_body .webix_row_select")[0] != undefined && $('li[operation="207"]').css("display") != 'none') {
        $('li[operation="207"]').click();
    }
    else if ($(".webix_ss_body .webisx_row_select")[0] == undefined) {
        $("#operation_selector").val(207).change();
    }
}

function closeTab(){
    window.parent.document.getElementsByClassName("tab_btn active")[0].getElementsByClassName("close_this_tab")[0].click();
}


function deleteItem(){

    // console.log($('li[operation="100"]').css("display")!='none'&&$('li[operation="100"]').css("display")!=undefined);

    if($('li[operation="100"]').css("display")!='none'&&$('li[operation="100"]').css("display")!=undefined)
        $('li[operation="100"]').click();
    //  $('.tab_div.active .tab_iframe',window.parent.document).contents().find('li[operation="100"]').click();
    else if($("#operation_selector option[value='100']").css('display')!=undefined){
        $("#operation_selector").val(100).change();


        //  $("#operation_selector option[value='100']").click();

    }
    event.preventDefault();

    /*
     * TODO
     * Change click in list-item to simulate url
     * */
}

function undoChanges(){
    if($('li[operation="51"]').css("display")!=undefined&&$('li[operation="51"]').css("display")!='none')
    {        //journal repair
        $('li[operation="51"]').click();
    }
    else {
        //inside document
        var isClosed=true;
        if($("#operation_selector option[value='51']").css('display')==undefined)
            isClosed=false;
        if(isClosed&&$("#cancel_entity_btn").css('display')=='none')
            $("#operation_selector").val(51).change();     //repair here
    }
}

function exportXsl(){
    if($("#exp_toexcel_btn")[0]!=undefined)
        $("#exp_toexcel_btn").click();
    else if(  $("#MN-print_xls-href")[0]!=undefined)
        $("#MN-print_xls-href").click();

}
function apppendEW() {

    var style=$('.tab_div.active .tab_iframe',window.parent.document).contents().find("#div_link_ew_operations").css("display");
    if(style!=undefined&&style!="none"){
        $('.tab_div.active .tab_iframe',window.parent.document).contents().find("#find_ew_in_tab").click();
    }
}

function printDoc(){
    var elem=$('.tab_div.active .tab_iframe',window.parent.document).contents().find("#MN-print-href");
    if($(elem).css('display')!=undefined&&$(elem).css('display')!='none'){
        var ifr_url=$(elem).attr('href');
        var tab_title=$(elem).attr('title');
        parent.application_create_new_tab(tab_title,ifr_url,false,false,false);
        return false;
    }}

function executeByLink(num,isLink) {
if(!isLink){
    var elem=$('.tab_div.active .tab_iframe',window.parent.document).contents().find("#w2 a:not(a[href='#']):eq("+num+")");

    if($(elem).css('display')!=undefined&&$(elem).css('display')!='none'){
        var ifr_url=$(elem).attr('href');
        var tab_title=$(elem).attr('title');
        window.parent.application_create_new_tab(tab_title,ifr_url,false,false,false);
    }
}
    else{
    var elem=$('.tab_div.active .tab_iframe',window.parent.document).contents().find("a[href='"+num+"']");

    if($(elem).css('display')!=undefined&&$(elem).css('display')!='none'){
        var ifr_url=$(elem).attr('href');
        var tab_title=$(elem).attr('title');
        window.parent.application_create_new_tab(tab_title,ifr_url,false,false,false);
    }
}
}

function beginSearch(parent){

    $(parent).find('.filter_search').click();
}

function getBodyFocus(){
    $("body").focus();
}

function EWClose(){
    if($('li[operation="50"]').css("display")!='none'&&$('li[operation="50"]').css("display")!=undefined) {
        $('li[operation="50"]').click();
    }
    else if($('li[operation="50"]').css("display")==undefined&&$("#operation_selector option[value='50']").css('display')!=undefined){
        $("#operation_selector").val(50).change();
    }
}

function simulateClose(){
    if($('.tab_div.active .tab_iframe',window.parent.document).contents().find("#cancel_entity_btn").css('display')!='none')
        $('.tab_div.active .tab_iframe',window.parent.document).contents().find("#cancel_entity_btn").click();                   //cancel here
}

function simulateSave(){
    if($('.tab_div.active .tab_iframe',window.parent.document).contents().find("#update_entity_btn").css('display')!='none')
        $('.tab_div.active .tab_iframe',window.parent.document).contents().find("#update_entity_btn").click();                   //save here
}

function updateCurrentTab(){
    var el=$('.tab_btn.active .refresh_this_tab',window.parent.document);
    if($(el).css('display')!=undefined&&$(el).css('display')!='none')
        $(el).click();
}

function innerLogout(){
    window.parent.outerLogout();
}

function tC_child(item){
    return (item>=96)?item-96:item-48;
}

function pressApos(item){
 if($(item).attr('disabled')!='disabled'&&$(item).attr('readonly')!='readonly')
insertTextAtCursor(item,'\'',0)
}

function insertTextAtCursor(el, text, offset) {
    try{
    var val = el.value, endIndex, range, doc = el.ownerDocument;
    if (typeof el.selectionStart == "number"
        && typeof el.selectionEnd == "number") {
        endIndex = el.selectionEnd;
        el.value = val.slice(0, endIndex) + text + val.slice(endIndex);
        el.selectionStart = el.selectionEnd = endIndex + text.length+(offset?offset:0);
    } else if (doc.selection != "undefined" && doc.selection.createRange) {
        el.focus();
        range = doc.selection.createRange();
        range.collapse(false);
        range.text = text;
        range.select();
    }
    }
    catch(exception){}
}

function navInPrint(code,el){

    if(code==40){
   $(el).parent().next().find('a').focus();
    }
    else if(code==38){
       $(el).parent().prev().find('a').focus();
    }

}

function closeAllItems(){
    for(var i=0;i<itemsToClose.length;i++)
        $(itemsToClose[i]).css("display","none");
    itemsToClose=new Array();
}

function setNoneToLists(){
    var els=[$('#w2'),$('#w2 .dropdown-menu.dropdown-enchanced-left:eq(0)'),$('#w2 .dropdown-menu.dropdown-enchanced-left:eq(1)')];
    for(var i=0;i<els.length;i++)
    $(els[i]).css("display","none");
}

function openCurrentEl(el){
    $(el).css("display","block");
    itemsToClose.push($(el));
}

function tryToOpen(firstItem){
    if(firstItem&&event.keyCode==37)
        openCurrentEl($(".dropdown-menu.dropdown-enchanced-left:eq(0)"));
    else if(!firstItem&&event.keyCode==37)
        openCurrentEl($(".dropdown-menu.dropdown-enchanced-left:eq(1)"));
}

function makeChildTabClick(num){

    num--;
    var realNum=$(".nav.nav-tabs.custom-tab-styling>li").length-7;
    console.log(realNum);
    if(num<=realNum)
        $(".nav.nav-tabs.custom-tab-styling>li:eq("+num+") a").click();
}

function closeModal(){
    $('.tab_div.active .tab_iframe',window.parent.document).contents().find(".modal-dialog button.close").click();
}

function modalSearch(){
    $('.tab_div.active .tab_iframe',window.parent.document).contents().find("#btn_find_modal").click();

}

function setCursorToEnd(item){
    var item=event.target;
    if(event.keyCode==35)
        item.setSelectionRange($(item).val().length,$(item).val().length);

}