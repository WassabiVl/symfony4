/* global isTabletSize */
(function() {
        "use strict";


        $(document).ready(function () {
            var headWrapper = $('.head-bar-image-wrapper');
            var headElements = headWrapper.children();


            headElements = headWrapper.children();
            var step = 0;
            if(headElements.length > 1){
                headElements = headElements.sort(function() { return 0.5 - Math.random(); });
            }
            $(headElements[0]).addClass('active');
            headElements.addClass('loaded');


            function iterateImage(){
                $(headElements[0]).removeClass('instant-active');
                var sizeOfElement = headElements.length;
                $(headElements[step++]).removeClass('active');
                step = (step % sizeOfElement === 0) ? 0 : step;
                $(headElements[step]).addClass('active');
            }

            // Are more than 1 Elements?
            if(headElements.length > 1){
                setInterval(iterateImage,9000);
            }
            var videoElement = headElements.find('video');
            function resizeVideo(){
                if(isTabletSize()){
                    $('.head-bar').height(videoElement.height()*0.8);
                    $('.head-bar-image-wrapper').height(videoElement.height()*0.8);
                }
            }
            if(videoElement.length > 0){
                resizeVideo();
                $(window).on('resize', resizeVideo);
            }

        });
    }
)();