Don't use cross-dependencies in this folder, for all basic functions use __basicFuntions.

alternative define them in __basicFunctions.js;

var yourFunctionName  = (typeof yourFunctionName === 'function') ? yourFunctionName : function(your,vars,here){window.setTimeout(function(){yourFunctionName(your,vars,here)},100)};

