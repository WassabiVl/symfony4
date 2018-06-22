(function() {
    "use strict";
    $(document).ready(function () {
        $('.accordionContent').slideUp(0);

        var slideSpeed = 400;
        $('.accordionToggle').on('click', function () {
            $('.accordionContent').slideUp(slideSpeed);
            if (!$(this).hasClass('open')) {
                $('.accordionToggle.open').removeClass('open');
                $(this).addClass('open');
                $(this).next('.accordionContent').first().slideDown(slideSpeed);
            }
            else {
                $('.accordionToggle.open').removeClass('open');
            }
        });
    });
})();

