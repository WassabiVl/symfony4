// (function () {
//     "use strict";
//     $(document).ready(function () {
//         $(".address-different-button").click(function(){
//             $(this).parent().next("td#reject").children("input").removeAttr("checked","checked");
//             $(this).prev("input").attr("checked","checked");
//             $(this).prev("input").toggleClass("fixed-selected");
//             if (!$(this).prev("input").hasClass("fixed-selected")){
//                 $(this).prev("input").removeAttr("checked","checked");
//             }
//         });
//         $("tr.data_row td#reject label").click(function(){
//             $(this).parent().prev("td#fixed").children("input").removeAttr("checked","checked");
//             $(this).prev("input").attr("checked","checked");
//             $(this).prev("input").toggleClass("rejected-selected");
//             if (!$(this).prev("input").hasClass("rejected-selected")){
//                 $(this).prev("input").removeAttr("checked","checked");
//             }
//         });
//     });
// })();