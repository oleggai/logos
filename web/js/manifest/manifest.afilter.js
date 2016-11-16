//страна-город-подразделение
var select_country = $('#af_ew_creation_country');
var select_city = $('#af_ew_creation_city');
var select_warehouse = $('#af_ew_creation_department');
var select_employee = $('#af_ew_creation_user');

var countries_by_cities = false;
var cities_by_warehouses = false;
var warehouses_by_employees = false;

var change_city = true;
var change_warehouse = true;
var change_employee = true;

// функции
$('#af_ew_lang').change(function () {
    $('#af_ew_entity').val('');
    $('#af_ew_entity').change();
});
$('#af_ew_entity').change(function () {
    if ($('#af_ew_entity').val()) {

// Номер сущности
        $('#af_ew_entity_num').prop('disabled', false);
        // Дата с-по
        $('#af_ew_entity_date_begin').prop('disabled', false);
        $('#af_ew_entity_date_end').prop('disabled', false);
        // Сущность это Накладная или Заказ
        if ($('#af_ew_entity').val() == 1 || $('#af_ew_entity').val() == 2) {
            $('#af_ew_order_type').prop('disabled', false);
        }
        else {
            $('#af_ew_order_type').val('');
            $('#af_ew_order_type').prop('disabled', true);
        }
    }
    else {

        $('#af_ew_entity_num').val('');
        $('#af_ew_entity_num').prop('disabled', true);
        $('#af_ew_order_type').val('');
        $('#af_ew_order_type').prop('disabled', true);
        $('#af_ew_entity_date_begin').val('');
        $('#af_ew_entity_date_begin').prop('disabled', true);
        $('#af_ew_entity_date_end').val('');
        $('#af_ew_entity_date_end').prop('disabled', true);
    }
    ;
});

$('#af_ew_places_from').change(function () {
    if (isNormalInteger($('#af_ew_places_from').val())) {
        $('#af_ew_places_from').css('background-color', 'white');
        validate_from_to('af_ew_places', 'from', 'to', false);
    } else {
        $('#af_ew_places_from').css('background-color', 'red');
    }
});
$('#af_ew_places_to').change(function () {
    if (isNormalInteger($('#af_ew_places_to').val())) {
        $('#af_ew_places_to').css('background-color', 'white');
        validate_from_to('af_ew_places', 'from', 'to', false);
    } else {
        $('#af_ew_places_to').css('background-color', 'red');
    }
});

$('#af_ew_date_begin').change(function () {
    validate_from_to('af_ew_date', 'begin', 'end', true);
});
$('#af_ew_date_end').change(function () {
    validate_from_to('af_ew_date', 'begin', 'end', true);
});

$('#af_ew_entity_date_begin').change(function () {
    validate_from_to('af_ew_entity_date', 'begin', 'end', true);
});
$('#af_ew_entity_date_end').change(function () {
    validate_from_to('af_ew_entity_date', 'begin', 'end', true);
});
''

function isNormalInteger(str) {
    if (!str.length)
        return true;
    return /^\+?(0|[1-9]\d*)$/.test(str);
}

/**
 * Проверка от-до (с-по для даты)
 * @param {string} field
 * @param {string} from
 * @param {string} to
 * @param {bool} is_date
 * @returns {null}
 */
function validate_from_to(field, from, to, is_date) {

    var from_field = $('#' + field + "_" + from);
    var to_field = $('#' + field + "_" + to);

    if (is_date) {
        var pattern = /(\d*).*\.(\d*).*\.(\d*).+/;
        var from_field_val = $('#' + field + "_" + from).val().replace(pattern, '$3$2$1')
        var to_field_val = $('#' + field + "_" + to).val().replace(pattern, '$3$2$1')
    } else {
        var from_field_val = $('#' + field + "_" + from).val();
        var to_field_val = $('#' + field + "_" + to).val();
    }


    if (!from_field_val && !to_field_val) {
        to_field.css('background-color', 'white');
        from_field.css('background-color', 'white');
        $('.filter_submit_btn').show();
        return;
    }
    if (!from_field_val) {
        from_field.css('background-color', 'red');
        $('.filter_submit_btn').hide();
        return;
    } else {
        from_field.css('background-color', 'white');
    }
    if (!to_field_val) {
        to_field.css('background-color', 'red');
        $('.filter_submit_btn').hide();
        return;
    } else {
        from_field.css('background-color', 'white');
    }
    if (from_field_val > to_field_val) {
        alert(from_field_val + ">" + to_field_val);
        to_field.css('background-color', 'red');
        from_field.css('background-color', 'red');
        $('.filter_submit_btn').hide();
        return;
    } else {
        to_field.css('background-color', 'white');
        from_field.css('background-color', 'white');
        $('.filter_submit_btn').show();
    }
    return;
}

function clearSelect(select) {
    var options = select.data('select2').options.options;
    select.html('');
    select.val('');
    options.data = [];
    select.select2(options).change();
}

function loadCities(country_ref) {

    jQuery.getJSON(url_get_cities, {country: country_ref, lang: $('#af_ew_lang').val()}, function (data) {

        var cities = select_city;
        var options = cities.data('select2').options.options;
        cities.html('');

        var items = [];
        for (var i = 0; i < data.length; i++) {
            items.push({"id": data[i]['id'], "text": data[i]['txt']});
            cities.append($('<option>', {val: data[i]['id'], text: data[i]['txt']}));
        }

        options.data = items;
        cities.select2(options).change();
    });
}

function loadWarehouses(city_ref) {

    jQuery.getJSON(url_get_warehouses, {city: city_ref, lang: $('#af_ew_lang').val()}, function (data) {

        var warehouses = select_warehouse;
        var options = warehouses.data('select2').options.options;
        warehouses.html('');

        var items = [];
        for (var i = 0; i < data.length; i++) {
            items.push({"id": data[i]['id'], "text": data[i]['txt']});
            warehouses.append($('<option>', {val: data[i]['id'], text: data[i]['txt'], city: data[i]['city']}));
        }

        options.data = items;
        warehouses.select2(options).change();
    });
}


function loadEmployees(warehouse_ref) {

    jQuery.getJSON(url_get_employees, {warehouse: warehouse_ref, lang: $('#af_ew_lang').val()}, function (data) {

        var employees = select_employee;
        var options = employees.data('select2').options.options;
        employees.html('');

        var items = [];
        for (var i = 0; i < data.length; i++) {
            items.push({"id": data[i]['id'], "text": data[i]['txt']});
            employees.append($('<option>', {val: data[i]['id'], text: data[i]['txt']}));
        }

        options.data = items;
        employees.select2(options).change();
    });
}

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
    if (this.value) {
        if (change_city) {
            loadCities(this.value);
        } else {
            change_city = true;
        }
    }
    else {
        clearSelect(select_city);
    }
});

select_city.change(function () {
    if (this.value) {
        if (change_warehouse) {
            loadWarehouses(this.value);
        } else {
            change_warehouse = true;
        }
        if (!select_country.value) {
            change_city = false;
            reLoadCountries(this.value);
            change_city = true;
        }
    }
    else {
        clearSelect(select_warehouse);
    }
});

select_warehouse.change(function () {
    if (this.value) {
        if (change_employee) {
            loadEmployees(this.value);
        } else {
            loadEmployees = true;
        }
        if (!select_city.value) {
            change_warehouse = false;
            reLoadCities(this.value);
            change_warehouse = true;
        }
    }
    else {
        clearSelect(select_employee);
    }
});

select_employee.change(function () {
    if (this.value) {
        if (!select_warehouse.value) {
            change_employee = false;
            reLoadWarehouses(this.value);
            change_employee = true;
        }
    }
});

// инициализация формы
function init_form() {
    reLoadCountries('');
    reLoadCities('');
    reLoadWarehouses('');
    null_form();
    $('#af_ew_entity').change();
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

