$("div[id^='page']").each(function (i, e) {
    var string = $(this).html().match(/[^\[img\]].+?(?=\[)/gm);
    if (string !== null) {
        var img = $('<img id="dynamic_"' + i + '>');
        img.attr('src', string[1]);
        img.appendTo(e);
        console.log(string[1], e)
    }
});