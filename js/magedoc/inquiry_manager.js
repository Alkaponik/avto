    
var InquiryManager = Class.create({
    initialize:function(container, editUrl, changeCallback, rowTempalte)
    {
        this.editUrl = editUrl;
        this.container = $(container);
        this.changeCallback = changeCallback;
        this.checkbox = this.container.select('input[type=checkbox]').first();
        this.vin = this.container.select('input[id^="vin"]').first();
        this.mileage = this.container.select('input[id^="mileage"]').first();
        this.customerVehicle = this.container.select('select[id^="customer_vehicle"]').first();
        this.checkbox.removeAttribute('disabled');
        this.vin.removeAttribute('disabled');
        this.mileage.removeAttribute('disabled');
        this.customerVehicle.removeAttribute('disabled');
        this.addRowButton = $(this.container).select('#add-product').first();
        this.deleteVehicleButton = $(this.container).select('#delete-vehicle').first();
        $(this.checkbox).observe('click', this.doFilterGrid.bind(this));
        $(this.addRowButton).observe('click', this.addRow.bind(this));
        $(this.deleteVehicleButton).observe('click', this.deleteVehicle.bind(this));
        $(this.customerVehicle).observe('change', this.changeVehicle.bind(this));
        this.rowTemplate = new Template('<tr id="#{_row_id}">' + $(rowTempalte).innerHTML + '</tr>');
        this.inquiryRows = {};
        this.typeId;
        this.chooser;
        this.initInquiry();
    },
    
    resetFilter : function()
    {
        this.checkbox.checked = false;
    },
            
    doFilterGrid : function()
    {
        if(typeof this.changeCallback == 'function'){
            this.changeCallback();
        }
        return this;
    },
    
    chooserCallback: function(typeId)
    { 
        if(typeof this.typeId !== 'undefined'){
            if(this.typeId !== typeId){
                if(this.checkbox.checked){
                    this.checkbox.checked = false;
                }

            }
        }
        this.typeId = typeId;
        this.doFilterGrid();
        for(var i in this.inquiryRows){
            this.inquiryRows[i].setFilter(typeId);
        }

    },
        
    getVehicleTypeId: function()
    {
        return $(this.container).select('#type').first().select('select').first().value;
    },

        
    initInquiry : function()
    {
        var chooserContainer = this.container.select('ul.[class~=vehicle-changer]').first();
        this.chooser = new InquiryVehicleChooser($(chooserContainer),
                    this.editUrl,
                    this.chooserData,
                        this.chooserValues, this.chooserCallback.bind(this));       
        if(this.getVehicleTypeId().length > 1){
            this.typeId = this.getVehicleTypeId();            
        }else{
            //Change vehicle only after page inc. products grid is initialized
            Event.observe(window, 'load', this.changeVehicle.bind(this));
        }
        var inquiries = this.container.select('div#[id~=inquiry-grid] tbody tr');
        for(var i = 0; i < inquiries.length; i++){
            if(inquiries[i].id !== 'row-template'){
                this.inquiryRows[inquiries[i].id] = new RowManager($(inquiries[i]),
                                                    this.editUrl,
                                                    this.typeId);
            }
        }
    },
    
    
    addRow: function()
    {
        var d = new Date();
        var containerId = 'id_row_container_' + d.getMilliseconds();       
        var templateData = {_row_id     : containerId,
                            _inquiry_id : 'added_inquiry_' + d.getMilliseconds()};
        var gridContainer = this.container.select('div#[id~=inquiry-grid] tbody').first();
        gridContainer.insert(this.rowTemplate.evaluate(templateData));
        this.inquiryRows[containerId] = new RowManager($(containerId), this.editUrl, this.typeId);
        this.inquiryRows[containerId].setFilter(this.typeId);
    },
    
    deleteVehicle: function()
    {
        if(confirm("Удалить авто?")){
            this.checkbox.checked = false;
            this.vin.disable();
            this.mileage.disable();
            this.doFilterGrid();
            var chooserContainer = this.container.
                                select('ul.[class~=vehicle-changer]').first().
                                select('.fields').first();
            var name = $(chooserContainer).select('#manufacturer').first().down('input').name;
            var isDeleted = new Element('input', {'name': name.replace('[vehicle][manufacturer]', '[is_deleted]'), 'value': 1, 'type': 'hidden'});
            this.container.insert(isDeleted);
            this.container.hide();
            for (var inquiryId in this.inquiryRows){
                this.inquiryRows[inquiryId].deleteRow();
            }
        }
    },

    changeVehicle: function()
    {
        var customerVehicle = this.container.select('[name*="customer_vehicle"]')[0].value;
        if(customerVehicles[customerVehicle] !== undefined){
            for(combobox in this.chooser.comboboxes){
                var id = combobox == 'date'? 'production_start_year' : combobox + '_id';
                if(customerVehicles[customerVehicle][id]){
                    this.chooser.comboboxes[combobox].setValue(customerVehicles[customerVehicle][id]);
                    this.chooser.onChangeCallback(combobox);
                }
            }

            if(this.vin && customerVehicles[customerVehicle]['vin']){
                this.vin.value = customerVehicles[customerVehicle]['vin'];
            }

            if(this.mileage && customerVehicles[customerVehicle]['mileage']){
                this.mileage.value = customerVehicles[customerVehicle]['mileage'];
            }

            //Don't set "Filter" by default
            //this.checkbox.checked = true;
        }else{
            for(combobox in this.chooser.comboboxes){
                //this.chooser.comboboxes[combobox].clearCombobox();
                this.chooser.comboboxes[combobox].setValue(null);
                this.chooser.onChangeCallback(combobox);
            }
            this.vin.value = '';
            this.mileage.value = '';

        }
    }
});
    