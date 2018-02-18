jQuery( function () {
	if (jQuery('#apply_for_vendor').is(':checked')) {
		jQuery('.agree-to-terms-container').show();
	}

	jQuery('#apply_for_vendor').on('click', function () {
		jQuery('.agree-to-terms-container').slideToggle();
	});
}); 
