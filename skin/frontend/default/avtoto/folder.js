var ContainerFolder = function() 
{
	this.container = $('car-models-list');
	this.items = this.container.select('li.model'); 
	for (var key=0; key < this.items.length; key++)
	{
		var item = $(this.items[key]);
		item.down('div').hide();
		item.down('span.expand-button').observe('click',
			this.expandClickHandler.bind(this, item));
		item.down('a.model-title').observe('click',
			this.expandClickHandler.bind(this, item));
	}
}

ContainerFolder.prototype = {
	expandClickHandler: function(item){
		var contentElement = item.down('div');
		var button = item.down('span.expand-button');
		if (contentElement.style.display == 'none'){
			Effect.SlideDown(contentElement, { duration: 1.0 });
			button.addClassName('active');
		}else{
			Effect.SlideUp(contentElement, { duration: 1.0 });
			button.removeClassName('active');
		}
		return false;
	} 
}

Event.observe(window, 'load', function(){ document.folder = new ContainerFolder();});