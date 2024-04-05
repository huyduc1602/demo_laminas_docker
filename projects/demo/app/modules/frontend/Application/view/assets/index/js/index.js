(function($){
	$('.items .img-thumb').lazyload({
		failure_limit: 10
	});

	var initCarousel = function (element){
    var lstIndx = 0,
      idTimeout = null,
      instCarousel = element
      .addClass('owl-carousel')
      .owlCarousel({
		    center: false,
		    loop: false,
				stagePadding: 0,
		    margin: 0,
		    nav: true,
		    dots: false,
		    mouseDrag: false,
		    navText: [
			    '<i class="fa fa-angle-left custom-owlNav" aria-hidden="true"></i>', 
			    '<i class="fa fa-angle-right custom-owlNav" aria-hidden="true"></i>'
			    ],
			  responsive: {
	        0: {
	          items: 1,
	          slideBy: 1
	        },
	        576: {
	          items: 2,
	          slideBy: 2
	        },
	        992: {
	        	items: 3,
	        	slideBy: 3
	        }
	      }
		  })

      .on('changed.owl.carousel', function(evt){
        instCarousel.trigger('lazy.load.image.resource');
        
        if( evt['page']['index'] > 0 ){
          instCarousel.find('.owl-prev').removeClass('disabled');
        }else if( evt['page']['index'] < (evt['page']['count']-1) ){
          instCarousel.find('.owl-next').removeClass('disabled');
        }
      })
      
      .on('lazy.load.image.resource', function(){
        if( null !== idTimeout ){ clearTimeout(idTimeout) }
        idTimeout = setTimeout(function(){
          $(window).trigger('resize')
        }, 700)
        
        if( instCarousel.find('.lazy.lazy-loading').length == 0 ){
          instCarousel.off('lazy.load.image.resource');
        }
      })
      .on('changed.owl.carousel', function(evt){
        instCarousel.trigger('lazy.load.image.resource');
        
        if( evt['page']['index'] > 0 ){
          instCarousel.find('.owl-prev').removeClass('disabled');
        }else if( evt['page']['index'] < (evt['page']['count']-1) ){
          instCarousel.find('.owl-next').removeClass('disabled');
        }
      })
    ;
  }
  
  $('.owl-blog').each(function(){
    initCarousel( $(this) );
  });
	
	//var imgThumbUrl = $('.items .img-thumb').attr('data-img');
	//$('.items .img-thumb').css('background-image', 'url(' + imgThumbUrl + ')');

	$('.clamp-detail').each(function(index, element) {
	  $clamp(element, { clamp: 3, useNativeClamp: true });
	});

	function tagSize(){
		$('#main-content').each(function(){
			if ($(window).outerWidth() >= 576) {
		    var highestBox = 0;
		    $('.tags-box:not(.sidebar-tags)').each(function(){
		    	$(this).css('height', 'auto');
		      if($(this).height() > highestBox) {
		        highestBox = $(this).height(); 
		      }
		    });
		    $('.tags-box:not(.sidebar-tags)').height(highestBox);
		  }else {
		  	$('.tags-box:not(.sidebar-tags)').css('height', 'auto');
		  }
	  });
  };
  tagSize();

	var timeId = null;
  $(window).on('resize', function() {
  	if (timeId !== null) {
  		clearTimeout(timeId);
  	}
  	timeId = setTimeout(tagSize, 300);
  });
})(jQuery);