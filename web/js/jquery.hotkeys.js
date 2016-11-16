var pressedKeyMass=new Array();
var handledElement=undefined;
var eC_child;
var middleel=new Array();
var todayClick=[0,0];

function registerKeyDown(event,el) {
    eC_child=event.keyCode;
    pressedKeyMass[event.keyCode]=true;
  //  console.log("-------- "+eC_child+" ----------");
  //  console.log(pressedKeyMass[18]);
  //  console.log(pressedKeyMass[77]);
  //  console.log(pressedKeyMass[99]);
    handledElement=el;
    checkHandlers();

}

function registerKeyUp(event) {
    pressedKeyMass[event.keyCode]=false;
    handledElement=undefined;
}

function clearKeys(){

    for(var i=0;i<pressedKeyMass.length;i++)
        pressedKeyMass[i]=false;
    for(var i=0;i<middleel.length;i++)
    middleel[i]=false;
    handledElement=undefined;
    todayClick[0]=0;
    todayClick[1]=0;
}

/*
* TODO
* search by enter
* */

function checkHandlers() {
    if (pressedKeyMass[18] && pressedKeyMass[16]){
        middleel[0]=true;
    }
    if(middleel[0]&&eC_child==87){
        window.parent.EW_tab_create();  clearKeys();     //Alt+Shift+W
    }
    if(middleel[0]&&eC_child==77){
        window.parent.MN_tab_create();  clearKeys();     //Alt+Shift+M
    }

    if (pressedKeyMass[18] && pressedKeyMass[69]&&eC_child==70) {
     //alt_f_e
        event.preventDefault();
        event.stopPropagation();
        $('.afilter_div_showhide_btn.showhide_btn_left').click();
        clearKeys();
    }

    if (pressedKeyMass[18] && pressedKeyMass[69]||pressedKeyMass[18] && pressedKeyMass[73]){
     event.preventDefault();
    }

    if (pressedKeyMass[18] && pressedKeyMass[69]&& pressedKeyMass[49]||pressedKeyMass[18] && pressedKeyMass[69]&& pressedKeyMass[97]) {
        executeByLink(2);
        clearKeys();                                                           //Alt_E_1
    }
    if (pressedKeyMass[18] && pressedKeyMass[69]&& pressedKeyMass[50]||pressedKeyMass[18] && pressedKeyMass[69]&& pressedKeyMass[98]) {
        executeByLink(0);
        clearKeys();                                                           //Alt_E_2
    }
    if (pressedKeyMass[18] && pressedKeyMass[69]&& pressedKeyMass[54]||pressedKeyMass[18] && pressedKeyMass[69]&& pressedKeyMass[102]) {
        executeByLink(1);
        clearKeys();                                                           //Alt_E_6
    }


    if (pressedKeyMass[18] && pressedKeyMass[77]){
        middleel[1]=true;
    }
    if (pressedKeyMass[18] && pressedKeyMass[77]&& pressedKeyMass[49]||pressedKeyMass[18] && pressedKeyMass[77]&& pressedKeyMass[97]) {
        executeByLink(3);
        clearKeys();                                                           //Alt_M_1
    }
    if (pressedKeyMass[18] && pressedKeyMass[77]&& pressedKeyMass[50]||pressedKeyMass[18] && pressedKeyMass[77]&& pressedKeyMass[98]) {
        executeByLink(4);
        clearKeys();                                                           //Alt_M_2
    }
    if (pressedKeyMass[18] && pressedKeyMass[77]&& pressedKeyMass[51]||eC_child==99&&middleel[1]) {
        executeByLink(5);
        clearKeys();                                                           //Alt_M_3
    }

    if (pressedKeyMass[18] && pressedKeyMass[73]&& pressedKeyMass[49]||pressedKeyMass[18] && pressedKeyMass[73]&& pressedKeyMass[97]) {
        executeByLink(6);
        clearKeys();                                                           //Alt_I_1
    }
    if (pressedKeyMass[18] && pressedKeyMass[73]&& pressedKeyMass[50]||pressedKeyMass[18] && pressedKeyMass[73]&& pressedKeyMass[98]) {
        executeByLink(7);
        clearKeys();                                                           //Alt_I_2
    }
    if (pressedKeyMass[18] && pressedKeyMass[73]&& pressedKeyMass[51]||pressedKeyMass[18] && pressedKeyMass[73]&& pressedKeyMass[99]) {
        executeByLink(8);
        clearKeys();                                                           //Alt_I_3
    }
    if (pressedKeyMass[18] && pressedKeyMass[73]&& pressedKeyMass[52]||pressedKeyMass[18] && pressedKeyMass[73]&& pressedKeyMass[100]) {
        executeByLink(9);
        clearKeys();                                                           //Alt_I_4
    }
    if (pressedKeyMass[18] && pressedKeyMass[73]&& pressedKeyMass[53]||pressedKeyMass[18] && pressedKeyMass[73]&& pressedKeyMass[101]) {
        executeByLink(10);
        clearKeys();                                                           //Alt_I_4
    }


    if (pressedKeyMass[18] && pressedKeyMass[85]&& pressedKeyMass[49]||pressedKeyMass[18] && pressedKeyMass[85]&& pressedKeyMass[97]) {
        executeByLink(11);
        clearKeys();                                                           //Alt_U_1
    }
    if (pressedKeyMass[18] && pressedKeyMass[85]&& pressedKeyMass[50]||pressedKeyMass[18] && pressedKeyMass[85]&& pressedKeyMass[98]) {
        executeByLink(12);
        clearKeys();                                                           //Alt_U_2
    }
    if (pressedKeyMass[18] && pressedKeyMass[85]&& pressedKeyMass[51]||pressedKeyMass[18] && pressedKeyMass[85]&& pressedKeyMass[99]) {
        executeByLink(13);
        clearKeys();                                                           //Alt_U_3
    }
    if (pressedKeyMass[18] && pressedKeyMass[85]&& pressedKeyMass[52]||pressedKeyMass[18] && pressedKeyMass[85]&& pressedKeyMass[100]) {
        executeByLink(14);
        clearKeys();                                                           //Alt_U_4
    }
    if (pressedKeyMass[18] && pressedKeyMass[85]&& pressedKeyMass[53]||pressedKeyMass[18] && pressedKeyMass[85]&& pressedKeyMass[101]) {
        executeByLink(15);
        clearKeys();                                                           //Alt_U_5
    }

    if (pressedKeyMass[18] && pressedKeyMass[83]&& pressedKeyMass[49]||pressedKeyMass[18] && pressedKeyMass[83]&& pressedKeyMass[97]) {
        executeByLink(16);
        clearKeys();                                                           //Alt_S_1
    }
    if (pressedKeyMass[18] && pressedKeyMass[83]&& pressedKeyMass[50]||pressedKeyMass[18] && pressedKeyMass[83]&& pressedKeyMass[98]) {
        executeByLink(17);
        clearKeys();                                                           //Alt_S_2
    }


    if (pressedKeyMass[18] && pressedKeyMass[80]&& pressedKeyMass[49]||pressedKeyMass[18] && pressedKeyMass[80]&& pressedKeyMass[97]) {
        executeByLink(18);
        clearKeys();                                                           //Alt_P_1
    }
    if (pressedKeyMass[18] && pressedKeyMass[80]&& pressedKeyMass[50]||pressedKeyMass[18] && pressedKeyMass[80]&& pressedKeyMass[98]) {
        executeByLink(19);
        clearKeys();                                                           //Alt_P_2
    }


    if (pressedKeyMass[18] && pressedKeyMass[65]) {
        executeByLink('/index.php?r=express-waybill%2Findex&printAr=1',true);
        clearKeys();                //Alt_a
    }
    if (pressedKeyMass[18] && pressedKeyMass[82]) {
        var lnk='';
        executeByLink('/index.php?r=express-waybill%2Findex&registryAr=1',true);
        clearKeys();                //Alt_r
    }

    if (pressedKeyMass[18] && pressedKeyMass[68]) {
        deleteItem();
        clearKeys();                //Alt_d
    }
    if (pressedKeyMass[18] && pressedKeyMass[67]) {
        EWClose();
        clearKeys();                    //Alt_c
    }
    if (pressedKeyMass[18] && pressedKeyMass[76]) {
        exportXsl();
        clearKeys();                  //Alt_l
    }
    if (pressedKeyMass[18] && pressedKeyMass[70]) {
        event.preventDefault();
        event.stopPropagation();
        apppendEW();
        showSearchDiv();
        clearKeys();  //Alt_f
    }
    if (pressedKeyMass[18] && pressedKeyMass[80]) {
        if($('.tab_div.active .tab_iframe',window.parent.document).contents().find('.express-waybill-form').css('display')==undefined){
        printDoc();
        clearKeys();                  //Alt_p
        }
    }
    if (pressedKeyMass[18] && pressedKeyMass[88]) {
        editTab();
        clearKeys();                   //Alt_x
    }
    if (pressedKeyMass[18] && pressedKeyMass[79]) {
        undoChanges();
        clearKeys();               //Alt_o
    }
    if (pressedKeyMass[18] && pressedKeyMass[46]) {
        if($(".modal").css('display')=='block')
       clearModalInputs();
        else{
        undoChanges();
        clearSearchFields();       //Alt_del
        }
    }
    if (pressedKeyMass[18] && pressedKeyMass[81]) {
        simulateClose();
        clearKeys();              //Alt_q
    }
    if (pressedKeyMass[18] && pressedKeyMass[87]) {
        simulateSave();
        clearKeys();               //Alt_W
    }
    if (pressedKeyMass[18] && pressedKeyMass[84]) {
        updateCurrentTab();
        clearKeys();            //Alt_t
    }
    if (pressedKeyMass[18] && pressedKeyMass[86]) {
       // innerLogout();
        clearKeys();                     //Alt_v
    }
    if ((pressedKeyMass[18]) && (eC_child >= 48 && eC_child <= 58 || eC_child >= 96 && eC_child <= 106)) {

       // window.parent.makeTabClick(tC_child(eC_child));
      //  makeChildTabClick(tC_child(eC_child));
        clearKeys();                 //Alt_+1-9 / Alt+num1-9
    }



    if(pressedKeyMass[18]&&pressedKeyMass[78]){
        createTab();   clearKeys();                 //Alt_n
    }
    if(pressedKeyMass[16]&&pressedKeyMass[67]){
        copyTab();   clearKeys();                   //Shift_c
    }


    if(pressedKeyMass[17]&&eC_child==222){
        todayClick[0]++;
        if(todayClick[0]>=2){
            clearTimeout(middleel[3]);
            pressApos(event.target);
    clearKeys();
        }
        else
        middleel[3]= setTimeout(clearKeys,1500);

    }

    if(pressedKeyMass[17]&&eC_child==192){
        todayClick[1]++;
        if(todayClick[1]>=2){
            clearTimeout( middleel[2]);
            pressApos(event.target);
            clearKeys();
        }
     else
        middleel[2]= setTimeout(clearKeys,1500);
    }

    if(pressedKeyMass[13])
    {

        if($(event.target).prop('tagName')=='INPUT'&&$(event.target).attr('type')=='text'||$(event.target).attr('disabled')=='disabled'||$(event.target).attr('readonly')=='readonly')
        $(event.target).blur();

        if($(".modal").css('display')=='block')
        modalSearch();
     else if($(handledElement).prop('tagName')==undefined)
        openTab();                    //enter
      else
    beginSearch(handledElement);

        clearKeys();

    }
    if(pressedKeyMass[27]){
        if($(".modal").css('display')=='block')
    closeModal();
        else
        closeTab();   clearKeys();                  //esc
    }
    if(pressedKeyMass[45]){
        createTab();                   //insert
    }

    if(eC_child==8){
        //console.log('closing');
       // console.log($(event.target).prop('tagName'));
        if($(event.target).prop('tagName')=='BODY'||$(event.target).prop('tagName')=='SELECT'||$(event.target).attr('disabled')=='disabled'||$(event.target).attr('readonly')=='readonly')
            event.preventDefault();
    }

    if (pressedKeyMass[18] && pressedKeyMass[83]) {
        editStatus();
    }

    if (pressedKeyMass[18] && pressedKeyMass[75]) {
        editNondelivery();
    }

/*
    if(eC_child==35){
        setCursorToEnd(event.target);                   //end ->
        clearKeys();
    }
*/
}
