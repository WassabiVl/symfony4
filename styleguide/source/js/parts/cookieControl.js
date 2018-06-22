/**
 * Cookie Control (CC)
 */
(function() {
    "use strict";
    // Variables
    var cookieLifetime	= 30; //days
    var fadeSpeed		= 800; //ms
    var cookieName		= 'Cookie Control';
    var ccContainer		= $('#cookie-control');

    if (document.cookie.indexOf(cookieName) >= 0) {
        //ccContainer should be initially hidden
        ccContainer.hide();	//but for safety

    } else {
        ccContainer.fadeIn(fadeSpeed);
    }

// function for accept Cookies
    $('#cookie-accept').on('click', function() {
        setCookie(cookieName, 1, cookieLifetime);
        ccContainer.fadeOut(fadeSpeed);
        return false;
    });

    function setCookie(name, value, expiryDays, domain, path) {
        expiryDays = expiryDays || 365;

        var exdate = new Date();
        exdate.setDate(exdate.getDate() + expiryDays);

        var cookie = [
            name + '=' + value,
            'expires=' + exdate.toUTCString(),	//deprecated (for up to IE8)
            'max-age=' + expiryDays * 24 * 60 * 60,
            'path=' + (path || '/')
        ];

        if (domain) {
            cookie.push(
                'domain=' + domain
            );
        }

        document.cookie = cookie.join(';');
    }


})();
// check if cookie exist, if not show CC
