/* global PhotoSwipeUI_Default, PhotoSwipe, mBreakpoint, sBreakpoint  */
(function() {
    "use strict";

    var pswpHtml = '<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true" style=""><div class="pswp__bg"></div><div class="pswp__scroll-wrap"><div class="pswp__container" style="transform: translate3d(0px, 0px, 0px);"><div class="pswp__item" style="display: block; transform: translate3d(-2131px, 0px, 0px);"></div><div class="pswp__item" style="transform: translate3d(0px, 0px, 0px);"><div class="pswp__zoom-wrap" style="transform: translate3d(652px, 262px, 0px) scale(0.507901);"><div class="pswp__img pswp__img--placeholder pswp__img--placeholder--blank" style="width: 1181px; height: 886px; display: none;"></div><img class="pswp__img" src="uploads/pics/fpo_4x3_02.png" style="display: block; width: 1181px; height: 886px;"></div></div><div class="pswp__item" style="display: block; transform: translate3d(2131px, 0px, 0px);"><div class="pswp__zoom-wrap" style="transform: translate3d(652px, 262px, 0px) scale(0.507901);"><img class="pswp__img" src="uploads/pics/fpo_4x3_03.png" style="opacity: 1; width: 1181px; height: 886px;"></div></div></div><div class="pswp__ui pswp__ui--fit pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter">1 / 2</div><button class="pswp__button pswp__button--close" title="Close (Esc)"></button><button class="pswp__button pswp__button--share" title="Share"></button><button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button><button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"><div class="pswp__share-tooltip"></div></div><button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button><button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div></div></div>';

    $(document).ready(function () {
        var galleryId = 0;
        var galleryItemPid = 0;
        $('.gallery-item').each(function(){
            if($('.pswpHtml').length === 0){
                $('body').append(pswpHtml);
            }
           // Check if grouped
            if(!$(this).parent().parent().hasClass('slick-cloned')) {
                var parent = $(this).parents('.grouped-gallery');
                if (parent.length) {
                    var parentGalleryId = $(parent).first().attr('data-gallery-id') ||
                        $($(parent).first().attr('data-gallery-id', galleryId++)).attr('data-gallery-id');
                    $(this).attr('data-relatedToGallery', parentGalleryId);
                }
            }
        });
        $('.gallery-item').on('click', function(){
            var relatedTo = $(this).attr('data-relatedToGallery') || null;

            var items = [];
            var _steps = 0;
            // Recheck if duplicated in slick.cloned
            $('.slick-cloned .gallery-item').each(function(){
                $(this).attr('data-relatedToGallery', '');
            });


            if(relatedTo !== null){
                $('[data-relatedToGallery="'+relatedTo+'"]').each(function(){
                    if(!$(this).parent().parent().hasClass('slick-cloned')) {
                        items.push({
                            src: $(this).attr('data-src'),
                            w: $(this).attr('data-width'),
                            h: $(this).attr('data-height'),
                            title: $(this).attr('title'),
                            pid: 'gallery-item-' + (galleryItemPid++)
                        });
                        $(this).attr('data-pos-at-gallery', _steps++);
                    }
                });
            }
            else{
                items = [{src:$(this).attr('data-src'), w: $(this).attr('data-width'), h: $(this).attr('data-height')}];
            }
            var pswpElement = document.querySelectorAll('.pswp')[0];

            // define options (if needed)
            var options = {
                // optionName: 'option value'
                // for example:
                galleryPIDs : true,
                shareButtons: [
                    {id:'facebook', label:'Teilen auf Facebook', url:'https://www.facebook.com/sharer/sharer.php?u={{url}}'},
                    {id:'twitter', label:'Tweet', url:'https://twitter.com/intent/tweet?text={{text}}&url={{url}}'},
                    {id:'pinterest', label:'Pin it', url:'http://www.pinterest.com/pin/create/button/?url={{url}}&media={{image_url}}&description={{text}}'},
                    {id:'download', label:'Bild herunterladen', url:'{{raw_image_url}}', download:true}
                ],
                index: parseInt($(this).attr('data-pos-at-gallery'),10) // start at first slide
            };

            // Initializes and opens PhotoSwipe ; Disable jshint for this case naming in PhotoSwipe is bad :(
            /*jshint camelcase: false */
            var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
            /*jshint camelcase: true */
            gallery.init();


        });

        $('.standalone-gallery').each(function(){
            var _autoplay = $(this).hasClass('autoplay');
            $(this).slick({
                centerMode: true,
                arrows: true,
                centerPadding: '40px',
                slidesToShow: 3,
                autoplay: _autoplay,
                infinite: true,
                responsive: [
                    {
                        breakpoint: mBreakpoint,
                        settings: {
                            arrows: false,
                            centerMode: true,
                            centerPadding: '20px',
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: sBreakpoint,
                        settings: {
                            arrows: false,
                            centerMode: true,
                            centerPadding: '20px',
                            slidesToShow: 2
                        }
                    }
                ]
            });
        });
    });

})();