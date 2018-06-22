(function() {
    "use strict";
    $(document).ready(function () {
        var obj = $('.top-jump');
        function scrollTop(){
            if($(window).scrollTop() >  $(window).height()){
                obj.fadeIn(500);
            }
            else{
                obj.fadeOut(500);
            }
        }
        obj.click(function(){$("html, body").animate({scrollTop:0},1000);});
        $(window).scroll(scrollTop);
    });

})();
