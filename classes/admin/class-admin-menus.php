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

		add_menu_page( __( 'WC Vendors', 'wcvendors' ), __( 'WC Vendors', 'wcvendors' ), 'manage_woocommerce', 'wcvendors', array( $this, 'addons_page' ), 'dashicons-cart', '50'  );


	}

	/**
	 * Addons menu item.
	 */
	public function addons_menu() {
		add_submenu_page( 'wcvendors', __( 'WC Vendors Extensions', 'woocommerce' ), __( 'Extensions', 'wcvendors' ), 'manage_woocommerce', 'wcv-addons', array( $this, 'addons_page' ) );
		remove_submenu_page( 'wcvendors', 'wcvendors' );
	}

	/**
	* 	Addons Page
	*/
	public function addons_page(){
		// WCVendors_Admin_Addons::output();
	}

}

new WCVendors_Admin_Menus();
