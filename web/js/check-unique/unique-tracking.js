$(document).ready(function() {
    var data = '';
    $('#liststatusesew-code').bind('change', function() {
        data = {'trackingNumber': $(this).val()};
        if($(this).val() != oldTrackingNumber) {
            checkUnique(data);
        }
    });
});

function checkUnique(data) {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: urlCheckUnique,
        data: data,
        success: function(responce) {
            if(responce.message) {
                parent.show_app_alert('Error', responce.message, 'Ok');
            }
        }
    });
}
