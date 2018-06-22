/**
 * Created by giese on 28.03.2017.
 */
(function() {
    "use strict";
    $(document).ready(function () {
        var animateElements = $('article, section');

        animateElements.each(function(){

            var maxRange =$(window).scrollTop()+$(window).height();
            animateElements.each(function(){
                if($(this).offset().top>maxRange){
                    $(this).addClass('hide');
                }
            });
        });

        $(window).scroll(function(){
            var maxRange =$(window).scrollTop()+$(window).height();
            animateElements.each(function(){
                if($(this).offset().top<maxRange){
                    $(this).removeClass('hide');
                }
            });
        });

    });
})();