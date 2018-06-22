(function() {
    "use strict";
    $(document).ready(function(){
        var errorHeader = $('.error-header');

        errorHeader.each(function(){
            var par = $(this).parent();
           if($(this).text().length === 0){
               $(this).remove();
           }
           if(par.text().length === 0){
               par.remove();
           }

        });
    });
})();