
$(document).ready(function() {
    hideBtn();
});

// кнопки
var contractAddBtnDoc  = $('#doc_contract_add_button');
var contractUpdateBtn  = $('#doc_contract_update_button');
var contractDeleteBtn  = $('#doc_contract_del_button');
var contractRefreshBtnDoc  = $('#doc_contract_refresh_button');

var entityNameContract = 'CONTRACT';
var entityIdContract = '';

var attachedDocContractIds = [];
var selectLeadGrid = false;

grid_contract.attachEvent("onSelectChange", function () {

    var item_id = grid_contract.getSelectedId();
    var item = this.getItem(item_id);
    entityIdContract = item_id;


    countreparty_contract_grid_doc.filter(function(obj){

        if (!item)
            return true;

        return obj.contract_id == item.id;
    });

    selectLeadGrid = true;

    contractAddBtnDoc.show();
    contractUpdateBtn.hide();
    contractDeleteBtn.hide();

});

countreparty_contract_grid_doc.attachEvent('onSelectChange', function() {
    showBtn();
});

// события
contractAddBtnDoc.click(function() {

    var this_ifr_id = window.frameElement.getAttribute("id");

    parent.application_create_new_tab(
        'Related Document Creation',
        url_prefix + '?r=common/attached-doc/create-attached-doc&entityName=' + entityNameContract + '&entityId=' + entityIdContract,
        'counterpatycontractdoc',
        'false',
        this_ifr_id,
        'false');
});

contractUpdateBtn.click(function() {

    var contract_item_id = countreparty_contract_grid_doc.getSelectedId();
    var contract_item = countreparty_contract_grid_doc.getItem(contract_item_id);

    if (!contract_item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        'Related Document ' + contract_item.id + ' editing',
        url_prefix + '?r=common/attached-doc/update&entityName=' + entityNameContract + '&entityId=' + contract_item.contract_id + '&id=' + contract_item.id,
        'counterpatycontractdoc' + contract_item.id,
        'false',
        this_ifr_id,
        'false');
});

contractRefreshBtnDoc.click(function() {

    var contract_item_id = grid_contract.getSelectedId();
    var contract_item = grid_contract.getItem(contract_item_id);

    countreparty_contract_grid_doc.clearAll();
    countreparty_contract_grid_doc.load(countreparty_contract_grid_doc.config.url, function(text, data, http_request) {
        countreparty_contract_grid_doc.filter(function (obj) {

            if (!contract_item)
                return true;

            return obj.contract_id == contract_item.id;
        });
    });

    attachedDocContractIds = [];

    contractAddBtnDoc.hide();
    contractUpdateBtn.hide();
    contractDeleteBtn.hide();

});

countreparty_contract_grid_doc.attachEvent("onItemDblClick", function () {

    var contract_item_id = this.getSelectedId();
    var contract_item = this.getItem(contract_item_id);

    if (!contract_item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        'Related document Contract ' + contract_item.id,
        url_prefix + '?r=common/attached-doc/view&entityName='+ entityNameContract +'&entityId'+ contract_item.contract_id +'&id=' + contract_item.id,
        'counterpatycontractdoc' + contract_item.id,
        'false',
        this_ifr_id,
        'false');

});

// ****************************** DELETE DOCUMENT CONTRACT *******************************

    // Сбор массива ид файлов прикрепленных
countreparty_contract_grid_doc.attachEvent('onCheck', function(row, column, state) {

/*        console.log(row);
        console.log(column);
        console.log(state);*/
        var checkIssetDoc = false;
        $.each(attachedDocContractIds, function(index, value) {
            if(typeof value !== 'undefined') {
                checkIssetDoc = true;
            }
        });

        if(checkIssetDoc) {
            contractDeleteBtn.hide();
        }

        var contract_item = this.getItem(row);

        entityIdContract = contract_item.contract_id;

        // Пороверка существования ключа
        if(attachedDocContractIds[contract_item.contract_id] === undefined) {
            attachedDocContractIds[contract_item.contract_id] = [];
        }

        var index = attachedDocContractIds[contract_item.contract_id].indexOf(row);

        if(index == -1) {
            attachedDocContractIds[contract_item.contract_id].push(row);
        }
        else {
            if(state == 'off') {
                attachedDocContractIds[contract_item.contract_id].splice(index, 1);
            }
        }

        contractDeleteBtn.show();

        //console.log(attachedDocContractIds);
});


contractDeleteBtn.click(function() {
    if(attachedDocContractIds.length == 0) {
        alert('Nothing selected!');
    }
    if(selectLeadGrid) {
        deleteAttachedDocContract(attachedDocContractIds[entityIdContract], entityIdContract);
    }
    else {
        $.each(attachedDocContractIds, function(index, value) {
            if(typeof value !== 'undefined') {
                deleteAttachedDocContract(value, index);
            }
        });
    }
});


function deleteAttachedDocContract(arr, id) {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: url_prefix + '?r=site/delete-document',
        data: {
            "attachedDocIds": arr.toString(),
            "entityName": entityNameContract,
            "entityId": id
        },
        success: function(responce) {
            if(responce.res == 1) {
                // релоад грида с ПД
                contractRefreshBtnDoc.click();
            }
        }
    });
}
// ****************************** DELETE DOCUMENT CONTRACT*******************************

function hideBtn() {
    contractAddBtnDoc.hide();
    contractUpdateBtn.hide();
    contractDeleteBtn.hide();
    //contractRefreshBtnDoc.hide();
}

function showBtn() {
    contractAddBtnDoc.show();
    contractUpdateBtn.show();
    //contractDeleteBtn.show();
    contractRefreshBtnDoc.show();
}
