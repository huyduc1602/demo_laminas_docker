(function($){
	var siteMenuClone = function() {
		
		$('.js-clone-nav').each(function() {
			var $this = $(this);
			$this.clone().attr('class', 'site-nav-wrap').appendTo('.site-mobile-menu-body');
		});


		setTimeout(function() {
			
			var counter = 0;
      $('.site-mobile-menu .has-children').each(function(){
        var $this = $(this);
        
        $this.prepend('<span class="arrow-collapse collapsed">');

        $this.find('.arrow-collapse').attr({
          'data-toggle' : 'collapse',
          'data-target' : '#collapseItem' + counter,
        });

        $this.find('> ul').attr({
          'class' : 'collapse',
          'id' : 'collapseItem' + counter,
        });

        counter++;

      });

    }, 1000);

		$('body').on('click', '.arrow-collapse', function(e) {
      var $this = $(this);
      if ( $this.closest('li').find('.collapse').hasClass('show') ) {
        $this.removeClass('active');
      } else {
        $this.addClass('active');
      }
      e.preventDefault();  
      
    });

		$(window).resize(function() {
			var $this = $(this),
				w = $this.outerWidth();

			if ( w > 991 ) {
				if ( $('body').hasClass('offcanvas-menu') ) {
					$('body').removeClass('offcanvas-menu');
				}
			}
		})

		$('body').on('click', '.js-menu-toggle', function(e) {
			var $this = $(this);
			e.preventDefault();

			if ( $('body').hasClass('offcanvas-menu') ) {
				$('body').removeClass('offcanvas-menu');
				$this.removeClass('active');
			} else {
				$('body').addClass('offcanvas-menu');
				$this.addClass('active');
			}
		}) 

		// click outisde offcanvas
		$(document).mouseup(function(e) {
	    var container = $(".site-mobile-menu");
	    if (!container.is(e.target) && container.has(e.target).length === 0) {
	      if ( $('body').hasClass('offcanvas-menu') ) {
					$('body').removeClass('offcanvas-menu');
				}
	    }
		});
	}; 
	siteMenuClone();

	var btn = $('#backToTop');
	$(window).scroll(function() {
	  if ($(window).scrollTop() > 150) {
	    btn.addClass('show');
	  } else {
	    btn.removeClass('show');
	  }
	});

	btn.on('click', function(e) {
	  e.preventDefault();
	  $('html, body').animate({scrollTop:0}, '450');
	});

	$(window).on('resize',function() {
		
  });

  $('.has-children').on('click tab', function() {
		if($(window).outerWidth() > 991) {
			$(this).closest('.site-menu').find('.has-children').not(this).find('.dropdown').removeClass('show');
			$(this).closest('.has-children')
			.find('.dropdown').toggleClass('show');
			var leftSide = $(this).find('.wrap-dropdown').get(0).getBoundingClientRect().left;
			if (leftSide < 0) {
				$(this).closest('.has-children')
				.find('.dropdown').css('right', 'calc(0% + '+ leftSide +'px)');
			} else {
				$(this).closest('.has-children')
				.find('.dropdown').css('right', '0');
			}
		}
	});

  $('body').on('click tab', function(event) {
		if ( $(event.target).closest('.has-children').length ) {

		}else{
			if( $(event.target).closest('.dropdown').length ){
			event.preventDefault();
				return false;
			}
			$('#header-container .show').removeClass('show');
		}
	});
  $(window).trigger('resize');

  // resize scrolling menu
	function resizeMenu(){
		if( window.innerWidth >= 992 ){
			if ($(this).scrollTop() > 70) {
	      $('.site-navigation').addClass('header-sm');
	      $('.logo').addClass('sm-logo');
	    } else {
	      $('.site-navigation').removeClass('header-sm');
	      $('.logo').removeClass('sm-logo');
	    }
		}
	};
	$(window).on('load resize scroll', function() {
		resizeMenu();	    
  });

 $('.m_form .input-group-prepend, .m_form .custom-input').bind('click', function (e) { e.stopPropagation() })
})(jQuery);