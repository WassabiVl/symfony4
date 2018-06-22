

// Hide Header on on scroll down
var didScroll;
var lastScrollTop = 0;
var delta = 37;
var navbarHeight = $('.footer-gradient.mobile-version footer.footer-dashboard').outerHeight();

$(window).scroll(function(event){
    didScroll = true;
});

setInterval(function() {
    if (didScroll) {
        hasScrolled();
        didScroll = false;
    }
}, 250);

function hasScrolled() {
    var st = $(this).scrollTop();

    // Make sure they scroll more than delta
    if(Math.abs(lastScrollTop - st) <= delta)
        return;

    // If they scrolled down and are past the navbar, add class .nav-up.
    // This is necessary so you never see what is "behind" the navbar.
    if (st > lastScrollTop && st > navbarHeight){
        // Scroll Down
        // $('.footer-gradient.mobile-version footer.footer-dashboard').addClass('tabs-closed').removeClass('tabs-open');
        $('.footer-gradient.mobile-version footer.footer-dashboard').animate({
            top: 0
        }, {
            duration: 0,
            specialEasing: {
                top: "swing"
            },
            // complete: function() {
            //     $( this ).after( "<div>Animation complete.</div>" );
            // }
        });;
    } else {
        // Scroll Up
        if(st + $(window).height() < $(document).height()) {
            // $('.footer-gradient.mobile-version footer.footer-dashboard').removeClass('tabs-closed').addClass('tabs-open');

                $('.footer-gradient.mobile-version footer.footer-dashboard').animate({
                top: 133
            }, {
                duration: 0,
                specialEasing: {
                    top: "swing"
                },
                // complete: function() {
                //     $( this ).after( "<div>Animation complete.</div>" );
                // }
            });;
        }
    }

    lastScrollTop = st;
}




////////////////GOING UP AND DOWN//////////////////////////////
// (function () {
//     "use strict";
//     $(document).ready(function () {
//         if ($(window).width() <= 540) {
//             var lastScrollTop = 0;
//             var direction;
//             $(window).scroll(function (event) {
//
//
//                 if(lastScrollTop <  $(window).scrollTop()){
//                     lastScrollTop = $(window).scrollTop();
//                     direction = "goingDown";
//                 } else if (lastScrollTop >= $(window).scrollTop()){
//                     direction = "goingUp";
//                 }
//
//                     console.log(direction);
//                     var menuBarHeight = 523;
//                     var windowHeight = $(window).height();
//                     var scrolled = $(window).scrollTop();
//                 // var scrolled = $(window).scrollTop();
//                       if (direction == "goingUp") {
//                         // console.log('pa abajo');
//                         // console.log("scrolled from top" + scrolled.toString())
//                         // console.log("window Height?" + windowHeight.toString())
//
//                           $('.footer-gradient.mobile-version footer.footer-dashboard').addClass('tabs-hidden');
//                         $('.footer-gradient.mobile-version footer.footer-dashboard').removeClass('tabs-visible');
//                     } else {
//                         // console.log('pa arriba');
//                         $('.footer-gradient.mobile-version footer.footer-dashboard').addClass('tabs-visible');
//                         $('.footer-gradient.mobile-version footer.footer-dashboard').removeClass('tabs-hidden');
//                     }
//             });
//
//         }
//
//     });
// })();
