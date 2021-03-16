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
$('[data-accordion-title]').on('click', function(e) {
  e.preventDefault();
  var $this = $(this);
  if ($this.parent().hasClass('show')) {
    $this.parent().removeClass('show');
  }
  else {
    $this.parent().parent().find('[data-accordion-item]').removeClass('show');
    $this.parent().toggleClass('show');
  }
});

// Burger Button
$('[data-toggle]').on('click', function(e) {
  e.preventDefault();

  $(this).toggleClass('active');
  $body.toggleClass('nav-open');
});
