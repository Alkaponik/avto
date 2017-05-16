var AddToCartHandler = function (cartLayer, container, elements) {
        this.addToCartUrlRegexp = /(productID=|prdID=)/;
        this.layer = $(cartLayer);
        this.canClosePopup = true;
        this.popupContainer = $(container);
        this.isSecure = document.location.protocol.indexOf('https:') == 0;
        for (var key = 0; key < elements.length; key++) {
            this.addHandler(elements[key]);
        }
        this.layer.observe('click', this.hidePopup.bind(this));
    }

    AddToCartHandler.prototype = {
        addHandler:function (element) {
            if (element.tagName == 'FORM') {
                var button = element.down('button')
            } else if (element.tagName == 'BUTTON') {
                var button = element;
                element = null;
            } else {
                return;
            }
            if (typeof button != 'undefined' && button) {
                var url = this.getAddToCartUrl(element, button);
                if (url) {
                    button.onclick = null;
                    button.observe('click', this.buttonClickHandler.bind(this, element, url));
                }
                button.observe('mousedown', this.buttonMousedownHandler);
                button.observe('mouseout', this.buttonMouseupHandler);
            }
        },
        buttonClickHandler:function (form, url, event) {
            Event.stop(event);
            this.buttonMouseupHandler(event);
            this.canClosePopup = false;
            if ((this.isSecure && url.indexOf('https:') != 0)
                || (!this.isSecure && url.indexOf('https:') == 0)) {
                if (form) {
                    form.submit();
                } else {
                    document.location = url;
                }
            }
            var params = form
                ? form.serialize(true)
                : {};
            params.is_ajax = true;

            this.loadAreas(url, params);

            this.showPopup();
            return false;
        },
        showPopup:function () {
            this.popupContainer.update(this.layer.down('.loading').cloneNode(true));
            this.layer.show();
            this.popupContainer.show()
        },
        hidePopup:function (force) {
            force = (typeof force == 'undefined') ? null : force;
            if (this.canClosePopup || force) {
                this.layer.hide();
                this.popupContainer.hide();
            }
        },
        getAddToCartUrl:function (form, button) {
            var url = null;
            if (button.onclick)
            {
                var url = new RegExp("setLocation\\((\"|')([^\)]+)(\"|')\\)", "g").exec(button.onclick.toString());
            }
            if (url && typeof url[2] != undefined) {
                url = url[2];
            } else if (form) {
                url = form.action;
            } else {
                return null;
            }
            return this.addToCartUrlRegexp.test(url)
                ? url
                : false;
        },
        loadAreas:function (url, params) {
            new Ajax.Request(url, {
                'method':'post',
                'evalScripts':true,
                'parameters':params,
                'onComplete':function () {
                    this.canClosePopup = true;
                }.bind(this),
                'onSuccess':function (transport) {
                    var response = transport.responseText.evalJSON();
                    this.loadAreaResponseHandler(response);
                }.bind(this)
            });
        },

        loadAreaResponseHandler:function (response) {
            if (response.location) {
                setLocation(response.location);
                return;
            }
            if (response['#add-product-popup'] && this.popupContainer) {
                this.popupContainer.update(response['#add-product-popup']);
            }
            if (response['#block-cart'] && ($('block-cart'))) {
                $('block-cart').replace(response['#block-cart']);
            }
            var closeButton = this.popupContainer.down('button.close');
            if (closeButton) {
                closeButton.observe('click', this.hidePopup.bind(this))
            }
        },
        buttonMousedownHandler: function(event){
            var e = event.findElement();
            var container = e.up('div.btn-cart');
            if (container)
            {
                container.addClassName('active');
            }
        },
        buttonMouseupHandler: function(event){
            var e = event.findElement();
            var container = e.up('div.btn-cart');
            if (container)
            {
                container.removeClassName('active');
            }
        }
    }

/**
 * Callback request sending from #add-product-popup
 */

jQuery(function($)
{
    try {
        CallbackRequestForm = function(formSelector){
            this.formSelector = formSelector,
            this.form = jQuery(formSelector)
        };


        CallbackRequestForm.prototype =
        {
            /**
             * Инициализация объекта, установка слушателей событий
             */
            init : function( ) {
                this.phoneFilterInput(this.formSelector);
                this.formSubmitHandler();
            },

            /**
             * Обработка события клика по кнопке отправить телефон
             *
             */
            formSubmitHandler : function( ) {
                $this = this;
                formSelector = this.formSelector;
                $(document).on("click", formSelector + " .submit", function() {

                    try {
                        var productInfo = jQuery(this).closest(".content").find(".product-info-json").html();
                        productInfo = jQuery.parseJSON(productInfo);
                        var productName = productInfo.name;
                        var productURL = productInfo.url;

                        var phone = jQuery(formSelector).find(".phone").val();
                        var validation = jQuery(formSelector).find("input[name=validation]").val();
                        phone = $this.validatePhone(phone);

                        if( phone ) {
                            jQuery(formSelector).find("button,input[type=submit]").attr('disabled', 'disabled');
                            $this.sendCallbackRequest({
                                telephone : phone,
                                name : productInfo.customerName,
                                product_name : productName,
                                product_url : productURL,
                                checkout_cart: JSON.stringify(productInfo.shoppingCart),
                                validation: validation,
                                bucket : 1
                            });
                        }
                        else {
                            $this.showValidationErrors();
                        }
                    } catch(ex) {
                        console.log(ex.message);
                        alert(ex.message);
                    }
                    return false;
                });
            },

            /**
             * Phone input validation
             * @param formSelector
             */
            phoneFilterInput : function(formSelector) {
                $this = this;
                $(document).on("keyup", formSelector + " .phone", function() {
                    $this.hideValidationErrors();
                    var phone = $(this).val();
                    phone = $this.correctPhoneString(phone);
                    $(this).val(phone);
                })
            },

            /**
             * Telephone validation
             * @param phone
             * @returns {boolean}
             */
            validatePhone : function( phone ) {
                phone = this.correctPhoneString(phone);
                return /\d{10}/.test(phone) ? phone : false;
            },

            /**
             * Filters telephone input (leave digits only)
             * @param phone
             * @returns {string}
             */
            correctPhoneString : function(phone) {
                return phone
                    .replace(/[^0-9]/g,"")
                    .substr(0, 10);
            },

            showValidationErrors : function() {
                jQuery( this.formSelector ).find(".validation-error").show();
            },

            hideValidationErrors : function() {
                console.log("Validation error is hidden");
                jQuery( this.formSelector ).find(".validation-error").hide();
            },

            sendCallbackRequest : function( data ) {
                jQuery.ajax({
                    url : '/callbackrequest/index/send/is_ajax/1/',
                    type : 'post',
                    data : data,
                    success : function( results ) {
                        jQuery("#ajax-layer").click();
                    },
                    error : function( xhr, ajaxOptions, thrownError ) {
                        console.log( 'Ошибка отправки заявки на перезвон' +  xhr.responseText);
                    }
                });
            }

        }

        var form = new CallbackRequestForm("#callback-request-form");
        form.init();

    } catch( e ){
        console.log(e.message);
    }

});