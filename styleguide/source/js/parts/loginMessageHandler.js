(function() {
    "use strict";
    $('.menu-user.login .status-message').hide();
    var statusMessage = $('.menu-user.login .status-message').html();
    if(statusMessage !== undefined && statusMessage.indexOf('Login failure') !== -1){
        $('html').append('<div class="flash-message">'+statusMessage+'</div>');
        window.setTimeout(function(){
            $('.flash-message').css('opacity','0');
        },8000);
    }
})();