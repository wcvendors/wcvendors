<?php
/**
 * Load assets
 *
 * @version  2.0.0
 * @category Class
 * @author   Jamie Madden, WC Vendors
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
	}

	/**
	 * Enqueue the styles 
	 */
	public function admin_styles(){ 

	}

	/**
	 * Enqueue the scripts 
	 */
	public function admin_scripts(){ 

		global $wp_query, $post;

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';


		// Products
		if ( in_array( $screen_id, array( 'edit-product' ) ) ) {
			// wp_register_script( 'mmp_quick-edit', mmp()->plugin_url() . '/assets/js/admin/quick-edit' . $suffix . '.js', array( 'jquery' ), WCV_VERSION );
			wp_register_script( 'mmp_quick-edit', mmp()->plugin_url() . '/assets/js/admin/quick-edit.js', array( 'jquery' ), WCV_VERSION );
			wp_enqueue_script( 'mmp_quick-edit' );
		}
	}
} 
return new WCVendors_Admin_Assets(); 