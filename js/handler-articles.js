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

function PopulatePage(data) {
  var content = data.content;
  var anchor = data.anchor;
//  var caption = data.caption;
//  var byline = data.byline;
  $('#articles .container').html(content);
//  $('#articlecaption h2').html(caption);
//  $('#articlecaption .byline').html(byline);
  setTimeout(function(){
    AssignReturnClickHandler();
    if (anchor) {
      GotoAnchor(anchor);
    } else {
      GotoAnchor('main');
    }
  }, 1000);
}

function ShowArticleGroups() {
  $.ajax({
    method: "GET",
    data: { ty: 'list' },
    url: "scripts/ajax.fetcharticle.php",
    success: function (data) {
      var status = data.status;
      var msg = data.msg;
      if (status === 'ok') {

        setTimeout(function(){
          var list = data.list;
          $('#articles .container').html(list);
          ShowImages();
          $('#articles').slideDown('slow');
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

function AssignReturnClickHandler() {
  $('#links > h2, .articlelist').click(function(event) {
    event.preventDefault();
    $('#return').prop('disabled', true);
    $('#articles').slideUp();
    setTimeout(function(){
      ShowArticleList();
      GotoAnchor('articlelist');
    }, 500);
  });
}

function ShowTOC(headerelement, listelement) {
  // select the H2 headings in the content section - these contain the text for the links
  var headings = $(headerelement);//".toc-content h2");
  if (headings.length > 1) {
    // create a new empty UL tag to hold the list of links
    var list = $(listelement);//"<div id='toc'>");
    list.append($("<h3>Table of Contents</h3>"));
    // we need a counter to make sure each anchor tag has a unique name
    var cAnchorCount = 0;
    // for each one of the H2s, create a named anchor to link to and a link for the list
    headings.each(function(indx, elem) {
      // set the HTML of this H2 to contain the new anchor tag that is the link destination
      $(this).html("<a name='toc-" + cAnchorCount + "'></a>" + $(this).html());
      // now make a new LI tag for the list that links to the anchor tag and has the text of the H2
      list.append(
        '<p class="toc-item" data-toc="' + cAnchorCount++ + '">' +
        '<span class="fa fa-arrow-circle-o-down" title="click to back to table of contents"></span>' +
        $(this).text() + "</p>");
    });
    // when we're done, insert the list after the H1 heading that lists the specials
    $('.toctopbtn').click(function() {
      GotoAnchor('toc');
    });
    $('.toc-item').click(function() {
      GotoAnchor('toc-' + $(this).attr("data-toc"));
    });
  }
}

function ShowArticlesByRef(ref) {
  $.ajax({
    dataType: 'json',
    method: "GET",
    data: { ty: 'ref', ref: ref },
    url: "scripts/ajax.fetcharticle.php",
    success: function (data) {
      var status = data.status;
      var msg = data.msg;
      if (status === 'ok') {

        var list = data.list;
        $('#articles .container').html(list);
        AssignReturnClickHandler();
        ShowTOC("#links .article h3", "#toc");
      } else {
        $('#msg').html('<p class="error">' + msg + '</p>');
      }
    },
    error: function (xhr, options, msg) {
      $('#msg').html('<p class="error">' + msg + '</p>');
    }
  });
}

function AssignArticleGroupClickHandler() {
  $('#articlelist .articlegroup').click(function(event) {
    event.preventDefault();
    $('#article').slideUp();
    var ref = $(this).data("ref");

    setTimeout(function(){
      ShowArticlesByRef(ref);
      $('#articles').slideDown('slow');
      GotoAnchor('articlestories');
    }, 500);

  });
}

function ShowArticleList() {
  $('#galleries').slideUp();
  $.ajax({
    dataType: 'json',
    method: "GET",
    data: { ty: 'list' },
    url: "scripts/ajax.fetcharticle.php",
    success: function (data) {
      var status = data.status;
      var msg = data.msg;
      if (status === 'ok') {

        setTimeout(function(){
          var list = data.list;
          $('#articlelist .container').html(list);
//
          AssignArticleGroupClickHandler();
          $('#articlelist').slideDown('slow');

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
/*
function ProcessQueryString() {
  var anchor = false;
  // parse and process query string (if any)
  var keys = {};
  location.search.substr(1).split("&").forEach(function (pair) {
    if (pair !== "") {
      var parts = pair.split("=");
      var k = parts[0];
      var v = parts[1];
      keys[k] = v;
      if (k === 'ref') {
        anchor = v;
      }
    }
  });
  if (!$.isEmptyObject(keys)) {
    var keylist = JSON.stringify(keys);

    $.ajax({
      method: "GET",
      data: { ty: 'main', keys: keylist },
      url: "scripts/ajax.fetcharticle.php",
      success: function (data) {
        $('#main').append(data);
        if (anchor) {
          GotoAnchor(anchor);
        }
      },
      error: function (msg) {
        alert('error: ' + msg.responseText);
      }
    });
  } else {
    $.ajax({
      method: "GET",
      data: { ty: 'recent' },
      url: "scripts/ajax.fetcharticle.php",
      success: function (data) {
        $('#main').append(data);
      },
      error: function (msg) {
        alert('error: ' + msg.responseText);
      }
    });
    ShowArticleGroups();
  }
}
*/
function ProcessQueryString() {
  ShowArticleList();
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
      url: "scripts/ajax.fetcharticle.php",
      success: function (data) {
        PopulatePage(data);
      },
      error: function (xhr, options, msg) {
        $('#msg').html('<p class="error">' + msg + ' - ' + keylist + '</p>');
      }
    });
//  } else {
//    ShowArticleList();
  }
}

ProcessQueryString();
