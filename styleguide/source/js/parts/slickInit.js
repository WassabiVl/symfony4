/* global isTabletSize */
(function() {
    "use strict";
    $(document).ready(function ($) {

        function slickResizeFunction(el){
            if(isTabletSize() &&! $(el).hasClass('slick-initialized')){
                $(el).slick({
                    slidesToShow: 1,
                    sildesToScroll: 1,
                    speed: 300,
                    variableWidth: false,
                    centerMode: true,
                    infinite: true,
                    centerPadding: '20px',
                    arrows: false,
                    dots: true,
                    responsive: [
                        {
                            breakpoint: 1440,
                            settings: {
                                slidesToShow: 1,
                                variableWidth: true,
                                centerMode: true,
                                arrows : false,
                                dots : true
                            }
                        }
                    ]
                });
            }
            else if(!isTabletSize()){
                if($(el).hasClass('slick-initialized')){
                    $(el).slick('unslick');
                    $(el).removeClass('slick-initialized');
                }
            }
        }

        function slickResizeDesktop(el, callback, parameter){
            if(isTabletSize()){
                if($(el).hasClass('slick-initialized')){
                    $(el).slick('unslick');
                    $(el).removeClass('slick-initialized');
                }
            }
            else{
                callback(parameter);
            }

        }

        $('.slick').each(function(){
           // var autoPlay = $(this).hasClass('autoplay');
            var arrows = $(this).hasClass('arrows');
            var dots = $(this).hasClass('dots');
            var noSwipe = $(this).hasClass('no-swipe');
            var elements = $(this).attr('data-slick-elements') || 1;
            var center = $(this).hasClass('slick-center');
            var focusable = $(this).hasClass('slick-focus');
            var variableWidth = $(this).hasClass('variable-width');
            var onlyMobile = $(this).hasClass('slick-only-mobile');
            var onlyDesktop = $(this).hasClass('slick-only-desktop');

            var notVarWidth = function(obj){
                obj.slick({
                    slidesToShow: elements,
                    sildesToScroll: 1,
                    speed: 300,
                    centerPadding: '20px',
                    arrows: arrows,
                    centerMode : center,
                    focusOnSelect: focusable,
                    dots: dots,
                    swipe: !noSwipe,
                    responsive: [
                        {
                            breakpoint: 1440,
                            settings: {
                                slidesToShow: 1,
                                variableWidth: true,
                                centerMode: true,
                                arrows : false,
                                dots : true
                            }
                        }
                    ]
                });
            };

            var withParameters = function(obj){
                obj.slick({
                    slidesToShow: 1,
                    sildesToScroll: 1,
                    speed: 300,
                    variableWidth: true,
                    centerMode: true,
                    infinite: true,
                    focusOnSelect: focusable,
                    centerPadding: '20px',
                    arrows: arrows,
                    dots: dots,
                    swipe: !noSwipe,
                    responsive: [
                        {
                            breakpoint: 1440,
                            settings: {
                                slidesToShow: 1,
                                arrows: false,
                                dots: true
                            }
                        }
                    ]
                });
            };

            var decideSlick = function(obj){
                if(!variableWidth){
                    notVarWidth(obj);
                }
                else{
                    withParameters(obj);
                }
            };


            if(onlyMobile){
                var el = $(this);
                $(window).on('resize',function(){
                    slickResizeFunction(el);
                });
                slickResizeFunction(el);


            }
            else if(onlyDesktop){
                var el = $(this);
                $(window).on('resize',function(){
                    slickResizeDesktop(el, decideSlick, el);
                });
                slickResizeDesktop(el, decideSlick, el);

            }
            else{
                console.log('decideSlick');
                decideSlick($(this));
            }

        });

    });

})(jQuery);
