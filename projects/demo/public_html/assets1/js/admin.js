(function($){
	var initValidNickname = function(target, opts){
		target.on('change blur', function(evt){
			var thisVals = target.val();
			if( '' != thisVals && opts['ex'].test(thisVals) ){
				var msg = target.attr('data-msg') || opts['msg'];
				notifyMe(msg, 'inverse', 'bgm-red');
				target.closest('.fg-float').addClass('has-error');
				
			}else target.closest('.fg-float').removeClass('has-error');
		});
		return target;
	}; 
	
	/**
	 * @todo: Kiem tra nickname
	 */
	$.fn.validNickname = function( opts ){
		opts = $.extend({
			'ex': new RegExp(/[\`\!\@\#\$\%\^\&\*\(\)\-\+\=\{\}\[\]\|\\\:\;\"\'\,\.\<\>\/\?\s]/g),
			'msg': 'This nickname not validated! Nickname only accept character 0-9, a-z, A-Z and "_".'
		}, opts || {});
		
		this.each(function(){
			initValidNickname($(this), $.extend(true, {}, opts));
		});
		return this;
	}
	
	//Welcome Message (not for login page)
    window.notifyMe = function(message, type, color){
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
            delay: 2500,
            animate: {
                enter: 'animated fadeIn',
                exit: 'animated fadeOut'
            },
            offset: {
                x: 20,
                y: 75
            }
        });
        if( color )
        	result['$template'].addClass( color + ' c-white');
	};
	

    var adminForm = $('#adminForm');
    
    var changeStateEvt = function(self){
    	var url = self.attr('data-url') || '',
    		token = self.attr('data-token')||'';
    	return $.ajax({
    		'url': url, 'type': 'POST',
    		'data': {}, 'dataType': 'JSON',
    		beforeSend: function(request){
    			request.setRequestHeader('Csrf-Token', token);
	        }
    	}).done(function(result){
    		if( result ){
    			if(result['code'] == 401 ){
    				window.location.href = window.location.href;
    				return;
    			}
    			
    			if( result['token'] ){
    				self.attr('data-token', result['token']);
    			}
    			
    			if( result['msg'] ){
    				if( false !== result['status'] )
    					notifyMe(result['msg'], 'inverse', 'bgm-green');
    				else notifyMe(result['msg'], 'inverse', 'bgm-red');
    			}
    			if( result['status'] ){
    				self.addClass('btn-success').removeClass('btn-danger')
	    			.find('.zmdi').addClass('zmdi-check').removeClass('zmdi-close');
    			}
    			else
    				self.addClass('btn-danger').removeClass('btn-success')
	    			.find('.zmdi').addClass('zmdi-close').removeClass('zmdi-check');
    			if( result['next-state'] ){
    				self.attr('data-url', url.replace(/(\/status\/.*)/, '/status/' + result['next-state'] ));
    			}
    			if( self.hasClass('one-time') ) self.off('click tab').prop('disabled', true).addClass('not-allow disabled');
    			
    		}else
    			notifyMe('Change status fail!', 'inverse', 'bgm-red');
    		
    		if( self.attr('data-callbak-evt') ){
    			self.trigger(self.attr('data-callbak-evt'), [result]);
    		}
    	})
    	.always(function(){
    		self.prop('disabled', false);
    		swal.close();
    	})
    	.fail(function(){
    		notifyMe('Change status fail!', 'inverse', 'bgm-red');
    	});
    };
    
    adminForm
    
    // -- change status event
    .on('click tab', '.status-btn:not([disabled])', function(evt){
    	evt.preventDefault();
    	var confirmTxt = $(this).attr('data-confirm') || '';
    	var self = $(this).prop('disabled', true);
    	if( !confirmTxt ){
    		var arr = $(this).attr('data-confirm-arr') || '';
    		if( arr ){ 
    			arr = JSON.parse(arr);
    			var idx = 0;
    			if( self.hasClass('btn-success') ) idx = 1;
    			confirmTxt = arr[idx];
			}
    	}
    	
    	if( confirmTxt ){
    		swal({
    			  title: window.jsonSystemLanguage['warning'],
    			  text: confirmTxt,
    			  type: "warning",
    			  showCancelButton: true,
    			  confirmButtonClass: "btn-danger",
    			  confirmButtonText: window.jsonSystemLanguage['yes'],
    			  cancelButtonText: window.jsonSystemLanguage['cancel'],
    			  closeOnConfirm: false,
    			  showLoaderOnConfirm: true
			},
			function( rs ){
    			  //swal("Deleted!", "Your imaginary file has been deleted.", "success");
    			  if( rs ) changeStateEvt(self);
    			  else self.prop('disabled', false);
			});
    	}else changeStateEvt(self);
    })
    
    // -- delete one
    .on('click tab', '.manage-delete', function(evt){
    	evt.preventDefault();
    	var url = $(this).attr('href');
    	swal({
			  title: window.jsonSystemLanguage['system_info'],
			  text: $(this).attr('data-confirm') || '',
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonClass: "btn-danger",
			  confirmButtonText: window.jsonSystemLanguage['yes'],
			  cancelButtonText: window.jsonSystemLanguage['cancel'],
			  closeOnConfirm: false,
			  showLoaderOnConfirm: true
		},
		function( rs ){
			if( rs ){ 
				var thisCheckbox = $(evt.target).closest('tr')
				.find('input:checkbox').prop('checked', true);
				
				adminForm
				.attr('action', $('.toolbar-delete:first').attr('href'))
				.find('td input:checkbox').not(thisCheckbox)
				.prop('checked', false);
				
				adminForm.submit();
			}
		});
    	return false;
    })
    // -- check all
    .find('#checkall').bind('change', function(evt){
    	adminForm.find('input:checkbox').prop('checked', $(this).prop('checked'));
    })
    
    ;
    
    
    // -- form search
    $('#filter-form').submit(function(evt){
    	$(this).find('input,select').each(function(){
    		var thisVal = $.trim($(this).val());
    		if( '' === thisVal ){
    			$(this).prop('disabled', true);
    		}
    	});
    });
    
    // -- toolbar remove
    $('body .toolbar-delete').bind('click tab', function(evt){
    	evt.preventDefault();
    	if( adminForm.find('input[name="id\[\]"]:checked').size() ){
    		var url = $(this).attr('href');
    		swal({
	  			  title: window.jsonSystemLanguage['system_info'],
	  			  text: $(this).attr('data-confirm') || '',
	  			  type: "warning",
	  			  showCancelButton: true,
	  			  confirmButtonClass: "btn-danger",
	  			  confirmButtonText: window.jsonSystemLanguage['yes'],
	  			  cancelButtonText: window.jsonSystemLanguage['cancel'],
	  			  closeOnConfirm: false
	  		},
	  		function( rs ){
	  			if( rs ){
	  				adminForm.attr('action', url).submit();
	  			}
	  		});
    	}else{
    		notifyMe($(this).attr('data-rq-one') || '', 'inverse', 'bgm-red');
    	}
    });
    
    // -- numeric input
	function validateNumber(event) {
		
	    var key = window.event ? event.keyCode : event.which;
	    if( 0 === key ) return true;
	    
	    if( $(this).hasClass('phone') ){
	    	if( key == 40 || key == 41 || key == 43 || key == 32 || key == 45)
	    		return true;
	    }
	    // -- kieu so nguyen
	    if( $(this).hasClass('integer') && (key === 46 || key === 44) ){
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
		.on('focus', '.numeric:not(.no-format)', function(evt){
			var thisVal = ($(this).val() || '');
			if( '0' === thisVal ){
				this.value = ''; 
				$(this).val('')
				.one('blur', function(){
					var thisVal = ($(this).val() || '');
					if( '' === thisVal ){
						var min = this.getAttribute('min') || '';
						this.value = min; $(this).val(min);
					}
				})
				;
			}	
		})
		.on('change', '.numeric:not(.no-format)', function(evt){
			
			var thisVal = $.phpjs.escNumFormant($.trim( $(this).val() ));
				thisVal = parseFloat(thisVal), isFloat = $(this).hasClass('float');
			// -- float value
			if( (true == isFloat) && (thisVal-parseInt(thisVal)) > 0 )
				isFloat = 2;
			else isFloat = undefined;
			
			var max = this.getAttribute('max') || '';
		    if( max !== '' && max < thisVal ){
		    	thisVal = max;
		    }
		    
		    var min = this.getAttribute('min') || '';
		    if( min !== '' && min > thisVal ){
		    	thisVal = min;
		    }
			var num = $.phpjs.number_format( thisVal || '0', isFloat );
			this.value = num;
	    	$(this).val(num);
		});

})(jQuery);
