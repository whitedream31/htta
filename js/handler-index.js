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

/*

art-social
art-friendship

 */

function AssignClickHandlers() {
  $('#art-culture').click(function(event) {
    event.preventDefault();
    GotoURL('articles.html?ty=ref&ref=about');
  });
  $('#art-social').click(function(event) {
    event.preventDefault();
    GotoURL('articles.html?ty=ref&ref=about');
  });
  $('#art-friendship').click(function(event) {
    event.preventDefault();
    GotoURL('articles.html?ty=ref&ref=about');
  });

  $('#click-event').click(function(event) {
    event.preventDefault();
    GotoURL('events.html');
  });
  $('#click-photos').click(function(event) {
    event.preventDefault();
    GotoURL('gallery.html');
  });
  $('#click-articles').click(function(event) {
    event.preventDefault();
    GotoURL('articles.html');
  });
  $('#click-about').click(function(event) {
    event.preventDefault();
    GotoURL('about.html');
  });
}

//

AssignClickHandlers();
