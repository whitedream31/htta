/*
 * handler for dealing with contact page in AJAX for HTTA
 * Ian Stewart (c) 2017
 */

function GotoAnchor(aid){
  var aTag = $("a[name='"+ aid +"']");
  if (aTag) {
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
  }
}

function GotoAnchor(aid){
  var aTag = $("a[name='"+ aid +"']");
  if (aTag && aTag.offset()) {
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
  }
}

function AssigContactClickHandler() {
  $('#frmcontact').submit(function(event) {
    event.preventDefault();
    var fldname = $('#fldname').val();
    var fldsubject = $('#fldsubject').val();
    var fldemail = $('#fldemail').val();
    var fldmessage = $('#fldmessage').val();
    $.ajax({
      method: "GET",
      dataType: 'json',
      data: {
        displayname: fldname,
        subject: fldsubject,
        email: fldemail,
        message: fldmessage
      },
      url: "scripts/ajax.sendemail.php",
      success: function (data) {
        var content = data.content;
        var status = data.status;
        if (status == 'ok') {
          $('#msg').html(content);
          $('#contactform').slideUp();
        } else {
          $('#msg').html('<p class="error">' + msg + '</p>');
        }
      },
      error: function (xhr, options, msg) {
        $('#msg').html('<p class="error">' + msg + '</p>');
      }
    });

  });
}

function ProcessQueryString() {
  // parse and process query string (if any)
  var keys = {};
  location.search.substr(1).split("&").forEach(function (pair) {
    if (pair !== "") {
      var parts = pair.split("=");
      var k = parts[0];
      var v = parts[1];
      keys[k] = v;
    }
  });
  if (!$.isEmptyObject(keys)) {
    var keylist = JSON.stringify(keys);
    $.ajax({
      method: "GET",
      dataType: 'json',
      data: { ty: 'query', keys: keylist },
      url: "scripts/ajax.fetchcontact.php",
      success: function (data) {
        var content = data.content;
        var status = data.status;
        if (status === 'ok') {
          $('#contactform').html(content);
//          $('#contactform').slideUp();
        } else {
          $('#msg').html('<p class="error">' + content + '</p>');
        }
      },
      error: function (xhr, options, msg) {
        $('#msg').html('<p class="error">' + msg + '</p>');
      }
    });
  }
}

AssigContactClickHandler();
ProcessQueryString();
