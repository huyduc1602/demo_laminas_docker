(function($) {
	var decodeUri = function(s) {
		return decodeURIComponent(s.replace(/\+/g, " "));
	}
	$.getUrlParams = function(url) {
		var re = /(?:\?|&(?:amp;)?)([^=&#]+)(?:=?([^&#]*))/g, 
		match, params = {}; 

		if ( typeof url == "undefined") url = document.location.href;

		while (match = re.exec(url)) {
			params[decodeUri(match[1])] = decodeUri(match[2]);
		}
		return params;
	}
	
	String.prototype.hexEncode = function(){
	    var hex, i;

	    var result = "";
	    for (i=0; i<this.length; i++) {
	        hex = this.charCodeAt(i).toString(16);
	        result += ("000"+hex).slice(-4);
	    }

	    return result
	}
	
	String.prototype.hexDecode = function(){
	    var j;
	    var hexes = this.match(/.{1,4}/g) || [];
	    var back = "";
	    for(j = 0; j<hexes.length; j++) {
	        back += String.fromCharCode(parseInt(hexes[j], 16));
	    }

	    return back;
	}
	
	window.isAnyPartOfElementInViewport = function (el) {

	    const rect = el.getBoundingClientRect();
	    // DOMRect { x: 8, y: 8, width: 100, height: 100, top: 8, right: 108, bottom: 108, left: 8 }
	    const windowHeight = (window.innerHeight || document.documentElement.clientHeight);
	    const windowWidth = (window.innerWidth || document.documentElement.clientWidth);

	    // http://stackoverflow.com/questions/325933/determine-whether-two-date-ranges-overlap
	    const vertInView = (rect.top <= windowHeight) && ((rect.top + rect.height) >= 0);
	    const horInView = (rect.left <= windowWidth) && ((rect.left + rect.width) >= 0);

	    return (vertInView && horInView);
	}
	
	$.scrollTo = function(target, options, callback) {
		if(typeof options == 'function' && arguments.length == 2) { 
			callback = options; options = target; 
		}
		var settings = $.extend({
			scrollTarget  : target,
			offsetTop     : 50,
			duration      : 300,
			easing        : 'swing'
		}, options);
		
		var scrollTarget = (typeof settings.scrollTarget == "number") ? settings.scrollTarget : $(settings.scrollTarget);
		var scrollY = (typeof scrollTarget == "number") ? scrollTarget : scrollTarget.offset().top - parseInt(settings.offsetTop);
		
		$('body').animate({scrollTop : scrollY }, parseInt(settings.duration), settings.easing, function(){
			if (typeof callback == 'function') {
				callback.call(this); 
			}
		});
	};
	// -- show dialog file
	var divTemp = $('<div class="hidden" />').appendTo($('body'));
	var templateFileModal = '&lt;div class=&quot;modal fade&quot; tabindex=&quot;-1&quot; role=&quot;dialog&quot;&gt;&lt;div class=&quot;modal-dialog modal-lg&quot; style=&quot;width:90%;height: 90%&quot;&gt;&lt;div class=&quot;modal-content&quot; style=&quot;height: 100%&quot;&gt;&lt;div class=&quot;modal-header&quot;&gt;&lt;button aria-label=&quot;Close&quot; data-dismiss=&quot;modal&quot; class=&quot;close&quot; type=&quot;button&quot;&gt;&lt;span aria-hidden=&quot;true&quot;&gt;&times;&lt;/span&gt;&lt;/button&gt; &lt;h4 class=&quot;modal-title&quot;&gt;__title__&lt;/h4&gt; &lt;/div&gt;&lt;iframe src=&quot;__link__&quot; style=&quot;height: calc(100% - 130px);width:100%;border:0 none&quot;&gt;&lt;/iframe&gt;&lt;div class=&quot;modal-footer&quot;&gt;&lt;button type=&quot;button&quot; class=&quot;btn btn-default&quot; data-dismiss=&quot;modal&quot;&gt;Close&lt;/button&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;';
		templateFileModal = divTemp.html(templateFileModal).text();
	
	/**
	 * @todo: show dialog choose image
	 * @param <object> options:{
	 * 	src: string, url to action image manager
	 *  title: string, title of modal
	 *  onReadyEvt: function, custom event when iframe ready loaded
	 * }
	 */
	$.fileManagerDialog = function( options ){
		options = options || {};
		var modalEle = templateFileModal
			.replace('__link__', options['src'] || '')
			.replace('__title__', options['title'] || '');
		modalEle = $($.parseHTML(modalEle));
		// -- remove dialog element
		modalEle.on('hidden.bs.modal', function(evt){
			modalEle.remove();
		});
		
		// -- custom event ready
		if( options['onReadyEvt'] && 'function' == typeof(options['onReadyEvt']) ){
			modalEle.find('iframe').bind('load', function(evt){
				options['onReadyEvt'].apply(this, [evt, modalEle, options['params'] || {}]);
			});
		}
		
		// -- show dialog
		$('body').append(
				modalEle.modal('show')
		);
		return modalEle;
	}
	
	// -- Show custom popover
	// color: http://getbootstrap.com/css/#helper-classes
	var templateMsg = divTemp.html('&lt;div class=&quot;popover&quot; role=&quot;tooltip&quot; style=&quot;width: 200px&quot;&gt;&lt;div class=&quot;arrow&quot;&gt;&lt;/div&gt;&lt;div class=&quot;popover-content __color__&quot;&gt;&lt;/div&gt;&lt;/div&gt;').text();
	/**
	 * @todo: Show popover
	 * @param string message
	 * @param function callback
	 * @param numeric duration
	 * @param string placement
	 * @param string color
	 * @return jQuery element
	 */
	$.fn.showMessage = function(message, callback, duration, placement, color) {
		placement = placement || 'right';
		color = color || '';
		// -- message is required
		if(!message) { return false; }
		
		if(false !== duration && typeof(duration) != 'number') {
			duration = 1500;
		}
		
		// -- Create popover
		var self = this;
		self.popover({
			'html': true,
			'content': message,
			'placement': 'auto ' + placement,
			'trigger': 'manual',
			'template': templateMsg.replace('__color__', color)
		});
		
		// -- Hidde message event
		if( false !== duration )
			self
			.on('shown.bs.popover', function() {
				var self = this;
				setTimeout(function() {
					$(self).popover('hide');
				}, duration);
			});
		
		// Start show popover
		self.popover('show');
		
		// -- change
		if( self.is(':input') ){
			self.one('keypress', function(evt){
				$(this).popover('destroy');
			});
		}
		return this;
	};
	
	
	/**
	 * @todo: Show popover
	 * @param object error-clss
	 * @param object success-clss
	 * @param string req-type
	 * @return jQuery element
	 */
	var initValidateInput = function( element, opts ){
		var self = $(element), feedbackClass = ' has-feedback';
		// -- error status
		opts['error-clss'] = opts['error-clss'] || JSON.parse(self.attr('data-error-clss'));
		// -- success status
		opts['success-clss'] = opts['success-clss'] || JSON.parse(self.attr('data-success-clss'));
		// -- requir type
		opts['req-type'] = opts['req-type'] || self.attr('data-req-type');
		// -- message
		opts['valide-msg'] = opts['valide-msg'] || self.attr('data-valide-msg');
		// -- exception
		opts['except'] = opts['valide-except'] || self.attr('data-except');
		// length
		if( opts['req-type'] == 'length' )
			opts['length'] = opts['length'] || JSON.parse(self.attr('data-length'));
		
		self
		
		.on('keypress', function(evt){
			$(this).closest('.form-group').find('.form-control-feedback')
			.removeClass(opts['success-clss']['icon'] + ' ' + opts['error-clss']['icon']);
		})
		
		.on('focusout blur', function(evt){
			if( true === $(this).data('isDbExist') ) return true;
			var thisVals = $(this).val(), isError = false;
			switch( opts['req-type'] ){
				case 'not-empty':
					if( '' == thisVals ) isError = true;
					break;
				case 'email':
					if( opts['except'] === 'empty' ){
						if( '' !== thisVals && !$.phpjs.isValidEmailAddress(thisVals) ) 
							isError = true;
					}else if( '' == thisVals || !$.phpjs.isValidEmailAddress(thisVals) ) 
						isError = true;
					break;
				case 'length':
					if( opts['length']['min'] && thisVals.length < opts['length']['min'] )
						isError = true;
					if( opts['length']['max'] && thisVals.length > opts['length']['max'] )
						isError = true;
					break;
				default: break;
			}
			
			// if not vailidate
			if( true == isError ){
				$(this).trigger('notValidated');
			}else
				$(this).trigger('isValidated');
		})
		// -- co loi
		.bind('notValidated', function(evt, msg){
			var self = $(this);
			self.closest('.form-group')
				.removeClass( opts['success-clss']['clss'] )
				.addClass( opts['error-clss']['clss'] + feedbackClass )
			.find('.form-control-feedback')
				.removeClass(opts['success-clss']['icon'])
				.addClass(opts['error-clss']['icon'])
			;
			// if has install tooltip
			if( !self.data('bs.tooltip') ){
				self.tooltip({
					'title': msg ? msg : opts['valide-msg'],
					'template': '<div class="tooltip-warning tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
				}).tooltip('show');
			}
			//showMessage( opts['valide-msg'], null, false, 'top', 'text-warning' )
			self = undefined;
		})
		// -- hop le
		.bind('isValidated', function(evt){
			$(this)
			.tooltip('destroy')
			.closest('.form-group')
				.removeClass( opts['error-clss']['clss'] )
				.addClass( opts['success-clss']['clss'] + feedbackClass )
			.find('.form-control-feedback')
				.removeClass(opts['error-clss']['icon'])
				.addClass(opts['success-clss']['icon']);
		})
		;
		return element;
	}
	
	/**
	 * @todo: Show popover
	 * @param object error-clss
	 * @param object success-clss
	 * @param string req-type
	 * @return jQuery element
	 */
	$.fn.validateInput = function( opts ){
		opts = $.extend(true, {}, opts || {});
		
		this.each(function(){
			initValidateInput(this, $.extend(true, {}, opts));
		});
		
		return this;
	}
			
	// -- Close popover
	$('body').bind('keypress', function(evt){
		// press ESC key
		if( evt.keyCode == 27 ){
			$(this).find('#layout_body_wrapper [aria-describedby]').popover('hide');
		}
	})
	.find('#filter-form').bind('submit', function(evt){
		$(this).find('input, textarea, select').each(function(idx){
			var thisVal = $.trim( $(this).val() );
			if( '' === thisVal ) $(this).prop('disabled', true);
		});
	})
	;
	
	/**	
	 * Khai bao bien dung chung 
	 */
	var jEles = {
		jAdminForm: $('#admin-form')
	};
	
	// -- numeric input
	var validateNumber = function (event) {
		
	    var key = window.event ? event.keyCode : event.which;
	    if( 0 === key ) return true;
	    var self = $(this);
	    if( self.hasClass('datepicker') ){
	    	if( key == 47 || key == 46)
	    		return true;
	    }
	    
	    if( self.hasClass('phone') ){
	    	if( key == 40 || key == 41 || key == 43 || key == 32)
	    		return true;
	    }
	    // -- kieu so nguyen
	    if( self.hasClass('integer') && (key === 46 || key === 44) ){
	    	return false;
	    }
	    	    
	    // -- so thuc
	    if ( key === 8 || key === 37 || key === 46 || key === 44 ) { //|| event.keyCode === 39
	        return true;
	    }else if ( key < 48 || key > 57 ) {
	        return false;
	    }
	    
	    return true;
	};
	
	$('body')
		.on('keypress', '.numeric', validateNumber)
		.on('change', '.numeric:not(.no-format)', function(evt){
			$(this).trigger('beforeFormat');
			
			var thisVal = $.phpjs.escNumFormant($.trim( $(this).val() ));
				thisVal = parseFloat(thisVal), isFloat = $(this).hasClass('float');
				// -- float value
				if( (true == isFloat) && (thisVal-parseInt(thisVal)) > 0 )
					isFloat = 2;
				else isFloat = undefined;
				
			var num = $.phpjs.number_format( thisVal || '0', isFloat );
	    	$(this).val(num).trigger('afterFormat');
		})
		.on('focus', '.numeric', function(evt){
			var self = $(this), val = $.trim(self.val());
			if( val === '0' ){
				self.val('');
			}
		})
		;
	divTemp.remove();
	
	var initSalarySelect = function(element, opts){
		var containerEle = $('<div class="salary-container hidden"/>'), 
			itemsEle = $('<ul />'), isOut = true;
			
		// -- render item
		var self = $(element), items = [], 
			itemStr = '<li class="salary-item"><a href="javascript:void(0);">__value__</a></li>',
		
		// -- Su kien di chuyen up or down bang phim mui ten
		nextEvt = function( method, position ){
			containerEle.removeClass('hidden');
			// find current active item
			var active = containerEle.find('.salary-item.active'),
				item = active[method]();
			
			// not exist active
			if( active.size() == 0 || item.size() == 0 ){
				item = containerEle.find('.salary-item:' + position );
			} item.addClass('active');
			
			containerEle
				.scrollTop(item.position().top)
				.find('.salary-item').not(item)
				.removeClass('active');
		},
			
		// -- Tinh toan vi tri hien thi moi khi show controll
		handerPosition = function( ){
			var postion = this.getBoundingClientRect(),
				windowHeight = $(window).height();
			
			if( (windowHeight - (postion.bottom + 200)) < -10 ) 
				containerEle.addClass('up');
			else 
				containerEle.removeClass('up');
			
			containerEle.width(self.outerWidth());
			return this;
		};
		
		// -- render items
		$.each(opts['items'], function( idx, item ){
			items += itemStr.replace('__value__', item);
		});
		containerEle.append(itemsEle.append(items))
		.bind('hiddenEvt', function(evt){
			$(this)
			.addClass('hidden')
			.find('.active').removeClass('active');
		})
		.hover(function(evt){ isOut = false; }, function(evt){ isOut = true; }); items = undefined;
		
		// -- Show item
		self
		.after($('<a href="javascript:void(0);" class="addon-salary">&nbsp;</a>').bind('click tab', function(evt){
			self.val( opts['min'] || '' ).trigger('change');
		}))
		.after(containerEle)
		.bind('focus click tab', function(evt){
			var isShow = !window.offSalaryMenu 
			&& !$(this).prop('disabled') 
			&& !$(this).prop('readonly') && !opts['isHideMenu'];
			if( isShow ){
				containerEle
				.removeClass('hidden');
				handerPosition.apply(this);
			}
		})
		.bind('keydown', function(evt){
			
			// -- select item by enter key
			if ( 13 == evt.keyCode ){
				var active = containerEle.find('.salary-item.active:first');
				if( active.size() ) active.trigger('click');
				return true;
			}
			// -- navigation up
			if ( 38 == evt.keyCode ){
				nextEvt('prev', 'last');
				return true;
			}
			// -- navigation down
			if ( 40 == evt.keyCode ){
				nextEvt('next', 'first');
				return true;
			}
			
			if( true === opts['hideModify'] ) containerEle.trigger('hiddenEvt');
		})
		.on('keypress', function(evt){
			var key = evt.which || evt.keyCode;
			if( key >= 48 && key <= 57 ){
				containerEle.trigger('hiddenEvt');
			}
			return true;
		})
		.bind('change', function(evt){
			var thisVal = parseFloat($(this).val());
			// -- max value
			if( opts['max'] && opts['max'] < thisVal ){
				$(this).val(opts['max']);
			}
			// -- min value
			if( undefined !== opts['min'] && opts['min'] > thisVal ){
				$(this).val(opts['min']);
			}
			
			if( 0 !== thisVal && !thisVal ){
				$(this).parent().find('.addon-salary').addClass('hidden');
			}else $(this).parent().find('.addon-salary').removeClass('hidden');
		})
		.attr('autocomplete','off').trigger('change')
		.parent().bind('focusout', function(evt){
			if( true === isOut ) containerEle.trigger('hiddenEvt');
		});
			
		// -- choose item
		itemsEle
		.find('.salary-item').bind('click tab', function(evt){
			evt.stopPropagation(); self.val( $(this).text() ).trigger('change').focus(); 
			containerEle.trigger('hiddenEvt');
		})
		.hover(function(evt){
			$(this).addClass('active');
			containerEle.find('.salary-item').not($(this)).removeClass('active');
		}, function(evt){ $(this).removeClass('active'); });
		itemsEle = undefined;
		return this;
	};
	
	if( navigator.userAgent )
		window.offSalaryMenu = /iphone|ipad|android|blackberry|nokia|opera\smini|windows\smobile|windows\sphone|iemobile/i.test(navigator.userAgent);
	else window.offSalaryMenu = false;
	/**
	 * Salary input
	 * @author PhapIt 19.02.2016
	 * @params Object {
	 *  'items': Array,
	 *  'min': integer,
	 *  'max': integer,
	 *  'hideModify': bool
	 * }
	 */
	$.fn.salarySelect = function(opts){
		if( opts && opts['items'] && opts['items'].length > 0  ){
			this.each(function(){
				initSalarySelect(this, opts);
			});
		}
		return this;
	}
	
	var ajaxPost = function(url, params, element){
			var icon = element.parent().find('.icon-check-loading').removeClass('hidden');
			return $.ajax({
				'url': url || '',
				'type': 'POST',
				'dataType': 'JSON',
				'data': params || {}
			})
			.done(function(result){
				if( true === result['is-exist'] ){
					element.trigger('isExistEvt');
				}else element.trigger('notExistEvt');
			})
			.always(function(){ icon.addClass('hidden'); })
			.fail(function(){ element.trigger('connectFailEvt'); });
		},
		initCheckKeywordExist = function(element, opts){
			var self = $(element), timeout = false;
			
			self
			.on('change', function(evt){
				var thisVal = $.trim(self.val());
				if( '' !== thisVal ){
					var params = $.extend({ 'keyword': thisVal }, opts['opts'] || {});
					ajaxPost(opts['url'], params, self);
				}
			})
			/*.on('blur', function(evt){
				console.log(3);
				var thisVal = $.trim(self.val());
				if( !self.parent().hasClass('has-warning') && '' !== thisVal ){
					//clearTimeout(timeout);
					var params = $.extend({ 'keyword': thisVal }, opts['opts'] || {});
					ajaxPost(opts['url'], params, self);
				}
			})*/
			.on('connectFailEvt', function(evt){
				$(this).tooltip({
					'placement': 'top',
					'title': opts['ajaxFailMsg'],
					'template': opts['tooltipTmpl']
				}) .tooltip('show');
			})
			.on('isExistEvt', function(evt){
				var self = $(this).data({'isDbExist': true});
				if( opts['evtExistCallBack'] ){
					switch(typeof(opts['evtExistCallBack'])){
						case 'function':
							opts['evtExistCallBack'].apply(this, [opts]);
							break;
						case 'string':
							self.trigger(opts['evtExistCallBack'], [opts['existMsg']]);
							break;
						default: break;
					}
				}
				self
				.tooltip({
					'placement': 'top',
					'title': opts['existMsg'],
					'template': opts['tooltipTmpl']
				}) .tooltip('show');
			})
			.on('notExistEvt', function(evt){
				var self = $(this).data({'isDbExist': false}).tooltip('destroy');
			})
			.after( '<i class="hidden icon-check-loading fa-li fa fa-spinner fa-pulse"></i>' )
			.parent().css('position', 'relative');
			return element;
		};
	
	/**
	 * Check keyword is exist on database
	 * @author PhapIt 27.04.2016
	 * @params Object {
	 *  'existMsg': string,
	 *  'loadingIcon': bool,
	 *  'url': string,
	 *  'bgColor': string,
	 *  'delay': integer,
	 *  'connectFailMsg': string
	 * }
	 */
	$.fn.checkKeywordExist = function(opts){
		opts = $.extend(true, {
			'loadingIcon': true, 
			'bgColor': 'success',
			'delay': 600,
			'ajaxFailMsg': 'Can not connect to server. Please try againt!',
			'evtExistCallBack': null
		}, opts || {} );
		
		// template
		opts['tooltipTmpl'] = '<div class="tooltip-'+ opts['bgColor'] + ' tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>';
		
		this.each(function(){
			initCheckKeywordExist(this, $.extend(true, {}, opts));
		});
		return this;
	}
	
	var initDetectKeyword = function(element, opts){
		var self = $(element);
		
		self
		.on('keyup', function(evt){
			var vals = (self.val() || '').trim();
			// --- Kiem tra email
			if( opts['rexEmail'].test(vals) 
				|| opts['rexPhone'].test(vals) 
				|| opts['rexUrl'].test(vals) ){
				self.trigger('existEvt', [evt]);
			}else{
				self.trigger('notExistEvt', [evt]);
			}
		})
		.on('existEvt', function(evt){
			self
			.data('isKeywordExist', true)
			.tooltip({
				'placement': 'auto top',
				'title': opts['existMsg'],
				'template': opts['tooltipTmpl']
			}).tooltip('show')
			.parent().addClass(opts['errorClss']);
		})
		.on('notExistEvt', function(evt){
			self.data({'isKeywordExist': false})
			.tooltip('destroy')
			.parent().removeClass(opts['errorClss']);
		})
		;
		return element;
	}
	/**
	 * Check keyword
	 * @author PhapIt 22.011.2016
	 * @params Object {
	 *  'existMsg': string,
	 *  'bgColor': string,
	 *  'evtExistCallBack': function
	 * }
	 */
	$.fn.detectKeyword = function(opts){
		opts = $.extend(true, { 
			'bgColor': 'warning',
			'errorClss': 'has-error',
			'existMsg': '禁止文字が含まれています',
			'evtExistCallBack': null,
			'rexEmail': new RegExp(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})/),
			'rexPhone': new RegExp(/(?:\+?(\d{1,3}))?([-.\s(]*(\d{1,3})[-.\s)]*)?((\d{1,3})[-.\s]*(\d{2,4})(?:[-.x\s]*(\d+))?)\s*$/),
			'rexUrl': new RegExp(/(www|http|\.(com|net|biz|jp|info|org|us|top|edu|gov))/)
		}, opts || {} );
		
		// template
		opts['tooltipTmpl'] = '<div class="tooltip-'+ opts['bgColor'] + ' tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>';
		
		this.each(function(){
			initDetectKeyword(this, $.extend(true, {}, opts));
		});
		return this;
	}
	
	var initValidNickname = function(target, opts){
		target.on('change blur', function(evt){
			var thisVals = target.val();
			if( '' != thisVals && opts['ex'].test(thisVals) ){
				var msg = target.attr('data-msg') || opts['msg'];
				target.tooltip({
					'placement': 'auto top',
					'title': opts['msg'],
					'template': opts['tooltipTmpl']
				}).tooltip('show');
				target
				.data({'isValid': false})
				.parent().addClass('has-error');
			}else target
			.data({'isValid': true})
			.tooltip('destroy')
			.parent().removeClass('has-error');
		});
		return target;
	}; 
	
	/**
	 * @todo: Kiem tra nickname
	 */
	$.fn.validNickname = function( opts ){
		opts = $.extend({
			'ex': new RegExp(/[\`\!\@\#\$\%\^\&\*\(\)\-\+\=\{\}\[\]\|\\\:\;\"\'\,\.\<\>\/\?\s]/g),
			'msg': 'This nickname not validated! Nickname only accept character 0-9, a-z, A-Z and "_".',
			'tooltipTmpl': '<div class="tooltip-warning tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
		}, opts || {});
		
		this.each(function(){
			initValidNickname($(this), $.extend(true, {}, opts));
		});
		return this;
	}
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
    	 	offTop = 10;
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
})(jQuery);