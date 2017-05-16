    
var RowManager = Class.create({
    initialize:function(container, editUrl, typeId)
    {
        this.editUrl = editUrl;
        this.container = $(container);
        this.inquiryChooser;
        this.floatKeyCodes = [8, 9, 13, 16, 17, 188, 190, 191, 48, 57];
        this.initRowElements(); 
        this.prepareRowElementsName();
        this.addHandlers();
        this.deleteButton = this.container.select('#delete').first();
        $(this.deleteButton).observe('click', this.deleteRow.bind(this));
        if(typeof typeId !== 'undefined'){
            this.inquiryChooser.setTypeId(typeId);
        }
    },
    
             
    initRowElements : function()
    {      
        this.inquiryChooser = new InquiryChooser($(this.container), this.editUrl,
                   '',  '', this.changeCallback.bind(this));
                   
        this.sku = $(this.container).select('input[name*=sku]').first();
        this.sku.removeAttribute('disabled');
        this.articleId = $(this.container).select('input[name*=article_id]').first();
        this.articleId.removeAttribute('disabled');
        this.productId = $(this.container).select('input[name*=product_id]').first();
        this.productId.removeAttribute('disabled');
        this.code = $(this.container).select('input[name*=code]').first();
        this.code.removeAttribute('disabled');
        this.cost = $(this.container).select('input[name*=cost]').first();
        this.cost.removeAttribute('disabled');
        this.price = $(this.container).select('input[name*=price]').first();
        this.price.removeAttribute('disabled');
        this.qty = $(this.container).select('input[name*=qty]').first();
        this.qty.removeAttribute('disabled');
        this.retailer = $(this.container).select('select[name*=retailer_id]').first();
        this.retailer.removeAttribute('disabled');
        this.rowTotal = $(this.container).select('input[name*="[row_total]"]').first();
        this.rowTotal.removeAttribute('disabled');
        this.discount = $(this.container).select('input[name*=discount_percent]').first();
        this.discount.removeAttribute('disabled');
        this.rowTotalWithDiscount = $(this.container).select('input[name*=row_total_with_discount]').first();
        this.rowTotalWithDiscount.removeAttribute('disabled');
        this.informationLink = $(this.container).select('.information').first();

        return this;           
    },
    
    prepareRowElementsName: function()
    {
        var date = new Date();
        var hash = date.getMilliseconds();
        var row_elements = this.container.select('[name]');
        for(var i = 0; i < row_elements.length; i++){
            row_elements[i].name = row_elements[i].name.split('template').join('added_inquiry_' + hash);
        }
    },
        
    changeCallback: function(number, artId, data)
    {
        this.code.value = number;

        if (typeof data.article != 'undefined'
            && data.article
            && typeof data.article.sup_id != 'undefined'){
            var supplierId = data.article.sup_id;
            var supplierName = data.article.sup_brand;
            this.inquiryChooser.comboboxes['supplier'].setValue(supplierId);
            this.inquiryChooser.comboboxes['supplier'].setText(supplierName);
        }else{
            var supplierName = data.supplier_text;
        }

        this.sku.value = supplierName.replace(/[\s\/\\-]+/g, '-') + '-' + number.replace(/[\s\/\\-]+/g, '-');
        //this.cost.value = '0.0000';
        //this.price.value = '0.0000';
        //this.qty.value = '0';
        //this.rowTotal.value = '0.0000';
        //this.discount.value = '0';
        //this.rowTotalWithDiscount.value = '0.0000';
        if (artId)
        {
            this.articleId.value = artId;
            this.informationLink.update("<a onclick=\"MageDoc_Adminhtml_Product_Information_WindowJsObject.getProductData('','"
            + artId +"', '" + this.typeId + "');return false;\" popup=\"1\">Information</a>");
            if (typeof data.article.product_id != 'undefined'){
                this.productId.value = data.article.product_id;
            }
            if (data.article.product_id){
                this.cost.value = data.article.cost;
                this.price.value = data.article.price;
                if (data.article.retailer_id){
                    this.retailer.value = data.article.retailer_id;
                }else{
                    this.retailer.value = 0;
                }
            }
        }else{
            this.articleId.value = null;
            this.productId.value = null;
            this.informationLink.update("");
        }
    },
    
    setFilter: function(typeId)
    {       
        this.typeId = typeId;
        var firstCombo = this.inquiryChooser.getFirstCombobox();
        this.inquiryChooser.getFirstCombobox().clearCombobox();
        this.inquiryChooser.getFirstCombobox().getRequestData(false, this.inquiryChooser.getFirstCombobox().getContainerId(), typeId);
        this.inquiryChooser.setTypeId(typeId);
    },
    
    
    deleteRow: function()
    {
        var isDeleted = new Element('input', {'name': this.sku.name.replace('[sku]', '[is_deleted]'), 'value': 1, 'type': 'hidden'});
        this.container.down('td').insert(isDeleted);
        var inputs = this.container.select('input');
        for (var i = 0; i < inputs.length; i++){
            if (inputs[i].name.indexOf('[is_deleted]') == -1){
                inputs[i].writeAttribute('disabled', 'disabled');
            }

        }
        var selects = this.container.select('select');
        for (var i = 0; i < selects.length; i++){
            selects[i].writeAttribute('disabled', 'disabled');
        }
        this.container.hide();
        
        return this;
    },
    
    calculateTotal:function(event)
    {
        if(this.price.value !== '' && this.qty.value !== ''){
            var result = this.price.value * this.qty.value;
            this.rowTotal.value = result.toFixed(4);
            if(this.discount.value !== ''){
            result = this.price.value * ((100 - this.discount.value)/100) * this.qty.value;
            this.rowTotalWithDiscount.value = result.toFixed(4);
            }
        }else{
            this.rowTotal.value = '0.0000'
            this.rowTotalWithDiscount.value = '0.0000';
        }        
    },
    
    addHandlers: function ()
    {   

        var numberInputs = this.container.select('.validate-number');
        for (var i = 0; i < numberInputs.length; i++){
            numberInputs[i].observe('keyup', this.filterFloatInput.bind(this));
        }
        $(this.price).observe('keyup', this.onEditPrice.bindAsEventListener(this));
        $(this.qty).observe('keyup', this.onEditQty.bindAsEventListener(this));
        $(this.discount).observe('keyup', this.onEditDiscount.bindAsEventListener(this));
        $(this.code).observe('change', this.onCodeChange.bindAsEventListener(this));
    },
     
    onEditPrice:function(event)
    {
        this.calculateTotal();
    },

    onEditQty:function(event)
    {
        this.calculateTotal();
    },

    onEditDiscount:function(event)
    {
        this.calculateTotal();
    },

    filterFloatInput: function(event)
    {
        var e = event.target;
        var value = e.value.replace(/,/g, '.').replace(/[^0-9\.]/g, '');
        if (value != e.value){
            e.value = value;
        }
    },

    onCodeChange: function(event){
        var e = event.target;
        var e = event.target;
        if (e.value){
            this.inquiryChooser.comboboxes['article'].clearSelect();
            this.inquiryChooser.comboboxes['article'].clearOptions();
            this.inquiryChooser.comboboxes['article'].getRequestData(e.value, 'code');
        }
    }
});
    