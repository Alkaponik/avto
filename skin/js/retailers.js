function RetailerInfo(retailers)
{
    //Cache lifetime in seconds
    this.CACHE_LIFETIME = 3600 * 24 * 1;
    this.updateFromDate = this.getUpdateFromDate();
    this.retailers = retailers;
    this.container = $('retailers-container');
    this.requestProductIds = [];
    this.currentProductId = null;
    this.minCost;
    this.minCostRetailerRow = null;
    this.isRequest = [];
}

RetailerInfo.prototype = {
    showPopup: function(productId)
    {
        this.currentProductId = productId;
        this.minCostRetailerRow = null;
        
        this.container.select('tbody').first().remove();
        var body = new Element('tbody');
        
        this.container.select('table').first().insert(body);
        
        this.updateProductData(productId);
        
        var retailerIds = this.getRetailerIdsSortedByWeight(this.data[productId]);
        
        for (var i = 0; i < retailerIds.length; i++) {
            var retailer = this.data[productId][retailerIds[i]];
            if (retailer['cost'] > 0){
               var row = this.addContainerRow(retailer, productId);
            }
        }
        this.highlightMinCostRetailer();
        this.container.show();
    },

    getNumDaysInMonth : function(month, year) 
    {
        return new Date(year, month, 0).getDate();
    },

    getUpdateFromDate: function()
    {
        return  new Date(Date.now() - this.CACHE_LIFETIME*1000).toISOString().replace('T', ' ').substr(0, 19);
    },

    getRetailerIdsSortedByWeight:function(data)
    {
        var keys = [];
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                keys.push(key);
            }
        }

        keys.sort(function(prev,next){return data[next]['weight'] - data[prev]['weight']});
        
        return keys;
    },

    addContainerRow:function(retailer, productId)
    {
        var retailerId = retailer['retailer_id'];
        var body = this.container.select('tbody').first();
         
        var tr = new Element('tr', {'id': 'retailerData_'+retailerId});
        tr.insert(new Element('td').update(retailer['name']));
        tr.insert(new Element('td').update(this.number_format(retailer['cost'], 2)));
        tr.insert(new Element('td').update(this.number_format(retailer['price'], 2)));
        tr.insert(new Element('td').update(retailer['domestic_stock_qty']));
        tr.insert(new Element('td').update(retailer['general_stock_qty']));
        tr.insert(new Element('td').update(retailer['qty']));
        tr.insert(new Element('td').update(retailer['updated_at'].substring(0,10)));
        body.insert(tr);
        if (this.retailers[retailerId]['is_update_enabled'] == 1 
            && (typeof this.requestProductIds[productId] == 'undefined'
                    || typeof this.requestProductIds[productId][retailerId] == 'undefined'
                    || this.requestProductIds[productId][retailerId] == false))
        {
            tr.addClassName('loading');
        }
        return tr;
    },
    
    highlightMinCostRetailer: function()
    {
        delete this.minCost;
        if (typeof this.data[this.currentProductId] == 'undefined')
        {
            return;
        }
        if (this.minCostRetailerRow !== null)
        {
            this.minCostRetailerRow.removeClassName('retailerMinCost');
        }
        for (var retailerId in this.data[this.currentProductId]){
            var retailer = this.data[this.currentProductId][retailerId]
            if((typeof this.minCost == 'undefined' || retailer['cost'] < this.minCost) && retailer['qty'] > 0){
                this.minCost = retailer['cost'];
                this.minCostRetailerRow = $('retailerData_'+retailerId);
            }
        }
        if (this.minCostRetailerRow !== null)
        {
            this.minCostRetailerRow.addClassName('retailerMinCost');
        }
    },

    updateProductData: function(productId)
    {
        if( typeof this.requestProductIds[productId] == 'undefined'){
            this.requestProductIds[productId] = [];
        }
        for(var retailerId in this.retailers){
            if(this.retailers[retailerId]['is_update_enabled'] == 1){
                if(typeof this.requestProductIds[productId][retailerId] == 'undefined'){
                    this.requestProductIds[productId][retailerId] = false;
                    if(typeof this.data[productId][retailerId] != 'undefined'){
                        if(this.data[productId][retailerId]['updated_at'] <= this.updateFromDate 
                            || this.data[productId][retailerId]['qty'] == 0){
                            this.updateProductRetailerData(productId, retailerId);
                        }else{
                            this.requestProductIds[productId][retailerId] = true;
                        }
                    }else{
                        this.updateProductRetailerData(productId, retailerId);
                    }
                 }
            }
        }
        
    },

    updateProductRetailerData:function(productId, retailerId)
    {

        new Ajax.Request('/system/requestController.php', {
            method: 'post',
            parameters:{
                'productId'  : productId,
                'retailerId' : retailerId
            },
            onSuccess: function(response){
                var result = response.responseJSON;
                var retailer = result[productId][retailerId];
                if( retailer != false 
                    && typeof retailer == 'object' 
                    && parseFloat(retailer['cost']) > 0 ){
                    this.data[productId][retailerId] = retailer;
                    this.isRequest[retailerId] = true;
                    this.showPopup(productId);
                    this.hideLoading(retailerId);
                }
            }.bind(this),
            onComplete: function(response){
                this.requestProductIds[productId][retailerId] = true;
            }.bind(this)
        });
    },
    
    number_format:function(number, decimals, point, separator)
		{
			if(!isNaN(number))
			{
				point = point ? point : '.';
				number = number.toString().split('.');
				if(separator)
				{
					var tmp_number = new Array();
					for(var i = number[0].length, j = 0; i > 0; i -= 3)
					{
						var pos = i > 0 ? i - 3 : i;
						tmp_number[j++] = number[0].substring(i, pos);
					}
					number[0] = tmp_number.reverse().join(separator);
				}
				if(decimals && number[1]) 
					number[1] = Math.round(parseFloat(number[1].substr(0, decimals) + '.' + number[1].substr(decimals, number[1].length), 10));
				return(number.join(point));
			}
			else return(null);
	},
    
    mouseOverHandler: function (productId, event) 
    {
        if(typeof this.data[productId] == 'undefined'){
            this.data[productId] = [];
        }
        if (this.container.style.display != 'none'){
            return;
        }
        
        this.container.setStyle({
            left: event.clientX-160,
            top: event.clientY+20
        });
        this.showPopup(productId);
    },
                
    mouseOutHandler: function () {
        this.container.hide();
    },

    addHandler: function (handles, reatilersData)
    {   
        this.data = reatilersData;
        for (var i=0; i<handles.length; i++){
        
            var handle = handles[i];
            var productId = handle.id.split('-')[1];
            $(handle).observe('mouseover', this.mouseOverHandler.bind(this, productId));
            $(handle).observe('mouseout', this.mouseOutHandler.bind(this, productId));
        }
    },

    hideLoading: function(retailerId)
    {
        if ($('retailerData_'+retailerId))
        {
        $('retailerData_'+retailerId).removeClassName('loading');
        }
    }
}