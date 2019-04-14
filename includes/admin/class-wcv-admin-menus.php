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
		add_action( 'admin_menu', array( $this, 'commissions_menu' ), 50 );
		add_action( 'admin_menu', array( $this, 'vendors_menu' ), 60 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 70 );
		add_action( 'admin_menu', array( $this, 'addons_menu'), 80 ); 

		// Add Screen Options 
		add_filter( 'set-screen-option', array( $this, 'set_screen_option' ), 99, 3 );
	}

	/**
	 * WC Vendors menu 
	 */
	public function admin_menu() {

		global $menu;

		if ( current_user_can( 'manage_woocommerce' ) ) {
			$menu[] = array( '', 'read', 'separator-woocommerce', '', 'wp-menu-separator wcvendors' );
		}

		add_menu_page( __( 'WC Vendors', 'wcvendors' ), __( 'WC Vendors', 'wcvendors' ), 'manage_woocommerce', 'wcvendors', null, 'dashicons-cart', '50'  );
	}

	/**
	 * Menu - Settings 
	 */
	public function settings_menu(){
		$settings_page = add_submenu_page( 'wcvendors', __( 'WC Vendors Settings', 'wcvendors' ),  __( 'Settings', 'wcvendors' ), 'manage_woocommerce', 'wcv-settings', array( $this, 'settings_page' ) );
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
	 * Menu - Vendors 
	 */
	public function vendors_menu(){ 
		$vendors_page = add_submenu_page( 'wcvendors', wcv_get_vendor_name( false ),  wcv_get_vendor_name( false ), 'manage_woocommerce', 'wcv-vendors', array( $this, 'vendors_page' ) );
	}

	/**
	 * Menu - Commissions 
	 */
	public function commissions_menu(){ 
		$commissions_page = add_submenu_page( 'wcvendors', __( 'Commissions', 'wcvendors' ), __( 'Commissions', 'wcvendors' ), 'manage_woocommerce', 'wcv-commissions', array( $this, 'commissions_page' ) );
		add_action( "load-$commissions_page", array( $this, 'commission_screen_options' ) );
		return true;
	}

	/**
	 * Addons menu item.
	 */
	public function addons_menu() {
		add_submenu_page( 'wcvendors', __( 'WC Vendors Extensions', 'woocommerce' ), __( 'Extensions', 'wcvendors' ), 'manage_woocommerce', 'wcv-addons', array( $this, 'addons_page' ) );
	}

	/**
	 * Adds screen options for the commissions page
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function commission_screen_options() {
		$option = 'per_page';

		$args = array(
			'label'   => __( 'Commissions', 'wcvendors' ),
			'default' => apply_filters( 'wcv_commission_list_default_item_per_page', 20 ),
			'option'  => 'commissions_per_page',
		);

		add_screen_option( $option, $args );

		return true;
	}

	/**
	 * Sets screen options for this page
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return mixed
	 */
	public function set_screen_option( $status, $option, $value ) {

		if ( 'commissions_per_page' === $option ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Vendors Page 
	 */
	public function vendors_page(){ 
		// WCVendors_Admin_Vendors::output(); 
	}

	/**
	 * Commissions Page 
	 */
	public function commissions_page(){ 
		WCVendors_Admin_Commission::output(); 
	}

	/**
	 * Settings Page 
	 */
	public function settings_page(){ 
		WCVendors_Admin_Settings::output();
	}

	/**	
	* 	Addons Page 
	*/ 
	public function addons_page(){ 
		// WCVendors_Admin_Addons::output(); 
	}

}

new WCVendors_Admin_Menus(); 