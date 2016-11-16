Array.prototype.clean = function(deleteValue) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] == deleteValue) {
            this.splice(i, 1);
            i--;
        }
    }
    return this;
};

var ews_string = '';
var ews_array = ews_string.split(",").clean("");
var ew_manual_input_add = $('#ew_manual_input_add');
var ew_auto_input = $('#ew_auto_input');
var ew_auto_done = $('#ew_auto_input_done');
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


    var multi_select = typeof new_ew == 'object';

    hideMessages();

    if (multi_select){

        if (new_ew.not_selected){
            ew_manual_input_error.text('ЭН не были выбраны');
            ew_manual_input_error.show();
            return;
        }
    }
    // накладная ужа добавлена
    else if (ews_array.indexOf(new_ew)>-1){
        ew_manual_input_error.text('ЭН уже добавлена');
        ew_manual_input_error.show();
        return;
    }


    ew_manual_input_waiting.show();

    $.ajax({ url: multi_select ? url_getnums : url_getstate ,
        data: 'ew_num='+ (multi_select ?  encodeURIComponent(JSON.stringify(new_ew)) : new_ew),
        type: 'post',
        success: function(output) {

            ew_manual_input_waiting.hide();
            var ew_state = multi_select ? JSON.parse(output) : output;

            if (multi_select){

                var new_ews_added = false;
                ew_state.forEach(function(e){
                    if (ews_array.indexOf(e.ew_num) == -1){
                        new_ews_added = true;
                        ews_array.push(e.ew_num);
                    }
                });

                if (new_ews_added){

                    ew_manual_input_error.hide();
                    ew_manual_input_success.show();
                    ews_array.push(new_ew);
                    refreshEwNums();
                }
                else{

                    ew_manual_input_error.text('Выбранные ЭН уже находятся в списке');
                    ew_manual_input_error.show();
                }

                return;
            }

            if (ew_state > 0 ) {
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


ew_auto_done.click(function(){

    addEw(JSON.parse(ew_auto_input.val()));
});