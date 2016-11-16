$(document).ready(function() {

    $('#delete-attached-doc').bind('click', function() {
        if(attachedDocIds.length == 0) {
            alert(messageDeleteDocNotChecked);
        } else {
            deleteAttachedDoc();
        }
    });
    $('#atm_refresh_button').on('click',atm_refresh_button);
    $('#atm_add_button').on('click',atm_add_button);
    $('#atm_edit_button').on('click',function(){atm_edit_button();});

});

// ****************************** DELETE DOCUMENT *******************************

grid_doc.attachEvent("onAfterLoad", function(){
    attachedDocIds = [];
    // Сбор массива ид файлов прикрепленных
    grid_doc.attachEvent('onCheck', function(row, column, state) {
        /*console.log('row: ' + row);
         console.log('column: ' + column);
         console.log('state: ' + state);*/
        var index = attachedDocIds.indexOf(row);
        if(index == -1) {
            attachedDocIds.push(row);
        }
        else {
            if(state == 'off') {
                attachedDocIds.splice(index, 1);
            }
        }
        console.log(attachedDocIds);
    });
});


function deleteAttachedDoc() {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: urlDeleteAttachedDoc,
        data: {
            "attachedDocIds": attachedDocIds.toString(),
            "entityName": entityName,
            "entityId": entityId
        },
        success: function(responce) {
            if(responce.res == 1) {
                // релоад грида с файлами
                grid_doc.clearAll(); grid_doc.loadNext(grid_doc.config.datafetch, 0, null, urlReloadGridDoc);
                grid_doc_file_data_was_loaded = true;
            }
        }
    });
}
// ****************************** DELETE DOCUMENT *******************************
function atm_refresh_button(){
    $('#grid_doc_grid_refresh_button').trigger('click');
}
function atm_add_button(){
    $('.add-btn').trigger('click');
}

function atm_edit_button(){
    loc=window.location;
  //  console.log(loc.pathname);
    id=current_grid.getItem(current_column)['id'];
    if($('html').attr('lang')=='en')
        labelName='Related document  ' + id +' editing';
    else
        labelName='Связанный документ ' + id+' редактирование';

    if(id!=undefined)
        parent.application_create_new_tab(
            labelName,
            loc.origin+((loc.pathname.indexOf('/web/')>=0)?'/web/':'/')+'index.php?r=common%2Fattached-doc%2Fupdate&entityName='+entityName+'&entityId='+entityId+'&id='+id);

}