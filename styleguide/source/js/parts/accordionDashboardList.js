(function() {
    "use strict";
    $(document).ready(function () {

        if ($(window).width() <= 800) {

            $('.table-item-content').slideUp(0);

            $('.table-item.table-item-toggle').on('click', function () {
                $(this).toggleClass('table-text-label');
                $(this).siblings().fadeToggle();
            });

        }

    });
})();