/**
 * For the My-Settings Page 
 */
(function() {
    "use strict";
    $('.delete-button').each(function(){
       $(this).attr('data-del-link', $(this).attr('href'));
       $(this).removeAttr('href');
        $(this).on('click', function(e){
            e.preventDefault();
            e.stopPropagation();
            var deleteLink = $(this).attr('data-del-link');
            var htmlLayout = '<div class="confirm-window">';
            htmlLayout += '<div class="content">';
            htmlLayout += '<p>';
            htmlLayout += 'Are you sure to delete this?';
            htmlLayout += '</p>';
            htmlLayout += '<a class="button button-red" href="'+deleteLink+'">Yes, Delete</a> &nbsp;&nbsp;';
            htmlLayout += '<a class="button close">No, don\'t delete</a>';

            htmlLayout += '</div>';
            htmlLayout += '</div>';
            $('html').append(htmlLayout);
            $('.confirm-window .close').on('click', function(){
                $('.confirm-window').remove();
            });
        });
    });

})();