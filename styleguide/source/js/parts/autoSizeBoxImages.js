/* global isTabletSize,isSmartPhoneSize, isPhabletSize */
(function() {
    "use strict";
    $(document).ready(function () {
        function resizeImages(){
            $('.auto-size-image').each(function(){

                if(isSmartPhoneSize()){
                    $(this).height(150);
                }
                else if(isPhabletSize()){
                    $(this).height(250);
                }
                else if(isTabletSize()){
                    $(this).height(350);
                }
                else{
                    $(this).height(0);
                    $(this).height($(this).parents('.box').height());
                }

            });
        }
        $(window).on('resize', resizeImages);
        $('.auto-size-image').each(function(){

            if(isSmartPhoneSize()){
                $(this).height(150);
            }
            else if(isPhabletSize()){
                $(this).height(250);
            }
            else if(isTabletSize()){
                $(this).height(350);
            } else {
                $(this).height($(this).parents('.box').height());
            }

        });
    });
}
)();