/*
 * handlers for dealing with events for HTTA
 * Ian Stewart (c) 2017
 */
function GotoURL($url) {
  window.location.href = $url;
}

function GotoAnchor(aid){
  var aTag = $("a[name='"+ aid +"']");
  if (aTag && aTag.offset()) {
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
//    $('#main .container').slideUp();
    $.ajax({
      method: "GET",
      dataType: 'json',
      data: { ty: 'query', keys: keylist },
      url: "scripts/ajax.fetchmembership.php",
      success: function (data) {
        var content = data.content;
        var anchor = data.anchor;
        $('#main .container').html(content);
//        $('#main .container').slideDown();
        if (anchor) {
          GotoAnchor(anchor);
        } else {
          GotoAnchor('main');
        }
      },
      error: function (xhr, options, msg) {
        $('#msg').html('<p class="error">' + msg + '</p>' + '<p>' + keylist + '</p>');
      }
    });
  } else {
//    $('#main .container').slideUp();
    $('#main .container').html('');
//    $('#main .container').show();
  }
}

function AssignClickHandlers() {
  $('#click-join').click(function(event) {
    event.preventDefault();
    GotoURL('membership.html?ty=ref&ref=join');
  });
  $('#click-agm').click(function(event) {
    event.preventDefault();
    GotoURL('membership.html?ty=ref&ref=agm');
  });
  $('#click-committee').click(function(event) {
    event.preventDefault();
    GotoURL('membership.html?ty=ref&ref=committee');
  });
  $('#click-constitution').click(function(event) {
    event.preventDefault();
    GotoURL('membership.html?ty=ref&ref=constitution');
  });
}

//

AssignClickHandlers();
ProcessQueryString();
