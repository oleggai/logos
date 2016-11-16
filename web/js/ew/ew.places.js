var ew_places_count  = $('[name="ew_places_count"]');
var ew_num_input = $('#expresswaybill-ew_num');

grid_places.attachEvent('onBeforeEditStop', function(state, editor, ignore){

    if (editor.column == 'place_bc' && state.old != state.value ) {

        if (!checkBarCode(state.value)) {
            state.value = state.old;
            this.editCancel();
        }
    }

});

grid_places.attachEvent('onAfterEditStop', function(state, editor) {
    if (editor.column == 'width' || editor.column == 'height' || editor.column == 'width') {

        var item = this.getItem(editor.row);
        calculate_dimensional_weight(item);
    }

    //if (editor.column == 'actual_weight' || editor.column == 'dimensional_weight') {
    after_change_places();
    //}

});

grid_places.attachEvent("onAfterAdd", function(id, index){

    var item = this.getItem(id);
    var ew_num = ew_num_input.val();

    item['place_number']=this.count();
    item['place_bc']= this.count()==1 ? ew_num : (ew_num + pad(this.count(),4));

    after_change_places();
});

grid_places.attachEvent("onAfterDelete", function(id){
    after_change_places();
});


/**
 * Расчет объемного веса
 * @param item
 */
function calculate_dimensional_weight(item) {
    var float_w = parseFloat(item['width']);
    var float_h = parseFloat(item['height']);
    var float_l = parseFloat(item['length']);

    if (!isNaN(float_h) && !isNaN(float_w) && !isNaN(float_l)) {
        item['dimensional_weight'] = (float_h * float_l * float_w) / 5000;
    }
}

/**
 * Проверка ШК на дубликаты
 * @param code
 * @returns {boolean}
 */
function checkBarCode(code){

    if (grid_places.find(function(obj){ return obj.place_bc.indexOf(code) != -1}).length>0){
        parent.show_app_alert('Error', "Attention! Barcode '"+code+"' already exists", 'Ok');
        return false;
    }


    var remote = $.ajax({
        type: "POST",
        data: {bc_code : code},
        url: url_validate_bc,
        async: false
    }).responseText;


    if (remote != "") {
        parent.show_app_alert('Error', remote, 'Ok');
        return false;
    }

    return true;
}

/**
 * Расчет объемного и фактического веса
 */
function after_change_places(){

    var total_dimensional_input = $('#expresswaybill-total_dimensional_weight_kg');
    var total_actual_input = $('#expresswaybill-total_actual_weight_kg');
    var total_actual_invoice_input = $('#expresswaybill-invoice_total_weight');

    var maximum_length = $('#expresswaybill-maximum_length');
    var maximum_width = $('#expresswaybill-maximum_width');
    var maximum_height = $('#expresswaybill-maximum_height');

    var sum_dimensional = 0;
    var sum_actual = 0;
    var i=0;

    var max_l = 0;
    var max_h = 0;
    var max_w = 0;

    grid_places.eachRow(

        function (row){

            var item = grid_places.getItem(row);

            var float_val = parseFloat(item['actual_weight']);
            if (!isNaN(float_val))
                sum_actual += float_val;
            float_val = parseFloat(item['dimensional_weight']);
            if (!isNaN(float_val))
                sum_dimensional += float_val;

            float_val = parseFloat(item['width']);
            if (!isNaN(float_val) && float_val>max_w)
                max_w = float_val;

            float_val = parseFloat(item['height']);
            if (!isNaN(float_val) && float_val>max_h)
                max_h = float_val;

            float_val = parseFloat(item['length']);
            if (!isNaN(float_val) && float_val>max_l)
                max_l = float_val;
        }
    );

    total_dimensional_input.val(sum_dimensional).change();
    total_actual_input.val(sum_actual).change();
    total_actual_invoice_input.val(sum_actual).change();

    maximum_length.val(max_l);
    maximum_height.val(max_h);
    maximum_width.val(max_w);


    ew_places_count.val(grid_places.count());
}

/**
 * Изменение кол-ва мест
 * @param new_count
 */
function changeEwCount(new_count){

    while (grid_places.count() < new_count)
        grid_places.add( { 'id' :  grid_places_auto_id-- } , 0);

    while (grid_places.count() > new_count){

        var max_num = -1;
        var item_id = -1;

        grid_places.eachRow(function (row) {
            var item = grid_places.getItem(row);
            if (max_num < item['place_number']) {
                max_num = item['place_number'];
                item_id = row;
            }
        });

        grid_places.remove(item_id);
    }

}

//grid_places_copy.sync(grid_places);