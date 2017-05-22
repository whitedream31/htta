/*
 * handler for populating articles in AJAX for HTTA
 * Ian Stewart (c) 2017
 */

function GotoAnchor(aid){
  var aTag = $("a[name='"+ aid +"']");
  if (aTag) {
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
  }
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
      url: "scripts/ajax.fetchevent.php",
      success: function (data) {
        var content = data.content;
        var anchor = data.anchor;
        $('#main .container').html(content);
        if (anchor) {
          GotoAnchor(anchor);
        } else {
          GotoAnchor('main');
        }
      },
      error: function (xhr, options, msg) {
        $('#msg').html('<p class="error">' + msg + '</p>');
      }
    });
  }
}

$.ajax({
  dataType: 'json',
  method: "GET",
  data: { ty: 'dates' },
  url: "scripts/ajax.fetchevent.php",
  success: function (data) {
    var status = data.status;
    var msg = data.msg;
    if (status === 'ok') {
      var pastdates = data.past;
      var soondates = data.soon;
      var futuredates = data.future;
      $('#soon').html(soondates);
      $('#future').html(futuredates);
      $('#past').html(pastdates);
      if (msg !== '') {
        $('#msg').html('<p class="pass">' + msg + '</p>');
      }
      GotoAnchor('content');
    } else {
      $('#msg').html('<p class="error">' + msg + '</p>');
    }
  },
  error: function (xhr, options, msg) {
    $('#msg').html('<p class="error">' + msg + '</p>');
  }
});

$("#past-button").click(function() {
  $.ajax({
    dataType: 'json',
    method: "GET",
    data: { ty: 'past' },
    url: "scripts/ajax.fetchevent.php",
    success: function (data) {
      $('#soon, #past, #future').hide();
      $('#main .container').html(data.dates);
      GotoAnchor('past');
    },
    error: function (xhr, options, msg) {
      $('#msg').html('<p class="error">' + msg + '</p>');
    }
  });
});

$("#soon-button").click(function() {
  $.ajax({
    dataType: 'json',
    method: "GET",
    data: { ty: 'soon' },
    url: "scripts/ajax.fetchevent.php",
    success: function (data) {
      $('#soon, #past, #future').hide();
      $('#main .container').html(data.dates);
      GotoAnchor('soon');
    },
    error: function (xhr, options, msg) {
      $('#msg').html('<p class="error">' + msg + '</p>');
    }
  });
});

$("#future-button").click(function() {
  $.ajax({
    dataType: 'json',
    method: "GET",
    data: { ty: 'future' },
    url: "scripts/ajax.fetchevent.php",
    success: function (data) {
      $('#soon, #past, #future').hide();
      $('#main .container').html(data.dates);
      GotoAnchor('future');
    },
    error: function (xhr, options, msg) {
      $('#msg').html('<p class="error">' + msg + '</p>');
    }
  });
});

/*
$.ajax({
  method: "GET",
  data: { ty: 'groups' },
  url: "scripts/ajax.fetcharticle.php",
  success: function (data) {
    $('#articlesgroups').append(data);
  },
  error: function (msg) {
    alert('error: ' + msg.responseText);
  }
});
*/

ProcessQueryString();
