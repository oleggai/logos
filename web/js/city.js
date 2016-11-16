/**
 * JS функции для работы с формой нас. пунктов
 * @author Мельник И.А.
 */


var select_region2 = $('[name="ListCity[region]"]');
var select_region1 = $('[name="city_select_region1"]');
var select_country = $("[name='city_select_region_country']");
var label_region1 = $('[name="city_region1_type"]');
var label_region2 = $("[name='city_region2_type']");

function city_clearSelect(select){

    var options = select.data('select2').options.options;
    select.html('');
    options.data = [];
    select.select2(options).change();
}

/**
 * Получение регионов 2го уровня, в зависимости от региона 1го уровня
 * @param region_ref Регион первого уровня
 */
function city_loadRegions2(region_ref){

    jQuery.getJSON(url_get_regions, { region :region_ref}, function(data) {

        var regions2 = select_region2;
        var options = regions2.data('select2').options.options;
        regions2.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            regions2.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        regions2.select2(options);


    });
}

/**
 * Получение регионов 1го уровня, в зависимости от страны
 * @param country_ref Страна
 */
function city_loadRegions1(country_ref){

    jQuery.getJSON(url_get_regions, { country:country_ref}, function(data) {

        var regions1 = select_region1;
        var options = regions1.data('select2').options.options;
        regions1.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            regions1.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        regions1.select2(options).change();
    });
}

function city_loadRegionType(region_ref, element){

    if (!region_ref)
        element.val('');
    else
    jQuery.getJSON(url_get_region_type, { region:region_ref},
        (function(this_item) {
            return function(data) {
                this_item.val(data)
                ;}
        ;}(element)
        )
    );
}

select_country.change(function(){
    if (this.value)
        city_loadRegions1(this.value);
    else
        city_clearSelect(select_region1);
});

select_region1.change(function(){
    if (this.value)
        city_loadRegions2(this.value);
    else
        city_clearSelect(select_region2);

    city_loadRegionType(this.value, label_region1);
});

select_region2.change(function(){
    city_loadRegionType(this.value, label_region2);
});
/*
grid_schedule.attachEvent('onAfterEditStop',function(state, editor) {
    var item = this.getItem(editor.row);
    // проверка правильности введенного времени
  //  if (state.value.localeCompare('24:00')>0)
     //   item[editor.column] = '24:00';


    // автозаполение расписания
    if (item['dayofweek'] != 6 && item['dayofweek'] != 7)
        grid_schedule.eachRow(
        function (row){

            var row = this.getItem(row);
            if (row['schedule_type'] == item['schedule_type'] && row['dayofweek'] != 6 && row['dayofweek'] != 7){

                if (row['time_begin']=='00:00')
                    row['time_begin'] = item['time_begin'];

                if (row['time_end']=='00:00')
                    row['time_end'] = item['time_end'];
            }
        }
    )

    // проверка начала и конца интервала
    //if (item['time_begin'].localeCompare(item['time_end'])>0)
    //    item['time_end'] = item['time_begin'];
});

*/

//grid_routes.attachEvent('onAfterEditStop',function(state, editor) {
//
//
//    var item = this.getItem(editor.row);
//
//    jQuery.getJSON(url_get_city, { id :item['city']}, function(data) {
//
//        item['region'] = data['region'];
//        item['regionType'] = data['regionType'];
//        item['region1'] = data['region1'];
//        item['region1Type'] = data['region1Type'];
//        item['country'] = data['country'];
//
//        grid_routes.refresh();
//    });
//
//
//});

grid_routes.attachEvent('onAfterEditStop', function(state, editor) {
    var item = this.getItem(editor.row);
    var grid = this;
    
    data = {
        id: item.city
    };
    if (item.uniq_id) {
        data.uniqId = item.uniq_id;
    }

    jQuery.getJSON(url_get_city, data, function(data) {
        data.id = item.id;
        grid.updateItem(data.id, data);
        grid.refresh();
    });
});

// Добавление SelectEntityWidget после добавления новой строки в грид
grid_routes.attachEvent('onAfterAdd', function(id, index) {
    var grid = this;
    var row = grid.data.getItem(id)
    
    $.get(url_get_city_select_entity, function(data) {
        var widgetHtml = $(data);
        var body = $('body');
        var uniqId = null;
        widgetHtml.each(function(key, data) {
            if (key == 1) {
                row.name_select_entity = $(data)[0].outerHTML;
                uniqId = $(data).find('.entity-uniq-id').val();
            } else {
                body.append(data);
            }
        });

        grid.updateItem(id, row);

        if (uniqId) {
            grid.attachEvent('onAfterRender', function() {
                eval("select_entity_"+uniqId+"_generate_view()");
            });
        }
    });

    return true;
});

/*
 * Обработка для SelectEntityWidget внутри грида (вкладка "направления")
 * 
 */
var cityRoutesGrid = {
    updateFromSelectEntity: function(data) {
//        console.log(data);
//        console.log(current_column);
//        var currRow = current_grid.getItem(itemId);
//        console.log(currRow);

        var itemId = current_column.row; // id в webix DataStorage
        data.id = itemId;
        current_grid.updateItem(itemId, data); // обновить данные в webix DataStorage
        current_grid.refresh(); // обновить webix таблицу
    }
};

$(function(){
    $('body').on('change', '.change-route-id-trigger', function() {
        if (this.value && this.value != "") {
            var routeInput = $(this);
            var entityUniqId = routeInput.nextAll('.entity-uniq-id');
            var data = {
                id: this.value,
            }
            if (entityUniqId.length > 0) {
                data.uniqId = entityUniqId.val();
            }
            jQuery.getJSON(url_get_city, data,
                function(data) {
                    cityRoutesGrid.updateFromSelectEntity(data);
                }
            );
        }
    });
});

$(function(){
    grid_schedule.attachEvent('onDataUpdate', function (id, data) {
        checkGridDate(data, [
            'time_begin',
            'time_end',
        ], 'grid_schedule');

    });
});