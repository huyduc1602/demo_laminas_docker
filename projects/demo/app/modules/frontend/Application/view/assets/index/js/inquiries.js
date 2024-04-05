
(function($){
	var invalidTxt = '正しくご入力ください。',
		inquiriesForm = $('#inquiries__form'),
		isDuplicateEmail= false,
		validItems = {company_name: false, person_inCharge: false, inq_email: false, inq_phone: false, department_name:true, inq_url: true, inq_content:true},
		inqServices = {},
		/**
		 * Check string is valid japanese name
		 * @return bool
		 */
		isValidName = function(str){
			if( str ){
				var isMatch = str.match(/[^\-\[\]\_a-zA-Z0-9\s\u3000\u3040-\u30ff\u3400-\u4dbf\u4e00-\u9fff\uf900-\ufaff\uff66-\uff9f]/);
				return null === isMatch;
			}
			return false;
		},

		evetError = function(evt){
			var self = $(this), thisVal = (self.val()||'').trim();
			if( thisVal !== '' ){
				if( self.attr('aria-describedby') ) self.parent().find('.warning-box').css('display', 'none');
				self.removeClass('not-null');
			}
			else self.one('keyup', evetError);
		},
		evtValidInput = function(element, vals ,showMSg){
			element.attr('data-original-title', invalidTxt);
			
			if( '' === vals  ){
				if (showMSg) {
					element.one('keyup', evetError).addClass('not-null');
					if( !element.attr('aria-describedby') ) element.parent().find('.warning-box')
					.css('display', 'block')
					.find('.warning-txt').text(invalidTxt); 
				}
				return false;
			} 
		
			return true;
		};
		evtValidInputPhone = function (element, vals, showMsg) {
			element.attr('data-original-title', invalidTxt);
			if( vals.length < 10 ){
				if (showMsg) {
					element.one('keyup', evetError).addClass('not-null');
					if( !element.attr('aria-describedby') ) element.parent().find('.warning-box')
					.css('display', 'block')
					.find('.warning-txt').text(invalidTxt); 
				}
				return false;
			}
			return true;
		}
		var emailRegex = /^([^@\s]+)@((?:[-a-z0-9]+\.)+[a-z]{2,})$/i;
		evtValidInputEmail = function (element, vals, showMsg){
			element.attr('data-original-title', invalidTxt);
			if (!emailRegex.test(vals)) {
				if (showMsg) {
					element.one('keyup', evetError).addClass('not-null');
					if( !element.attr('aria-describedby') ) element.parent().find('.warning-box')
					.css('display', 'block')
					.find('.warning-txt').text(invalidTxt); 
				}
				return false;
			}
			return true;
		}

	inquiriesForm
	.on('blur change', '#company_name, #person_inCharge', function(evt){
		validItems[evt.target.getAttribute('id')] = evtValidInput(
			$(this), (this.value||'').trim(), true, isValidName
		);
	})
	.on('blur change', '.sv-types', function(evt){
		inqServices[evt.target.getAttribute('id')] = evt.target.checked
	})
	.on('blur keyup', '#inq_content', function(evt) {
		var characterCount = $(this).val().length,
		      current = $('#current'),
		      maximum = $('#maximum'),
		      theCount = $('#the-count');	
		  current.text(characterCount);
		  if ($(this).val().length >= 2000) {
			current.css('color', '#ed1b24');
			maximum.css('color', '#ed1b24');
			theCount.css('font-weight','bold');
		  } else {
			current.css('color', '#666');
			maximum.css('color', '#666');
			theCount.css('font-weight','normal');
		  }     
	})
	.on('blur change', '#inq_phone', function(evt) {
		validItems[evt.target.getAttribute('id')] = evtValidInputPhone(
			$(this), (this.value ||'').trim(), true, isValidName
		);
	})
	.on('blur change', '#inq_email', function(evt) {
		validItems[evt.target.getAttribute('id')] = evtValidInputEmail(
			$(this), (this.value ||'').trim(), true, isValidName
		);
	})
	
	.find('input.require_input').parent().find('.warning-box').css('display', 'none').find('.warning-txt').text(invalidTxt);
	
	for(var keyItm in validItems ){
		if (['company_name', 'person_inCharge'].includes(keyItm)) {
			validItems[keyItm] = evtValidInput(
				$('#'+ keyItm), ($('#'+ keyItm).val()||'').trim(), false, isValidName
			);
		}
		if (['inq_phone'].includes(keyItm)) {
			validItems[keyItm] = evtValidInputPhone(
				$('#'+ keyItm), ($('#'+ keyItm).val()||'').trim(), false, isValidName
			);
		}
		if (['inq_email'].includes(keyItm)) {
			validItems[keyItm] = evtValidInputEmail(
				$('#'+ keyItm), ($('#'+ keyItm).val()||'').trim(), false, isValidName
			);
		}
	}
		
	$('.sv-types').each(function() {
		inqServices[this.getAttribute('id')] = this.checked;
	})
	
	$('#next-step').on('click', function(evt){
		var valPhone = $('#inq_phone'),
			valCompany = $('#company_name'),
			valPersonInCharge = $('#person_inCharge'),
			valEmail = $('#inq_email'),
			varlistService = $('input[name="inq_services[]"]:checked');
		evtValidInputPhone(valPhone, valPhone.val(), true);
		evtValidInput(valCompany, valCompany.val(), true);
		evtValidInput(valPersonInCharge, valPersonInCharge.val(), true);
		evtValidInputEmail(valEmail, valEmail.val(), true);
		
		if(varlistService.length < 1 || valPersonInCharge.val() == '' || valCompany.val() == '' || valPhone.val().length < 10 || !emailRegex.test(valEmail.val())  ) {
			window.notifyMe('ご入力内容をご確認ください', 'inverse', 'bg-danger', 5000);
		} 
		if (varlistService.length < 1 ) {
			$('input[name="inq_services[]"]').parent().parent().parent().parent()
			.find('.warning-box')
			.css('display', 'block')
			.find('.warning-txt').text(invalidTxt); 
			return false 
		} else {
			$('input[name="inq_services[]"]').parent().parent().parent().parent()
			.find('.warning-box')
			.css('display', 'none')
		
		}
		
		var postVals = {},
			inqServiceVals = [];
		for(let key in inqServices ) {
			if (inqServices[key])
				inqServiceVals.push((inquiriesForm.find('#' + key).val() || '').trim());
		}
		if ( !inqServiceVals.length ) {return false;}

		for(var keyItm in validItems ){

			if( !validItems[keyItm] ){
				return false;
			}
			else{
				postVals[keyItm] = (inquiriesForm.find('#' + keyItm).val() || '').trim();	
			}
		}

		postVals.inq_services = inqServiceVals;

		postVals.screen = window.screen.availWidth + 'x' + window.screen.availHeight;

		var self = $(this);
		$('.page-loader:first').show();

		$.ajax({
    		url: '/inquiry',
    		type: 'POST',
    		data: postVals,
    		dataType: 'JSON',
			beforeSend: function(){
				// self.addClass('not-allow disabled');
			},
			success: function(rs){
				// self.removeClass('not-allow disabled');
				if(rs['success']){
					inquiriesForm.submit();
				} else{
					
					window.notifyMe(rs['msg'] || 'ご入力内容をご確認ください', 'inverse', 'bg-danger');
					$('.page-loader').hide();				
				}
			},
			error: function(){
				window.notifyMe('ご入力内容をご確認ください', 'inverse', 'bg-danger');
				$('.page-loader').hide();
			}
    	})
	});


	$('.inquiries__input').each(function() {
		$(this).on('focus', function(event) {
			$(this).removeClass('not-null').parent().find('.warning-box').css('display', 'none');
		});
	});
	
})(jQuery);