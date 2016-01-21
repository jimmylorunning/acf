$(document).ready(function() {
  $('.blockyblock').fitVids();
  $(window).bind( 'hashchange', function() {
    console.log("scrolled!");
    scrollBy(0, -80);
  });
  $('div.alert.alert-dismissible').delay(3000).slideUp(300);
});