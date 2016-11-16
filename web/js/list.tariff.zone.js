/**
 * Файл JS для тарифных зон
 * Содержит функции для выбора страны/региона
 * @author Дмитрий Чеусов
 * @type type
 */

var select_region2 = $('[name="ListCity[region]"]');
var select_region1 = $('[name="select_region1"]');
var select_country = $("[name='select_region_country']");
var label_region1 = $('[name="region1_type"]');
var label_region2 = $("[name='region2_type']");
var select_cities = $("[name='select_cities']");

function clearSelect(select){

    var options = select.data('select2').options.options;
    select.html('');
    options.data = [];
    select.select2(options).change();
}

/**
 * Получение списка городов, в зависимости от региона 2 уровня
 * @param city_ref Город
 */
function loadCities(city_ref){

    jQuery.getJSON(url_get_cities, { region:city_ref}, function(data) {

        var cities = select_cities;
        var options = cities.data('select2').options.options;
        cities.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            cities.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        cities.select2(options).change();
    });
}

/**
 * Получение регионов 2го уровня, в зависимости от региона 1го уровня
 * @param region_ref Регион первого уровня
 */
function loadRegions2(region_ref){

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
function loadRegions1(country_ref){

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

function loadRegionType(region_ref, element){

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
        loadRegions1(this.value);
    else
        clearSelect(select_region1);
});

select_region1.change(function(){
    if (this.value)
        loadRegions2(this.value);
    else
        clearSelect(select_region2);

    loadRegionType(this.value, label_region1);
});

select_region2.change(function(){
    if (this.value)
        loadCities(this.value);
    else
        clearSelect(select_cities);

    loadRegionType(this.value, label_region2);
});

