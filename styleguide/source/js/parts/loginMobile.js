(function () {
    "use strict";
    $(document).ready(function () {
        $(".authenticate-user-mobile").click(function(){
            $(this).toggleClass("open");
            $(".login-box").fadeToggle();
        });
    });
})();