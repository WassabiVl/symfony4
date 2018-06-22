(function() {
    "use strict";
    $(document).ready(function () {
        $('.billingAddressContent').slideUp(0);
        var slideSpeed = 400;
        $('.billingAddressToggle').on('click', function () {
            $('.billingAddressContent').slideUp(slideSpeed);
            if (!$(this).hasClass('open')) {
                $('.billingAddressToggle.open').removeClass('open');
                $(this).addClass('open');
                $(this).next('.billingAddressContent').first().slideDown(slideSpeed);
            }
            else {
                $('.billingAddressToggle.open').removeClass('open');
            }
        });
    });
})();


