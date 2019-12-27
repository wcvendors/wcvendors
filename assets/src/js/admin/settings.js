/* global wcvendors_settings_params */
(function($) {
	// Edit prompt
	$(function() {
		var changed = false;

		$('input, textarea, select, checkbox').change(function() {
			changed = true;
		});

		$('.wcv-nav-tab-wrapper a').click(function() {
			if (changed) {
				window.onbeforeunload = function() {
					return wcvendors_settings_params.i18n_nav_warning;
				};
			} else {
				window.onbeforeunload = '';
			}
		});

		$('.submit input').click(function() {
			window.onbeforeunload = '';
		});
	});

	// Select all/none
	$('.wcvendors').on('click', '.select_all', function() {
		$(this)
			.closest('td')
			.find('select option')
			.attr('selected', 'selected');
		$(this)
			.closest('td')
			.find('select')
			.trigger('change');
		return false;
	});

	$('.wcvendors').on('click', '.select_none', function() {
		$(this)
			.closest('td')
			.find('select option')
			.removeAttr('selected');
		$(this)
			.closest('td')
			.find('select')
			.trigger('change');
		return false;
	});
})(jQuery);
