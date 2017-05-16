Combobox.prototype.defaultChangeCallback = function(){
    var cityId = this.container.id;
    if(cityId.indexOf('city') >= 0){
        var warehouseId = cityId.replace('city', 'warehouse');
        var warehouseIdCombobox = window[warehouseId+'_combobox'];
        warehouseIdCombobox.url = '/admin/novaposhta_warehouses/getwarehouses';
        warehouseIdCombobox.clearCombobox();
        warehouseIdCombobox.getRequestData(this.select.value, 'city_id');
        for (var prop in warehouseIdCombobox.allOptionIds)break;
        //warehouseIdCombobox.setValue();
        console.log(prop);
        warehouseIdCombobox.renderInputText();
    }
};