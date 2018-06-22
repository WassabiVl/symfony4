(function() {
    "use strict";
    $(document).ready(function () {
        $('.color-switch-button').click(function(){
            $(this).parent().parent().removeClass();
            $(this).parent().parent().addClass('fragment '+$(this).attr('data-color'));

        });

    });

})();
