(function($){
	$(document).on('submit', '#wptbm-form', function(e){
		e.preventDefault();
		var $form = $(this);
		var $btn = $form.find('button[type="submit"]');
		var $msg = $form.find('.wptbm-message');
		$msg.removeClass('ok err').text('');
		$btn.prop('disabled', true);

		var data = $form.serialize();
		$.post(WPTBM.ajaxUrl, data)
		 .done(function(resp){
			 if (resp && resp.success) {
				 $msg.addClass('ok').text(resp.data.message || 'Success.');
				 $form[0].reset();
			 } else {
				 var err = (resp && resp.data && resp.data.message) ? resp.data.message : 'Error. Please try again.';
				 $msg.addClass('err').text(err);
			 }
		 })
		 .fail(function(){
			 $msg.addClass('err').text('Network error. Please try again.');
		 })
		 .always(function(){
			 $btn.prop('disabled', false);
		 });
	});
})(jQuery);