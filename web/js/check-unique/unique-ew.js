$(document).ready(function() {
    var data = '';
    $('#expresswaybill-ew_num').bind('change', function() {
        data = {'ewNumber': $(this).val()};
        if($(this).val() != oldEwNumber) {
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
