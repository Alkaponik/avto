jQuery(function(){
    var raty = jQuery("#raty");
    var productID = raty.data("id")
    var cookieKey =  'product-raty-' + productID;

    raty.raty({
        score: function() {
            return  getCookie(cookieKey) === undefined ? jQuery(this).data('rating') : getCookie(cookieKey);
        },
        click: function(score, evt) {
            if(getCookie(cookieKey) === undefined) {
                setCookie(cookieKey, score);
                var votes = jQuery(this).data('votes') + 1;
                jQuery(this).closest(".ratings").find(".rating-links").html(votes + " голос(ов)");
                jQuery.ajax({
                    url : '?vote=1&is_ajax=1',
                    type : 'post',
                    data : {
                        productID : productID,
                        score : score
                    },
                    success : function(results) {
                    }
                })
            }
        },
        readOnly : function() {
            return getCookie(cookieKey) !== undefined;
        }
    });
});


function setCookie(name, value, options) {
    options = options || {};

    var expires = options.expires;

    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires*1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
        options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updatedCookie = name + "=" + value;

    for(var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];
        if (propValue !== true) {
            updatedCookie += "=" + propValue;
        }
    }

    document.cookie = updatedCookie;
}

// возвращает cookie с именем name, если есть, если нет, то undefined
function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
}
