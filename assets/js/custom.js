(function ($) {

  "use strict";

  // PRE loader
  document.addEventListener('DOMContentLoaded', function () {
    $('.preloader').fadeOut(200);
  });

  // Parallax Js
  function initParallax() {
    $('#home').parallax("100%", 0.3);
    $('#about').parallax("20%", 0.3);
    $('#work').parallax("40%", 0.3);
    $('#contact').parallax("60%", 0.3);
    $('#footer').parallax("80%", 0.3);
    }
  initParallax();


  // WOW Animation js
  new WOW({ mobile: false }).init();

})(jQuery);
