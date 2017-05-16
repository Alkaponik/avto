/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     enterprise_default
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

// Add validation hints

if (!window.Enterprise) {
    window.Enterprise = {};
}
/*
Enterprise.Tabs = Class.create(Enterprise.Tabs, {
    activateTab: function(tab) {
        this.activeTab = $(tab);
        this.select();
    }
});

Enterprise.OPCTabs = Class.create(Enterprise.Tabs, {
    initialize: function (container) {
        this.container = $(container);
        this.container.addClassName('tab-list');
        this.tabs = this.container.select('dt.tab');
        this.radios = this.container.select('dt.tab input.radio');
        this.labels = this.container.select('dt.tab label');
        this.activeInput = this.radios.first();
        this.activeTab = this.tabs.first();
        this.tabs.first().addClassName('first');
        this.tabs.last().addClassName('last');
        this.onInputClick = this.handleInputClick.bindAsEventListener(this);
        for (var i = 0, l = this.radios.length; i < l; i ++) {
            this.radios[i].observe('click', this.onInputClick);
        }
        this.select();
    },
    handleInputClick: function (evt) {
        this.activeInput = Event.findElement(evt, 'input');
        this.activeTab = this.activeInput.up('dt');
        this.select();
    },
    select: function () {
        for (var i = 0, l = this.tabs.length; i < l; i ++) {
            if (this.tabs[i] == this.activeTab) {
                this.tabs[i].addClassName('active');
                this.tabs[i].style.zIndex = this.tabs.length + 2;
                new Effect.Appear (this.tabs[i].next('dd'), { duration:0.5 });
                this.tabs[i].parentNode.style.height=this.tabs[i].next('dd').getHeight() + 15 + 'px';
            } else {
                this.tabs[i].removeClassName('active');
                this.tabs[i].style.zIndex = this.tabs.length + 1 - i;
                this.tabs[i].next('dd').hide();
            }
        }
    }

});


Enterprise.OpcProgress = {
    initialize: function (container) {
        this.container = $(container);
        this.id = this.container.id;
        this.stepTitles    = this.container.select('.step-title');
        switch (this.stepTitles.length){
            case 5:
                this.stepTitles.each(function(item){
                    item.addClassName('distributed');
                });
                break;
        }
    }
};

Enterprise.DOB = Class.create(Varien.DOB, {
    initialize: function(selector, required, format) {
        var el = $$(selector)[0];
        var container       = {};
        container.day       = Element.select(el, '.dob-day')[0];
        container.month     = Element.select(el, '.dob-month')[0];
        container.year      = Element.select(el, '.dob-year')[0];
        container.full      = Element.select(el, '.dob-full input')[0];
        container.advice    = Element.select(el, '.validation-advice')[0];

        new Varien.DateElement('container', container, required, format);
    }
});


Enterprise.PhonenumbersUpdater =  Class.create();
Enterprise.PhonenumbersUpdater.prototype = {
    initialize: function (countryEl, phoneNumberEl, codesSelectEl, countryCodes, updateOnLoad)
    {
        this.countryEl = $(countryEl);
        this.phoneNumberEl = $(phoneNumberEl);
        this.countryCodesSelectEl = $(codesSelectEl);
        this.countryCodes = countryCodes;

        if (updateOnLoad){
            this.update();
            this.countryEl.changeUpdater = this.update.bind(this);
            Event.observe(this.countryEl, 'change', this.update.bind(this));
        }
    },

    update: function()
    {
        if (this.countryCodes[this.countryEl.value]) {
            
            this.countryCodesSelectEl.value = this.countryEl.value;
        }
    }
}

phonenumbersUpdater = Enterprise.PhonenumbersUpdater;


Enterprise.PhonenumberElement = Class.create();
Enterprise.PhonenumberElement.prototype = {
    initialize: function(type, content, required) {
        if (type == 'container') {
            // content must be container with data
            this.country_code    = content.country_code;
            this.phonenumber  = content.phonenumber;
            this.full   = content.full;
            this.advice = content.advice;
        } else {
            return;
        }

        this.required = required;

        this.country_code.addClassName('validate-custom');
        this.country_code.validate = this.validate.bind(this);
        this.phonenumber.addClassName('validate-custom');
        this.phonenumber.validate = this.validate.bind(this);

        this.advice.hide();
    },
    validate: function() {
        var error = false;
        if (this.country_code.value=='' && this.phonenumber.value=='') {
            if (this.required) {
                error = 'This phonenumber is a required value.';
            } else {
                this.full.value = '';
            }
        } else if (this.country_code.value=='' || this.phonenumber.value=='') {
            error = 'Please enter a valid full phonenumber.';
        } else {
            var index = this.country_code.selectedIndex;
            var code_value = index >= 0 ? this.country_code.options[index].innerHTML : undefined;
            var pattern=/\+\d+/;
            var result = (code_value.match(pattern));

            this.full.value = '('+result + ')' + this.phonenumber.value;
        }

        if (error !== false) {
            try {
                this.advice.innerHTML = Translator.translate(error);
            }
            catch (e) {
                this.advice.innerHTML = error;
            }
            this.advice.show();
            return false;
        }

        // fixing elements class
        this.country_code.removeClassName('validation-failed');
        this.phonenumber.removeClassName('validation-failed');

        this.advice.hide();
        return true;
    }
};

Enterprise.Phonenumber = Class.create();
Enterprise.Phonenumber.prototype = {
    initialize: function(selector, required) {
        var el = $$(selector)[0];
        var container       = {};
        container.country_code  = $('country-code');
        container.phonenumber   = $('phonenumber');
        container.full      = Element.select(el, '.phonenumber-full input')[0];
        container.advice    = Element.select(el, '.validation-advice')[0];
        
        new Enterprise.PhonenumberElement('container', container, required);
    }
};
*/
Enterprise.SwapContent = Class.create();
Enterprise.SwapContent.prototype = {
    initialize: function() {
        var current_products = $$('.active');
        current_products.each(function(item){
             var str=item.id;
             var pattern=/\d+/;
             var result = (str.match(pattern));
             var id = result[0];
             $('featured-category-'+id).up().up().addClassName('current');
        });
        this.arrActiveBlockContent = new Array();
        this.categoriesLinks = $$('.subcategories-list a');
        this.categoriesLinks.each(function(link){
             link.observe("mouseover", function() {
                 var str=link.id;
                 var pattern=/\d+/;
                 var result = (str.match(pattern));
                 var id = result[0];
                 var products = link.up(5).select('.featured-product-info');
                 products.each(function(item){
                  //item.hide();
                  item.removeClassName('active')
                 });
                 var cats = link.up(3).select('li');
                 cats.each(function(item){
                  item.removeClassName('current');
                 });
                 var product = $('featured-product-'+id);
                 if (product){
                     product.addClassName('active');
                 }
                 link.up().up().addClassName('current');

             }.bind(this));
        });
    }
};