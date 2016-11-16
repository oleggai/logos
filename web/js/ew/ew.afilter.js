var url_get_countries = "index.php?r=dictionaries/country/get-list";
var url_get_cities = "index.php?r=dictionaries/list-city/get-list";
var url_get_warehouses = "index.php?r=dictionaries/warehouse/get-list";
var url_get_employees = "index.php?r=dictionaries/employee/get-list";
var url_get_counterparties = "index.php?r=counterparty/counterparty/get-list";
var url_get_ew_types = "index.php?r=ew/express-waybill/get-ew-type-list";
var url_get_order_types = "index.php?r=dictionaries/wb-order-type/get-list";
var url_get_country_by_city = "index.php?r=dictionaries/country/get-country-by-city";
var url_get_city_by_warehouse = "index.php?r=dictionaries/list-city/get-city-by-warehouse";
var url_get_city_by_cp = "index.php?r=counterparty/counterparty/get-cp-info";
var url_get_warehouse_by_employee = "index.php?r=dictionaries/warehouse/get-warehouse-by-employee";
var url_get_list_with_closed = 'index.php?r=dictionaries/ew-state/get-list';
var url_get_list_without_closed = 'index.php?r=common/get-list';

var select_country = $('#af_ew_creation_country');
var select_city = $('#af_ew_creation_city');
var select_warehouse = $('#af_ew_creation_department');
var select_employee = $('#af_ew_creation_user');
//var select_state = $('#af_entity_state');

var select_cp_country = $('#af_ew_counterparty_country');
var select_cp_city = $('#af_ew_counterparty_city');
var select_cp = $('#af_ew_counterparty_counterparty');

var select_type = $('#af_ew_order_type');
var lang = $('#af_ew_lang');

var countries_by_cities = false;
var cities_by_warehouses = false;
var warehouses_by_employees = false;

var cities_by_cps = false;

var change_city = true;
var change_warehouse = true;
var change_employee = true;

var change_cp_city = true;
var change_cp = true;

var warningColor='rgb(169, 68, 66)';

$('#af_ew_order_type').change(function () {
    if ($('#af_ew_order_type').val()) {
        // reload state without closed
        loadSelect(select_state, url_get_list_without_closed);
    } else {
        // reload state  with closed
        loadSelect(select_state, url_get_list_with_closed);
    }
});
$('#af_ew_lang').change(function () {
    $('#af_ew_entity').val('');
    $('#af_ew_entity').change();
    $('.multiSel').html('');
    $('.clearablecheckbox_hida').hide();
});

$('#af_ew_entity').change(function () {
    if ($('#af_ew_entity').val()) {
// Номер сущности
        $('#af_ew_entity_number').prop('disabled', false);
        // Дата с-по
        $('#af_ew_entity_date_begin').prop('disabled', false);
        $('#af_ew_entity_date_end').prop('disabled', false);
        // Сущность это Накладная
        if ($('#af_ew_entity').val() == 1) {
            $('#af_ew_order_type').prop('disabled', false);
            $('#af_ew_order_carrier').prop('disabled', false);
            if (!$('#af_ew_order_type').val()) {
                // reload state with closed
                //loadCheckboxes(select_state, url_get_list_with_closed);
                //$('.multiSel_check_af_entity_state').html('');
                //$('.hida_check_af_entity_state').hide();
            }
        }
        else {
            $('#af_ew_order_type').val('');
            $('#af_ew_order_type').prop('disabled', true);
            $('#af_ew_order_carrier').val('');
            $('#af_ew_order_carrier').prop('disabled', true);
            // reload state without closed
            //loadCheckboxes(select_state, url_get_list_without_closed);
            //$('.multiSel_check_af_entity_state').html('');
            //$('.hida_check_af_entity_state').hide();
        }
    }
    else {
        $('#af_ew_entity_number').val('');
        $('#af_ew_entity_number').prop('disabled', true);
        $('#af_ew_order_type').val('');
        $('#af_ew_order_type').prop('disabled', true);
        $('#af_ew_order_carrier').val('');
        $('#af_ew_order_carrier').prop('disabled', true);
        $('#af_ew_entity_date_begin').val('');
        $('#af_ew_entity_date_begin').prop('disabled', true);
        $('#af_ew_entity_date_end').val('');
        $('#af_ew_entity_date_end').prop('disabled', true);
    }
    ;
});
//-----------------------------------------------------------
$('#af_ew_places_from').change(function () {
    if (isNormalInteger($('#af_ew_places_from').val())) {
        $('#af_ew_places_from').css('border-color', 'gray');
        validate_from_to('af_ew_places', 'from', 'to', false);
    } else {
        $('#af_ew_places_from').css('border-color', warningColor);
    }
});
$('#af_ew_places_to').change(function () {
    if (isNormalInteger($('#af_ew_places_to').val())) {
        $('#af_ew_places_to').css('border-color', 'gray');
        validate_from_to('af_ew_places', 'from', 'to', false);
    } else {
        $('#af_ew_places_to').css('border-color', warningColor);
    }
});

$('#af_ew_weight_from').change(function () {
    if (isNormalInteger($('#af_ew_weight_from').val())) {
        $('#af_ew_weight_from').css('border-color', 'gray');
        validate_from_to('af_ew_weight', 'from', 'to', false);
    } else {
        $('#af_ew_weight_from').css('border-color', warningColor);
    }
});
$('#af_ew_weight_to').change(function () {
    if (isNormalInteger($('#af_ew_weight_to').val())) {
        $('#af_ew_weight_to').css('border-color', 'gray');
        validate_from_to('af_ew_weight', 'from', 'to', false);
    } else {
        $('#af_ew_weight_to').css('border-color', warningColor);
    }
});

$('#af_ew_cdcost_from').change(function () {
    if (isNormalInteger($('#af_ew_cdcost_from').val())) {
        $('#af_ew_cdcost_from').css('border-color', 'gray');
        validate_from_to('af_ew_cdcost', 'from', 'to', false);
        if (!$('#af_ew_cdcost_currency').val() && ($('#af_ew_cdcost_from').val() || $('#af_ew_cdcost_to').val())) {
            $('#af_ew_cdcost_currency').css('border-color', warningColor);
            $('.afilter_submit_btn').hide();
            return;
        } else {
            $('#af_ew_cdcost_currency').css('border-color', 'gray');
            $('.afilter_submit_btn').show();
        }
    } else {
        $('#af_ew_cdcost_from').css('border-color', warningColor);
    }
});
$('#af_ew_cdcost_to').change(function () {
    if (isNormalInteger($('#af_ew_cdcost_to').val())) {
        $('#af_ew_cdcost_to').css('border-color', 'gray');
        validate_from_to('af_ew_cdcost', 'from', 'to', false);
        if (!$('#af_ew_cdcost_currency').val() && ($('#af_ew_cdcost_from').val() || $('#af_ew_cdcost_to').val())) {
            $('#af_ew_cdcost_currency').css('border-color', warningColor);
            $('.afilter_submit_btn').hide();
            return;
        } else {
            $('#af_ew_cdcost_currency').css('border-color', 'gray');
            $('.afilter_submit_btn').show();
        }
    } else {
        $('#af_ew_cdcost_to').css('border-color', warningColor);
    }
});
$('#af_ew_cdcost_currency').change(function () {
    $('#af_ew_cdcost_from').change();
    $('#af_ew_cdcost_to').change();
    if (!$('#af_ew_cdcost_from').val() && !$('#af_ew_cdcost_to').val() && $('#af_ew_cdcost_currency').val()) {
        $('#af_ew_cdcost_from').css('border-color', warningColor);
        $('#af_ew_cdcost_to').css('border-color', warningColor);
        $('.afilter_submit_btn').hide();
    } else {
        $('#af_ew_cdcost_from').css('border-color', 'gray');
        $('#af_ew_cdcost_to').css('border-color', 'gray');
        $('.afilter_submit_btn').show();
    }
});
//-----------------------------------------------------------
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
//-----------------------------------------------------------
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
    var from_field_v1 = from_field_val;
    from_field_val = parseInt(from_field_val);
    to_field_val = parseInt(to_field_val);


    if (!from_field_val && !to_field_val) {
        to_field.css('border-color', 'gray');
        from_field.css('border-color', 'gray');
        $('.afilter_submit_btn').show();
        return;
    }
    if (!from_field_val&&from_field_v1!=='0') {
        console.log(from_field_v1);
        from_field.css('border-color', warningColor);
        $('.afilter_submit_btn').hide();
        return;
    } else {
        from_field.css('border-color', 'gray');
    }
    if (!to_field_val) {
        to_field.css('border-color', warningColor);
        $('.afilter_submit_btn').hide();
        return;
    } else {
        from_field.css('border-color', 'gray');
    }
    if (from_field_val > to_field_val) {
        to_field.css('border-color', warningColor);
        from_field.css('border-color', warningColor);
        $('.afilter_submit_btn').hide();
        return;
    } else {
        to_field.css('border-color', 'gray');
        from_field.css('border-color', 'gray');
        $('.afilter_submit_btn').show();
    }
    return;
}

function loadSelect(select, url) {
    jQuery.getJSON(url, {lang: lang.val()}, (function (this_item) {
        return function (data) {
            this_item.empty();
            for (var i = 0; i < data.length; i++)
                this_item.append($('<option></option>').val(data[i]['id']).html(data[i]['txt']));
        };
    }(select)));
}

function loadCheckboxes(select, url) {
    jQuery.getJSON(url, {lang: lang.val()}, (function (this_item_super) {
        return function (data) {
            var this_item = this_item_super.find('ul');
            this_item.empty();
            for (var i = 0; i < data.length; i++)
                if(data[i]['id']!=='') // without empty
                    this_item.append($('<li class="dropdown_label"></li>')
                    .html(''
                        +'<label class="dropdown_label">'
                        +'<input onclick="clickMultiSelect(this)" type="checkbox" value="'
                        +data[i]['id']+'" id="check_'
                        +this_item.attr("class")+'" class="check_'
                        +this_item.attr("class")+'" />'
                        +data[i]['txt']+'</label>'));

            };
    }(select)));
}

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

function loadCpSelect2(select, url) {
    jQuery.getJSON(url, {
        country: select_cp_country.val(),
        city: select_cp_city.val(),
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


function loadCpCities() {
    loadCpSelect2(select_cp_city, url_get_cities);
}

function loadCps() {
    loadCpSelect2(select_cp, url_get_counterparties);
}


function loadCities() {
    loadSelect2(select_city, url_get_cities);
}

function loadWarehouses() {
    loadSelect2(select_warehouse, url_get_warehouses);
}

function loadEmployees() {
    loadSelect2(select_employee, url_get_employees);
}

// @todo @refactor to reLoadSelect2() {
function reLoadCpCountries(city_ref) {
    if (!countries_by_cities) {
        jQuery.getJSON(url_get_country_by_city, {}, function (data) {
            countries_by_cities = data;
        });
    } else {
        country_city_values = countries_by_cities[city_ref];
        if (typeof country_city_values !== 'undefined') {
            select_cp_country.val(country_city_values.country_id);
            if (select_cp_country.val())
                select_cp_country.change();
        }
    }
}

function reLoadCpCities(cp_ref) {
    if (!cities_by_cps) {
        jQuery.getJSON(url_get_city_by_cp, {}, function (data) {
            cities_by_cps = data;
        });
    } else {
        city_cp_values = cities_by_cps[cp_ref];
        if (typeof city_cp_values !== 'undefined') {
            // Код и тип контрагента
            $('#af_ew_counterparty_code').val(city_cp_values.code);
            $('#af_ew_person_type').val(city_cp_values.person_type_id);
            $('#af_ew_counterparty_code').addClass('x');
            $('#af_ew_person_type').change();
            // не подставлять город и страну (?)
//            select_cp_city.val(city_cp_values.city_id);
//            change_cp_city = false;
//            change_cp = false;
//            if (select_cp_city.val())
//                select_cp_city.change();
//            select_cp_country.val(city_cp_values.country_id);
//            if (select_cp_country.val())
//                select_cp_country.change();
//            change_cp_city = true;
//            change_cp = true;
            // не подставлять телефон и индекс (?)
//            $('#af_ew_counterparty_phone').val(city_cp_values.phone);
//            $('#af_ew_counterparty_index').val(city_cp_values.index);
            
        } else {
            $('#af_ew_counterparty_code').val('');
            $('#af_ew_person_type').val('');
            $('#af_ew_counterparty_code').removeClass('x');
            $('#af_ew_person_type').removeClass('x');
        }
        
    }
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
// } 

// --------------------------------
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
// --------------------------------
select_cp_country.change(function () {
    if (change_cp_city) {
        loadCpCities();
    } else {
        change_cp_city = true;
    }
});

select_cp_city.change(function () {
    if (change_cp) {
        loadCps();
    } else {
        change_cp = true;
    }
    if (!select_cp_country.value) {
        change_cp_city = false;
        reLoadCpCountries(this.value);
        change_cp_city = true;
    }
});

select_cp.change(function () {
    if (!select_cp_city.value) {
        change_cp = false;
        reLoadCpCities(this.value);
        change_cp = true;
    }
});

// инициализация формы
function init_form() {
    reLoadCountries('');
    reLoadCities('');
    reLoadWarehouses('');
    reLoadCpCities('');
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

    select_cp_country.val('');
    select_cp_city.val('');
    select_cp.val('');
    change_cp_city = true;
    change_cp = true;
}

init_form();