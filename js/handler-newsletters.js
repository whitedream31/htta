/*
 * handler for populating newsletters in AJAX for HTTA
 * Ian Stewart (c) 2017
 */

 /*
function GotoAnchor(aid){
  var aTag = $("a[name='"+ aid +"']");
  if (aTag) {
    $('html,body').animate({scrollTop: aTag.offset().top},'slow');
  }
}
*/

// show the (recent) newsletter list in the current page in the footer
function PopulateNewsletterList() {
  $.ajax({
    dataType: 'json',
    method: "GET",
    data: { ty: 'list' },
    url: "scripts/ajax.fetchnewsletter.php",
    success: function (data) {
      var status = data.status;
      var msg = data.msg;
      if (status === 'ok') {

        setTimeout(function(){
          var list = data.list;
          $('#recentnewsletters').html(list);

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

PopulateNewsletterList();

/*
$("#subscription").submit(function(e) {
  e.preventDefault();
  var email = $('#email').val();
  $.ajax({
    dataType: 'json',
    method: "GET",
    data: { email: email },
    url: "scripts/ajax.processsubscriber.php",
    success: function (data) {
      var msg = data.msg;
      $('#subscribemessage').html(msg);
//$('#subscribemessage').html(data);
    },
    error: function (xhr, options, msg) {
      $('#msg').html('<p class="error">' + msg + '</p>');
    }
  });

});
*/

//ProcessQueryString();
