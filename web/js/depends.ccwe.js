/**
 * JS обработчик зависимостей Country-City-Warehouse-Employee
 * Для использования опишите объект ccwe перед использованием файла
 *
    var ccwe = {
    select_country: $('[name="status_country"]'),
    select_city: $('[name="status_city"]'),
    select_warehouse: $('[name="status_warehouse"]'),
    select_employee: $('[name="ид2"]'),
};
 *
 */
var url_get_cities = "index.php?r=dictionaries/list-city/get-list";
var url_get_warehouses = "index.php?r=dictionaries/warehouse/get-list";
var url_get_employees = "index.php?r=dictionaries/employee/get-list";
var url_get_employee_address = "index.php?r=dictionaries/employee/get-address";

var ccwe_select_country = ccwe['select_country'];
var ccwe_select_city = ccwe['select_city'];
var ccwe_select_warehouse = ccwe['select_warehouse'];
var ccwe_select_employee = ccwe['select_employee'];

var ccwe_default_address = {
    country: ccwe_select_country.val(),
    city: ccwe_select_city.val(),
    warehouse:ccwe_select_warehouse.val(),
    employee: ccwe_select_employee.val()
};
var ccwe_address = {};

function ccwe_reset(){

    ccwe_address = ccwe_default_address;
    ccwe_select_country.val(ccwe_address['country']).change();
}

function ccwe_loadSelect2(select, url, val) {
    jQuery.getJSON(url, {
        country: ccwe_select_country.val(),
        city: ccwe_select_city.val(),
        warehouse: ccwe_select_warehouse.val(),
        employee: ccwe_select_employee.val(),
    }, function (data) {
        ccwe_loadSelect(select, data, val);
    });
}

function ccwe_loadSelect(select, data, val){

    var options = select.data('select2').options.options;
    select.html('');
    var items = [];
    for (var i = 0; i < data.length; i++) {
        items.push({"id": data[i]['id'], "text": data[i]['txt']});
        select.append($('<option>', {val: data[i]['id'], text: data[i]['txt']}));
    }
    options.data = items;
    select.select2(options).val(val).change();
}

ccwe_select_country.change(function () {

    ccwe_loadSelect2(ccwe_select_city, url_get_cities, ccwe_address['city']);
});

ccwe_select_city.change(function () {

    ccwe_loadSelect2(ccwe_select_warehouse, url_get_warehouses, ccwe_address['warehouse']);
});

ccwe_select_warehouse.change(function () {

    ccwe_loadSelect2(ccwe_select_employee, url_get_employees, ccwe_address['employee']);
});

ccwe_select_employee.change(function(){

    if (Object.keys(ccwe_address).length != 0){
        ccwe_address = {};
    }

});

ccwe_select_employee.on("select2:select",function(){

    jQuery.getJSON(url_get_employee_address, { employee: ccwe_select_employee.val()}, function (data) {

        ccwe_address = data;
        ccwe_select_country.val(ccwe_address['country']).change();
    });
});

ccwe_select_warehouse.on("select2:select",function(){

    jQuery.getJSON(url_get_employee_address, { warehouse: ccwe_select_warehouse.val()}, function (data) {

        ccwe_address = data;
        ccwe_select_country.val(ccwe_address['country']).change();
    });
});

ccwe_select_city.on("select2:select",function(){

    jQuery.getJSON(url_get_employee_address, { city: ccwe_select_city.val()}, function (data) {

        ccwe_address = data;
        ccwe_select_country.val(ccwe_address['country']).change();
    });
});

