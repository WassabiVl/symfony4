/**
 * ===============================================
 * Add's sourcelink on q-elements
 * ===============================================
 */
(function() {
    "use strict";
    $(document).ready(function () {
        $('q').each(function () {
            var insertString = '<span>&nbsp;(</span><a class="quote-link" href="' + $(this).attr('cite') + '">Quelle</a><span>)</span>';
            $(insertString).insertAfter($(this));
        });
    });
})();