/**
 * JS функции для работы с формой мини-справочника Первозчиков
 * @author DAYankovetsky
 */

var counterpartyId = $('#counterparty');
var carrierCounterpartyName = $('#carrier_counterparty_name');
var url_get_counterparty = "index.php?r=counterparty/counterparty/get-counterparty";

counterpartyId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_counterparty, { id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data['counterparty_name']);
                };
            }(carrierCounterpartyName)
            )
        );
    }
    else {
        carrierCounterpartyName.val("").trigger('input');
    }
}).change();