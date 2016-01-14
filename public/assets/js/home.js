$(document).ready(function(){
  $('.mobile-carousel').slick({
    infinite: true,
    dots: false,
    mobileFirst: true,
    adaptiveHeight: true,
    prevArrow: "<button type='button' class='prev-arrow'><img src='/images/arrow_left.png'></button>",
    nextArrow: "<button type='button' class='next-arrow'><img src='/images/arrow_right.png'></button>",
  });
});