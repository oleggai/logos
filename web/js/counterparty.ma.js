// Manual Address scripts
var btnMAAdd = $('#ma_add_button');
var btnMAUpdate = $('#ma_update_button');
var btnMADel = $('#ma_del_button');
var btnMARestore = $('#ma_restore_button');
var btnMARefresh = $('#ma_refresh_button');

function maShowButtons(item){
    if (is_deleted)
    {
        btnMAAdd.hide();
        btnMAUpdate.hide();
        btnMADel.hide();
        btnMARestore.hide();
        btnMARefresh.hide();
    }
    else if (!item){
        btnMADel.hide();
        btnMARestore.hide();
        btnMAUpdate.hide();
    }
    else if (item['state']==1){
        btnMADel.show();
        btnMARestore.hide();
        btnMAUpdate.show();
    }
    else{
        btnMADel.hide();
        btnMARestore.show();
        btnMAUpdate.hide();
    }
}

btnMAAdd.click(function() {

    var cId = cpId.val();
    if (cId == '')
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Создание адреса':'Address create',
        url_prefix + '?r=counterparty/counterparty-manual-adress/create&counterparty=' + cId,
        'counterpartyaddresscreate',
        'false',
        this_ifr_id,
        'false');
});

btnMAUpdate.click(function() {

    var item_id = grid_manual_adresses.getSelectedId();
    var item = grid_manual_adresses.getItem(item_id);

    if (!item)
        return;

    if (item['state']==100){
        parent.show_app_alert('Attention!', "Item is deleted, edit disable. To enable edit restore item", 'Ok');
        return;
    }

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        'Address ' + item['id'] +' editing',
        url_prefix + '?r=counterparty/counterparty-manual-adress/update&id=' + item['id'],
        'counterpartyaddressupdate' + item['id'],
        'false',
        this_ifr_id,
        'false');
});

btnMADel.click(function() {

    var item_id = grid_manual_adresses.getSelectedId();
    var item = grid_manual_adresses.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-manual-adress/delete-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    btnMARefresh.click();
});

btnMARestore.click(function() {

    var item_id = grid_manual_adresses.getSelectedId();
    var item = grid_manual_adresses.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-manual-adress/restore-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    btnMARefresh.click();
});

btnMARefresh.click(function() {

    grid_manual_adresses.clearAll();
    grid_manual_adresses.load(grid_manual_adresses.config.url);

    var item_id = grid_manual_adresses.getSelectedId();
    var item = grid_manual_adresses.getItem(item_id);
    maShowButtons(item);
});

grid_manual_adresses.attachEvent("onItemDblClick", function () {

    var item_id = grid_manual_adresses.getSelectedId();
    var item = grid_manual_adresses.getItem(item_id);

    if (!item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        'Address ' + item['id'] + ' view',
        url_prefix + '?r=counterparty/counterparty-manual-adress/view&id=' + item['id'],
        'counterpatyaddressview' + item['id'],
        'false',
        this_ifr_id,
        'false');

});

grid_manual_adresses.attachEvent("onSelectChange", function (){
    var item_id = this.getSelectedId();
    var item = this.getItem(item_id);

    maShowButtons(item);
});

maShowButtons(null);
