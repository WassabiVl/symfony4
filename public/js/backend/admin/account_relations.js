(function($){
    $(document).ready(function(){
        function accountEmbedForms(){
            ($('#account_type').val() != "Producer") ? $('.account_embed_producer').hide() : $('.account_embed_producer').show();
            ($('#account_type').val() != "Carrier") ? $('.account_embed_carrier').hide() : $('.account_embed_carrier').show();
            ($('#account_type').val() != "Customer") ? $('.account_embed_customer').hide() : $('.account_embed_customer').show();
        }
        accountEmbedForms();
        if($('#edit-account-form, #new-account-form').length) {
            $('#account_type').on('change',accountEmbedForms);
        }
    });
}(jQuery));