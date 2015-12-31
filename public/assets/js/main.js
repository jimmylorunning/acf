$(document).ready(function() {
  $('.blockyblock').fitVids();
  $(window).bind( 'hashchange', function() {
    console.log("scrolled!");
    scrollBy(0, -80);
  });
});