// (function () {
//     "use strict";
//     $(document).ready(function () {
//
// var svg1 = d3.selectAll("svg.today-graphic"),
//     width = svg1.attr("width"),
//     height = svg1.attr("height"),
//     radius = Math.min(width, height) / 2;
//
// var svg2 = d3.selectAll("svg.month-graphic"),
//     width = svg2.attr("width"),
//     height = svg2.attr("height"),
//     radius = Math.min(width, height) / 2;
//
//
// var g1 = svg1.append("g")
//     .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
//
// var g2 = svg2.append("g")
//     .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");
//
// var color = d3.scaleOrdinal([
//     '#3c5888', '#9eacc4'
// ]);
//
// var pie = d3.pie().value(function(d) {
//     return d.percent;
// });
//
// var path = d3.arc()
//     .outerRadius(radius - 10).innerRadius(0);
//
// var label = d3.arc()
//     .outerRadius(radius).innerRadius(radius - 80);
//
// var roothPath = document.location.hostname;
// d3.csv("../lombego/layout/data-graphics/production-stats-today.csv", function(error, data) {
//     if (error) {
//         throw error;
//     }
//
//     var today = g1.selectAll(".today")
//         .data(pie(data))
//         .enter()
//         .append("g")
//         .attr("class", "today");
//
//     today.append("path")
//         .attr("d", path)
//         .attr("fill", function(d) { return color(d.data.badgetoday); });
//
//     console.log(arc)
//
//         .text(function(d) { return d.data.badgetoday });
// });
//
// d3.csv("../lombego/layout/data-graphics/production-stats-month.csv", function(error, data) {
//     if (error) {
//         throw error;
//     }
//
//     var month = g2.selectAll(".month")
//         .data(pie(data))
//         .enter()
//         .append("g")
//         .attr("class", "month");
//
//     month.append("path")
//         .attr("d", path)
//         .attr("fill", function(d) { return color(d.data.badgemonth); });
//
//     console.log(arc)
//
//         .text(function(d) { return d.data.badgemonth; });
// });
//
//     });
// })();