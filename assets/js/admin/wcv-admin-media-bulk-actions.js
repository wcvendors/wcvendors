'use strict';
( function( $ ) {
	var $vendorSelect = $('#vendor');
	var $bulkActionTop = $('#bulk-action-selector-top');
	var $bulkActionBottom = $('#bulk-action-selector-bottom');

	$bulkActionTop.after( $vendorSelect );
	$bulkActionBottom.after( $vendorSelect.clone().attr('name', 'vendor2').attr('id', 'vendor2') );

	function showVendorSelect() {
		if( $(this).val() == 'assign_vendor' ) {
			$(this).parent().find('.assign-vendor').show().select2();
		} else {
			$(this).parent().find('.assign-vendor').hide();
		}
	}

	$bulkActionTop.on( 'change', showVendorSelect );
	$bulkActionBottom.on( 'change', showVendorSelect );
})(jQuery);
