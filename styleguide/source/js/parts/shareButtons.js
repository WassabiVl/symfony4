;(function($){
    "use strict";
    /**
     * jQuery function to prevent default anchor event and take the href * and the title to make a share popup
     *
     * @param  {[object]} e           [Mouse event]
     * @param  {[integer]} intWidth   [Popup width defalut 500]
     * @param  {[integer]} intHeight  [Popup height defalut 400]
     * @param  {[boolean]} blnResize  [Is popup resizeabel default true]
     */
    $.fn.customerPopup = function (e, intWidth, intHeight, blnResize) {

        // Prevent default anchor event
        e.preventDefault();

        // Set values for window
        intWidth = intWidth || '500';
        intHeight = intHeight || '400';
        var strResize = (blnResize ? 'yes' : 'no');

        // Set title and open popup with focus on it
        var strTitle = ((typeof this.attr('title') !== 'undefined') ? this.attr('title') : 'Social Share'),
            strParam = 'width=' + intWidth + ',height=' + intHeight + ',resizable=' + strResize;
        window.open(this.data('src'), strTitle, strParam).focus();
    };

    /* ================================================== */

    $(document).ready(function ($) {
        $('.share-button').on("click", function(e) {
            $(this).customerPopup(e);
        });

        $('.expand-share').on("click",function(){
           $(this).toggleClass('expanded');
           if($(this).hasClass('expanded')){

               // Calculate all inner elements
               var widthInnerElements = 0;
               $(this).children().each( function(){
                  widthInnerElements += $(this).width();
               });
               widthInnerElements += 2*parseInt($(this).css('padding-right'),10);
               $(this).css('margin-left', '-' + parseInt(widthInnerElements,10) + "px");
           }
           else{
               $(this).css('margin-left', '');
           }
        });
    });


}(jQuery));