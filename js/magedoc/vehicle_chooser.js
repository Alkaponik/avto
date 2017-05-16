var VehicleChooser = Class.create(ChooserAbstract, {

    initParams: function()
    {
        this.typeId = null;
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
                var comboboxName = this.getComboContainer(comboContainerId).readAttribute('data-name');
                var currentData = (typeof this.data[comboboxName] == 'undefined')
                ? {} : this.data[comboboxName];
                var currentValue = (typeof this.values[comboboxName] == 'undefined')
                ? '' : this.values[comboboxName];
                this.comboboxes[comboContainerId] =
                new Combobox(this.getComboContainer(comboContainerId), this.url, this.onChangeCallback.bind(this), currentData, currentValue);
                if(typeof parentId != 'undefined'){
                    this.requestChildren[parentId] = comboContainerId;
                }
                parentId = comboContainerId;
            }
        }
        this.requestChildren[parentId] = false;

    },

    onChangeCallback: function(containerId)
    {
        var value = this.comboboxes[containerId].getValue();
        var childId;
        if(childId = this.requestChildren[containerId]){
            this.clearChildren(childId);
            var itemName = this.getComboContainer(containerId).readAttribute('data-param')
                ? this.getComboContainer(containerId).readAttribute('data-param')
                : this.getComboContainer(containerId).readAttribute('data-name');
            this.comboboxes[childId].getRequestData(value, itemName);
            this.comboboxes[childId].input.removeAttribute('disabled');
            this.comboboxes[childId].select.removeAttribute('disabled');
            if(typeof this.changeCallback == 'function'){
                this.changeCallback(false);
            }
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
        this.comboboxes[childId].input.disabled = 'disabled';
        if(childId = this.requestChildren[childId]){
            this.clearChildren(childId);
        }
    }

        

});
    