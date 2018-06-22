(function () {
    "use strict";
    $(document).ready(function () {
        $('.clickable-with-content').click(function () {
            $(this).next('.button-popup-overlay').first().show();
        });
        $('.button-popup-overlay-content-close').click(function () {
            $($(this).parents('.button-popup-overlay')[0]).hide();
        });
        // $('.button-popup-overlay').click(function(){
        //     $(this).hide();
        // });
        // $('.button-popup-overlay-content').click(function(e){
        //     e.preventDefault();
        // });
        // $('#ServerSort').click(function(event){
        //     event.preventDefault(); //or return false;
        // });
    });
})();
