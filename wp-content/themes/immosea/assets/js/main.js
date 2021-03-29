import $ from 'jquery';
import 'slick-carousel';


const $body   = $('body');
const $slider = $('.hero__slider');

$slider.slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  infinite: false,
  dots: false,
  fade: true,
  arrows: true,
  autoplay: false
});


//Accordion
$('[data-accordion-title]').on('click', function(){
  var $speed = '400';

  $(this).toggleClass('active')
    .siblings()
    .removeClass('active')
    .end()

    .next()
    .slideToggle($speed)
    .siblings('[data-accordion-content]')
    .slideUp();
});


// Burger Button
$('[data-toggle]').on('click', function(e) {
  e.preventDefault();

  $(this).toggleClass('active');
  $body.toggleClass('nav-open');
});

