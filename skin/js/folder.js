var ContainerFolder = function(container, params) 
{
    this.params = {
        'itemClass':    'li.model',
        'titleClass':   'a.model-title',
        'expandButtonClass': 'span.expand-button'
    }
    if (typeof params == 'object')
    {
        for (var key in params)
        {
            this.params[key] = params[key]; 
        }
    }
    this.activeElementNames = window.location.hash
        ? window.location.hash.substring(1).split(',')
        : [];
	this.container = $(container);
	this.items = this.container.select(this.params.itemClass); 
	for (var key=0; key < this.items.length; key++)
	{
		var item = $(this.items[key]);
        var button = item.select(this.params.expandButtonClass).first();
        var title = item.select(this.params.titleClass).first();
		button.observe('click',
			this.expandClickHandler.bind(this, item));
		title.observe('click',
			this.expandClickHandler.bind(this, item));
        if (this.activeElementNames.indexOf(title.name) == -1)
        {
            item.down('div').hide();
        }else{
            button.addClassName('active');
        }
	}
}

ContainerFolder.prototype = {
	expandClickHandler: function(item, event){
        Event.stop(event);
        var e = Event.element(event);
		var contentElement = item.down('div');
		var button = item.select(this.params.expandButtonClass).first();
        var title = item.select(this.params.titleClass).first();
        var titleName = title.name;
        var isButtonClicked = e == button;

		if (!this.isActive(item)){
			Effect.SlideDown(contentElement, { duration: 1.0 });
			button.addClassName('active');
            if (titleName)
            {
                this.activeElementNames.push(titleName);
                window.location.hash = "#"+this.activeElementNames.join(',');
            }
		}else{
			Effect.SlideUp(contentElement, { duration: 1.0 });
			button.removeClassName('active');
            if (titleName && (this.activeElementNames.indexOf(titleName) != -1))
            {
                this.activeElementNames.splice(this.activeElementNames.indexOf(titleName), 1);
                if (this.activeElementNames.length)
                {
                    window.location.hash = "#"+this.activeElementNames.join(',');
                }else if (isButtonClicked
                    && typeof button.up('a').hash != "undefined"){
                    window.location.hash = button.up('a').hash;
                }else{
                    window.location.hash = "#/";
                }
                
            }
		}
		return false;
	},
        
    isActive: function(item){
        var contentElement = item.down('div');
        return contentElement.style.display != 'none';
    }
}

Event.observe(window, 'load', function(){
        $$('a.read-more').each(function(item){
            item.observe('click', function(event)
            {
                Event.stop(event);
                element = Event.element(event);
                var container = element.previous('div');
                if (container.hasClassName('hide')){
                    container.removeClassName('hide');
                    element.addClassName('open');
                }else{
                    container.addClassName('hide');
                    element.removeClassName('open');
                }
            });
        });
});