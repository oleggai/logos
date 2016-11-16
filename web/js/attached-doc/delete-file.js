
$(document).ready(function() {
    $('#delete-attached-file').bind('click', function() {
        if(attachedFileIds.length == 0) {
            alert(messageDeleteFileNotChecked);
        } else {
            deleteAttachedFile();
        }
    });

    $('#atm_refresh_button').on('click',atm_refresh_button);
    $('#atm_add_button').on('click',atm_add_button);
    $('#atm_edit_button').on('click',function(){atm_edit_button();});

});

// ****************************** DELETE FILE *******************************
grid_doc_file.attachEvent("onAfterLoad", function(){
    attachedFileIds = [];
    // Сбор массива ид файлов прикрепленных
    grid_doc_file.attachEvent('onCheck', function(row, column, state) {
        /*console.log('row: ' + row);
         console.log('column: ' + column);
         console.log('state: ' + state);*/
        var index = attachedFileIds.indexOf(row);
        if(index == -1) {
            attachedFileIds.push(row);
        }
        else {
            if(state == 'off') {
                attachedFileIds.splice(index, 1);
            }
        }
//console.log(attachedFileIds);
    });
});

function deleteAttachedFile() {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: urlDeleteAttachedDocFile,
        data: {
            "attachedFileIds": attachedFileIds.toString(),
            "entityName": entityName,
            "entityId": entityId,
            "attDocId": attDocId
        },
        success: function(responce) {
            if(responce.res == 1) {
                // релоад грида с файлами
                grid_doc_file.clearAll(); grid_doc_file.loadNext(grid_doc_file.config.datafetch, 0, null, urlReloadGridFile);
                grid_doc_file_data_was_loaded = true;
            }
        }
    });
    grid_doc_file.refresh();
}
// ****************************** DELETE FILE *******************************

//иммитация кнопки обновления элемента в гриде
function atm_refresh_button(){
    $('#grid_doc_file_grid_refresh_button').trigger('click');
}

//иммитация кнопки добавления элемента в грид
function atm_add_button(){
    $('.add-btn').trigger('click');
}


//Создание ссылки для новой вкладки на редактирование

function atm_edit_button(){
    loc=window.location;
    //console.log(loc.pathname);
    id=current_grid.getItem(current_column)['id'];
    if($('html').attr('lang')=='en')
        labelName='Related file  ' + id +' editing';
    else
        labelName='Связанный файл ' + id+' редактирование';

    if(id!=undefined)
    parent.application_create_new_tab(
        labelName,
        loc.origin+((loc.pathname.indexOf('/web/')>=0)?'/web/':'/')+'index.php?r=common%2Fattached-doc-file%2Fupdate&entityName='+entityName+'&entityId='+entityId+'&id='+id);
}
