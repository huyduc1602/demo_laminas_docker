jQuery(document).ready(function( $ ) {

  // Header fixed and Back to top button
 $(window).on('scroll load', function(event) {
   if ($(this).scrollTop() > 220) {
     $('.back-to-top').fadeIn('slow');
     $('#header').addClass('header-fixed');
   } else {
     $('.back-to-top').fadeOut('slow');
     $('#header').removeClass('header-fixed');
   }
 });
 $(window).scroll(function() {
   if ($(this).scrollTop() > 220) {
     $('.back-to-top').fadeIn('slow');
     $('#header').addClass('header-fixed');
   } else {
     $('.back-to-top').fadeOut('slow');
     $('#header').removeClass('header-fixed');
   }
 });
  $('.back-to-top').click(function(){
    $('html, body').animate({scrollTop : 0},1500, 'easeInOutExpo');
    return false;
  });
  

  // Initiate superfish on nav menu
  $('.nav-menu').superfish({
    animation: {opacity:'show'},
    speed: 400
  });

  // Mobile Navigation
  if( $('#nav-menu-container').length ) {
    var $mobile_nav = $('#nav-menu-container').clone().prop({ id: 'mobile-nav'});
    $mobile_nav.find('> ul').attr({ 'class' : '', 'id' : '' });
    $('body').append( $mobile_nav );
    $('body').prepend( '<button type="button" id="mobile-nav-toggle"><i class="fa fa-bars"></i></button>' );
    $('body').append( '<div id="mobile-body-overly"></div>' );
    $('#mobile-nav').find('.menu-has-children').prepend('<i class="fa fa-chevron-down"></i>');

    $(document).on('click', '.menu-has-children i', function(e){
      $(this).next().toggleClass('menu-item-active');
      $(this).nextAll('ul').eq(0).slideToggle();
      $(this).toggleClass("fa-chevron-up fa-chevron-down");
    });

    $(document).on('click', '#mobile-nav-toggle', function(e){
      $('body').toggleClass('mobile-nav-active');
      $('#mobile-nav-toggle i').toggleClass('fa-times fa-bars');
      $('#mobile-body-overly').toggle();
    });

    $(document).click(function (e) {
      var container = $("#mobile-nav, #mobile-nav-toggle");
      if (!container.is(e.target) && container.has(e.target).length === 0) {
       if ( $('body').hasClass('mobile-nav-active') ) {
          $('body').removeClass('mobile-nav-active');
          $('#mobile-nav-toggle i').toggleClass('fa-times fa-bars');
          $('#mobile-body-overly').fadeOut();
        }
      }
    });
  } else if ( $("#mobile-nav, #mobile-nav-toggle").length ) {
    $("#mobile-nav, #mobile-nav-toggle").hide();
  }
  
  $('.js__render--right').each(function() {
    var self = $(this);
    $(this).waypoint({
      handler: function(){
        self.addClass('animation');
      },
      offset: '70%'
    });
  });

  $('.js__render--left').each(function() {
    var self = $(this);
    $(this).waypoint({
      handler: function(){
        self.addClass('animation');
      },
      offset: '70%'
    });
  });
  
  $(".js__bg").bgswitcher({
    images: [
      "https://www.shinwa3.com/uploads/images-vtest/top-images/top06_agingbeef.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/top-images/top00_agingbeef.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/top-images/top01_agingbeef.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/top-images/top02_kamamoto_tanbei.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/top-images/top03_agingbeef.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/top-images/top04_agingbeef.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/top-images/top09_kamamoto_tanbei.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/top-images/top11_kamamoto_hanbei.jpg"
    ],
    loop: true,
    interval: 7000,
    duration: 9000,
  });

  $(".item-images--1").bgswitcher({
    images: [
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide1/agingbeef_01.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide1/agingbeef_02.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide1/agingbeef_03.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide1/agingbeef_04.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide1/agingbeef_05.jpg",
    ],
    loop: true,
    interval: 7000,
    duration: 1000,
  });

  $(".item-images--2").bgswitcher({
    images: [
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide2/kamamoto_hanbei_01.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide2/kamamoto_hanbei_02.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide2/kamamoto_hanbei_03.jpg",
    ],
    loop: true,
    interval: 7000,
    duration: 1000,
  });

  $(".item-images--3").bgswitcher({
    images: [
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide3/kamamoto_tanbei_01_660.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide3/kamamoto_tanbei_02.jpg",
      "https://www.shinwa3.com/uploads/images-vtest/carousel/slide3/kamamoto_tanbei_03.jpg",
    ],
    loop: true,
    interval: 7000,
    duration: 1000,
  });
  // if ($('body').children('#preloader').length > 0) {
  //   $('html').css('overflow', 'hidden');
  // }

  var cont = $('.tags__content');
  if (cont.height() >= 70) {
    $('body').on('click tab', '.tags__box__expand', function(event) {
      event.preventDefault();
      $(this).closest('.tags__box').toggleClass('expaned');
    });
  } else {
    $('.tags__box__expand').find('a').addClass('disabled not-allow');
  }

  $(".megamenu").on("click", function(e) {
    e.stopPropagation();
  });
  
//Welcome Message (not for login page)
  window.notifyMe = function(message, type, color, intV){
  	 var delay = 4000;
  	 switch(color){
  	 	case 'danger-color' 	: delay = 8000; break;
  	 	case 'error-color' 	: delay = 8000; break;
  	 	case 'info-color' 	: delay = 4000; break;
  	 	case 'warning-color' 	: delay = 8000; break;
  	 	case 'success-color' 	: delay = 4000; break;
  	 	default: break;
  	 }
  	 var offTop;
  	 if ($('body').children('#header-container').length > 0) {
  	 	offTop = $('.navbar.navbar-dropdown').outerHeight() + 5
  	 } else {
  	 	offTop = 90;
  	 };
     var result = $.growl({
          message: message
      },{
          type: type,
          allow_dismiss: true,
          label: 'Cancel',
          className: 'btn-xs btn-inverse',
          placement: {
              from: 'top',
              align: 'right'
          },
          delay: intV || delay,
          animate: {
              enter: 'animated fadeIn',
              exit: 'animated fadeOut'
          },
          offset: {
              x: 10,
              y: offTop
          }
      });
      if( color )
      	result['$template'].addClass( color + ' white-text custom-notifyMe');
      return result;
	};

  $(window).on('resize', function () {
    if($(window).innerWidth() >= 768) {
      $('.apply-btn').stick_in_parent({
        offset_top: 100,
      });
    } else {
      $('.apply-btn').trigger('sticky_kit:detach');
    }
  }).trigger('resize');
});
AOS.init({
  once: true, 
});

$(window).on("load", function() {
  //$('body').find('#preloader').addClass('removing')
  $('body').find('#preloader').remove();
});
