var TabButtonToggler = Class.create({
    initialize : function( defaultActiveTab )
    {
        this.hideAll();
        $$('ul.tabs li a.tab-item-link').invoke('observe', 'click', this.changeButtonsVisibility.bindAsEventListener(this));
        this.showTabButtons(defaultActiveTab);
    },

    hideAll : function()
    {
        $$('button.tab_button_toggler').invoke('hide');
    },

    changeButtonsVisibility : function( event )
    {
        var element = Event.findElement(event, 'a');
        this.hideAll();
        this.showTabButtons( element.readAttribute('name') );
    },

    showTabButtons : function(tabName)
    {
        $$('button.tab_button_toggler.'+tabName).invoke('show');
    }
});




