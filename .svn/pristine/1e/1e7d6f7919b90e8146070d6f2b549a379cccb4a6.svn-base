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
    this.artId;
    this.minCostRetailerRow = null;
    this.isRequest = [];

}

RetailerInfo.prototype = {
    showPopup: function()
    {
        this.minCostRetailerRow = null;
        this.container.select('tbody').first().remove();
        var body = new Element('tbody');
        this.container.select('table').first().insert(body);
        
        this.updateProductData(this.artId);
        var retailerIds = this.getRetailerIdsSortedByWeight(this.data);
        
        for (var i = 0; i < retailerIds.length; i++) {
            var retailer = this.data[retailerIds[i]];
            if (retailer['cost'] > 0){
               var row = this.addContainerRow(retailer);
            }
        }
        this.highlightMinCostRetailer();
        this.container.show();
    },

    setArtId: function(artId)
    {
        this.artId = artId;
        return this;
    },

    setUrl: function(url)
    {
        this.url = url;
        return this;
    },

    setData: function(data)
    {
        this.data = data;
        return this;
    },

    setContainer: function(container)
    {
        this.container = $(container);
        return this;
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
        var retailers = this.retailers;
        keys.sort(function(prev,next)
        {             
            var nextWeight = (data[next]['qty'] > 0 ? 1 : 0)
                * data[next]['cost'] * (100-(retailers[next]['sort_order'] * 10)) / 100;
            var prevWeight = (data[prev]['qty'] > 0 ? 1 : 0)
                * data[prev]['cost'] * (100-(retailers[prev]['sort_order'] * 10)) / 100;
            return nextWeight - prevWeight;

        });        
        return keys;
    },

    addContainerRow:function(retailer)
    {
        var retailerId = retailer['retailer_id'];
        var name = this.retailers[retailerId]['name'];
        
        var body = this.container.select('tbody').first();
         
        var tr = new Element('tr', {'id': 'retailerData_'+retailerId});
        tr.insert(new Element('td').update(this.retailers[retailerId]['name']));
        tr.insert(new Element('td').update(this.number_format(retailer['cost'], 2)));
        tr.insert(new Element('td').update(this.number_format(retailer['price'], 2)));
        tr.insert(new Element('td').update(retailer['domestic_stock_qty']));
        tr.insert(new Element('td').update(retailer['general_stock_qty']));
        tr.insert(new Element('td').update(retailer['qty']));
        tr.insert(new Element('td').update(retailer['updated_at']));
        body.insert(tr);
        if (this.retailers[retailerId]['is_update_enabled'] == 1 
            && (typeof this.requestProductIds[this.artId] == 'undefined'
                    || typeof this.requestProductIds[this.artId][retailerId] == 'undefined'
                    || this.requestProductIds[this.artId][retailerId] == false))
        {
            tr.addClassName('loading');
        }
        return tr;
    },
    
    highlightMinCostRetailer: function()
    {
        delete this.minCost;
        if (this.minCostRetailerRow !== null)
        {
            this.minCostRetailerRow.removeClassName('retailerMinCost');
        }
        for (var retailerId in this.data){
            var retailer = this.data[retailerId];
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

    updateProductData: function(artId)
    {
        
        if( typeof this.requestProductIds[artId] == 'undefined'){
            this.requestProductIds[artId] = [];
        }
        for(var retailerId in this.retailers){
            
            if(this.retailers[retailerId]['is_update_enabled'] == 1){
                if(typeof this.requestProductIds[artId][retailerId] == 'undefined'){
                    this.requestProductIds[artId][retailerId] = false;
                    if(typeof this.data[retailerId] != 'undefined'){
                            if(this.data[retailerId]['updated_at'] <= this.updateFromDate 
                                || this.data[retailerId]['qty'] == 0){
                                this.updateProductRetailerData(artId, retailerId);
                            }else{
                                this.requestProductIds[artId][retailerId] = true;
                            }
                    
                    }else{
                        this.updateProductRetailerData(artId, retailerId);
                    }
                 }
            }
        }
    },

    updateProductRetailerData:function(artId, retailerId)
    {

        new Ajax.Request(this.url, {
            method: 'post',
            parameters:{
                'art_id'  : artId,
                'retailer_id' : retailerId
            },
            onSuccess: function(response){
                
                var result = response.responseText;
                result = result.evalJSON();               
                
                var retailer = result[retailerId];
                if( retailer != false 
                    && typeof retailer == 'object' 
                    && parseFloat(retailer['cost']) > 0 ){
                    
                    this.data[retailerId] = retailer;
                    this.isRequest[retailerId] = true;                    
                    this.showPopup();
                    this.hideLoading(retailerId);
                }
            }.bind(this),
            onComplete: function(response){
                this.requestProductIds[artId][retailerId] = true;
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
    

    hideLoading: function(retailerId)
    {
        if ($('retailerData_'+retailerId))
        {
        $('retailerData_'+retailerId).removeClassName('loading');
        }
    }
}