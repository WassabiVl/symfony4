"use strict";
/*jshint unused: true*/
/*exported isSmartPhoneSize, isTabletSize, isHeigthLow, LightenColor, isPhabletSize */


    /**
     * Functions for responsive
     *
     */
    var sBreakpoint =  480;
    var pBreakpoint =  640;
    var mBreakpoint =  1480;

    var heightBreakPoint =  820;

    var productData = productData;

    /**
     * Is screensize smartphone?
     * @returns {boolean}
     */

    var isSmartPhoneSize = function(){
        if(sBreakpoint >= window.innerWidth){
            return true;
        }
        return false;
    };

    /**
     * Is screensize phablet?
     * @returns {boolean}
     */
    var isPhabletSize = function(){
        if(pBreakpoint >= window.innerWidth){
            return true;
        }
        return false;
    };

    /**
     * Is screensize tablet?
     * @returns {boolean}
     */
    var isTabletSize = function(){
        if(mBreakpoint >= window.innerWidth){
            return true;
        }
        return false;
    };


    /**
     *
     */
    var isHeigthLow = function(){
        if(heightBreakPoint >= $(window).height()){
            return true;
        }
        return false;
    };

    /**
     * Language Pack
     */

    window.language = {
        "DE" : {
        },
        "EN" : {
            "ERROR_REQUIRED" : "The field %s is required.",
            "ERROR_PASSWORD_LENGTH" : "The field %s has a minimum size of 8.",
            "ERROR_MAIL_INVALID" : "The E-Mail you entered in field %s is invalid.",
            "ERROR_URL" : "The given URL in field is invalid.",
            "ERROR_LINKEDIN_URL" : "The LinkedIn URL is invalid.",
            "ERROR_FACEBOOK_URL" : "The Facebook URL is invalid.",
            "ERROR_TWITTER_URL" : "The Twitter URL is invalid.",
            "ERROR_DOESNT_MATCH" : "The field %s is not equivalent with %sAlt .",
            "ERROR_NOT_CHECKED" : "You have to check at least one."
        }
    };

    window.langDefault = "EN";
    window.langKey = "EN";


    window.translate = function(key){
        if(typeof window.langKey === 'undefined' || window.langKey === null || typeof window.language[window.langKey] === 'undefined'){
            console.log('langKey not set or invalid, fallback to default: ' + window.langDefault);
            window.langKey = window.langDefault;
        }

        var useLang = window.langKey;

        if(typeof window.language[window.langKey][key] === 'undefined'){
            console.log("Key doesn't exist in given language, fall back to default: "+ window.langDefault);
            useLang = window.langDefault;
        }

        if(window.language[useLang][key]){
            return window.language[useLang][key];
        }
        else{
            return key;
        }
    };
    /**
     *
     * @param color
     * @param percent Lighten the function by the value
     * @returns {string}
     * @constructor
     */
    /*jshint bitwise: false*/
    var LightenColor = function(color, percent) {
        var num = parseInt(color,16),
            amt = Math.round(2.55 * percent),
            R = (num >> 16) + amt,
            B = (num >> 8 & 0x00FF) + amt,
            G = (num & 0x0000FF) + amt;

        return (0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (B<255?B<1?0:B:255)*0x100 + (G<255?G<1?0:G:255)).toString(16).slice(1);
    };





