var scheduleType = $('#af_schedule_type');
var timeBegin = $('#af_time_begin');
var timeEnd = $('#af_time_end');

scheduleType.change(function () {
    if (scheduleType.val()) {
        timeBegin.prop('disabled', false);
        timeEnd.prop('disabled', false);
    }
    else {
        timeBegin.val('');
        timeBegin.prop('disabled', true);
        timeEnd.val('');
        timeEnd.prop('disabled', true);
    }
}).change();

var dimensionType = $('#af_dimension_type');
var dimensionValue = $('#af_dimension_value');

dimensionType.change(function() {
    if (dimensionType.val()) {
        dimensionValue.prop('disabled', false);
    }
    else {
        dimensionValue.prop('disabled', true);
        dimensionValue.val('');
    }
}).change();