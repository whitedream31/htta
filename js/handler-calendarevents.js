/*
 * handler for populating articles in AJAX for HTTA
 * Ian Stewart (c) 2017
 */

$.ajaxSetup({
  cache: false
});

function GotoAnchor(aid){
  var aTag = $("a[name='"+ aid +"']");
  if (aTag) {
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
  }
}

function GetParameterByName(name, def) {
  url = window.location.href;
  name = name.replace(/[\[\]]/g, "\\$&");
  var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
    results = regex.exec(url);
  if (!results) return def;
  if (!results[2]) return def;
  return decodeURIComponent(results[2].replace(/\+/g, " "));
}

function ProcessCalendar(mth, yr) {
  $.ajax({ 
    type: 'GET',
    url: "scripts/ajax.fetchcalendarevents.php",
  //  dataType: 'json',
    data: {
      m: mth,
      y: yr
    },
    success: function (data) { 
      $('#calendarheader').html(data);
      jQuery.getScript('js/handler-calendar.js');
    },
    error: function (msg) {
      window.console.log('error: ' + msg.responseText);
    }
  });
}

var mth = GetParameterByName('m', 0);
var yr = GetParameterByName('y', 2000);

ProcessCalendar(mth, yr);

//GotoAnchor('content');
