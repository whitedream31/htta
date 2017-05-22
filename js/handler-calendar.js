function UpdateCalendar() {
  var m = $('#monthselection').val();
  var y = $('#yearselection').val();
  ProcessCalendar(m, y);
}
$('#monthselection').change(function(){
  UpdateCalendar();
});
$('#yearselection').change(function(){
  UpdateCalendar();
});

function ShowEventDetails(stamp) {
  $.ajax({
    method: "GET",
    data: { ty: 'caldate', stamp: stamp },
    dataType: 'json',
    url: "scripts/ajax.fetchevent.php",
    success: function (data) {
      var status = data.status;
      var msg = data.msg;
      if (status === 'ok') {
        $('#eventlist').html(msg);
      } else {
        $('#msg').html('<p class="error">' + msg + '</p>');
      }
    },
    error: function (xhr, options, msg) {
      $('#msg').html('<p class="error">' + msg + '</p>');
    }
  });
}

// click on the date with an event
$('.cellentryhitpost').click(function(e) {
  e.preventDefault();
  var stamp = $(this).data('id');
  ShowEventDetails(stamp);
});

$('td.calbtnnext a.calbtn').click(function(e) {
  e.preventDefault();
  var m = $(this).data('month');
  var y = $(this).data('year');
  ProcessCalendar(m, y, false);
});

$('td.calbtnprev a.calbtn').click(function(e) {
  e.preventDefault();
  var m = $(this).data('month');
  var y = $(this).data('year');
  ProcessCalendar(m, y, false);
});

function SendInterestedEmail(eventid, email, num, section) {
  $.ajax({ 
    type: 'GET',
    url: "scripts/ajax.sendinterestedemail.php",
    data: {
      event: eventid,
      email: email,
      num: num
    },
    success: function (data) {
      section.html(data);
    },
    error: function (msg) {
      window.console.log('error: ' + msg.responseText);
    }
  });
}

// main

//$('#showsummary').hide();

$('#hidesummary').click(function(e) {
  e.preventDefault();
  $('#eventsummary').slideUp();
  $('#hidesummary').hide();
  $('#showsummary').show();
});

$('#showsummary').click(function(e) {
  e.preventDefault();
  $('#eventsummary').slideDown();
  $('#hidesummary').show();
  $('#showsummary').hide();
});

$('.interesteddata').hide();

$('.eventinterest').click(function(e) {
  e.preventDefault();
  $(this).next().slideDown();
});

$('.interesteddata .interestedbutton').click(function(e) {
  e.preventDefault();
  var num = $(this).parent().children("input[type=number]").first().val();
  var email = $(this).parent().children("input[type=email]").first().val();
  var anchor = $(this).parent().parent().children().next('.eventinterest').first();
  var eventid = anchor.data('id');
  SendInterestedEmail(eventid, email, num, $(this).parent());
  anchor.hide();
});
