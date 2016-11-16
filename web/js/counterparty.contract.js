// кнопки
var contractAddBtn  = $('#contract_add_button');
var contractUpdateBtn  = $('#contract_update_button');
var contractDeleteBtn  = $('#contract_del_button');
var contractRestoreBtn  = $('#contract_restore_button');
var contractRefreshBtn  = $('#contract_refresh_button');

//функции
function contractShowButtons(item){
    if (is_deleted)
    {
        contractAddBtn.hide();
        contractUpdateBtn.hide();
        contractDeleteBtn.hide();
        contractRestoreBtn.hide();
        contractRefreshBtn.hide();
    }
    else if (!item){
        contractDeleteBtn.hide();
        contractRestoreBtn.hide();
        contractUpdateBtn.hide();
    }
    else if (item['state']==1){
        contractDeleteBtn.show();
        contractRestoreBtn.hide();
        contractUpdateBtn.show();
    }
    else{
        contractDeleteBtn.hide();
        contractRestoreBtn.show();
        contractUpdateBtn.hide();
    }
}

// события
contractAddBtn.click(function() {

    var cId = cpId.val();
    if (cId == '')
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Создание контракта':'Contract create',
        url_prefix + '?r=counterparty/counterparty-contract/create&counterparty=' + cId,
        'counterpartycontractcreate',
        'false',
        this_ifr_id,
        'false');
});

contractUpdateBtn.click(function() {

    var item_id = grid_contract.getSelectedId();
    var item = grid_contract.getItem(item_id);

    if (!item)
        return;

    if (item['state']==100){
        parent.show_app_alert('Attention!', "Item is deleted, edit disable. To enable edit restore item", 'Ok');
        return;
    }

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Контракт ':'Contract ' + item['id'],
        url_prefix + '?r=counterparty/counterparty-contract/update&id=' + item['id'],
        'counterpartycontractupdate' + item['id'],
        'false',
        this_ifr_id,
        'false');
});

contractDeleteBtn.click(function() {

    var item_id = grid_contract.getSelectedId();
    var item = grid_contract.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-contract/delete-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    contractRefreshBtn.click();
});

contractRestoreBtn.click(function() {

    var item_id = grid_contract.getSelectedId();
    var item = grid_contract.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-contract/restore-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    contractRefreshBtn.click();
});

contractRefreshBtn.click(function() {

    grid_contract.clearAll();
    grid_contract.load(grid_contract.config.url);

    var item_id = grid_contract.getSelectedId();
    var item = grid_contract.getItem(item_id);
    contractAddBtnDoc.hide();
    contractRefreshBtnDoc.click();
    contractShowButtons(item);
});

grid_contract.attachEvent("onSelectChange", function (){

    var item_id = this.getSelectedId();
    var item = this.getItem(item_id);

    contractShowButtons(item);
});


grid_contract.attachEvent("onItemDblClick", function () {

    var item_id = grid_contract.getSelectedId();
    var item = grid_contract.getItem(item_id);

    if (!item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        'Contract ' + item['id'],
        url_prefix + '?r=counterparty/counterparty-contract/view&id=' + item['id'],
        'counterpatycontractview' + item['id'],
        'false',
        this_ifr_id,
        'false');

});

contractShowButtons(null);