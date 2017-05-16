var InquiryChooser = Class.create(ChooserAbstract, {

    initParams: function()
    {
        this.typeId = null;
        this.strId = null;
        this.supplierId = null;
    },

    setTypeId: function(typeId)
    {
        this.typeId = typeId;
        this.strId = false;
        this.supplierId = false;
    },
        
    initComboboxes: function()
    {
        var parentId;
        var children = this.getAllComboboxes();
        for (var childItem in children) {
            var comboContainerId = children[childItem].id;
            if(typeof comboContainerId != 'undefined'
                && typeof this.ignored[comboContainerId] == 'undefined'
                ){
                this.comboboxes[comboContainerId] =
                new Combobox(this.getComboContainer(comboContainerId), this.url, this.onChangeCallback.bind(this), {}, {});
                    
                if(typeof parentId != 'undefined'){
                    this.requestChildren[parentId] = comboContainerId;
                }
                parentId = comboContainerId;
            }
        }
        if (typeof this.comboboxes['article'] != 'undeifned'){
            this.comboboxes['article'].settings.callbackOnEveryChange = true;
        }
        this.requestChildren[parentId] = false;
        this.container.select('input,select').each(function(element){
            element.removeAttribute('disabled');
        });
    },

    getFirstCombobox:function()
    {
        return this.comboboxes[this.getAllComboboxes().first().id];
    },


    onChangeCallback: function(containerId)
    {
        var value = this.comboboxes[containerId].getValue();
            
        if(containerId == 'category'){
            this.strId = value;
        }else if(containerId == 'supplier'){
            this.supplierId = value;
        }

        var value = this.comboboxes[containerId].getValue();
        var childId;
        if(childId = this.requestChildren[containerId]){
            this.clearChildren(childId);
            if (value !== null){
                this.comboboxes[childId].getRequestData(value, containerId, this.typeId, this.strId, this.supplierId);
            }
        }else{
            if(typeof this.changeCallback == 'function'){
                var data = {};
                for (var key in this.comboboxes){
                    data[key] = this.comboboxes[key].getData();
                    data[key+'_value'] = this.comboboxes[key].getValue();
                    data[key+'_text'] = this.comboboxes[key].getText();
                }
                var codeElement = this.container.down('input[name=\''+this.comboboxes['supplier'].input.name.replace('supplier', 'code')+'\']');
                var code = value !== null 
                    ? this.comboboxes[containerId].getData('code')
                    : codeElement.value;
                var supplierName = this.comboboxes['supplier'].getText()
                this.changeCallback(code, value, data);
            }
        }
    }

});
    