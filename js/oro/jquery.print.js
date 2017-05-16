/*
Blog Entry:
Ask Ben: Print Part Of A Web Page With jQuery

Author:
Ben Nadel / Kinky Solutions

Link:
http://www.bennadel.com/index.cfm?event=blog.view&id=1591

Date Posted:
May 21, 2009 at 9:10 PM

*/

jQuery.fn.print = function(){
    if (this.size() > 1){
    this.eq( 0 ).print();
    return;
    } else if (!this.size()){
    return;
    }
var strFrameName = ("printer-" + (new Date()).getTime());

var jFrame = jQuery( "<iframe name='" + strFrameName + "'>" );

jFrame
.css( "width", "1px" )
.css( "height", "1px" )
.css( "position", "absolute" )
.css( "left", "-9999px" )
.appendTo( jQuery( "body:first" ) )
;

// Get a FRAMES reference to the new frame.
var objFrame = window.frames[ strFrameName ];

// Get a reference to the DOM in the new frame.
var objDoc = objFrame.document;

// Grab all the style tags and copy to the new
// document so that we capture look and feel of
// the current document.

// Create a temp document DIV to hold the style tags.
// This is the only way I could find to get the style
// tags into IE.
var jStyleDiv = jQuery( "<div>" ).append(
    jQuery( "style" ).clone()
    );

    // Write the HTML for the document. In this, we will
    // write out the HTML of the current element.
    objDoc.open();
    objDoc.write( "<!DOCTYPE html>" );
        objDoc.write( "<html>" );
            objDoc.write( "<body>" );
                objDoc.write( "<head>" );
                    objDoc.write( "<title>" );
                        objDoc.write( document.title );
                        objDoc.write( "</title>" );
                    objDoc.write( jStyleDiv.html() );
                    objDoc.write( "</head>" );
                objDoc.write( this.html() );
                objDoc.write( " <script type='text/javascript'>replot();</script>" );
                objDoc.write( "</body>" );
            objDoc.write( "</html>" );
        objDoc.close();

        // Print the document.
        objFrame.focus();

        objFrame.print();

        // Have the frame remove itself in about a minute so that
        // we don't build up too many of these frames.
        setTimeout(
        function(){
            jFrame.remove();
            },
        (60 * 1000)
        );
        }