/*
 * handler for populating gallery items in AJAX for HTTA
 * Ian Stewart (c) 2017
 */

function GotoAnchor(aid){
  var aTag = $("a[name='"+ aid +"']");
  if (aTag && aTag.offset()) {
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
  }
}

function DoReturnClickHandler() {
  $('#return').click(function(event) {
    event.preventDefault();
    ShowGallaryList();
  });
}

function ShowGalleryByRef(ref) {
  $.ajax({
    dataType: 'json',
    method: "GET",
    data: { ty: 'ref', ref: ref },
    url: "scripts/ajax.fetchgallery.php",
    success: function (data) {
      var status = data.status;
      var msg = data.msg;
      if (status === 'ok') {

        var list = data.list;
        $('#galleries .container').html(list);
        $('#links a').click(function(event) {
          event.preventDefault();
          blueimp.Gallery($('#links a'), $('#blueimp-gallery').data());
        });
        DoReturnClickHandler();
      } else {
        $('#msg').html('<p class="error">' + msg + '</p>');
      }
    },
    error: function (xhr, options, msg) {
      $('#msg').html('<p class="error">' + msg + '</p>');
    }
  });
}

function ShowImages() {
  $('#galleries .gallerygroup').click(function(event) {
    event.preventDefault();
    $('#galleries').slideUp();
    var ref = $(this).data("ref");

    setTimeout(function(){

      ShowGalleryByRef(ref);
      $('#galleries').slideDown('slow');

    }, 1000);

  });
}

function ShowGallaryList() {
  $('#galleries').slideUp();
  $.ajax({
    dataType: 'json',
    method: "GET",
    data: { ty: 'list' },
    url: "scripts/ajax.fetchgallery.php",
    success: function (data) {
      var status = data.status;
      var msg = data.msg;
      if (status === 'ok') {

        setTimeout(function(){
          var list = data.list;
          $('#galleries .container').html(list);
          ShowImages();
          $('#galleries').slideDown('slow');

        }, 500);

      } else {
        $('#msg').html('<p class="error">' + msg + '</p>');
      }
    },
    error: function (xhr, options, msg) {
      $('#msg').html('<p class="error">' + msg + '</p>');
    }
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
//$('#main .container').html('<p>' + keylist + '</p>'); exit;
    $.ajax({
      method: "GET",
      dataType: 'json',
      data: { ty: 'query', keys: keylist },
      url: "scripts/ajax.fetchgallery.php",
      success: function (data) {
        var content = data.content;
        var anchor = data.anchor;
        $('#galleries .container').html(content);
        if (anchor) {
          GotoAnchor(anchor);
        } else {
          GotoAnchor('main');
        }
        DoReturnClickHandler();
      },
      error: function (xhr, options, msg) {
        $('#msg').html('<p class="error">' + msg + '</p>');
      }
    });
  } else {
    ShowGallaryList();
  }
}

ProcessQueryString();
