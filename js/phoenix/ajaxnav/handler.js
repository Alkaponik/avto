/**
 * Version: 1.1
 * options:
 *  clickHandlerActionWrappers - array of element classes and associated click wrappers
 *  showOverlay - default true
 *  cacheAheadDepth - whether to cache element action results ahead
 *  cacheClasses - classes of elements which actions to cache
 */

var AjaxUpdateHandler = function(elementLayer, container, elements, options){
    this.buttonActionRegexpString = "setLocation\\((\"|')([^\\)]+checkout\\/cart\\/add\\/[^\\)]+)(\"|')\\)";
    this.buttonCartFormActionRegexpString = "setLocation\\((\"|')([^\\)]+ajaxcart\\/cart\\/addForm\\/[^\\)]+)(\"|')\\)";
    this.layer = $(elementLayer);
    this.canClosePopup = true;
    this.popupContainer = $(container);
    this.elements = elements;
    this.isSecure = document.location.protocol.indexOf('https:') == 0;
    this.loading = 0;
    this.onLoadCompleteCallback = null;
    
    this.layer.observe('click', this.hidePopup.bind(this));
    this.baseHost = window.location.host;
    this.basePath = window.location.pathname;
    this.baseSearch = window.location.search;
    this.baseUrl = window.location.href;
    this.state = null;
    this.options = {
        'debug': true,
        'showOverlay': true,
        'hideOverlay': true ,
        'cacheAheadDepth': 1,
        'showLoading': true,
        'cacheClasses': ['previous', 'next'],
        'cacheImagesPreload': true,
        'skipClickHandlingClasses': ['dummy-btn'],
        'forceShowOverlayClasses': ['btn-cart'],
        'forceHideOverlayClasses': [],
        'skipHideOverlayClasses': [],
        'updateHandles': ['#magedoc-ajax-popup-container'],
        'clickHandlerActionWrappers': {'.next': this.slideNext.bind(this), '.previous': this.slideBack.bind(this)},
        'urlRegexp': /^https?:\/\/[^\/]+\/(?!checkout\/onepage\/|customer\/|wishlist\/|catalog\/product_compare\/)/,
        'imageUrlRegexp': /https?:\/\/[a-zA-Z0-9_\-\/\.]+\.(jpg|png)/gi,
        'sliderContainer': '#slider'
    };
    this.slider = null;
    if (typeof options != 'undefined'){
        for(var key in options){
            this.options[key] = options[key];
        }
    }
    this.cache = {};
    //this.initHandlers();
    //this.initSlider();
    this.initState(this.loadAreaOnSuccessCallback.bind(this));
    Event.observe(window, 'hashchange', this.hashChangeHandler.bind(this));
};

AjaxUpdateHandler.prototype = {
    getElements: function()
    {
        return (typeof this.elements == 'string')
            ? $$(this.elements)
            : this.elements;
    },
    initHandlers: function()
    {
        this.log('initHandlers');
        var elements = this.getElements();
        this.log(elements);
        for (var key = 0; key < elements.length; key++){
            if (this.testElement(elements[key])){
                this.addHandler(elements[key]);
            }
        }
    },
    addHandler: function(element){
        this.log(element);
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
                        select.observe('change', this.elementClickHandler.bind(this, form, url, null));
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
            for (var i=0; i < this.options.skipClickHandlingClasses.length; i++){
                if (event.target.hasClassName(this.options.skipClickHandlingClasses[i])) {
                    return;
                }
            }
        }
        this.canClosePopup = false;
        if (this.canShowPopup(event)){
            this.showPopup();
        }
        if (this.loading){
            this.log('addCallback');
            this.onLoadCompleteCallback = this.elementClickHandler.bind(this, form, url, callback, event);
            return;
        }
        
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
        
        if (event){
            this.saveState(url, params);
        }
        
        if (typeof callback != 'function'){
            callback = this.loadAreaOnSuccessCallback.bind(this);
        }
        var defaultAction = this.loadAreasHandler.bind(this, url, params, callback, event);
        var actionFound = false;
        if (event){
            for (var key in this.options.clickHandlerActionWrappers){
                if (element = event.findElement(key)){
                    var actionFound = true;
                    this.options.clickHandlerActionWrappers[key](form, url, defaultAction, event);
                    break;
                }
            }
        }
        if (!actionFound){
            defaultAction();
        }

        return false;
    },
    loadAreasHandler: function(url, params, callback, event){
        this.log('loadAreasHandler');
        if (this.useCache() && this.cache[this.getCacheKey(url, params)]){
            this.canClosePopup = true;
            this.loadAreaResponseHandler(this.cache[this.getCacheKey(url, params)]);
            this.hideOverlay(event);
            //this.initCache();
            if (typeof callback == 'function'){
                callback();
            }
            //this.initHandlers();
        }else{
            this.loadAreas(url,
                params,
                //function(){/*this.initHandlers();*/ callback()}.bind(this, callback),
                callback.bind(this),
                this.hideOverlay.bind(this, event));
        }
    },
    showPopup: function(){
        if (this.options.showLoading){
            this.popupContainer.update(this.layer.down('.loading').cloneNode(true));
        }else{
            this.popupContainer.update('');
        }
        this.popupContainer.addClassName('loading');
        this.layer.show();
        this.popupContainer.show()
    },
    hidePopup: function(force){
        force = typeof force != 'undefined' ? force : false;
        if (this.canClosePopup || force){
            this.layer.hide();
            this.popupContainer.hide();
        }
    },
    hideOverlay: function(event)
    {
        if (this.canHideOverlay(event)){
            this.hidePopup();
        }
        this.popupContainer.removeClassName('loading');
    },
    canShowPopup: function(event)
    {
        return this.options.showOverlay
            || (event
                    && this.options.forceShowOverlayClasses.length
                    && event.findElement('.'+this.options.forceShowOverlayClasses.join(',.')))
    },
    canHideOverlay: function(event)
    {
        return (this.options.hideOverlay
                    || event
                        && (this.options.forceHideOverlayClasses.length
                            && event.findElement('.'+this.options.forceHideOverlayClasses.join(',.'))))
                && (!event || (typeof event.type == 'unknown') || !this.options.skipHideOverlayClasses.length
                    || !event.findElement('.'+this.options.skipHideOverlayClasses.join(',.')))
            
    },
    getUrl: function(form, element){
        this.log('getUrl');
        if(element.tagName == 'BUTTON'){
            var buttonActionRegexp = new RegExp(this.buttonActionRegexpString, "g");
            if(element.onclick){
                var url = buttonActionRegexp.exec(element.onclick.toString());
                if (!url) {
                    buttonActionRegexp = new RegExp(this.buttonCartFormActionRegexpString, "g");
                    url = buttonActionRegexp.exec(element.onclick.toString());
                }
            }

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
        url = this.options.urlRegexp.test(url)
            ? url
            : false;
        return url;
    },
    loadAreas: function(url, params, onSuccessCallback, onCompleteCallback, cacheResult){
        this.log('loadAreas');
        this.log(url);
        this.log(params);
        if (!url){
            if (typeof onCompleteCallback == 'function'){
                onCompleteCallback();
            }
            return;
        }
        cacheResult = typeof cacheResult != 'undefined' ? cacheResult : false;
        this.loading++;
        new Ajax.Request(url, {
            'method': 'get',
            'evalScripts': false,
            'parameters': params,
            'onComplete': function(){
                this.canClosePopup = true;
                this.loading--;
                if (typeof onCompleteCallback == 'function'){
                    onCompleteCallback();
                }
                if ((typeof this.onLoadCompleteCallback == 'function') && (this.loading == 0)){
                    this.onLoadCompleteCallback();
                    this.onLoadCompleteCallback = null;
                }
            }.bind(this),
            'onSuccess': function(transport){
                var response = transport.responseText.evalJSON();
                if (cacheResult){
                    this.cache[this.getCacheKey(url, params)] = response;
                    if (this.options.cacheImagesPreload){
                        for (handle in response){
                            var images = response[handle].match(this.options.imageUrlRegexp);
                        }
                        var container = jQuery('#image_preload_container');
                        if (!container.length){
                            var container = jQuery('<div id="image_preload_container" style="display: none;"></div>');
                            container.appendTo(document.body);
                        }
                        container.html('');
                        for (var i = 0; i < images.length; i++){
                            container.append(jQuery('<img src="'+images[i]+'"/>'));
                        }
                    }
                }else{
                    this.loadAreaResponseHandler(response);
                }
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
        for (var handle in response){
            if (handle == 'factfinder.scic') {
                this.log(response[handle]);
                var factfinderJs = response[handle].replace(/<script.+>/m, '').replace(/<\/script>/m, '');
                this.log(factfinderJs);
                eval(factfinderJs + 'factfinderSCIC.init();');
            }

            var elements = $$(handle);
            if (elements){
                for (var key = 0; key < elements.length; key++){
                    var element = elements[key];
                    if (this.options.updateHandles.indexOf(handle) != -1){
                        element.update(response[handle]);
                    }else{
                        element.replace(response[handle]);
                    }
                }
            }
        }
        
        var closeButton = this.popupContainer.down('button.close');
        if (closeButton){
            closeButton.observe('click', this.hidePopup.bind(this))
        }
    },

    loadAreaOnSuccessCallback: function()
    {
        this.initHandlers();
        this.initCache();
        this.initSlider();
    },

    log: function(message){
        if (this.options.debug && typeof console != 'undefined'){
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
            var state = '#!/' + pathDelta + (searchDelta ? '?' + searchDelta : '');
            var currentState = window.location.hash;
            this.state = state;
            window.location.hash = state;
            if (currentState != state) {
                Element.fire(document, 'ajaxnav:urlhashchange');
            }
        }
        this.statePath = pathDelta;
        this.stateSearch = searchDelta;
    },

    initState: function(callback){
        this.state = window.location.hash;
        this.statePath = '';
        this.stateSearch = '';
        if (this.state.indexOf('#!/') === 0){
            this.statePath = this.state.substr(3).split('?')[0];
            this.stateSearch = this.state.split('?')[1] ? this.state.split('?')[1] : '';
        }

        if (this.statePath || this.stateSearch){
            this.elementClickHandler(null, this.composeStateUrl(this.statePath, this.stateSearch), callback, null);
        }else{
            if (typeof callback == 'function'){
                callback();
            }
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
    },

    getCacheKey: function(url, params){
        var cacheKey = url;
        for (var key in params){
            cacheKey + '_' + params[key];
        }
        return cacheKey;
    },

    initCache: function(element){
        this.log('initCache');
        if (!this.useCache()){
            return;
        }
        if (typeof element != 'undefined' && element)
        {
            var elements = [element];
        }else{
            var elements = [];
            for (var i=0; i<this.getElements().length; i++){
                var element = this.getElements()[i];
                for (var j=0; j<this.options.cacheClasses.length; j++){
                    if (element.hasClassName(this.options.cacheClasses[j])){
                        elements.push(element);
                        break;
                    }
                }
            }
        }
        for (var i=0; i < elements.length; i++){
            var element = elements[i];
            if (element.tagName != 'FORM'){
                var url = this.getUrl(null, element);
                var params = url.match('is_ajax=true')
                    ? {}
                    : {is_ajax: true};
                if (!this.cache[this.getCacheKey(url, params)]){
                    this.loadAreas(url, params, null, null, true);
                }
            }
        }
    },

    useCache: function(){
        return this.options.cacheAheadDepth;
    },

    slideNext: function(form, url, callback, event)
    {
        this.log('slideNext');
        if (this.cache[url]){
            var container = new Element('div');
            container.innerHTML = this.cache[url]['#generator_container'];
            var slider = this.getSlider();
            if (container.down('div#slider')){
                slider
                    .append(container.down('div#slider').innerHTML)
                    .anythingSlider();
                slider.bind('slide_complete', callback);
                slider.data('AnythingSlider').goForward();
            }else{
                callback();
            }
        }
    },

    slideBack: function(form, url, callback, event)
    {
        this.log('slideBack');
        if (this.cache[url]){
            var container = new Element('div');
            container.innerHTML = this.cache[url]['#generator_container'];
            var slider = this.getSlider();
            if (container.down('div#slider')){
                slider.data('AnythingSlider').currentPage = 2;
                slider
                    .prepend(container.down('div#slider').innerHTML)
                    .anythingSlider();
                slider.bind('slide_complete', callback);
                slider.data('AnythingSlider').goBack();
            }else{
                callback();
            }
        }
    },
    getSlider: function(){
        //return new Element('div');
        return typeof jQuery != 'undefined'
            ? jQuery(this.options.sliderContainer)
            : { length: 0 };
    },

    initSlider: function(){
        //return;
        this.log('initSlider');
        if (this.getSlider().length){
            document.slider = this.getSlider().anythingSlider(AjaxUpdateHandler.prototype.sliderOptions);
        }
    },

    sliderOptions: {
        expand              : true,
        resizeContents      : true,
        showMultiple        : 1,
        easing              : "swing",

        buildArrows         : false,
        buildNavigation     : false,
        buildStartStop      : false,

        startText           : "",
        stopText            : "",

        enableStartStop     : false,

        // Navigation
        startPanel          : 1,
        changeBy            : 1,
        hashTags            : false,
        infiniteSlides      : false,

        delay               : 3000,
        resumeDelay         : 15000,
        animationTime       : 600
    }
};


