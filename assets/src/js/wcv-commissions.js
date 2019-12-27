/* global wcv_commissions_select */
jQuery(function() {
	jQuery('.select2').select2({
		placeholder: wcv_commissions_select.placeholder,
		allowClear: wcv_commissions_select.allowclear
	});
});
