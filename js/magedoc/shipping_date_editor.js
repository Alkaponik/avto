ShippingDateEditor = Class.create();
ShippingDateEditor.prototype = {
    initialize: function(containerId,editElementId,dateFormat,editUrl)
    {
        this.container = $(containerId);
        this.editElement = $(editElementId);
        this.dateFormat = dateFormat;
        this.thiseditUrl = editUrl;

        this.editWindow = null;
        this.isRequestRunning = false;

        this.initContainer();
    },

    initContainer: function()
    {

        this.editedCell    = null;
        this.previousValue = null;
        this.hasPreviousValue = false;

        this.container.up().observe('mouseover', function(){
            this.onCellMouseOver(this.container);
        }.bind(this)).observe('mouseout', function(){
            this.onCellMouseOut(this.container);
        }.bind(this));

        this.hoveredCell = null;
        this.mouseCell   = null;
        this.hoverStart  = null;
    },

    compareCells: function(cell1, cell2)
    {
        return (cell1 && cell2 ? cell1.identify() == cell2.identify() : false);
    },

    createDiv: function(id, classNames)
    {
        var div = $(document.createElement('DIV'));
        if (id) {
            div.id = id;
        }
        if (classNames) {
            $A(classNames).each(function(className){
                div.addClassName(className);
            });
        }
        return div;
    },

    getCellOverlay: function(cell)
    {
        return cell.up().select('#blcg-column-editor-overlay-shipping_date')[0];
    },

    positionCellOverlay: function(cell, overlay, mustShow)
    {
        overlay = (overlay ? overlay : this.getCellOverlay(cell));
        var offset   = cell.cumulativeOffset(),
            csOffset = cell.cumulativeScrollOffset(),
            width;
        offset.left -= csOffset.left;

        if (!overlay.visible()) {
            overlay.show();
            width = overlay.getWidth();
            overlay.hide();
        } else {
            width = overlay.getWidth();
        }

        if (mustShow) {
            overlay.show();
        }
    },

    fillCellOverlay: function(cell, overlay)
    {
        overlay = (overlay ? overlay : this.getCellOverlay(cell));

        // @todo to make it (even) "cleaner", all those classes should be wrapped in a dedicated parameter
        if (cell.hasClassName('blcg-column-editor-editing')) {
            if (!overlay.hasClassName('blcg-column-editor-overlay-container-editing')) {
                overlay.innerHTML = '';
                var div = this.createDiv(null, ['blcg-column-editor-overlay-validate']);
                div.observe('click', function(){ this.validateEdit(); }.bind(this));
                overlay.appendChild(div);
                div = this.createDiv(null, ['blcg-column-editor-overlay-cancel']);
                div.observe('click', function(){ this.cancelEdit(); }.bind(this));
                overlay.appendChild(div);
                overlay.removeClassName('blcg-column-editor-overlay-container-idle');
                overlay.addClassName('blcg-column-editor-overlay-container-editing');
            }
        } else if (!overlay.hasClassName('blcg-column-editor-overlay-container-idle')) {
            overlay.innerHTML = '';
            var div = this.createDiv(null, ['blcg-column-editor-overlay-edit']);
            div.observe('click', function(){ this.editCell(cell); }.bind(this));
            overlay.appendChild(div);
            overlay.removeClassName('blcg-column-editor-overlay-container-editing');
            overlay.addClassName('blcg-column-editor-overlay-container-idle');
        }
    },

    showCellOverlay: function(cell, overlay)
    {
        overlay = (overlay ? overlay : this.getCellOverlay(cell));
        this.fillCellOverlay(cell, overlay);
        this.positionCellOverlay(cell, overlay, true);
    },

    hideCellOverlay: function(cell, overlay)
    {
        overlay = (overlay ? overlay : this.getCellOverlay(cell));
        overlay.hide();
    },

    stopHoverStart: function()
    {
        if (this.hoverStart) {
            window.clearTimeout(this.hoverStart);
            this.hoverStart = null;
        }
    },

    stopHoverEnd: function()
    {
        if (this.hoverEnd) {
            window.clearTimeout(this.hoverEnd);
            this.hoverEnd = null;
        }
    },

    onCellMouseOver: function(cell)
    {
        this.mouseCell = cell;

        this.stopHoverStart();

        this.hoverStart = window.setTimeout(function(){
            this.hoverStart = null;
            this.stopHoverEnd();

            if (this.hoveredCell) {
                this.hideCellOverlay(this.hoveredCell);
            }

            this.hoveredCell = cell;
            this.showCellOverlay(cell);
        }.bind(this), 50);
    },

    onCellMouseOut: function(cell)
    {
        if (this.compareCells(this.mouseCell, cell)) {
            this.mouseCell = null;
            this.stopHoverStart();
        }
        if (this.compareCells(this.hoveredCell, cell)) {
            this.stopHoverEnd();

            this.hoverEnd = window.setTimeout(function(){
                this.hoverEnd = null;
                this.hideCellOverlay(this.hoveredCell);
                this.hoveredCell = null;
            }.bind(this), 25);
        }
    },

    editCell: function(cell)
    {
        this.editedCell = cell;
        cell.addClassName('blcg-column-editor-editing');
        this.previousValue = cell.innerHTML;
        this.hasPreviousValue = true;

        var form = document.createElement('form');
        form.id = 'blcg-column-editor-form-' + cell.identify();
        this.editElement.select('#shipping_date')[0].removeAttribute('disabled');
        form.innerHTML = this.editElement.innerHTML;
        var previousDate = cell.select('#order_shipping_date_text')[0].innerHTML;
        form.select('input')[0].setValue(previousDate);
        cell.select('#order_shipping_date_text')[0].remove();
        cell.appendChild(form);
        Calendar.setup({
            inputField : 'shipping_date',
            ifFormat : this.dateFormat,
            showsTime: false,
            button : 'shipping_date_trig',
            align : 'Bl',
            singleClick : true,
        });

        this.fillCellOverlay(cell);
        this.positionCellOverlay(cell, null, this.compareCells(cell, this.mouseCell));

        cell.getElementsBySelector('.blcg-editor-required-marker').each(function(e){
            e.hide();
            cell.addClassName('blcg-column-editor-editing-required');
        });

        // add Editor support for Enter / Escape keys
        var formInputs = $(form).select('.required-entry');

        formInputs.each(function(input){
            input.observe('keydown', function(e){
                switch (e.keyCode) {
                    case Event.KEY_RETURN: // Enter completes edit
                        e.preventDefault();
                        this.validateEdit();
                        break;

                    case Event.KEY_ESC: // Escape cancels edit
                        e.preventDefault();
                        this.cancelEdit();
                        break;
                }
            });
        });
    },

    validateEdit: function(formParams)
    {
        if (this.editedCell) {
            var cell = this.editedCell;
            var cellId = cell.identify();
            var value = '';

            var form = $('blcg-column-editor-form-' + cellId);
            if (form) {
                var validator = new Validation(form);
                if (validator) {
                    if (!validator.validate()) {
                        return;
                    }
                    value = form.select('input')[0].getValue();
                    this.saveOrderShippingDate(value);
                }
            }

            cell.addClassName('blcg-column-editor-updated');
            cell.removeClassName('blcg-column-editor-editing');
            cell.removeClassName('blcg-column-editor-editing-required');
            cell.innerHTML = "<div id='order_shipping_date_text'>"+ value + "</div>";

            this.previousValue = null;
            this.hasPreviousValue = false;
            this.editedCell = null;
            this.editElement.select('#shipping_date')[0].writeAttribute('disabled','disabled');

            this.fillCellOverlay(cell);
            this.positionCellOverlay(cell, null, this.compareCells(cell, this.mouseCell));
        }
    },

    cancelEdit: function(fromDialog, errorMessage)
    {
        if (this.isRequestRunning) {
            return;
        }
        if (this.editedCell) {
            if (this.hasPreviousValue) {
                this.editedCell.innerHTML = this.previousValue;
                this.hasPreviousValue = false;
            }

            this.previousValue = null;
            this.editedCell.removeClassName('blcg-column-editor-editing');
            this.editedCell.removeClassName('blcg-column-editor-editing-required');
            this.fillCellOverlay(this.editedCell);
            this.positionCellOverlay(this.editedCell);
            this.editElement.select('#shipping_date')[0].writeAttribute('disabled','disabled');
            this.editedCell = null;
        }
    },

    saveOrderShippingDate: function(value){
        var data = {};
        data['order[shipping_date]'] = value;
        var url = this.thiseditUrl;
        if(url){
            new Ajax.Request(url, {
                parameters:data,
                onSuccess: function(transport) {
                    var response = transport.responseText ? transport.responseText.evalJSON(): {};
                    if (response.error) {
                        alert(response.message);
                    }
                }
            });
        }
    }
};