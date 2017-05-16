var InquiryVehicleChooser = Class.create(ChooserAbstract, {

    initParams: function()
    {
        this.typeId = null;
    },

    getComboContainer:function(comboContainerId)
    {
        return this.container.select('#'+comboContainerId).first();
    },

    getTypeId: function()
    {
        return this.typeId;
    },

    initComboboxes: function()
    {
        var parentId;
        var children = this.getAllComboboxes();
        for (var childItem in children) {
            var comboContainerId = children[childItem].id;
            if(typeof comboContainerId != 'undefined'){
                var comboboxName = comboContainerId;
                this.comboboxes[comboContainerId] =
                new Combobox(this.getComboContainer(comboContainerId), this.url, this.onChangeCallback.bind(this), {});
                if(typeof parentId != 'undefined'){
                    this.requestChildren[parentId] = comboContainerId;
                }
                parentId = comboContainerId;
            }
        }
        this.typeId = this.comboboxes['type'].getValue();
        this.requestChildren[parentId] = false;
        this.container.select('input,select').each(function(element){
            element.removeAttribute('disabled');
        });
    },

    onChangeCallback: function(containerId)
    {
        var value = this.comboboxes[containerId].getValue();
        var childId;
        if(childId = this.requestChildren[containerId]){
            this.clearChildren(childId);
            this.comboboxes[childId].getRequestData(value, containerId);
        }else{
            if(typeof this.changeCallback == 'function'){
                this.typeId = value;
                this.changeCallback(value);
            }
        }
    },

    clearChildren : function(childId)
    {
        this.comboboxes[childId].clearCombobox();

        if(childId = this.requestChildren[childId]){
            this.clearChildren(childId);
        }
    }
        

});
    