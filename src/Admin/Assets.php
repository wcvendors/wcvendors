<?php
/**
 * Load admin assets
 *
 * @package WCVendors/Admin
 */

namespace WCVendors\Admin;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * WCVendors_Admin_Assets Class.
 */
class Assets {

	/**
	 * Hook in tabs.
	 */
	public function init_hooks() {
		add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_filter('woocommerce_screen_ids', array($this, 'screen_ids'));
	}

	/**
	 * Enqueue the styles
	 */
	public function admin_styles() {
		wp_register_style('wcv_vendors_style', WCV_PLUGIN_URL . 'assets/src/scss/wcv-vendors.css');
		wp_enqueue_style('wcv_vendors_style');
	}

	/**
	 * Enqueue the scripts
	 */
	public function admin_scripts() {

		global $wp_query, $post;

		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		// Products screen.
		if (in_array($screen_id, array('edit-product'), true)) {
			wp_register_script('wcv_quick-edit', WCV_PLUGIN_URL . 'assets/js/admin/product-quick-edit.js', array('jquery'), WCV_VERSION, true);
			wp_enqueue_script('wcv_quick-edit');
		}

		// Vendor Management screen.
		if (in_array($screen_id, array('wc-vendors_page_wcv-vendors-management'), true)) {

			wp_register_script('wcv_vendor-edit', WCV_PLUGIN_URL . 'assets/src/js/admin/vendor-edit.js', array('jquery'), WCV_VERSION, true);
			wp_enqueue_script('wcv_vendor-edit');

			wp_localize_script('wcv_vendor-edit', 'wcv_ajax_obj', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('wcv_toogle_vendor_nonce')]);
		}
	}

	/**
	 * Load the WooCommerce Admin settings styles on the wcv-settings page
	 */
	public function screen_ids($screen_ids) {
		$screen_ids[] = 'wc-vendors_page_wcv-settings';
		return $screen_ids;
	}
}
