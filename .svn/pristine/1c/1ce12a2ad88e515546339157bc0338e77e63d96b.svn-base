var magedocGridMassaction = Class.create(varienGridMassaction, {
    _apply:function ($super) {

        if (!this.validateFiters()) {
            return;
        }

        var item = this.getSelectedItem();

        if (this.checkedString.length == 0) {
            if (!confirm("Are you really want to import all the listed prices?")) {
                return;
            }
        }

        this.currentItem = item;
        var fieldName = (item.field ? item.field : this.formFieldName);
        var fieldsHtml = '';


        this.formHiddens.update('');
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name:fieldName, value:this.checkedString}));
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name: 'massaction_prepare_key', value: fieldName}));

        var filters = $$('.filter input', ' .filter select');
        var elements = [];
        for (var i in filters) {
            if (filters[i].value && filters[i].value.length) elements.push(filters[i]);
        }
        this.url = item.url;
        item.url = varienGrid.prototype._addVarToUrl.bind(this)(this.url, this.filterVar, encode_base64(Form.serializeElements(elements)));

        if (this.useAjax && item.url) {
            new Ajax.Request(item.url, {
                'method':'post',
                'parameters':this.form.serialize(true),
                'onComplete':this.onMassactionComplete.bind(this)
            });
        } else if (item.url) {
            this.form.action = item.url;
            this.form.submit();
        }

    },

    apply:function ($super) {
        if (!this.validateFiters()) {
            //alert(this.errorText);
            return;
        }
        if (varienStringArray.count(this.checkedString) == 0) {
            if (!confirm("Do you really want to import all the listed prices?")) {
                return;
            }
        }

        var item = this.getSelectedItem();
        if (!item) {
            this.validator.validate();
            return;
        }
        this.currentItem = item;
        var fieldName = (item.field ? item.field : this.formFieldName);
        var fieldsHtml = '';

        if (this.currentItem.confirm && !window.confirm(this.currentItem.confirm)) {
            return;
        }

        this.formHiddens.update('');
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name:fieldName, value:this.checkedString}));
        new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name:'massaction_prepare_key', value:fieldName}));

        /* Adding filters' to massaction parameters */
        //new Insertion.Bottom(this.formHiddens, this.fieldTemplate.evaluate({name:this.filterVar, value:this.serializeFilters()}));
        this.url = item.url;
        item.url = varienGrid.prototype._addVarToUrl.bind(this)(this.url, this.filterVar, this.serializeFilters());

        if (!this.validator.validate()) {
            return;
        }

        if (this.useAjax && item.url) {
            new Ajax.Request(item.url, {
                'method':'post',
                'parameters':this.form.serialize(true),
                'onComplete':this.onMassactionComplete.bind(this)
            });
        } else if (item.url) {
            this.form.action = item.url;
            this.form.submit();
        }
    },

    validateFiters:function () {
        var isUpdate = this.select.value == 'update';
        var categoryFilter = $$('.filter select[name=category]').first();
        if (categoryFilter && !isUpdate && !categoryFilter.value) {
            alert("Please set category filter");
            return false;
        }

        return true;
    },

    serializeFilters: function () {
        var filters = $$('.filter input', ' .filter select');
        var elements = [];
        for (var i in filters) {
            if (filters[i].value && filters[i].value.length) elements.push(filters[i]);
        }
        return encode_base64(Form.serializeElements(elements));
    },

    setFilterVar:function (filterVar) {
        this.filterVar = filterVar;
    }

});

