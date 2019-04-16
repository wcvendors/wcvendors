<?php
/**
 * Load assets
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors_Admin_Assets Class.
 */
class WCVendors_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
	}

	/**
	 * Enqueue the styles
	 */
	public function admin_styles() {

	}

	/**
	 * Enqueue the scripts
	 */
	public function admin_scripts() {

		global $wp_query, $post;

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Products
		if ( in_array( $screen_id, array( 'edit-product' ) ) ) {
			wp_register_script( 'wcv_quick-edit', WCVendors()->plugin_url() . '/assets/js/admin/product-quick-edit.js', array( 'jquery' ), WCVendors()->get_version() );
			wp_enqueue_script( 'wcv_quick-edit' );
		}
	}

	/*
	*   Load the WooCommerce Admin settings styles on the wcv-settings page
	*/
	public function screen_ids( $screen_ids ) {
		$screen_ids[] = 'wc-vendors_page_wcv-settings';
		return $screen_ids;
	}
}
return new WCVendors_Admin_Assets();
