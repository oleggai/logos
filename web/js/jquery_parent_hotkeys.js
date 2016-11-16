var parent_pressedKeyMass=new Array();
var parent_handledElement;
var eC;

function parent_registerKeyDown(event,el) {

    //  console.log(event.keyCode);
    eC=event.keyCode;
    parent_pressedKeyMass[event.keyCode]=true;
    parent_handledElement=el;
    parent_checkHandlers();

}

function parent_registerKeyUp(event) {
    parent_pressedKeyMass[event.keyCode]=false;
    parent_handledElement=undefined;
}

function parent_clearKeys(){
    for(var i=0;i<parent_pressedKeyMass.length;i++)
        parent_pressedKeyMass[i]=false;
}

/*
 * TODO
 * search by enter
 * */

function parent_checkHandlers(){

    if(eC==9){
        if($(document.activeElement).hasClass('indexBody'))
        $('.tab_div.active .tab_iframe',window.parent.document).contents().find("body").focus();
    }

    if(parent_pressedKeyMass[18]&&eC==86){
        //outerLogout();
        parent_clearKeys();                //Alt_v
    }

    if((parent_pressedKeyMass[18])&&(eC>=48&&eC<=58||eC>=96&&eC<=106)){
     //   makeTabClick(tC(eC));                   //Alt_+1-9 / Alt+num1-9
    }
    if(parent_pressedKeyMass[45]){
        $('.tab_div.active .tab_iframe',window.parent.document).get(0).contentWindow.createTab();  //insert
        parent_clearKeys();
    }
    if(parent_pressedKeyMass[18]&&parent_pressedKeyMass[16]&&parent_pressedKeyMass[87]){
        EW_tab_create();         //Alt+Shift+W
    }
    if(parent_pressedKeyMass[18]&&parent_pressedKeyMass[16]&&parent_pressedKeyMass[77]){
        MN_tab_create();         //Alt+Shift+W
    }
}

function tC(item){
   return (item>=96)?item-96:item-48;
}

