Array.prototype.clean = function(deleteValue) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == deleteValue) {
            this.splice(i, 1);
            i--;
        }
    }
    return this;
};

/// формирование даты на стороне клиента
if ($("[name='isNewRecord']").val()) {

    var today = new Date().format('d.m.Y H:i:s');
    $("#manifest-_date").val(today);
}

if (mn_disableedit) {
    $('#div_link_ew_operations').hide();
}

var ews_array = ews_string.split(",").clean("");
var ew_manual_input_add = $('#ew_manual_input_add');
var ew_auto_input = $('#ew_auto_input');
var ew_manual_input = $('#ew_manual_input');
var ew_manual_input_error = $('#ew_manual_input_error');
var ew_manual_input_waiting = $('#ew_manual_input_waiting');
var ew_manual_input_success = $('#ew_manual_input_success');
var ew_delete_error = $('#ew_delete_error');
var ew_delete_success = $('#ew_delete_success');
var ew_delete_selected = $('#ew_delete_selected');

function refreshEwNums(){
    var filter_string = ews_array.join();
    if (filter_string=="")
        filter_string = "-1";

    var newurl = grida.config.url.replace(/f_ew_nums=[^&]+/, 'f_ew_nums='+filter_string)
    grida.clearAll();
    grida.load(newurl);
}

function hideMessages() {
    ew_manual_input_success.hide();
    ew_manual_input_waiting.hide();
    ew_manual_input_error.hide();
    ew_delete_success.hide();
    ew_delete_error.hide();
}

function addEw(new_ew){


    hideMessages();

    // накладная ужа добавлена
    if (ews_array.indexOf(new_ew)>-1){
        ew_manual_input_error.text('ЭН уже добавлена');
        ew_manual_input_error.show();
        return;
    }


    ew_manual_input_waiting.show();
    $.ajax({ url: url_getstate,
        data: 'ew_num='+new_ew,
        type: 'post',
        success: function(output) {

            ew_manual_input_waiting.hide();
            var ew_state = output;

            // temp Появилась проблема, поскольку МН мы переносим вручную, а все ЭН уже закрытые и их нельзя добавить через проверку при добавлении ЭН в МН. Можно ли пока отключить эту проверку?
            // ew_state == 2
            if (ew_state == 1 || ew_state == 2) {
                ew_manual_input_error.hide();
                ew_manual_input_success.show();
                ews_array.push(new_ew);
                refreshEwNums();
                return;
            }

            if (ew_state == 0)
                ew_manual_input_error.text('ЭН не найдена');
            else if (ew_state == 2)
                ew_manual_input_error.text('ЭН в состоянии закрыта');
            else if (ew_state == 100)
                ew_manual_input_error.text('ЭН в состоянии удалена');
            else
                ew_manual_input_error.text('ЭН в состоянии '+ew_state);

            ew_manual_input_error.show();
        }
    });
}

ew_manual_input.keydown(function (e) {
    if (e.which == 13) {
        ew_manual_input_add.click();
        e.preventDefault();
        return false;
    }
});

ew_delete_selected.click(function(){

    hideMessages();

    var countBefore = ews_array.length;

    grida.eachRow(
        function (row){
            var item = grida.getItem(row);
            var bingo = false;

            if (item.check)
                ews_array.clean(item.ew_num);
        }
    );

    if (countBefore != ews_array.length) {
        refreshEwNums();
        ew_delete_success.show();
    }
    else
        ew_delete_error.show();
});

ew_manual_input_add.click(function () {
    addEw(ew_manual_input.val());
});

/*
ew_auto_input.bind('change', function () {
    addEw(ew_auto_input.val());
});
*/

window.onload = function(){
    document.forms[0].onsubmit = function(){

        var form = document.forms[0];

        // места
        var container = document.getElementById('post_ews_list');
        if (container==null)
        {
            container = document.createElement('div');
            container.id = 'post_ews_list';
            form.appendChild(container);
        }
        else
            container.innerHTML = '';

        var i=0;
        for (; i < ews_array.length; i++){

            var value = ews_array[i];
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'Ew_' +i+ '[ew_num]';
            input.value = value;

            container.appendChild(input);
        }

    };
};


var mn_event_input_waiting = $('#mn_event_input_waiting');
var mn_event_input_success = $('#mn_event_input_success');
var mn_event_input_error = $('#mn_event_input_error');

$("#mn_add_event_button").click(function(){

    addMnEvent($("[name=mn_add_event_dropdown]").val());
});

function refreshMnEvents(){

    grid_events.clearAll();
    grid_events.load(grid_events.config.url);
}

function hideMessages() {
    mn_event_input_success.hide();
    mn_event_input_waiting.hide();
    mn_event_input_error.hide();
}

function addMnEvent(new_event){

    hideMessages();

    mn_event_input_waiting.show();
    $.ajax({ url: url_callevent,
        data: 'entity_id='+mn_id+"&event="+new_event,
        type: 'post',
        success: function(output) {

            mn_event_input_waiting.hide();
            var result = output;


            if (result == true) {
                mn_event_input_error.hide();
                mn_event_input_success.show();
                refreshMnEvents();
                return;
            }


            mn_event_input_error.text(result);
            mn_event_input_error.show();
        }
    });
}

grid_related_entities.attachEvent('onItemDblClick', function() {

    var item_id = grid_related_entities.getSelectedId();
    var item = grid_related_entities.getItem(item_id);
    if (!item)
        return;

    var thisIframeId = window.frameElement.getAttribute("id");
    var tabName = 'EW ' + item['doc_num'] + ' view';
    var url = url_prefix + '?r=ew/express-waybill/view' + '&id=' + item['id'];
    var uniqueTabId = 'expresswaybillview' + item['id'];
    parent.application_create_new_tab(tabName, url, uniqueTabId, 'false', thisIframeId, 'false');

});