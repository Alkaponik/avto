var RelatedGrid = function(grids)
{
    this.grids = grids;
    this.init();
    this.addHandlers();
};

RelatedGrid.prototype = {
    init: function(){
        for (var i=0; i < this.grids.length; i++){
            this.grids[i].stopPropogation = {};
        }
    },

    addHandlers: function(){
        for (var i=0; i < this.grids.length; i++){
            var elements = $(this.grids[i].containerId).select('.filter input, .filter select');
            for (var j = 0; j < elements.length; j++){
                elements[j].stopObserving('change', this.inputChangeHandler.bindAsEventListener(this));
                elements[j].observe('change', this.inputChangeHandler.bindAsEventListener(this));
            }
        }
    },

    inputChangeHandler: function(event){
        var e = event.target;
        for (var i=0; i < this.grids.length; i++){
            var elements = $(this.grids[i].containerId).select('.filter [name='+ e.name+']');
            for (var j = 0; j < elements.length; j++){
                if (e != elements[j]){
                    elements[j].value = e.value;
                }
            }
        }
    },

    triggerSearch: function(exceptGrid){
        for (var i=0; i < this.grids.length; i++){
            if (this.grids[i] !== exceptGrid){
                this.grids[i].stopPropogation.search = true;
                this.grids[i].doFilter();
            }
        }
    },

    triggerReset: function(exceptGrid){
        for (var i=0; i < this.grids.length; i++){
            if (this.grids[i] !== exceptGrid){
                this.grids[i].stopPropogation.reset = true;
                this.grids[i].resetFilter();
            }
        }
    }
};