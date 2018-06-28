/**
 * Created by giese on 24.11.2017.
 */
"use strict";
$(document).ready(function(){
    var primary = $('.address-hide-double.primary-address');
    var secondary = $('.address-hide-double.secondary-address');
    if(primary.length && secondary.length){

        var allInputsPrimary = primary.find('input');
        var allInputsSecondary = secondary.find('input');
        var duplicateInput = false;
        var action = allInputsPrimary.on('input', function(){
            if(duplicateInput){
                $('#'+$(this).attr('id').replace(primary.attr('id'), secondary.attr('id'))).val($(this).val());
            }
        });
        secondary.parent().hide();
        primary.append('<div class="address-different-button">'+$('.address-hide-double.primary-address').data('same-address-text')+'<div class="different-address option-yes"><div class="radio-wrapper"></div>ja</div><div class="different-address option-no"><div class="radio-wrapper active"></div>nein</div></div>');
        $('.address-different-button').on('click',function(){
           // secondary.parent().show();
            // $(this).hide();
            duplicateInput = false;
            allInputsSecondary.each(function () {
              $(this).val('');
            });
        });
        $(".different-address.option-yes").on('click',function(){
            secondary.parent().show();
            $(this).children().addClass("active");
            $(".option-no").children().removeClass("active");
        });
         $(".different-address.option-no").on('click',function(){
             secondary.parent().hide();
             $(this).children().addClass("active");
             $(".option-yes").children().removeClass("active");
         });
    }

    /**
     * Address validation
     */
    addressValidation(window.location.pathname);

});


/**
 * addressValidation
 * Inserts the Google API, which loads the map and the onChange
 * for the address validations
 */

/**
 * @param currentPage
 */
function addressValidation(currentPage){
    console.log(currentPage);
    if(currentPage == "/setting/address/new" || currentPage.startsWith("/setting/address/edit/") || currentPage.startsWith("/register/")){
        console.log("Address validation loaded");

        //Put the GoogleAPI here
        var s = document.createElement("script");
        s.type = "text/javascript";
        s.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyCok4jZhx21rgKeCIhw096dGc0mwe0M6_4&callback=initMap";
        // s.attr("async");
        // s.attr("defer");
        $("head").append(s);

        // head.append("<script async defer src=\"https://maps.googleapis.com/maps/api/js?key=AIzaSyCok4jZhx21rgKeCIhw096dGc0mwe0M6_4&callback=initMap\"></script>");
    }

}


function initMap() {
    console.log("Google Maps script loaded");

    //Confirm if the address exists
    // var address = 'papito 2a Weimar';
    // getGeocodeAddress(address);


//Confirm if all the fields are filled out
    $(document).change(function(){
        // debugger;

        //Edit and New Address
        // var street = $("#new_address_embed_form_street");
        // var stnumber = $("#new_address_embed_form_buildingNumber");
        // var zip = $("#new_address_embed_form_zip");
        // var city = $("#new_address_embed_form_city");
        // var state = $("#new_address_embed_form_state");
        // var country = $("#new_address_embed_form_country");

        var street = $("input[id$='street']");
        var stnumber = $("input[id$='buildingNumber']");
        var zip = $("input[id$='zip']");
        var city = $("input[id$='city']");
        var state = $("input[id$='state']");
        var country = $("select[id$='country']");

        var fieldsReady = areFieldsFull(street, zip, city, country, stnumber, state);
        var addressStr;

        if(fieldsReady){
            enabledFields(false, street, zip, city, country, stnumber, state);

            addressStr = street.val() + " " + stnumber.val() + ", " + city.val() + " " + zip.val() + " "  + state.val() + ", " + country.val();
            console.log(addressStr);
            getGeocodeAddress(addressStr, street, zip, city, country, stnumber, state);
        }
    });

}


/**
 * areFieldsFull
 * Confirms if all the fields are full and ready to validate
 */

/**
 *
 * @param street
 * @param zip
 * @param city
 * @param country
 * @param stnumber
 * @param state
 * @returns {boolean}
 */
function areFieldsFull(street, zip, city, country, stnumber, state) {

    if(street.val() && street.val() !== ""
        && zip.val() && zip.val() !== ""
        && city.val() && city.val() !== ""
        && country.val() && country.val() !== ""
        && stnumber.val() && stnumber.val() !== ""
        && state.val() && state.val() !== ""){
        return true;
    }

    return false;
}


/**
 * getGeocodeAddress
 * Gets address
 */

/**
 *
 * @param address
 * @param street
 * @param zip
 * @param city
 * @param country
 * @param stnumber
 * @param state
 */
function getGeocodeAddress(address, street, zip, city, country, stnumber, state) {

    // Get geocoder instance
    var geocoder = new google.maps.Geocoder();

    //Do the google call
    geocoder.geocode({'address': address}, function(results, status) {

        console.log(status);
        if (status === google.maps.GeocoderStatus.OK && results.length > 0) {
            console.log(results);
        }else{
            console.log("Invalid address");
        }
        this.enabledFields(true, street, zip, city, country, stnumber, state);
        this.setFieldsByStatus(status, street, zip, city, country, stnumber, state);

    });
}

/**
 * enabledFields
 * Disables the fields if the validation is running;
 * enables the fields if the validation ran and lets the user edit the fields
 */

/**
 *
 * @param grant
 * @param street
 * @param zip
 * @param city
 * @param country
 * @param stnumber
 * @param state
 */
function enabledFields(grant, street, zip, city, country, stnumber, state){

    if(grant){
        street.removeAttr('disabled');
        stnumber.removeAttr('disabled');
        zip.removeAttr('disabled');
        city.removeAttr('disabled');
        state.removeAttr('disabled');
        country.removeAttr('disabled');
    }else{
        street.attr('disabled','disabled');
        stnumber.attr('disabled','disabled');
        zip.attr('disabled','disabled');
        city.attr('disabled','disabled');
        state.attr('disabled','disabled');
        country.attr('disabled','disabled');
    }
}

/**
 * setFieldsByStatus
 * Changes the colors after validation
 */
/**
 *
 * @param stat
 * @param street
 * @param zip
 * @param city
 * @param country
 * @param stnumber
 * @param state
 */
function setFieldsByStatus(stat, street, zip, city, country, stnumber, state){
    var border = "#ced4da";

    var alertmsg = $( '<div class="alert alert-warning alert-address"> <strong>Warning!</strong> Your address is not valid. Please fix the fields. </div>' );



    if(stat == "OK"){
        border = "#00FF00";
        if($(".alert-address").length > 0){
            $(".alert-address").remove();
        }
    }else if(stat == "ZERO_RESULTS"){
        border = "#FF0000";
        if($(".alert-address").length < 1){
            alertmsg.insertBefore( street );
        }
    }

    street.css("border", "1px solid " + border);
    stnumber.css("border", "1px solid " + border);
    zip.css("border", "1px solid " + border);
    city.css("border", "1px solid " + border);
    state.css("border", "1px solid " + border);
    country.css("border", "1px solid " + border);
}


/**
 * createJSON
 * Creates JSON with the data on click of the button Accept changes
 */
function createJSON(){
    if(window.location.pathname == "/super_admin/confirm") {
        var genJSON = [];

        $(".data_row").each(function (key, val) {
            var orderid = $(val).find("[id^=order_]").html().replace(/\s/g, "");
            var fixed = $(val).find("#fixed").html() == "Yes" ? true : false;
            var reject = $(val).find("[id^=checkbox_order_]").is(':checked');

            var obj = {};
            obj["orderID"] = orderid;
            obj["fixed"] = fixed;
            obj["reject"] = reject;

            genJSON.push(obj);
        });

        //In case you need to stringify from array to JSON, otherwise use the genJSON variable
        // var jsonString = JSON.stringify(genJSON);
        // console.log(jsonString);

        $.ajax({
            url: '/super_admin/json',
            type: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: genJSON,
            async: true,
            success: function (response) {
                console.log(response);
            },
            complete: function(XMLHttpRequest, status){
                // var res = $.parseJSON(XMLHttpRequest.responseText);
                console.log(status);
            }
        });
    }
}
