(function($) {
	$('form.inquiries__confirm .submit__btn').on('click', function(){
		var button = $(this);
		$.ajax({
			url: '/confirm-inquiries',
			method: 'POST',
			data: __inq__ || {},
			dataType: 'json',
			beforeSend: function(){
				button.addClass('not-allow disabled');
				$('.page-loader').show();
			},
			success: function(rs){
				button.removeClass('not-allow disabled');
				if(rs['success']){
					window.location.href = rs['redirect_link'];
				}else{
					notifyMe(rs['msg'], 'inverse', 'bg-warning', 5000);
				}
				$('.page-loader').hide();
			},
			error: function(){
				button.removeClass('not-allow disabled');
				$('.page-loader').hide();
				window.notifyMe('__network_error__', 'inverse', 'bg-warning');
			}
		});
	});
	$('form.inquiries__confirm .back_FormConfirm').on('click', function() {
		$('#inquiries__confirm').attr('action', "/inquiry").submit();
	})
})(jQuery);