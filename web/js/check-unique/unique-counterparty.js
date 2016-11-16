$(document).ready(function() {
    var data = '';
    $('#counterparty-counterparty_id').bind('change', function() {
        data = {'counterpartyNumber': $(this).val()};
        if($(this).val() != oldCounterpartyNumber) {
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
