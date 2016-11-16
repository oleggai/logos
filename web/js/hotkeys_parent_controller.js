$(function(){
    $(document).on('keydown',function(event){parent_registerKeyDown(event,this);});
    $(document).on('keyup',parent_registerKeyUp);
});
function outerLogout() {
    $('#logout_button', window.parent.document).click();
}
function makeTabClick(num){
    num--;
    var realNum=$("#all_tabs .tab_btn").length;
    if(num<=realNum)
        $("#all_tabs .tab_btn:eq("+num+")").click();
    parent_clearKeys();
}

function EW_tab_create(){
    var toEval=$("a.documents_icon.dropdown-toggle").next().find('li:eq(0) a').attr('onclick')
    eval(toEval);
    parent_clearKeys();
}
function MN_tab_create(){
    var toEval=$("a.documents_icon.dropdown-toggle").next().find('li:eq(1) a').attr('onclick')
    eval(toEval);
    parent_clearKeys();
}
