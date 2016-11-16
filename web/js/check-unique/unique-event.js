$(document).ready(function() {
    var data = '';
    $('#listevents-code').bind('change', function() {
        data = {'eventNumber': $(this).val()};
        if($(this).val() != oldEventNumber) {
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
