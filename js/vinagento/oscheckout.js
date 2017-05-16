var Onestepcheckout=Class.create();
Onestepcheckout.prototype={initialize:function(a){this.loadWaitingReview=this.loadWaitingPayment=this.loadWaitingShippingMethod=false;this.failureUrl=a.failure;this.reloadReviewUrl=a.reloadReview;this.reloadPaymentUrl=a.reloadPayment;this.successUrl=a.success;this.response=[]},ajaxFailure:function(){location.href=this.failureUrl},processRespone:function(a){var b;if(a&&a.responseText)try{b=a.responseText.evalJSON()}catch(c){b={}}if(b.redirect)location.href=b.redirect;else if(b.error)if(b.fields){a=
b.fields.split(",");for(var d=0;d<a.length;d++)null==$(a[d])&&Validation.ajaxError(null,b.error)}else alert(Translator.translate(b.error_messages));else{this.response=b;if(b.shippingMethod)this.updateShippingMethod();else if(b.payment){this.updatePayment();payment.initWhatIsCvvListeners()}else this.updateReview()}},setLoadWaitingShippingMethod:function(a){this.loadWaitingShippingMethod=a;if(a==true){$("onestepcheckout-shipping-method-ajax-loader")&&Element.show("onestepcheckout-shipping-method-ajax-loader");
$("checkout-shipping-method-load")&&Element.hide("checkout-shipping-method-load")}else{$("onestepcheckout-shipping-method-ajax-loader")&&Element.hide("onestepcheckout-shipping-method-ajax-loader");$("checkout-shipping-method-load")&&Element.show("checkout-shipping-method-load")}},resetLoadWaitingShippingMethod:function(){this.setLoadWaitingShippingMethod(false)},updateShippingMethod:function(){if($("checkout-shipping-method-load")){$("checkout-shipping-method-load").update(this.response.shippingMethod);
this.resetLoadWaitingShippingMethod();if($$("#checkout-shipping-method-load .no-display input").length!=0)$$("#checkout-shipping-method-load .no-display input")[0].checked==true&&shippingMethod.saveShippingMethod();else this.response.payment&&this.reloadPayment()}else this.response.payment&&this.reloadPayment()},setLoadWaitingPayment:function(a){this.loadWaitingPayment=a;if(a==true){Element.show("onestepcheckout-payment-ajax-loader");Element.hide("checkout-payment-method-load")}else{Element.hide("onestepcheckout-payment-ajax-loader");
Element.show("checkout-payment-method-load")}},resetLoadWaitingPayment:function(){this.setLoadWaitingPayment(false)},updatePayment:function(){$("checkout-payment-method-load").update(this.response.payment);this.resetLoadWaitingPayment();payment.switchMethod(payment.currentMethod);if($$("#checkout-payment-method-load .no-display input").length!=0)$$("#checkout-payment-method-load .no-display input")[0].checked==true&&payment.savePayment();else{var a=false;$$("#checkout-payment-method-load input").each(function(b){if(b.checked==
true)a=true});a==true?payment.savePayment():this.reloadReview()}},setLoadWaitingReview:function(a){this.loadWaitingReview=a;if(a==true){Element.show("onestepcheckout-review-ajax-loader");Element.hide("checkout-review-load")}else{Element.hide("onestepcheckout-review-ajax-loader");Element.show("checkout-review-load")}},resetLoadWaitingReview:function(){this.setLoadWaitingReview(false)},updateReview:function(){$("checkout-review-load").update(this.response.review);this.resetLoadWaitingReview();if(this.response.success)location.href=
this.successUrl},reloadReview:function(){this.setLoadWaitingReview(true);new Ajax.Request(this.reloadReviewUrl,{method:"post",onComplete:this.resetLoadWaitingReview,onSuccess:this.processRespone.bind(this),onFailure:this.ajaxFailure.bind(this)})},reloadPayment:function(){this.setLoadWaitingPayment(true);new Ajax.Request(this.reloadPaymentUrl,{method:"post",onComplete:this.resetLoadWaitingPayment,onSuccess:this.processRespone.bind(this),onFailure:this.ajaxFailure.bind(this)})},showOptionsList:function(a,
b){if(a){new Effect.toggle(b,"appear");new Effect.toggle(a.id,"appear");console.log(a.id.substring(0,10));if(a.id.substring(0,10)=="option-exp")new Effect.toggle("option-clo-"+a.id.substring(11));else new Effect.toggle("option-exp-"+a.id.substring(11))}}};var Login=Class.create();
Login.prototype={initialize:function(a,b,c){this.width=b;this.height=c;this.loginUrl=a;this.loadWaitingLogin=false;this.response=[]},show:function(){$("tool-tip-login").setStyle({opacity:0,visibility:"visible"});new Effect.Opacity("tool-tip-login",{duration:0.9,from:0,to:0.8});Element.show("tool-tip-login-form")},hide:function(){new Effect.Opacity("tool-tip-login",{duration:0.9,from:0.8,to:0,afterFinish:function(){$("tool-tip-login").setStyle({opacity:0,visibility:"hidden"})}});Element.hide("tool-tip-login-form")},
login:function(){var a=$("login-form");if((a=new Validation(a))&&a.validate()){a=$("login-email").value;var b=$("login-password").value;this.setLoadWaitingLogin(true);new Ajax.Request(this.loginUrl,{parameters:{username:a,password:b},method:"post",onComplete:this.resetLoadWaitingLogin,onSuccess:this.processRespone.bind(this),onFailure:onestepcheckout.ajaxFailure.bind(this)})}},processRespone:function(a){var b;if(a&&a.responseText)try{b=a.responseText.evalJSON()}catch(c){b={}}if(b.error){$("onestepcheckout-error-message").update(b.error);
this.resetLoadWaitingLogin()}else location.href=""},setLoadWaitingLogin:function(a){if(this.loadWaitingLogin=a){Element.show("onstepcheckout-login");Element.hide("onestepcheckout-login-form")}else{Element.hide("onstepcheckout-login");Element.show("onestepcheckout-login-form")}},resetLoadWaitingLogin:function(){this.setLoadWaitingLogin(false)}};var Billing=Class.create();
Billing.prototype={initialize:function(a,b,c,d){this.useBilling=a;this.saveCountryUrl=b;this.switchMethodUrl=c;this.addressUrl=d},enalbleShippingAddress:function(){this.setStepNumber();if($("billing:use_for_shipping_yes").checked==true){Element.show("shipping-address-form");this.useBilling=false;$("shipping-address-select")?shipping.setAddress($("shipping-address-select").value):shipping.saveCountry()}else{Element.hide("shipping-address-form");this.useBilling=true;this.saveCountry()}},saveCountry:function(){var a=
$("billing:country_id").value,b=$("billing:postcode").value;if(this.useBilling){onestepcheckout.setLoadWaitingShippingMethod(true);new Ajax.Request(this.saveCountryUrl,{parameters:{country_id:a,postcode:b,use_for:"billing"},method:"post",onComplete:onestepcheckout.resetLoadWaitingShippingMethod.bind(onestepcheckout),onSuccess:onestepcheckout.processRespone.bind(onestepcheckout),onFailure:onestepcheckout.ajaxFailure.bind(onestepcheckout)})}else{onestepcheckout.setLoadWaitingPayment(true);new Ajax.Request(this.saveCountryUrl,
{parameters:{country_id:a,postcode:b,use_for:"shipping"},method:"post",onComplete:onestepcheckout.resetLoadWaitingPayment.bind(onestepcheckout),onSuccess:onestepcheckout.processRespone.bind(onestepcheckout),onFailure:onestepcheckout.ajaxFailure.bind(onestepcheckout)})}},register:function(){var a="";if($("billing:register").checked==true&&$("billing:register").value==1){Element.show("register-customer-password");a="register"}else{Element.hide("register-customer-password");a="guest"}a&&new Ajax.Request(this.switchMethodUrl,
{parameters:{method:a},method:"post",onFailure:onestepcheckout.ajaxFailure.bind(onestepcheckout)})},setAddress:function(a){if(a)request=new Ajax.Request(this.addressUrl+a,{method:"get",onSuccess:this.fillForm.bindAsEventListener(this),onFailure:onestepcheckout.ajaxFailure.bind(onestepcheckout)})},newAddress:function(a){if(a){this.resetSelectedAddress();Element.show("billing-new-address-form")}else Element.hide("billing-new-address-form")},resetSelectedAddress:function(){var a=$("billing-address-select");
if(a)a.value=""},fillForm:function(a){var b={};if(a&&a.responseText)try{b=a.responseText.evalJSON()}catch(c){b={}}else this.resetSelectedAddress();arrElements=Form.getElements(review.form);for(var d in arrElements)if(arrElements[d].id){a=arrElements[d].id.replace(/^billing:/,"");if(b[a]!=undefined&&b[a])arrElements[d].value=b[a]}this.saveCountry()},setStepNumber:function(){steps=$$("#step-number");for(var a=0;a<steps.length;a++)if(steps[a].className!="step-1"&&steps[a].className!="step-review")if($("billing:use_for_shipping_yes").checked==
true){steps[a].className!="shipping"&&steps[a].removeClassName("step-"+a);steps[a].addClassName("step-"+(a+1))}else{steps[a].className!="step-2"&&steps[a].addClassName("step-"+a);steps[a].removeClassName("step-"+(a+1))}}};var Shipping=Class.create();
Shipping.prototype={initialize:function(a,b){this.saveCountryUrl=a;this.addressUrl=b},saveCountry:function(){if(billing.useBilling==false){var a=$("shipping:country_id").value,b=$("shipping:postcode").value;onestepcheckout.setLoadWaitingShippingMethod(true);new Ajax.Request(this.saveCountryUrl,{parameters:{country_id:a,postcode:b},method:"post",onComplete:onestepcheckout.resetLoadWaitingShippingMethod.bind(onestepcheckout),onSuccess:onestepcheckout.processRespone.bind(onestepcheckout),onFailure:onestepcheckout.ajaxFailure.bind(onestepcheckout)})}},
setAddress:function(a){if(a)request=new Ajax.Request(this.addressUrl+a,{method:"get",onSuccess:this.fillForm.bindAsEventListener(this),onFailure:onestepcheckout.ajaxFailure.bind(onestepcheckout)})},newAddress:function(a){if(a){this.resetSelectedAddress();Element.show("shipping-new-address-form")}else Element.hide("shipping-new-address-form");shipping.setSameAsBilling(false)},resetSelectedAddress:function(){var a=$("shipping-address-select");if(a)a.value=""},setSameAsBilling:function(a){($("shipping:same_as_billing").checked=
a)&&this.syncWithBilling()},syncWithBilling:function(){$("billing-address-select")&&this.newAddress(!$("billing-address-select").value);$("shipping:same_as_billing").checked=true;if(!$("billing-address-select")||!$("billing-address-select").value){arrElements=Form.getElements(review.form);for(var a in arrElements)if(arrElements[a].id){var b=$(arrElements[a].id.replace(/^shipping:/,"billing:"));if(b)arrElements[a].value=b.value}shippingRegionUpdater.update();$("shipping:region_id").value=$("billing:region_id").value;
$("shipping:region").value=$("billing:region").value}else $("shipping-address-select").value=$("billing-address-select").value},fillForm:function(a){var b={};if(a&&a.responseText)try{b=a.responseText.evalJSON()}catch(c){b={}}else this.resetSelectedAddress();arrElements=Form.getElements(review.form);for(var d in arrElements)if(arrElements[d].id){a=arrElements[d].id.replace(/^shipping:/,"");if(b[a]!=undefined&&b[a])arrElements[d].value=b[a]}this.saveCountry()},setRegionValue:function(){$("shipping:region").value=
$("billing:region").value}};var ShippingMethod=Class.create();
ShippingMethod.prototype={initialize:function(a,b){this.saveUrl=a;this.isReloadPayment=b},saveShippingMethod:function(){for(var a=document.getElementsByName("shipping_method"),b="",c=0;c<a.length;c++)if(a[c].checked)b=a[c].value;if(b!=""){this.isReloadPayment==1&&onestepcheckout.setLoadWaitingPayment(true);new Ajax.Request(this.saveUrl,{parameters:{shipping_method:b},method:"post",onComplete:onestepcheckout.resetLoadWaitingPayment.bind(onestepcheckout),onSuccess:onestepcheckout.processRespone.bind(onestepcheckout),
onFailure:onestepcheckout.ajaxFailure.bind(onestepcheckout)})}}};var Payment=Class.create();
Payment.prototype={beforeInitFunc:$H({}),afterInitFunc:$H({}),beforeValidateFunc:$H({}),afterValidateFunc:$H({}),initialize:function(a){this.saveUrl=a},init:function(){for(var a=$$("input[name^=payment]"),b=null,c=0;c<a.length;c++){if(a[c].name=="payment[method]"){if(a[c].checked)b=a[c].value}else a[c].disabled=true;a[c].setAttribute("autocomplete","off")}b&&this.switchMethod(b)},savePayment:function(){var a=document.getElementsByName("payment[method]");value="";for(var b=0;b<a.length;b++)if(a[b].checked)value=
a[b].value;if(value!=""){onestepcheckout.setLoadWaitingReview(true);new Ajax.Request(this.saveUrl,{parameters:{method:value},method:"post",onComplete:onestepcheckout.resetLoadWaitingReview.bind(onestepcheckout),onSuccess:onestepcheckout.processRespone.bind(onestepcheckout),onFailure:onestepcheckout.ajaxFailure.bind(onestepcheckout)})}},switchMethod:function(a){if(this.currentMethod&&$("payment_form_"+this.currentMethod)){var b=$("payment_form_"+this.currentMethod);b.hide();b=b.select("input","select",
"textarea");for(var c=0;c<b.length;c++)b[c].disabled=true}if($("payment_form_"+a)){b=$("payment_form_"+a);b.show();b=b.select("input","select","textarea");for(c=0;c<b.length;c++)b[c].disabled=false}else $(document.body).fire("payment-method:switched",{method_code:a});this.currentMethod=a},initWhatIsCvvListeners:function(){$$(".cvv-what-is-this").each(function(a){Event.observe(a,"click",toggleToolTip)})}};var Review=Class.create();
Review.prototype={initialize:function(a,b,c){this.form=a;this.saveUrl=b;this.agreementsForm=c;this.onestepcheckourForm=new VarienForm(this.form)},save:function(){if((new Validation(this.form)).validate()){onestepcheckout.setLoadWaitingReview(true);var a=Form.serialize(this.form);if(this.agreementsForm)a+="&"+Form.serialize(this.agreementsForm);a.save=true;new Ajax.Request(this.saveUrl,{method:"post",parameters:a,onComplete:onestepcheckout.resetLoadWaitingReview.bind(onestepcheckout),onSuccess:onestepcheckout.processRespone.bind(onestepcheckout),
onFailure:onestepcheckout.ajaxFailure.bind(onestepcheckout)})}}};var Agreements=Class.create();Agreements.prototype={initialize:function(a){this.duration=(11-a)*0.15},scrollShow:function(a){new Effect.toggle(a,"blind",{duration:this.duration})}};