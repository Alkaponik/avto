var SupplyManager = function(supplyConfig)
{
    this.supplyConfig = supplyConfig;
    this.initRetailerChangeHandlers();
};

SupplyManager.prototype = {
    reserveItem: function (itemId, itemType, qty) {
        //var qty = item.up('tr').select('td')[8];
        var supplyStatusElement = $$('select[name=\'' + itemType + '[' + itemId + '][supply_status]\']').first();
        var retailerElement = $$('select[name=\'' + itemType + '[' + itemId + '][retailer_id]\']').first();
        var supplyStatus = supplyStatusElement.value;
        var retailerSupplyStatus = this.getRetailerDefaultSupplyStatus(retailerElement.value);
        if (supplyStatus == 'unreserved') {
            $$('input[name=\'' + itemType + '[' + itemId + '][qty_reserved]\']').first().value = qty;
            supplyStatusElement.value = retailerSupplyStatus;
        }
    },

    shipItem: function (itemId, itemType, qty) {
        //var qty = item.up('tr').select('td')[8];
        var supplyStatusElement = $$('select[name=\'' + itemType + '[' + itemId + '][supply_status]\']').first();
        var supplyStatus = supplyStatusElement.value;
        if (supplyStatus != 'shipped') {
            $$('input[name=\'' + itemType + '[' + itemId + '][qty_supplied]\']').first().value = qty;
            supplyStatusElement.value = 'shipped';
            if (!$$('input[name=\'' + itemType + '[' + itemId + '][receipt_reference]\']').first().value){
                $$('input[name=\'' + itemType + '[' + itemId + '][receipt_reference]\']').first().value = $('document_reference').value;
            }

        }
    },

    getRetailerDefaultSupplyStatus: function(retailerId){
        var supplyStatus = 'reserved';
        if (typeof this.supplyConfig[retailerId] != 'undefined'){
            if (this.supplyConfig[retailerId]['delivery_type'].indexOf('delivery') == 0){
                supplyStatus = 'warehouse_delivery';
            }
        }
        return supplyStatus;
    },

    initRetailerChangeHandlers: function(){
        $$('select[name*=\'[retailer_id]\']').each(function (element){
            element.observe('change', this.retailerChangeHandler.bindAsEventListener(this));
        }.bind(this));
    },

    retailerChangeHandler: function(event){
        var e = event.target;
        var supplyStatusElement = e.up('tr').select('select[name=\''+e.name.replace('retailer_id', 'supply_status')+'\']').first();
        var supplyDateElement = e.up('tr').select('input[name=\''+e.name.replace('retailer_id', 'supply_date')+'\']').first();
        var supplyStatus = supplyStatusElement.value;
        if (supplyStatus == 'unreserved' && typeof this.supplyConfig[e.value] != 'undefined') {
            supplyDateElement.value = this.supplyConfig[e.value].supply_date;
        }else if(supplyStatus == 'reserved' || supplyStatus == 'warehouse_delivery'){
            supplyStatusElement.value = this.getRetailerDefaultSupplyStatus(e.value);
        }
    }
}