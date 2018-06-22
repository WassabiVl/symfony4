/**
 * For the My-Settings Page 
 */
(function() {
    "use strict";

    var checkField = 'data-validate';


    var checkcheckFields = ['input#password-current','input#password-new','input#password-confirmnew'];
    var submitIntercepts = ['#femanager_field_submit','.tx-lombego-jobs input[type=submit]'];







    // Exist all those checkFields?
    var checkFieldsExist = true;

    $(checkcheckFields).each(function(){
       if($(this).length === 0){
           checkFieldsExist = false;
       }
    });

    var requiredString = 'required,';
    function removeRequired(obj){
        var oldString = $(obj).attr(checkField);
        oldString = (oldString !== undefined) ? oldString : '';
        var startPos =  oldString.indexOf(requiredString);
        var length = requiredString.length;
        if(startPos !== -1){
            var newString = oldString.substr(0,startPos) + oldString.substr(startPos+length);
            $(obj).attr(checkField, newString);
        }
    }

    if(checkFieldsExist){
        $(checkcheckFields).each(function(){
            $(this).on('change', function () {
                // Check if one of the checkFields has a value so its required
                var filled = false;
                $(checkcheckFields).each(function(){
                    filled = (filled || $(this).val() !== '') ? true : false;
                });

                if (filled) {
                    $(checkcheckFields).each(function(){
                        // checkFields no longer empty
                        $(this).removeClass('refresh-after-empty');
                        // Remove old requireds
                        removeRequired(this);
                        var oldVal =  $(this).attr(checkField);
                        oldVal = (oldVal !== undefined) ? oldVal : '';
                        $(this).attr(checkField, requiredString + oldVal);
                    });
                }
                else {
                    $(checkcheckFields).each(function(){
                        removeRequired(this);
                    });
                }

                // If no checkFields has any input, retrigger it to remove the required checkField
                var checkFieldSet = false;
                $(checkcheckFields).each(function(){
                    checkFieldSet = (checkFieldSet || $(this).val().length > 0) ? true : false;
                });
                if(!checkFieldSet && $('.refresh-after-empty').length === 0){
                    $(checkcheckFields).each(function(){
                        $(this).addClass('refresh-after-empty');

                        $(this).trigger('change');

                    });
                }
            });
        });
    }

    function errorHandling(field, errorMessage){
        var error = window.translate(errorMessage);
        error = error.replace("%s",field.attr('data-field-name'));
        error = error.replace("%sAlt",field.attr('data-field-alt-name'));
        error = '<span>'+error+'</span>';
        var nextField = field.next();
        if(nextField.is('div.validate-error') && nextField.html().indexOf(error) === -1) {
            nextField.append(error);
        }
        else{
            $('<div class="validate-error">'+error+'</div>').insertAfter(field);
        }
        if(field.is('input') && field.is('input:not([type=checkbox])') && field.is('input:not([type=radio])')){
            field.addClass('error');
        }

    }

    function removeError(field){
        var errorField = field.next();
        if(field.is('input') && field.is('input:not([type=checkbox])') && field.is('input:not([type=radio])')){
            field.removeClass('error');
        }
        if(errorField.hasClass('validate-error')){
            errorField.remove();
        }
    }

    function validateCheckField(field){
        var cF = field.attr(checkField);
        var validates = (cF !== undefined) ? cF.split(',') : [];


        var ex = '', check = '', i = 0;




        // Special CheckField for requiredOneOf and sameAs
        if((cF !== undefined && cF.indexOf('requiredOneOf') !== -1)){
            check = cF.match(/requiredOneOf\(.*(?=\))/);
            check = check[0].substr(14);
            check = check.split(',');
            var isChecked = false;
            for(i=0;i<check.length;i++){
                if($(check[i]).is(':checked')){
                    isChecked = true;
                }
            }
            var last = $($(check).last()[0]).next();
            removeError(last);
            if(!isChecked){
                errorHandling(last, "ERROR_NOT_CHECKED");
            }
        }

        // First remove old Errors
        removeError(field);

        // Special Check for same as

        if((cF !== undefined && cF.indexOf('sameAs') !== -1)){
            check = cF.match(/sameAs\(.*(?=\))/);
            check = check[0].substr(7);
            check = check.split(',');
            var isSame = true;
            for(i=0;i<check.length;i++){
                if(field.val() !== $(check[i]).val()){
                    isSame = false;
                }
            }
            if(!isSame){
                errorHandling(field, "ERROR_DOESNT_MATCH");
            }
        }


        // Now handle if a new Error exist
        if(validates.length > 0 && validates[0] !== ''){
            if($.inArray('required', validates) !== -1 || field.val().length > 0){
                // Next checks it isn't empty
                if($.inArray('required', validates) !== -1 && field.val().length < 1){
                    console.log('require');
                    errorHandling(field, "ERROR_REQUIRED");
                }
                if($.inArray('password', validates) !== -1 && field.val().length < 8){
                    errorHandling(field, "ERROR_PASSWORD_LENGTH");
                }
                if($.inArray('email', validates) !== -1){
                    // Check Mail
                    ex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                    if(!ex.test(field.val())){
                        errorHandling(field, "ERROR_MAIL_INVALID");
                    }
                }
                if($.inArray('url', validates) !== -1){
                    ex = /^(http[s]?:\/\/){0,1}(www\.){0,1}[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,5}[\.]{0,1}/;
                    if(!ex.test(field.val())){
                        errorHandling(field, "ERROR_URL");
                    }
                }
                if($.inArray('facebook', validates) !== -1){
                    ex = /^(((http|https)+:){0,1}\/\/){0,1}([\d*\w*\-*]+\.)*facebook.com\/(?:(?:\w)*#!\/)?(?:pages\/)?(?:[?\w\-]*\/)?(?:profile.php\?id=(?=\d.*))?([\w\-]*)?$/;
                    if(!ex.test(field.val())){
                        errorHandling(field, "ERROR_FACEBOOK_URL");
                    }
                }
                if($.inArray('twitter', validates) !== -1){
                    ex = /^(((http|https)+:){0,1}\/\/){0,1}([\d*\w*\-*]+\.)*twitter\.com\/[-a-zA-Z0-9@:%._\+~#=]{1,512}/;
                    if(!ex.test(field.val())){
                        errorHandling(field, "ERROR_TWITTER_URL");
                    }
                }
                if($.inArray('linkedin', validates) !== -1){
                    ex = /^(((http|https)+:){0,1}\/\/){0,1}([\d*\w*\-*]+\.)*linkedin\.com\/[-a-zA-Z0-9@:%._\+~#=]{1,512}/;
                    if(!ex.test(field.val())){
                        errorHandling(field, "ERROR_LINKEDIN_URL");
                    }
                }
            }
        }

    }
    $(document).ready(function(){
        $('input, textarea').on('change blur', function(){
            validateCheckField($(this));
        });

        $(submitIntercepts).each(function(){
            $(this).on('click',function(e){
                e.preventDefault();
                // Ok run every input through the validate
                $('input, textarea').each(function(){
                    validateCheckField($(this));
                });
                // Now check if valid
                if($('.validate-error').length === 0){
                    console.log($(this).closest('form'));
                    $(this).closest('form').submit();
                }
                else{
                    // Scroll to first error
                    $('body').stop().animate({scrollTop : (($('.validate-error').first().offset().top)-200)}, 500);
                }
            });
        });
    });
})();