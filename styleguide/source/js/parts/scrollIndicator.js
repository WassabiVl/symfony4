(function() {
    "use strict";
    $(document).ready(function () {
       $('.scroll-x-indicator').each(function(){
            var _this = $(this);
            var itemWrapper = $('#'+$(this).attr('data-rel-id-items'));
            var scrollItems = itemWrapper.children();
            var scrollContainer = $('#'+$(this).attr('data-rel-id-scroll-container'));
            var breakPoints = [];

           function calcBreakpoints(){
                breakPoints = [];
                scrollItems.each(function(){
                    var scrollBarSize = itemWrapper.width() - scrollContainer.width();
                    var relativeSize = ($(this).width() / itemWrapper.width()) * scrollBarSize;
                    var lastValue = breakPoints.slice(-1)[0] || 0;
                    breakPoints.push(relativeSize+lastValue);
                });
                breakPoints.pop();
           }
           function setActiveIndicator(n){
               _this.children().removeClass('active');
               $(_this.children()[n]).addClass('active');
           }
           function getActive(){
                for(var i = breakPoints.length ; i >= 0; i--){
                    if(i===0){
                        setActiveIndicator(i);
                    }
                    else if(breakPoints[i-1] <  scrollContainer.scrollLeft()){
                        setActiveIndicator(i);
                        break;
                    }
                }
            }
           calcBreakpoints();
            for(var i=0;i<scrollItems.length;i++){
                $(this).append('<div class="scroll-indicator-point" data-id="'+i+'"></div>');
            }
           $(this).children('.scroll-indicator-point').click(function(){
               var i = $(this).attr('data-id');
               if(parseInt(i)===0){
                   scrollContainer.animate({scrollLeft : '1'}, 500);
               }
               else{
                   var posLeft = ((scrollContainer.scrollLeft() + $(scrollItems[i]).offset().left) - (scrollContainer.outerWidth() - $(scrollItems[i]).width())/2);
                   scrollContainer.animate({scrollLeft : posLeft}, 500);
               }
           });
            getActive();
            scrollContainer.on('scroll',getActive);
                $(window).resize(function(){
                    calcBreakpoints();
                    getActive();
            });
       });
    });
})();