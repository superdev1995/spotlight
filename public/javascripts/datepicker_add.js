/**
 * Created by QuentinVarlet on 19/11/2018.
 */

  $(document).ready(function(){
    moment.locale('en');

    var date = new Date();
    bugun = moment(date).format("YYYY/MM/DD");

    var date_input=$('input[name="date_from"]'); //our date input has the name "date"
    var date_input_to=$('input[name="date_to"]'); //our date input has the name "date"

    var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
    var options={

    container: container,
    todayHighlight: true,
    autoclose: true,
    format: 'yyyy/mm/dd',
    language: 'en',

    };
    date_input.val(bugun);
    date_input_to.val(bugun);

    date_input.datepicker(options).on('focus', function(date_input){
        $("h3").html("focus event");
    });

    date_input_to.datepicker(options).on('focus', function(date_input){
        $("h3").html("focus event");
    });


    date_input.change(function () {
        var deger = $(this).val();
        $("h3").html("<font color=green>" + deger + "</font>");
    });
  });