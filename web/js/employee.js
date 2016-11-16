
var txtSurnameRu = $('#surnameRu');
var txtNameRu = $('#nameRu');
var txtSecondNameRu = $('#secondNameRu');
var txtSurnameFullRu = $('#surnameFullRu');
var txtSurnameShortRu = $('#surnameShortRu');

var txtSurnameEn = $('#surnameEn');
var txtNameEn = $('#nameEn');
var txtSecondNameEn = $('#secondNameEn');
var txtSurnameFullEn = $('#surnameFullEn');
var txtSurnameShortEn = $('#surnameShortEn');

var txtSurnameUk = $('#surnameUk');
var txtNameUk = $('#nameUk');
var txtSecondNameUk = $('#secondNameUk');
var txtSurnameFullUk = $('#surnameFullUk');
var txtSurnameShortUk = $('#surnameShortUk');

var select_cities = $('[name="Employee[city_id]"]');
var select_country = $('[name="Employee[country_id]"]');
var select_warehouse = $('[name="Employee[warehouse_id]"]');

function formatSurnameShort(surname, name, secondName) {
    if (surname == '' || name == '')
        return '';

    if (secondName == '')
        return surname + ' ' + name.substring(0, 1) + '. ';

    return surname + ' ' + name.substring(0, 1) + '. ' + secondName.substring(0, 1) + '.';
}

function formatSurnameFull(surname, name, secondName) {
    if (surname == '' || name == '')/* || secondName == '')*/
        return '';

    if (secondName == '')
        return surname + ' ' + name;

    return surname + ' ' + name + ' ' + secondName;
}

function setSurnameFullRu() {
    txtSurnameFullRu.val(formatSurnameFull(txtSurnameRu.val(), txtNameRu.val(), txtSecondNameRu.val()));
}

function setSurnameShortRu() {
    txtSurnameShortRu.val(formatSurnameShort(txtSurnameRu.val(), txtNameRu.val(), txtSecondNameRu.val()));
}

function setSurnameFullEn() {
    txtSurnameFullEn.val(formatSurnameFull(txtSurnameEn.val(), txtNameEn.val(), txtSecondNameEn.val()));
}

function setSurnameShortEn() {
    txtSurnameShortEn.val(formatSurnameShort(txtSurnameEn.val(), txtNameEn.val(), txtSecondNameEn.val()));
}

function setSurnameFullUk() {
    txtSurnameFullUk.val(formatSurnameFull(txtSurnameUk.val(), txtNameUk.val(), txtSecondNameUk.val()));
}

function setSurnameShortUk() {
    txtSurnameShortUk.val(formatSurnameShort(txtSurnameUk.val(), txtNameUk.val(), txtSecondNameUk.val()));
}

txtSurnameRu.change(function() { setSurnameFullRu(); setSurnameShortRu(); });
txtNameRu.change(function() { setSurnameFullRu(); setSurnameShortRu(); });
txtSecondNameRu.change(function() { setSurnameFullRu(); setSurnameShortRu(); });

txtSurnameEn.change(function() { setSurnameFullEn(); setSurnameShortEn(); });
txtNameEn.change(function() { setSurnameFullEn(); setSurnameShortEn(); });
txtSecondNameEn.change(function() { setSurnameFullEn(); setSurnameShortEn(); });

txtSurnameUk.change(function() { setSurnameFullUk(); setSurnameShortUk(); });
txtNameUk.change(function() { setSurnameFullUk(); setSurnameShortUk(); });
txtSecondNameUk.change(function() { setSurnameFullUk(); setSurnameShortUk(); });


function clearSelect(select){

    var options = select.data('select2').options.options;
    select.html('');
    options.data = [];
    select.select2(options).change();
}

/**
 * Получение городов в зависимости от страны
 * @param country_ref Страна
 */
function loadCities(country_ref){

    jQuery.getJSON(url_get_cities, { country:country_ref}, function(data) {

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

function loadWarehouses(city_ref){

    jQuery.getJSON(url_get_warehouses, { city:city_ref}, function(data) {

        var warehouses = select_warehouse;
        var options = warehouses.data('select2').options.options;
        warehouses.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            warehouses.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        warehouses.select2(options).change();
    });
}

select_country.change(function(){
    if (this.value)
        loadCities(this.value);
    else
        clearSelect(select_cities);
});

select_cities.change(function(){
    if (this.value)
        loadWarehouses(this.value);
    else
        clearSelect(select_warehouse);
});
