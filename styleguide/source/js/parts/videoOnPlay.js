(function() {
    "use strict";
    $(document).ready(function () {
        $('.play-button').click(function () {
            var element = $($(this).parent().find('.youtube-video')[0]);
            var video = $(element.find('iframe')[0]);
            video.attr('src',video.attr('data-target-src'));
            element.show();
            element.on('click',function(){
               video.attr('src','');
                $(this).hide();
            });
        });
    });
})();