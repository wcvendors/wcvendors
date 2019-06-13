/* global wcv_admin_commission_params	*/
(function( $ ) {
	'use strict';
	$( '.export_csv' ).click({
		window.confirm( wcv_admin_commission_params.confirm_prompt );
	});

})( jQuery );
