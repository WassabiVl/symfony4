(function () {
    "use strict";
    $(document).ready(function () {
        // $('.my-account-item').click(function () {
        //     $('ul.my-account-dropdown').toggleClass('show');
        // });
        $('body').click(function (event) {
            var myAccountClicked = ($(event.target).is('.username.clickable-hoverable') );
            if(myAccountClicked) {
                $('ul.my-account-dropdown').addClass('show');
            } else {
                $('ul.my-account-dropdown').removeClass('show');
            }
        });
    });
})();


