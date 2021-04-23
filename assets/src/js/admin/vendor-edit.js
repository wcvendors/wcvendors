jQuery(document).ready(function($) {
	$('.vendor_enable_disable').on('change', function(e) {
		e.preventDefault();
		let that = $(this);
		let vendor_id = that.val();
		let data = {
			action: 'enable_vendor',
			vendor_id: vendor_id,
			security: wcv_ajax_obj.nonce
		};

		$.ajax({
			method: 'POST',
			url: ajaxurl,
			data: data,
			beforeSend: function() {
				console.log('Sending ajax');
			},
			success: function(response) {
				console.log(response);
			},
			error: function(err) {
				console.log(err);
			}
		});
	});
});
