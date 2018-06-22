(function() {
    "use strict";
    $(document).ready(function () {

        if ($(window).width() <= 800) {

            $('.producer-statistics-slider').on('click', function () {
                $('.producer-d-stats').toggleClass('in-view');
                $('div.footer-gradient').toggleClass('in-view');
            });
        }
    });
})();