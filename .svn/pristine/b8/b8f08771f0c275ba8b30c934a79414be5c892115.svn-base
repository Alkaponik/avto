varienGrid.prototype.resetFilter =  varienGrid.prototype.resetFilter.wrap(
    function(origMethod)
    {
        Manager.resetAllFilters();
        origMethod();
    }
);

varienGrid.prototype.doFilter =  varienGrid.prototype.doFilter.wrap(
    function(origMethod)
    {
        
        var typeField = new Element('input', {'id': 'gridTypes', 'style' : 'display:none;', 'name' : 'types'});
        $(this.containerId).select('.filter').first().insert(typeField);
        var types = '';
        var fieldsets = $$('.inquiry-container');
        for(var i in fieldsets){
            if(typeof fieldsets[i] == 'object'){
                var container = fieldsets[i];
                var checkbox = $(container).select('input[type=checkbox]').first();
                var typeId = $(container).select('#type').first().select('select').first().value;
                
                if(checkbox.checked 
                    && typeof typeId != 'undefined'
                    && typeId !== ''){
                        types += (types.length == 0) 
                                ? typeId : ',' + typeId;
                    }
            }
        }
        $('gridTypes').value = types;
        origMethod();
    }
);


AdminOrder.prototype.productConfigureSubmit = AdminOrder.prototype.productConfigureSubmit.wrap(
        function(origMethod, listType, area, fieldsPrepare, itemsFilter)
        {
            var blocks = [];
            for(var i in area){
                if(area[i] != 'search'){
                    blocks[i] = area[i];
                }
            }
            origMethod(listType, blocks, fieldsPrepare, itemsFilter);
        }
);

AdminOrder.prototype.productGridRowClick = AdminOrder.prototype.productGridRowClick.wrap(
        function(origMethod, grid, event)
        {
            var eventElement = Event.element(event);
            var isInputRetailer = eventElement.tagName == 'SELECT' && eventElement.name == 'retailer_id';
            if (!isInputRetailer)
            {
                origMethod(grid, event);
            }
        }
);

    /**
     * Submit configured products to quote
     */
    AdminOrder.prototype.productGridAddSelected = function(){
        if(this.productGridShowButton) Element.show(this.productGridShowButton);
        var area = ['search', 'items', 'shipping_method', 'totals', 'giftmessage','billing_method'];
        // prepare additional fields and filtered items of products
        var fieldsPrepare = {};
        var itemsFilter = [];
        var products = this.gridProducts.toObject();
        for (var productId in products) {
            itemsFilter.push(productId);
            var paramKey = 'item['+productId+']';
            for (var productParamKey in products[productId]) {
                //paramKey += '['+productParamKey+']';
                fieldsPrepare[paramKey + '['+productParamKey+']'] = products[productId][productParamKey];
            }
        }
        this.productConfigureSubmit('product_to_add', area, fieldsPrepare, itemsFilter);
        productConfigure.clean('quote_items');
        this.hideArea('search');
        this.gridProducts = $H({});
    };
