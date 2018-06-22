/*
global  isPhabletSize, isTabletSize
 */
(function() {
    "use strict";
    $(document).ready(function () {
         function resizeBox(){
                $('.autosize-box').each(function(){
                    var maxHeight = 0;
                    $(this).find('.box').each(function(){
                        $(this).css('height','auto');
                        $(this).css('min-height',$(this).height());
                        if($(this).height()>maxHeight){
                            maxHeight = $(this).height();
                        }
                    });
                if(!$(this).hasClass('not-mobile') || !isTabletSize()){
                    console.log('x');
                    $(this).find('.box').each(function(){
                        if(isPhabletSize()){
                            $(this).css('height','auto');
                        }
                        else{
                            $(this).height(0);
                            $(this).height(maxHeight);
                        }
                    });
                }
                else{
                    $(this).find('.box').each(function(){
                        $(this).css('min-height',0);
                    });
                }


            });
        }
        $(window).on('resize', resizeBox);
        resizeBox();
        $('.autosize-box').find("img")
            .on('load', function() { resizeBox(); });
        window.setTimeout(resizeBox, 200);
        $(window).load(function(){
            resizeBox();
        });
    });
}
)();