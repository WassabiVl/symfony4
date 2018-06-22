/* global isSmartPhoneSize, isTabletSize, isHeigthLow */
(function() {
    "use strict";
    $(document).ready(function () {

        var nav = $('.menu');
        var navWrapper = $('.nav-wrapper');


        /**
         * ===============================================
         * Open/Close Menu
         * ===============================================
         */
        
        // Save scrollPos (iOS Hack)
        var scrollPos = 0;
        $('.menu-icon').click(function () {
            if ($(this).hasClass('close-menu')) {
                nav.removeClass('show');
                navWrapper.removeClass('show');
                $('html').removeClass('noscroll');
                $('body').removeClass('noscroll');
                $(this).removeClass('close-menu');
                if (isTabletSize() || isHeigthLow()) {
                    console.log(scrollPos);
                    $('html').scrollTop(scrollPos);
                }
            }
            else {
                nav.addClass('show');
                navWrapper.addClass('show');
                scrollPos = $('html').scrollTop();
                $('html').addClass('noscroll');
                $('body').addClass('noscroll');
                $(this).addClass('close-menu');
                if (isTabletSize() || isHeigthLow()) {
                    // For a smooth scroll
                    $('.nav-list').addClass('noscroll');
                }


            }

        });

        $(window).resize(function () {
            if ($('.menu-icon').hasClass('close-menu')) {
                if (isSmartPhoneSize() || isHeigthLow()) {
                    $('html').addClass('noscroll');
                    $('body').addClass('noscroll');
                    scrollPos = $('html').scrollTop();
                }
                else {
                    $('html').removeClass('noscroll');
                    $('body').removeClass('noscroll');
                    $('html').scrollTop(scrollPos);
                }
            }
        });

        // iOS Hack to avoid hover side-effects (also for other devices) in Menu
        var touchHack = document.addEventListener('touchstart', function () {
            $('.nav-effect').remove();
            touchHack = '';
        }, false);



        /**
         * ===============================================
         * Hide menu on scroll down and show again on scroll up
         * ===============================================
         */

        var scrollTop = 0;
        var moveMenuOff = 0;
        var safeHeightTop = 25; // Workaround to fix issue with iOS bounce
        var safeHeightBottom = 100; // Workaround to fix issue with iOS bounce
        var topPadding = 0;
        var menu = $('.nav-wrapper');
        var maxMenuHeight = $('.nav-wrapper.link-nav');
        // Hide menu on scroll
        $(document).scroll(function(){
            if(true){
                var _curScrollTop = $(document).scrollTop();
                if(!$('.nav-icon').hasClass('close-menu') || !isTabletSize()){
                    if(scrollTop !== _curScrollTop){
                        if(safeHeightTop < _curScrollTop && scrollTop < _curScrollTop && _curScrollTop !== 0){
                            if(moveMenuOff < maxMenuHeight.outerHeight()){
                                moveMenuOff += _curScrollTop-scrollTop;
                            }

                        }
                        else if(_curScrollTop === 0){
                            $('.head-bar-inner-wrapperr').css('transition','none');
                            moveMenuOff = 0;
                        }
                        else if(_curScrollTop > 0 && _curScrollTop+safeHeightBottom<($(document).height()-$(window).height())){
                            menu.css('transition','0.3s top ease-in-out');
                            moveMenuOff = 0;
                        }
                    }
                    if(topPadding > $(document).scrollTop()){
                        moveMenuOff = (topPadding-$(document).scrollTop());
                        menu.css('top',+moveMenuOff+'px');
                    }
                    else{

                        menu.css('top','-' + moveMenuOff+'px');
                        if($('.icon-search').parent().children('form').hasClass('active')){
                            $('.icon-search').trigger('click');
                        }
                    }

                    scrollTop = _curScrollTop;
                }
            }
            else{
                menu.css('top','auto');
            }
        });
        $(document).trigger('scroll');
        


        $('.drop-down').click(function(){
            $(this).parent().toggleClass('open');
        });
    });
})();