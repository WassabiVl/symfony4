(function () {
    "use strict";
    $(document).ready(function () {
        $('.addresses-button').click(function () {
            $(this).next('.addresses-popup-overlay').first().show();
        });
        $('.addresses-popup-overlay-content-close').click(function () {
            $($(this).parents('.addresses-popup-overlay')[0]).hide();
            $(".address-shipping-existing-select-form").show();
            $(".overlay-content-billing-module").show();
            $(".address-lightbox").hide();
            $("a.address-back").hide();
        });
        $('.addresses-popup-overlay').click(function(event){
            var lightboxBlack = $(event.target);
            if ( lightboxBlack.is(".addresses-popup-overlay") ) {
                lightboxBlack.hide();
            }
        });
        $('.addresses-popup-overlay-content').click(function(e){
            e.preventDefault();
        });

        // $(".overlay-content-shipping-module .address-item-select label").click(function(){
        //     $(".overlay-content-shipping-module .address-item-select input").removeAttr("checked","checked");
        //     $(this).prev("input").attr("checked","checked");
        // });
        // $(".overlay-content-billing-module .address-item-select label").click(function(){
        //     $(".overlay-content-billing-module .address-item-select input").removeAttr("checked","checked");
        //     $(this).prev("input").attr("checked","checked");
        // });
        $("a#address-shipping-see-all, a#address-billing-see-all, .icon-delete-address a").click(function(){
            // window.open($(this).attr("href"));
            window.location = $(this).attr("href");
        });
        $("a.address-new-shipping, a.address-new-billing, .icon-edit-address").click(function(){
            $(".address-shipping-existing-select-form").hide();
            $(".overlay-content-billing-module").hide();
            $("a.address-back").show();
        });
        // CHANGE FORM TYPE DEPENDING ON REQUEST
        $("a.address-new-shipping").click(function(){
            $(".address-lightbox.shipping-lightbox").show();
        });
        $(".icon-edit-address.icon-shipping").click(function(){
            $(".title-edit-shipping-address").show();
        });
        $("a.address-new-billing").click(function(){
            $(".address-lightbox.billing-lightbox").show();
        });
        $(".icon-edit-address.icon-billing").click(function(){
            $(".title-edit-billing-address").show();
        });
        // REMOVE BACK BUTTON ON INITIAL VIEW
        $("a.address-back").click(function(){
            $(".address-shipping-existing-select-form").show();
            $(".overlay-content-billing-module").show();
            $(".address-lightbox").hide();
            $(this).hide();
        });
        // $('form[name="new_address_embed_form"]').submit(function (e) {
        //     e.preventDefault();
        //     var formId = this.id;  // "this" is a reference to the submitted form
        // });
        // $('form[name="new_address_embed_form"] button[type=submit], button:submit').click(function(){
        //     $('form[name="new_address_embed_form"]').submit(function (e) {
        //         e.preventDefault();
        //         var formId = this.id;
        //         console.log("clicking form");
        //     });
        // });
    });
})();
