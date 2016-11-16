var url_get_cities = "index.php?r=dictionaries/list-city/get-list";
var url_get_warehouses = "index.php?r=dictionaries/warehouse/get-list";
var url_get_employees = "index.php?r=dictionaries/employee/get-list";
var url_get_country_by_city = "index.php?r=dictionaries/country/get-country-by-city";
var url_get_city_by_warehouse = "index.php?r=dictionaries/list-city/get-city-by-warehouse";
var url_get_warehouse_by_employee = "index.php?r=dictionaries/warehouse/get-warehouse-by-employee";

var select_country = $('#af_cp_creation_country');
var select_city = $('#af_cp_creation_city');
var select_warehouse = $('#af_cp_creation_department');
var select_employee = $('#af_cp_creation_user');
var lang = $('#af_counterparty_lang');

var countries_by_cities = false;
var cities_by_warehouses = false;
var warehouses_by_employees = false;

var change_city = true;
var change_warehouse = true;
var change_employee = true;


function loadSelect2(select, url) {
    jQuery.getJSON(url, {
        country: select_country.val(),
        city: select_city.val(),
        warehouse: select_warehouse.val(),
        lang: lang.val()
    }, function (data) {
        var options = select.data('select2').options.options;
        select.html('');
        var items = [];
        for (var i = 0; i < data.length; i++) {
            items.push({"id": data[i]['id'], "text": data[i]['txt']});
            select.append($('<option>', {val: data[i]['id'], text: data[i]['txt']}));
        }
        options.data = items;
        select.select2(options).change();
    });
}


function clearSelect(select) {
    var options = select.data('select2').options.options;
    select.html('');
    select.val('');
    options.data = [];
    select.select2(options).change();
}

function loadCities() {
    loadSelect2(select_city, url_get_cities)
}

function loadWarehouses() {
    loadSelect2(select_warehouse, url_get_warehouses)
}


function loadEmployees() {
    loadSelect2(select_employee, url_get_employees)
}
// @todo @refactor to reLoadSelect2()
function reLoadCountries(city_ref) {
    if (!countries_by_cities) {
        jQuery.getJSON(url_get_country_by_city, {}, function (data) {
            countries_by_cities = data;
        });
    } else {
        country_city_values = countries_by_cities[city_ref];
        if (typeof country_city_values !== 'undefined') {
            select_country.val(country_city_values.country_id);
            if (select_country.val())
                select_country.change();
        }
    }
}

function reLoadCities(warehouse_ref) {
    if (!cities_by_warehouses) {
        jQuery.getJSON(url_get_city_by_warehouse, {}, function (data) {
            cities_by_warehouses = data;
        });
    } else {
        city_warehouse_values = cities_by_warehouses[warehouse_ref];
        if (typeof city_warehouse_values !== 'undefined') {
            select_city.val(city_warehouse_values.city_id);
            if (select_city.val())
                select_city.change();
        }
    }
}

function reLoadWarehouses(employee_ref) {
    if (!warehouses_by_employees) {
        jQuery.getJSON(url_get_warehouse_by_employee, {}, function (data) {
            warehouses_by_employees = data;
        });
    } else {
        warehouses_employees_values = warehouses_by_employees[employee_ref];
        if (typeof warehouses_employees_values !== 'undefined') {
            select_warehouse.val(warehouses_employees_values.warehouse_id);
            if (select_warehouse.val())
                select_warehouse.change();
        }
    }
}

select_country.change(function () {
    if (change_city) {
        loadCities();
    } else {
        change_city = true;
    }
});

select_city.change(function () {
    if (change_warehouse) {
        loadWarehouses();
    } else {
        change_warehouse = true;
    }
    if (!select_country.value) {
        change_city = false;
        reLoadCountries(this.value);
        change_city = true;
    }
});

select_warehouse.change(function () {
    if (change_employee) {
        loadEmployees();
    } else {
        change_employee = true;
    }
    if (!select_city.value) {
        change_warehouse = false;
        reLoadCities(this.value);
        change_warehouse = true;
    }
});

select_employee.change(function () {
    if (!select_warehouse.value) {
        change_employee = false;
        reLoadWarehouses(this.value);
        change_employee = true;
    }
});

// инициализация формы
function init_form() {
    reLoadCountries('');
    reLoadCities('');
    reLoadWarehouses('');
    null_form();
}

function null_form() {
    select_country.val('');
    select_city.val('');
    select_warehouse.val('');
    select_employee.val('');
    change_city = true;
    change_warehouse = true;
    change_employee = true;
}

init_form();


var af_doc_type = $("#af_doc_type");
var af_doc_serial = $('#af_doc_serial');
var af_doc_number = $('#af_doc_number');
var af_filter_btn = $('.afilter_submit_btn');

/**
 * Смена типа документа. Для паспорта Украины используются маски
 */
af_doc_type.change(function() {
    if(this.value==1){
        af_doc_serial.inputmask({mask:"ff"});
        af_doc_number.inputmask("999999");
    }
    else
    {
        af_doc_serial.off();
        af_doc_number.off();
    }

    af_doc_number.change(function(){setFindBtnEnabled()});
    af_doc_serial.change(function(){setFindBtnEnabled()});

    setFindBtnEnabled();
});


function setFindBtnEnabled() {

    if (af_doc_type.val()==1 && (!af_doc_serial.val() || !af_doc_number.val())) {
        af_filter_btn.attr('disabled', 'disabled');
        return;
    }

    if (af_doc_type.val()>0  && !af_doc_number.val()) {
        af_filter_btn.attr('disabled', 'disabled');
        return;
    }

    af_filter_btn.removeAttr('disabled', false);
}

$(document).ready(function(){
    // ждем пока подгрузится maskedinput
    setTimeout(function() {
        af_doc_type.change();
    }, 1000);

});