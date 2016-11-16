
var invoice_cost = $('#expresswaybill-ewinvoice-invoice_cost');
var decl_cost = $('#expresswaybill-customs_declaration_cost');

$('#expresswaybill-ewinvoice-currency').change(function(){
    $('#expresswaybill-customs_declaration_currency').val($('#expresswaybill-ewinvoice-currency').val());
})

grid_invoice_positions.attachEvent('onAfterEditStop', function(state, editor) {
    if (editor.column == 'pieces_quantity' || editor.column == 'cost_per_piece') {
        var item = this.getItem(editor.row);
        var pieces_quantity = item['pieces_quantity'];
        var cost_per_piece = item['cost_per_piece'];
        if (!isNaN(pieces_quantity) && !isNaN(cost_per_piece)) {
            item['total_cost'] = pieces_quantity * cost_per_piece;

            var sum_cost = 0;
            this.eachRow( function (row){
                var s_item = this.getItem(row);
                var total_cost = s_item['total_cost'];
                if (!isNaN(total_cost)) {
                    sum_cost = sum_cost + parseFloat(total_cost);
                }
                });

            invoice_cost.val(sum_cost);
            decl_cost.val(sum_cost);

        }
    }
});
