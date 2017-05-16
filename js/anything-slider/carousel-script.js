jQuery(function($){

    buildArrows = !(jQuery("ul#slider>li.panel").not(".cloned").size() == 1)


    jQuery("#slider").anythingSlider({
        showMultiple : 2,
        changeBy     : 1,
        buildNavigation : false,
        buildStartStop : false,
        resizeContents : false,
        easing : "linear",
        hashTags : false,
        buildArrows : buildArrows
    });



    jQuery("ul#slider>li.panel").each(function(){
        if( jQuery(this) ){

        }
    })
})