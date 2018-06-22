(function () {
    "use strict";
    $(document).ready(function () {
        $('body').click(function (event) {
            var searchClicked = ($(event.target).is('li.dashboard-search-tool') || ($(event.target).is('li.dashboard-search-tool a')) || ($(event.target).is('li.dashboard-search-tool .search-wrapper-footer')) || ($(event.target).is('li.dashboard-search-tool input')));
            if(searchClicked) {
               $('input.footer-search-field').addClass('open');
               $('li.dashboard-search-tool').addClass('clicked');
            } else {
                $('input.footer-search-field').removeClass('open');
                $('li.dashboard-search-tool').removeClass('clicked');
            }
        });
        $('input.footer-search-field').click(function(){
            $('div.search-wrapper-footer').toggleClass('before-color');
        });
    });
})();
