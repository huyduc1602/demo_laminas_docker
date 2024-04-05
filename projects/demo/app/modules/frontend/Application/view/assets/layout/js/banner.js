(function($){
  $('.item').lazyload({
    failure_limit: 10
  });
  var initCarousel = function (element){
    var lstIndx = 0,
      idTimeout = null,
      instCarousel = element
      .addClass('owl-carousel')
      .owlCarousel({
        center: false,
        autoplay: true,
        loop: true,
        stagePadding: 0,
        margin: 5,
        nav: true,
        dots: false,
        mouseDrag: false,
        autoplayHoverPause: true,
        rewind: true,
        navText: [
          '<i class="fa fa-angle-left custom-owlNav" aria-hidden="true"></i>', 
          '<i class="fa fa-angle-right custom-owlNav" aria-hidden="true"></i>'
          ],
        responsive: {
          0: {
            items: 1,
            nav: false,
            slideBy: 1
          },
          576: {
            items: 2,
            slideBy: 2
          },
          768: {
            items: 3,
            slideBy: 3
          },
          992: {
            items: 4,
            slideBy: 4
          },
          1200: {
            items: 5,
            slideBy: 5
          }
        },
        autoplay: false,
        autoplayTimeout: 3000,
        onInitialized: function() {
          setTimeout(function() {
            instCarousel.trigger('play.owl.autoplay');
          }, 7000)   
        }
      })

      .on('changed.owl.carousel', function(evt){
        $('.lazy-loading').lazyload({
          failure_limit: 10
        });
        instCarousel.trigger('lazy.load.image.resource');
        
        if( evt['page']['index'] > 0 ){
          instCarousel.find('.owl-prev').removeClass('disabled');
        }else if( evt['page']['index'] < (evt['page']['count']-1) ){
          instCarousel.find('.owl-next').removeClass('disabled');
        }
      })
      .on('next.owl.carousel', function(evt){
        instCarousel.trigger('lazy.load.image.resource');
      })
      .on('lazy.load.image.resource', function(evt){

        if( null !== idTimeout ){ clearTimeout(idTimeout) }
        idTimeout = setTimeout(function(){
          $(window).trigger('resize')
        }, 700)
        
        if( instCarousel.find('.lazy.lazy-loading').length == 0 ){
          instCarousel.off('lazy.load.image.resource');
        }
      })
    ;
  }
  
  $('.owl-banner').each(function(){
    initCarousel( $(this) );
  });
  
})(jQuery);