var NotificationManager = function(elementClass){
    this.elements = $$(elementClass);
    this.initHandlers();
}

NotificationManager.prototype = {
    initHandlers: function(){
        for (var i = 0; i < this.elements.length; i++){
            this.elements[i].observe('click', this.handleElementClick.bind(this));
        }
    },

    handleElementClick: function(event){
        Event.stop(event);
        var e = event.target;
        new Ajax.Request(e.href, {
            onLoading: function(e){e.hide(); e.up('td').addClassName('loading')}.curry(e),
            onComplete: function(e, response){e.up('td').removeClassName('loading').update(response.responseText)}.curry(e)
        });
    }
}
