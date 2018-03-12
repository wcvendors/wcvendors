<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin class handles all admin custom page functions for admin view
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */
class WCVendors_Admin_Menus {

	/**
	 * Constructor
	 */
	public function __construct(){

		// Add menus
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 70 );
		add_action( 'admin_menu', array( $this, 'addons_menu'), 80 );



	}

	/**
	 * WC Vendors menu
	 */
	public function admin_menu() {

		global $menu;

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$menu[] = array( '', 'read', 'separator-woocommerce', '', 'wp-menu-separator wcvendors' );
		}

		add_menu_page( __( 'WC Vendors', 'wc-vendors' ), __( 'WC Vendors', 'wc-vendors' ), 'manage_woocommerce', 'wc-vendors', array( $this, 'addons_page' ), 'dashicons-cart', '50'  );
	}

	/**
	 * Addons menu item.
	 */
	public function addons_menu() {
		add_submenu_page( 'wc-vendors', __( 'WC Vendors Extensions', 'woocommerce' ), __( 'Extensions', 'wc-vendors' ), 'manage_woocommerce', 'wcv-addons', array( $this, 'addons_page' ) );
		remove_submenu_page( 'wc-vendors', 'wc-vendors' );
	}

	/**
	* 	Addons Page
	*/
	public function addons_page(){
		// WCVendors_Admin_Addons::output();
	}

	/**
	 * Settings menu item
	 */
	public function settings_menu(){
		$settings_page = add_submenu_page( 'wc-vendors', __( 'WC Vendors Settings', 'wcvendors' ),  __( 'Settings', 'wcvendors' ), 'manage_woocommerce', 'wcv-settings', array( $this, 'settings_page' ) );
 		add_action( 'load-' . $settings_page, array( $this, 'settings_page_init') );
	}


	/**
	 *  Loads required objects into memory for use within settings
	 */
	public function settings_page_init() {

		global $current_tab, $current_section;

		// Include settings pages
		WCVendors_Admin_Settings::get_settings_pages();

		// Get current tab/section
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		// Save settings if data has been posted
		if ( ! empty( $_POST ) ) {
			WCVendors_Admin_Settings::save();
		}

		// Add any posted messages
		if ( ! empty( $_GET['wcv_error'] ) ) {
			WCVendors_Admin_Settings::add_error( stripslashes( $_GET['wcv_error'] ) );
		}

		if ( ! empty( $_GET['wcv_message'] ) ) {
			WCVendors_Admin_Settings::add_message( stripslashes( $_GET['wcv_message'] ) );
		}
	}

	/**
	 * Settings Page
	 */
	public function settings_page(){
		WCVendors_Admin_Settings::output();
	}


}

new WCVendors_Admin_Menus();
