(function() {
    "use strict";
    var numPatients = 0;
    var detailedObj = [];
    var optionsType = ['Patient', 'Pause', 'Meeting', 'Andere'];
    var mbqDefault = 350, mbqEvtDefault = 370;
    var amtRecommended = [2000, 4000, 6000, 8000, 10000, 12000, 14000, 16000];
    // var mbqChanged = {};

    $(document).ready(function () {

        $('.calculator-icon').click(function(){
            $('.calculator-layout-app').toggleClass('open');
        });

        $('.calculator-button').click(function(){
            animateResults("show");

            if($("div[id^='mbq_patient_total_txt']").length > 0){
                var lastItem = $("div[id^='mbq_patient_total_txt']").length;
                var getAmt = getRecommendedAmt($("#mbq_patient_total_txt" + lastItem).text());
                $("#calculator_total").html(getAmt);
            }else{
                $("#calculator_total").html("0");
            }
        });

        $('.add-event-trigger').click(function(){
            $('.add-event-unfold a.button.event-confirm').fadeToggle();
            $('.add-event-unfold').slideToggle();
            $(this).toggleClass('open');
        });

        $("#createBaseSchedule").on("click", createList);
        $("#event_confirm").on("click", addNewEvent);


        //On New Event only
        $("#new_event_type").on("change", function () {
            if(this.value != "Patient"){
                $("#new_event_mbq").css('visibility', 'hidden');
            }else{
                $("#new_event_mbq").css('visibility', 'visible');
            }
        });

    });

    function animateResults(shouldShow) {
        if(shouldShow == "show"){
            $('.calculator-results').fadeIn();
        }else if(shouldShow == "hide"){
            $('.calculator-results').fadeOut();
        }

        $('html, body').animate({
            scrollTop: $('html, body').height()
        }, 'slow');
    }

    function createList(){
        // resetObj();
        if(detailedObj.length < 1){
            isCalculatorBottomOpen();
            console.log("creating object");
            resetObj();
        }else{
            console.log("object not empty; regenerating new data");

            animateResults("hide");
            // updateObjNew(detailedObj);
            resetObj();
        }
    }

    //Clear and resets object
    function resetObj() {
        clearObjArray(detailedObj);
        animateResults("hide");
        generateFirstData(0, detailedObj);
        updateUIObj();
        console.log(detailedObj);
    }

    //Gets the recommended value for the total
    function getRecommendedAmt(roughVal) {
        //Round the amount
        var roundVal = (Math.ceil(parseInt(roughVal)/1000))*1000;
        for(var x = 0; x < amtRecommended.length; x++){
            //Compare it to the nearest top value in the options we have
            if(roundVal > amtRecommended[x] && roundVal <= amtRecommended[x + 1]){
                roundVal =  amtRecommended[x + 1];
            }
        }
        //Return the rounded value (that changed in the loop
        return roundVal;
    }

    function updateObjNew(oldObj){
        var totalPatients = $("#patient-quantity option:selected" ).text().match(/\d/g).join("");

        if(totalPatients < oldObj.length){
            console.log("total patients " + totalPatients + " < " + oldObj.length);
            //cut the obj, recalculate, and update it
            cutObj(totalPatients);
            updateUIObj();
        }else if(totalPatients > oldObj.length){
            console.log("total patients " + totalPatients + " > " + oldObj.length);
            //retain the object, add rows, recalculate, update
            recalculateOldObj(totalPatients);
            updateUIObj();
        }else if(totalPatients == oldObj.length){
            //retain the object, recalculate, update
            console.log("total patients " + totalPatients + " === " + oldObj.length);
            updateUIObj();
        }
    }

    //Cut object if the number of events are less than the current object
    function cutObj(limit) {
        for (var i = detailedObj.length; i > limit; i--) {
            detailedObj.pop();
        }
    }

    function updateUIObj() {

        $(".results-content-row").remove();
        detailedObj.forEach(function (t, key) {
            generatePatientItem(t.type, t.pat, t.startTime, t.mbqPatient, t.prod, t.totalProd);
        });
    }

    //Populate the detailedObj for the first time
    function generateFirstData(stPos, mainObj){
        numPatients = $("#patient-quantity option:selected" ).text().match(/\d/g).join("");
        //Default values: (a) Ln(2); (b) Constant
        var ln2 = Math.LN2;
        var konst = 109.74;
        var pos = stPos;
        // var time;
        var  interval =$("#intervals-patients").val();
        var arrTime = $("#ordered_product_category_form_relatedOrder_targetTime_time_hour option:selected" ).val();
        //Get Values: (c) Number of Patients; (d) Interval; (e) Time; (f) Dosage; (g) Time Started; (h) Pickup Time; (i) Arrival Time
        var mbqPatient = mbqDefault;
        //Calculate values: (j) Minutes Passed; (k) Product; (l) Total Product [so far, in total]; (k) cumulative product
        var minsPassed = 0, prod = 0, totalProd = 0, addProd = 0;
        var listLim = numPatients;

        for(pos; pos < listLim; pos++){
            minsPassed += (pos == 0) ? 0 : parseInt(interval);
            //The exponent for the number of patients
            var exp = Math.exp((minsPassed/konst) * ln2);
            prod = Math.round(mbqPatient * exp);
            totalProd += Math.round(prod/ 10) * 10;
            totalProd = (totalProd >= 5000) ? Math.round(totalProd/100) *100 : totalProd;
            // time = getTimeFormat(minsPassed);
            // console.log("time: " + time);
            var pat = pos + 1;
            var startTime = calculateStartingTime(arrTime, minsPassed);
            var simpleItem = {};

            simpleItem._id = pos;
            simpleItem.type = optionsType[0]; // default: 'Patient';
            simpleItem.pat = pat;
            simpleItem.minsPassed = minsPassed;
            simpleItem.startTime = startTime;
            simpleItem.mbqPatient = mbqPatient;
            simpleItem.prod = prod;
            simpleItem.totalProd = totalProd;

            mainObj.push(simpleItem);
        }
    }

    //Recalculate the updated object
    function recalculateOldObj(numPatients) {
        var newObj = [];
        var arrTime = $("#ordered_product_category_form_relatedOrder_targetTime_time_hour option:selected" ).val();
        //Default values: (a) Ln(2); (b) Constant; (c) Interval; (d) numPatients
        var ln2 = Math.log(2);
        var konst = 109.74;
        var interval = 0;
        // var numPatients = detailedObj.length;
        numPatients = typeof numPatients !== 'undefined' ? numPatients : detailedObj.length;
        //Calculate values: (j) Minutes Passed; (k) Product; (l) Total Product [so far, in total]; (k) cumulative product
        var minsPassed = 0, prod = 0, totalProd = 0, addProd = 0;
        var startTime, mbqPatient, evtType;


        for(var pos = 0; pos < numPatients; pos++){
            //Verify the nulls! Just if we're adding more patients
            var ObjExists = typeof detailedObj[pos] !== 'undefined';

            if(ObjExists){
                interval = calculateMinsDifference(arrTime, detailedObj[pos].startTime);
                mbqPatient = detailedObj[pos].mbqPatient;
                startTime =  detailedObj[pos].startTime;
                evtType =  detailedObj[pos].type;

            }else{
                interval = parseInt(calculateMinsDifference(arrTime, newObj[pos - 1].startTime)) + parseInt($("#intervals-patients").val());
                mbqPatient = parseInt(mbqDefault);
                startTime = calculateStartingTime(arrTime, interval);
                evtType =  optionsType[0];//default: Patient
            }

            minsPassed = parseInt(interval);

            //The exponent for the number of patients
            var exp = Math.exp((minsPassed/konst) * ln2);
            prod = Math.round(mbqPatient * exp);
            totalProd += Math.round(prod/ 10) * 10;
            totalProd = (totalProd >= 5000) ? Math.round(totalProd/100) *100 : totalProd;
            var pat = pos + 1;
            var simpleItem = {};
            simpleItem._id = pos;
            simpleItem.pat = pat;
            simpleItem.type = evtType; // default: 'Patient';
            simpleItem.minsPassed = minsPassed;
            simpleItem.startTime = startTime;
            simpleItem.mbqPatient = mbqPatient;
            simpleItem.prod = prod;
            simpleItem.totalProd = totalProd;

            newObj.push(simpleItem);
        }

        clearObjArray(detailedObj);
        detailedObj = newObj;
    }

    //Render the divs with each event
    function generatePatientItem(type, item, time, mbqPatient, MbqHL, totalMbq){

        var rowItem = "<div class='results-row results-content results-content-row' id='results_row_"+item+"'></div>";
        var itemNumber = "<div class='results-cell results-cell-patientnum'><label for='event_type"+item+"'>";
        itemNumber += "<select id='event_type_"+item+"' name='event_type_"+item+"'>";

        //Pass the options from the optionsType global array
        optionsType.forEach(function (opt) {
            itemNumber += "<option value='"+ opt +"'>"+ opt +"</option>";
        });

        itemNumber += "</select></label></div>";
        var itemTime = "<div class='results-cell results-cell-time'><label for='event_time"+item+"'> <input type='time' id='event_time_"+item+"' value='"+ time +"'></label></div>";
        var itemMbq = "<div class='results-cell results-cell-patientmbq'><input type='number' id='mbq_patient_"+item+"' value='"+mbqPatient+"'/></div>";
        var itemMbqHL = "<div class='results-cell results-cell-patientmbqhl' id='mbq_patient_txt_"+item+"'>"+ MbqHL +"</div>";
        var itemTotalMbq = "<div class='results-cell results-cell-totalmbq' id='mbq_patient_total_txt"+item+"'>"+ totalMbq +"</div>";

        $( ".results-header" ).append($(rowItem));
        $("#results_row_" + item).append($(itemNumber));

        optionsType.forEach(function (val) {
            $("#event_type_" + item + " option[value='"+ type + "']").attr('selected','selected');
        });

        $("#results_row_" + item).append($(itemTime));
        $("#results_row_" + item).append($(itemMbq));
        $("#results_row_" + item).append($(itemMbqHL));
        $("#results_row_" + item).append($(itemTotalMbq));

        //On change of MBQ
        $("#mbq_patient_" + item).on("change",onMBQChange);
        //On change of time
        $("#event_time_" + item).change(onTimeChange);
        //On change of event type (patient, meeting, pause, andere)
        $("#event_type_" + item).on("change", onEventTypeChange);

        //If the type is not patient, hide it
        if(type != optionsType[0]){
            $("#mbq_patient_" + item).css('visibility', 'hidden');
            $("#mbq_patient_txt_" + item).css('visibility', 'hidden');
        }else{
            $("#mbq_patient_" + item).css('visibility', 'visible');
            $("#mbq_patient_txt_" + item).css('visibility', 'visible');
        }

        //Open the calculator div IF the detailedObj is full
        isCalculatorBottomOpen();
    }

    function isCalculatorBottomOpen(){
        if(detailedObj.length < 1){
            $(".calculator-bottom").addClass("open");
        }
    }

    function addNewEvent(evt) {
        animateResults("hide");
        //event_confirm
        var newEvt = {};
        var lastID = detailedObj[detailedObj.length - 1]._id;
        var lastpat = detailedObj[detailedObj.length - 1].pat;
        var arrTime = $("#ordered_product_category_form_relatedOrder_targetTime_time_hour option:selected" ).val();
        var currentTime = $("#new_event_time").val();
        var minsPassed = calculateMinsDifference(arrTime, currentTime);
        var defaultMbqVal;

        if($("#new_event_type").val() != "Patient"){
            defaultMbqVal = 370;
        }else{
            defaultMbqVal = parseInt($("#new_event_mbq").val());
        }

        newEvt._id = lastID + 1;
        newEvt.type = $("#new_event_type").val(); // default: 'Patient';
        newEvt.pat = lastpat + 1;
        newEvt.startTime = currentTime;
        newEvt.minsPassed = minsPassed;
        newEvt.mbqPatient = defaultMbqVal;
        detailedObj.unshift(newEvt);
        //Recalculate and Fix the object
        refactorObj();
        console.log(detailedObj);
    }

    //Confirm and fix duplicate time
    function fixDuplicateTime() {
        var duplicateTime = false;
        var arrTime = $("#ordered_product_category_form_relatedOrder_targetTime_time_hour option:selected" ).val();

        for(var ix = 0; ix < detailedObj.length; ix++){
            var limit = ix + 1;
            var withinBounds = limit < detailedObj.length && limit > 0;
            if(withinBounds && (detailedObj[ix].minsPassed == detailedObj[limit].minsPassed)){
                // console.log("EQUAL AT POSITION " + ix);
                duplicateTime = true;
            }

            if(duplicateTime && withinBounds){
                detailedObj[limit].minsPassed += 30;
                detailedObj[limit].startTime = calculateStartingTime(arrTime, detailedObj[limit].minsPassed);
                duplicateTime = false;
            }
        }
    }

    function onTimeChange(evt) {
        animateResults("hide");
        $("#calculator-wait").css("display", "block");
        $(".calculator-wrapper").css("cursor","wait");
        window.setTimeout(function(){
            $("#calculator-wait").css("display", "none");
            $(".calculator-wrapper").css("cursor","normal");
            var targetId = evt.target.id;
            var targetIdNum = targetId.toString().match(/\d+$/);
            var targetSel = $("#" + targetId);
            var currTime = targetSel.val();
            var arrTime = $("#ordered_product_category_form_relatedOrder_targetTime_time_hour option:selected" ).val();

            detailedObj.forEach(function (val,key) {
                if(val.pat == targetIdNum){
                    //update time
                    val.startTime = currTime;
                    //update minutes passed
                    val.minsPassed = calculateMinsDifference(arrTime, currTime);
                }
            });

            //Recalculate and Fix the object
            refactorObj();
        }, 2000);
    }

    function onMBQChange(evt) {
        animateResults("hide");
        $("#calculator-wait").css("display", "block");
        window.setTimeout(function(){
            $("#calculator-wait").css("display", "none");

            var targetId = evt.target.id;
            var targetIdNum = targetId.toString().match(/\d+$/);
            var targetSel = $("#" + targetId);
            var currMBQ = targetSel.val();

            console.log("targetId: " + targetId);
            //
            detailedObj.forEach(function (val,key) {
                if(val.pat == targetIdNum){
                    //update patient mbq
                    val.mbqPatient = currMBQ;
                }
            });

            //Recalculate and Fix the object
            refactorObj();
        }, 2000);
    }

    function onEventTypeChange(evt) {
        animateResults("hide");
        $("#calculator-wait").css("display", "block");
        window.setTimeout(function(){
            $("#calculator-wait").css("display", "none");
            var targetId = evt.target.id;
            var targetIdNum = targetId.toString().match(/\d+$/);
            var targetSel = $("#" + targetId);
            var currEvt = targetSel.val();

            detailedObj.forEach(function (val,key) {
                if(val.pat == targetIdNum){
                    if(currEvt !== "Patient"){
                        val.mbqPatient = mbqEvtDefault;
                        val.type = currEvt;
                    }else{
                        val.type = "Patient";
                    }
                }
            });

            //Recalculate and Fix the object
            refactorObj();

            //Hide the Patient MBq and Patient MBq+HL boxes if the type is not patient
            if(currEvt !== "Patient"){
                $("#mbq_patient_" + targetIdNum).css('visibility', 'hidden');
                $("#mbq_patient_txt_" + targetIdNum).css('visibility', 'hidden');
            }else{
                $("#mbq_patient_" + targetIdNum).css('visibility', 'visible');
                $("#mbq_patient_txt_" + targetIdNum).css('visibility', 'visible');
            }

        }, 2000);
    }

    //Rearrange obj with bubble sort
    function rearrangeObj(a, par){
        var swapped;
        do {
            swapped = false;
            for (var i = 0; i < a.length - 1; i++) {
                if (a[i][par] > a[i + 1][par]) {
                    var temp = a[i];
                    a[i] = a[i + 1];
                    a[i + 1] = temp;
                    swapped = true;
                }
            }
        } while (swapped);
    }

    //Organize, fix, and recalculate object
    function refactorObj() {
        //Organize object
        rearrangeObj(detailedObj, 'minsPassed');
        //Confirm if there is no duplicate, and fix if there is
        fixDuplicateTime();
        //Recalculate Obj
        recalculateOldObj();
        //update UI
        updateUIObj();
    }

    //Format time based on minutes
    function getTimeFormat(mins){
        var hr = mins/60;
        var m = hr - Math.floor(hr);
        var fhr = (Math.floor(hr) < 10) ? "0"+Math.floor(hr) : Math.floor(hr);
        var fm1 = (m > 0 && m < 1) ? (m*60) : m;
        var fm2 = (Math.floor(fm1) < 10) ? "0"+Math.floor(fm1) : Math.floor(fm1);
        var ts = fhr + ":" + fm2;

        return ts;
    }

    //Convert from hour&minutes to minutes
    function getTimeInMinutes(time) {
        var hour = time.match(/^\d+/);
        var minutes = time.match(/\d+$/);
        var hrToMins = parseInt(hour) * 60;
        var mins = parseInt(minutes);
        var totalMinutes = hrToMins + mins;

        return totalMinutes;
    }

    //add minutes to starting hour based on minsPassed
    function calculateStartingTime(startHr, mins){
        return getTimeFormat(parseInt(startHr)*60 + mins);
    }

    //Calculate how many minutes passed if time has changed/was asked
    function calculateMinsDifference(arrTime, currentTime) {
        // console.log("arrTime: " +arrTime+ "  currentTime: " + currentTime);
        var currTimeMins = getTimeInMinutes(currentTime);
        var arrTimeMins = arrTime * 60;
        var minsDiff = currTimeMins - arrTimeMins;

        // console.log(minsDiff);
        return minsDiff;
    }

    //Clear detailedObj (or any array) so we can repopulate it with the recalculated data
    function clearObjArray(theArray) {
        for (var i = theArray.length; i > 0; i--) {
            theArray.pop();
        }
    }
})();

