var ChooserAbstract = Class.create({

    initialize: function(containerId, requesUrl, currentData, currentValues, changeCallback, ignoredElements)
    {
        this.container = $(containerId);
        this.comboboxes = {};
        this.requestChildren = {};
        this.url = requesUrl;
        this.data = currentData || {};
        this.values = currentValues || {};
        this.changeCallback = changeCallback || null;
        this.ignored = ignoredElements || {};
        this.initParams();
        this.initComboboxes();
    },

    initParams: function() {},

    getComboContainer:function(comboContainerId)
    {
        return this.container.select('#'+comboContainerId).first();
    },

    initComboboxes: function(containerHandle)
    {
        var parentId;
        var children = this.getAllComboboxes();
        for (var childItem in children) {
            var comboContainerId = children[childItem].id;
            if(typeof comboContainerId != 'undefined'){
                var comboboxName = this.getComboContainer(comboContainerId).select('select.[class~=combo-select]').first().name;
                comboboxName = comboboxName.substr(0, comboboxName.length - 2);
                var currentData = (typeof this.data[comboboxName] == 'undefined')
                ? {} : this.data[comboboxName];
                var currentValue = (typeof this.values[comboboxName] == 'undefined')
                ? '' : this.values[comboboxName];
                this.comboboxes[comboContainerId] =
                new Combobox(this.getComboContainer(comboContainerId), this.url, this.onChangeCallback.bind(this), currentData, currentValue );
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
            var itemName = this.comboboxes[containerId].getName();
            itemName = itemName.substr(0, itemName.length - 2);
            this.comboboxes[childId].getRequestData(value, itemName);
        }else{
            if(typeof this.changeCallback == 'function'){
                this.changeCallback(value);
            }
        }
    },

    getAllComboboxes: function()
    {
        return this.container.select('div.combo-container');
    },

    clearChildren : function(childId)
    {
        this.comboboxes[childId].clearCombobox();

        if(childId = this.requestChildren[childId]){
            this.clearChildren(childId);
        }
    }

});
