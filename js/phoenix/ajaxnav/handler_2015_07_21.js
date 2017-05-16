var AjaxUpdateHandler = function(elementLayer, container, elements){
    this.urlRegexp = /^https?:\/\/[^\/]+\/(?!checkout\/|catalogsearch\/|customer\/|wishlist\/|catalog\/product_compare\/)/;
    this.buttonActionRegexp = new RegExp("setLocation\\((\"|')([^\)]+)(\"|')\\)", "g");
    this.layer = $(elementLayer);
    this.canClosePopup = true;
    this.popupContainer = $(container);
    this.elements = elements;
    this.isSecure = document.location.protocol.indexOf('https:') == 0;
    this.initHandlers();
    this.layer.observe('click', this.hidePopup.bind(this));
    this.baseHost = window.location.host;
    this.basePath = window.location.pathname;
    this.baseSearch = window.location.search;
    this.baseUrl = window.location.href;
    this.state = null;
    this.initState();
    Event.observe(window, 'hashchange', this.hashChangeHandler.bind(this));
}

AjaxUpdateHandler.prototype = {
    getElements: function()
    {
        //this.log(this.elements);
        return typeof this.elements == 'string'
            ? $$(this.elements)
            : this.elements;
    },
    initHandlers: function()
    {
        this.log('initHandlers');
        var elements = this.getElements();
        for (var key = 0; key < elements.length; key++){
            if (this.testElement(elements[key])){
                this.addHandler(elements[key]);
            }
        }
    },
    addHandler: function(element){
        //this.log(element);
        var form = null;
        if (element.tagName == 'FORM'){
            var form = element;
            var element = element.down('button')
        }
        if (typeof element != 'undefined' && element){
            var url = this.getUrl(form, element);
            if (url){
                if (element.tagName == 'OPTION'){
                    var select = element.up('select');
                    if (select){
                        select.onchange = null;
                        element.observe('click', this.elementClickHandler.bind(this, form, url, null));
                    }
                }else{
                    element.onclick = null;
                    element.observe('click', this.elementClickHandler.bind(this, form, url, null));
                }
            }
        }
    },
    testElement: function(element)
    {
        return true;
    },
    elementClickHandler: function(form, url, callback, event){
        this.log('elementClickHandler');
        if (event){
            Event.stop(event);
        }
        this.canClosePopup = false;
        if ((this.isSecure && url.indexOf('https:') != 0)
                || (!this.isSecure && url.indexOf('https:') == 0)){
            if (form){
                form.submit();
            }else{
                document.location = url;
            }
        }
        var params = form
            ? form.serialize(true)
            : {};
        params.is_ajax = true;
        this.showPopup();
        if (event){
            this.saveState(url, params);
        }
        
        this.loadAreas(url, 
            params,
            this.initHandlers.bind(this),
            this.hidePopup.bind(this));
        if (typeof callback == 'function'){
            callback();
        }
        return false;
    },
    showPopup: function(){
        this.popupContainer.update(this.layer.down('.loading').cloneNode(true));
        this.layer.show();
        this.popupContainer.show()
    },
    hidePopup: function(){
        if (this.canClosePopup){
            this.layer.hide();
            this.popupContainer.hide();
        }
    },
    getUrl: function(form, element){
        if(element.tagName == 'BUTTON'){
            var url = this.buttonActionRegexp.exec(element.onclick.toString());
            if (url && typeof url[2] != undefined){
                url = url[2];
            }else{
                url = null
            }
        }else if(element.tagName == 'A'){
            var url = element.href;
        }else if(element.tagName == 'OPTION'){
            var url = element.value;
        }
        if(!url){
            if (form){
                url = form.action;
            }else{
                return null;
            }
        }
        return this.urlRegexp.test(url)
            ? url
            : false;
    },
    loadAreas: function(url, params, onSuccessCallback, onCompleteCallback){
        new Ajax.Request(url, {
            'method': 'get',
            'evalScripts': false,
            'parameters': params,
            'onComplete': function(){
                this.canClosePopup = true;
                if (typeof onCompleteCallback == 'function'){
                    onCompleteCallback();
                }
            }.bind(this),
            'onSuccess': function(transport){
                var response = transport.responseText.evalJSON();
                this.loadAreaResponseHandler(response);
                if (typeof onSuccessCallback == 'function'){
                    onSuccessCallback();
                }
            }.bind(this)
        });
    },

    loadAreaResponseHandler: function(response){
        if (response.location){
            setLocation(response.location);
            return;
        }
        for (var handle in  response){
            var elements = $$(handle);
            if (elements){
                for (var key = 0; key < elements.length; key++){
                    var element = elements[key];
                    element.replace(response[handle]);
                }
            }
        }
        
        var closeButton = this.popupContainer.down('button.close');
        if (closeButton){
            closeButton.observe('click', this.hidePopup.bind(this))
        }
    },

    log: function(message){
        if (typeof console != 'undefined'){
            console.log(message);
        }
    },

    saveState: function(url){
        var pathDelta = '';
        var searchDelta = '';
        if (url.indexOf(this.baseHost + this.basePath) != -1){
            var path = url.split('?')[0].split(this.baseHost + this.basePath);
            if (typeof path[1] != 'undefined'){
                pathDelta = path[1];
            }
            var search = url.split('?');
            if (typeof search[1] != 'undefined'){
                if (this.baseSearch){
                    search = ('?' + search[1]).split(this.baseSearch);
                    if (typeof search[1] != 'undefined'){
                        searchDelta = search[1];
                    }
                }else{
                    searchDelta = search[1];
                }
            }
        }
        if (pathDelta || searchDelta){
            window.location.hash = '#!/' + pathDelta + (searchDelta ? '?' +searchDelta : '')
            this.state = window.location.hash;
        }
        this.statePath = pathDelta;
        this.stateSearch = searchDelta;
    },

    initState: function(){
        this.state = window.location.hash;
        this.statePath = '';
        this.stateSearch = '';
        if (this.state.indexOf('#!/') === 0){
            this.statePath = this.state.substr(3).split('?')[0];
            this.stateSearch = this.state.split('?')[1] ? this.state.split('?')[1] : '';
        }
        
        if (this.statePath || this.stateSearch){
            this.elementClickHandler(null, this.composeStateUrl(this.statePath, this.stateSearch), null, null);
        }
    },

    hashChangeHandler: function(){
        if (this.state != window.location.hash){
            this.initState();
        }
    },

    composeStateUrl: function(pathDelta, searchDelta){
        var baseUrl = this.baseUrl.split('#!/',2)[0];
        if (baseUrl.indexOf('?') !== -1){
            var basePath = baseUrl.split('?',2)[0];
            var baseSearch = '?' + baseUrl.split('?',2)[1].split('#',2)[0];
        }else{
            var basePath = baseUrl.split('?',2)[0].split('#',2)[0];
            var baseSearch = '';
        }
        if (pathDelta){
            basePath += pathDelta;
        }

        if (searchDelta){
            if (!baseSearch){
                baseSearch = '?';
            }else{
                baseSearch += '&';
            }
            baseSearch += searchDelta;
        }
       
        return basePath+baseSearch;
    }
}


