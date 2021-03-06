var Combobox = Class.create({
    
    MIN_REQUEST_CHARACTER_COUNT : 0,

    initialize: function(containerId, requestUrl, changeCallback, currentData, currentValue, settings)
    {   
        this.settings = {
            'isAjax':   false,
            'bothSideCompare': true,
            'callbackOnEveryChange': false
        }
        if (typeof settings != "undefined"){
            for (i in settings){
                this.settings[i] = settings[i];
            }
        }
        this.data = {};
        this.options = {};
        this.allOptions = {};
        this.allOptionIds = [];
        this.container = $(containerId);
        this.input = $(this.container.down('input'));
        this.select = $(this.container.down('select'));
        this.name = this.select.name;
        this.value = currentValue || this.select.value || '';
        this.oldValue = null;
        this.text = this.input.value;
        this.clicked = false;
        this.buttonDown = false;
        this.url = requestUrl;
        this.changeCallback = changeCallback;
        this.setDefaultData(currentData);
        this.placeholder = $(this.input).readAttribute('placeholder');
        if(!this.dataIsEmpty()){
            this.rendererSelect();
        }else{
            for(var i = 0; i < this.select.options.length; i++){
                this.data[this.select.options[i].value] = this.select.options[i].text;
                this.options[this.select.options[i].value] = this.select.options[i];
                this.allOptions[this.select.options[i].value] = this.select.options[i];
                this.allOptionIds.push(this.select.options[i].value);
            }
            if(this.value && this.value.length){
                this.input.value = this.options[this.value].text;
                this.text = this.options[this.value].text;
            }
        }

        this.addHandlers();  
        if(!this.text)
        {
            this.showPlaceholder();
        }

    },

    dataIsEmpty: function ()
    {
        
        for(var i in this.data){
            return false;
        }
        return true;
    },


    getContainerId: function()
    {
        return this.container.id;
    },

    rendererSelect : function() 
    {
        this.clearSelect(); 
        for(var key in this.data){
            if(typeof this.data[key] != 'undefined'){
                if(typeof this.data[key]['label'] != 'undefined'){
                    this.addOptionToSelect(this.data[key]['label'], key, this.value == key);
                }else{
                    this.addOptionToSelect(this.data[key], key, this.value == key);
                }
            }
        } 
             
    },

    setMinChars: function(minChars)
    {
        this.minChars = minChars;
    },
   

    getMinChars: function()
    {
        return (typeof this.minChars == 'undefined') 
            ? this.MIN_REQUEST_CHARACTER_COUNT : this.minChars;
    },

    clearSelect: function()
    {
        while (this.select.firstChild) {
            this.select.removeChild(this.select.firstChild);
        }
    },

    clearCombobox: function()
    {
        this.clearSelect();
        this.clearOptions();
        this.input.value = '';
        this.showPlaceholder();
        this.reset();
    },

    clearOptions: function(){
        this.data = {};
        this.options = {};
        this.allOptions = {};
        this.allOptionIds = [];
    },

    reset: function()
    {
        if (this.value !== null){
            this.oldValue = this.value;
        }
        this.value = null;
        this.text = null;
        this.select.value = null;
        this.select.selectedIndex = -1;
    },

    hideOption: function(key){
        this.options[key].writeAttribute('selected', null);
        if (!Prototype.Browser.Gecko){
            this.select.removeChild(this.options[key]);
        }else{
            this.options[key].hide();
        }
    },

    showAllOptions: function(){
        if (!Prototype.Browser.Gecko){
            while (this.select.firstChild) {
                 this.select.removeChild(this.select.firstChild);
            }
            for(var i = 0; i < this.allOptionIds.length; i++){
                this.select.appendChild(this.allOptions[this.allOptionIds[i]]);
            }
        }else{
            for(var key in this.options){
                this.options[key].show();
            }
        }
    },

    getRequestData:function(value, itemName, type_id, str_id, supplier_id)
    {
        new Ajax.Request(this.url, {
            method: 'post',
            parameters:{
                'item'      : itemName || this.name,
                'value'     : value || '',
                'bothSideCompare' : this.settings.bothSideCompare,
                'type_id'   : type_id || '',
                'str_id'    : str_id || '',
                'supplier_id'    : supplier_id || ''
            },
            onSuccess: function(response){
                var result = response.responseText;
                if(result.length > 2){
                    result = result.evalJSON();
                    this.data = result;
                    this.rendererSelect();
                }
            }.bind(this)
        });
                
    },

    getText: function()
    {
        return this.text;
    },

    getValue: function()
    {
        return this.value;  
    },

    setValue: function(value){
        this.value = value
        this.select.value = value;
        var text = typeof this.data[value] != 'undefined' ?
                typeof this.data[value]['label'] != 'undefined'
                    ? this.data[value]['label']
                    : this.data[value]
                : null;
        this.setText(text);
        this.input.value = this.text;
    },

    setText: function(text){
        this.text = text;
        this.input.value = this.text;
    },

    getName: function()
    {
        return this.name;  
    },

    filter : function(text) 
    {
        if (text == this.text){
            return;
        }
        this.reset();
        
        if(typeof this.data != 'undefined'){
            if(!this.dataIsEmpty()){
                if(this.settings.isAjax){
                    if(text.length >= this.getMinChars()){
                        this.getRequestData(text);  
                    }
                }else{
                    if (text.indexOf(this.text) != 0){
                        this.showAllOptions();
                    }
                    var isSelected = false;
                    for(var key in this.data){
                        if(this.data[key] !== null){
                            if(typeof this.data[key]['label'] !== 'undefined'){
                                var optionText = this.data[key]['label'].toString().toLowerCase();
                            }else{
                                var optionText = this.data[key].toString().toLowerCase();
                            }
                            if (!this.matchText(optionText, text)){
                                this.hideOption(key);
                            }else if(text == optionText && !isSelected){
                                this.options[key].selected = 'selected';
                                isSelected = true;
                            }
                        }
                    }
                }
            }
        }
        this.text = text;
    },

    matchText: function(string, substring)
    {
        return (this.settings.bothSideCompare && string.indexOf(substring.toLowerCase()) != -1)
            || string.indexOf(substring.toLowerCase()) == 0;
    },
        
    addOptionToSelect: function(text, value, selected)
    {
        var options = { 'value': value, 'title': text };
        if (selected){
            options.selected = 'selected';
        }
        var option = new Element('option', options).update(text);
        this.select.insert(option);
        this.options[value] = option;
        this.allOptions[value] = option;
        this.allOptionIds.push(value);
        return option;
    },
       
    setDefaultData: function (options)
    {   
        this.data = options;
    },
        
    addHandlers: function ()
    {   
        $(this.select).observe('mousedown', this.onMouseDownSelectHandler.bind(this));
        $(this.select).observe('keydown', this.onKeyDownSelectHandler.bind(this));
        $(this.select).observe('click', this.onClickSelectHandler.bind(this));
        $(this.select).observe('blur', this.onBlurSelectHandler.bind(this));
        $(this.input).observe('click', this.onClickInputHandler.bind(this));
        $(this.input).observe('blur', this.onBlurInputHandler.bind(this));
        $(this.input).observe('keyup', this.onEditInputHandler.bind(this));
        $(this.input).observe('keydown', this.onKeyDownInputHandler.bind(this));
        $(this.input).observe('change', this.onChangeInputHandler.bind(this));
    },
     
    onKeyDownInputHandler:function(event)
    {
        if(event.keyCode == 38 || event.keyCode == 40){
            this.buttonDown = true;
            //this.select.focus();
            this.select.style.display = 'block';
            if (this.select.selectedIndex == -1){
                this.select.selectedIndex = 0;
            }else if(event.keyCode == 38 && this.select.selectedIndex > 0){
                this.select.selectedIndex--;
            }else if(event.keyCode == 40 && this.select.selectedIndex < this.select.options.length - 1){
                this.select.selectedIndex++;
            }
        }else if (event.keyCode == 9 || event.keyCode == 27){
            this.select.hide();
        }
    },

    onKeyDownSelectHandler:function(event)
    {
        if(event.keyCode == 13){
            this.selectOption();
            this.select.hide();
        }
    },


    onEditInputHandler: function (event) 
    {
        this.select.style.display = 'block';
        this.filter(this.input.value);
        if(event.keyCode == 13){
            this.select.hide();
            if (this.select.selectedIndex == -1
                && this.select.options[0]
                && this.matchText(this.select.options[0].text, this.input.value)){
                this.select.selectedIndex = 0;
                this.selectOption();
            }else if (this.select.selectedIndex != -1){
                this.selectOption();
            }
        }
    },

    onMouseDownSelectHandler: function() 
    {
        this.clicked = true;
    },

    onBlurSelectHandler:function () 
    {
        this.select.hide();
    },

    onBlurInputHandler: function (event) 
    {
        this.input.value = this.text;
        if(!this.text){
            this.showPlaceholder();
        }
        if(!this.clicked && !this.buttonDown){
            this.select.hide();
        }
        this.buttonDown = false;
        this.clicked = false;
    },

    onChangeInputHandler: function()
    {
        if (this.value === null && (this.oldValue !== null
            || this.settings.callbackOnEveryChange)){
            this.oldValue = null;
            if(typeof this.changeCallback == 'function'){
                this.changeCallback(this.container.id);
            }
        }
    },

    onClickInputHandler: function () 
    {
        this.input.value = '';
        this.select.value = '';
        this.showAllOptions();
        this.select.style.display = 'block';
    },

    onClickSelectHandler:function (event) 
    {
        event.target.selected = 'selected';
        this.select.hide();
        this.selectOption();
        
    },

    selectOption: function()
    {
        if (this.select.selectedIndex == -1){
            return;
        }
        this.input.value = this.select.options[this.select.selectedIndex].text;
        this.value = this.select.value;
        this.text = this.input.value;
        if(typeof this.changeCallback == 'function'){
            this.changeCallback(this.container.id);
        }
    },

    isOpera: function()
    {
        return typeof window.opera != 'undefined';
    },

    checkBrowserPlaceholderSupport: function()
    {
        if(Prototype.Browser.Opera){
            if(navigator.appVersion.substring(0,2) < 11
                   || navigator.appVersion[1] == '.'){
                return false;
            }else{
                return true;
            }
        }
        if(Prototype.Browser.IE){
            return false;
        }
        if(Prototype.Browser.Gecko){
            if(navigator.userAgent.split('Firefox/')[1].substring(0,1) < 4){
                return false;
            }
        }
        
        return true;
    },
    
    showPlaceholder: function()
    {
        if (!this.checkBrowserPlaceholderSupport()){
            if (this.placeholder)
            {
                this.input.addClassName('placeholder');
                this.input.value = this.placeholder;
            }
         }
    },

    hidePlaceholder: function()
    {
        this.input.removeClassName('placeholder');
    },

    getData: function(key, value)
    {
        if (typeof value == "undefined")
        {
            value = this.value;
        }
        if (typeof key == "undefined" && typeof this.data[value] != "undefined"){
            return this.data[value];
        }
        return value !== null && typeof this.data[value] != "undefined"
            && typeof this.data[value][key] != "undefined"
            ? this.data[value][key]
            : null;
    }

});
    

    