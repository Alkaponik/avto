/**
 * {magecore_license_notice}
 *
 * @category   MageCore
 * @package    Dashboard
 * @copyright  {magecore_copyright}
 * @license    {magecore_license}
 */
jQuery(document).ready(function() {

    jQuery("#dashboards").change(function () {
        location.href = jQuery(this).find("option:selected").val();
    })

    jQuery('<div id="detebar-drop-overlay"></div>').appendTo('body');
    jQuery('#date-range').click(function () {
        jQuery(this).parent('div.datebar').toggleClass('date-open');
        var overlayHeight = jQuery('body > .wrapper').height();
        var overlayWidth = jQuery('body > .wrapper').width();
        jQuery('#detebar-drop-overlay').width(overlayWidth).height(overlayHeight);
        jQuery('#detebar-drop-overlay').toggleClass('date-open-overlay');

    });
    jQuery('#detebar-drop-overlay, #datebar-cancel').click(function () {
        jQuery('div.datebar').removeClass('date-open');
        jQuery('.compare-range').css({'display':'none'})
        jQuery('#detebar-drop-overlay').removeClass('date-open-overlay');
    });
    jQuery('#print-image-now').click(function () {
        jQuery('#jqplot-image-container').printElement({
            printBodyOptions: {
                classNameToAdd : 'print-style'
            }
        });
    });
    jQuery("#daterange-select").change(function () {
        var dates = jQuery(this).val().split("#");
        jQuery("#from").val(dates[0]);
        jQuery("#to").val(dates[1]);
    })

    if (jQuery('#compare-form').attr('checked') == 'checked') {
        jQuery('.compare-range').css({'display':'block'})
    } else {
        jQuery('.compare-range').css({'display':'none'})
    }

    jQuery("#metric-print-container").dialog({
        autoOpen:false,
        width:800,
        resizable:false,
        draggable:false,
        modal:true,
        title:'Print Settings'
    });
    jQuery('#print-metric-cancel').click(function () {
        jQuery('#metric-print-container').dialog("close");
        jQuery('#metric-image-container').empty();
    });
    jQuery('#print-metric-now').click(function () {
        jQuery('#metric-image-container').printElement({
            printBodyOptions: {
                classNameToAdd : 'print-style'
            }
        });
    });

    jQuery('.customPrintThisPage').show();

    jQuery(window).resize(function () {
        replot();
        var tempWindowWidth = jQuery(this).width();
        var tempWindowHeight = jQuery(this).height();
        jQuery('.ui-widget-overlay').width(tempWindowWidth).height(tempWindowHeight);
        jQuery(".ui-dialog").position({
            my:"center",
            at:"center",
            of:window
        });
    });
    jQuery('.settings-detailse').dialog({
        autoOpen:false,
        //height: 300,
        width:560,
        resizable:false,
        draggable:false,
        modal:true,
        title:Translator.translate('Widget Settings')
    });
    jQuery('#settings-cancel').click(function () {
        jQuery('.settings-detailse').dialog("close");
    });

    jQuery("#tabs").tabs();

});

jQuery(window).load(function () {
    printImageGraff();
});

function printImageGraff() {
    if (!jQuery.jqplot.use_excanvas) {
        jQuery('div.jqplot-target').each(function () {
            jQuery("#jqplot-print-container").dialog({
                autoOpen:false,
                //height: 300,
                width: 800,
                resizable: false,
                draggable: false,
                modal: true,
                title: 'Print Settings'
            });
            jQuery('#print-image-cancel').click(function () {
                jQuery('#jqplot-print-container').dialog("close");
            });
            if (!jQuery.jqplot._noToImageButton) {
                var btn = jQuery(document.createElement('button'));
                btn.text('Create Image');
                btn.addClass('jqplot-image-button');
                btn.bind('click', {chart:jQuery(this)}, function (evt) {
                    var imgelem = evt.data.chart.jqplotToImageElem();
                    jQuery('#jqplot-image-container').empty();
                    jQuery(this).parents('.entry-edit-head').clone().prependTo(jQuery('#jqplot-image-container'));
                    jQuery('#jqplot-image-container').append(imgelem);
                    jQuery('#jqplot-print-container').dialog('open');
                    return false;
                });
                var addMyButton =jQuery(this).parents('.entry-edit').find('div.nav-tools span.settings');
                var addMyButton2 =jQuery(this).parents('.entry-edit').find('div.nav-tools');
                if (!jQuery(addMyButton2).find('.jqplot-image-button').size()){
                    jQuery(addMyButton).before(btn);
                }

                btn = null;
                addMyButton = null;
            }
        });
    }
}

function addWidget() {
    widgetId = '';
    /*Clear current widget values*/
    jQuery("#tabs :input").val('');
    /*Restore defaults for lines number and sectors number*/
    jQuery("#lines-number").val(jQuery("#lines-number option:last-child").val());
    jQuery("#sectors-number").val(jQuery("#sectors-number option:last-child").val());
    jQuery("#tabs").tabs("select", 0);
    jQuery("#save-widget span span span").html(Translator.translate("Add"));
    jQuery("#delete-widget").hide();
    jQuery(".settings-detailse").dialog("open");
    /*loadTabContent("timeline",false);
     loadTabContent("metric");*/
}
