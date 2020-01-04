<?php

namespace WCVendors\Admin;

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
class Menus {

	/**
	 * WordPress hooks.
	 */
	public function init_hooks() {

		// Add menus
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'commissions_menu' ), 50 );
		add_action( 'admin_menu', array( $this, 'vendors_menu' ), 60 );
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 70 );
		add_action( 'admin_menu', array( $this, 'addons_menu' ), 80 );

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

		add_menu_page( __( 'WC Vendors', 'wc-vendors' ), __( 'WC Vendors', 'wc-vendors' ), 'manage_woocommerce', 'wc-vendors', null, 'dashicons-cart', '50' );
	}

	/**
	 * Menu - Settings
	 */
	public function settings_menu() {
		$settings_page = add_submenu_page(
			'wc-vendors',
			__( 'WC Vendors Settings', 'wc-vendors' ),
			__( 'Settings', 'wc-vendors' ),
			'manage_woocommerce',
			'wcv-settings',
			array(
				$this,
				'settings_page',
			)
		);
		add_action( 'load-' . $settings_page, array( $this, 'settings_page_init' ) );
	}

	/**
	 *  Loads required objects into memory for use within settings
	 */
	public function settings_page_init() {

		global $current_tab, $current_section;

		// Include settings pages
		Settings::get_settings_pages();

		// Get current tab/section
		$current_tab     = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] );
		$current_section = empty( $_REQUEST['section'] ) ? '' : sanitize_title( $_REQUEST['section'] );

		// Save settings if data has been posted
		if ( ! empty( $_POST ) ) {
			Settings::save();
		}

		// Add any posted messages
		if ( ! empty( $_GET['wcv_error'] ) ) {
			Settings::add_error( stripslashes( $_GET['wcv_error'] ) );
		}

		if ( ! empty( $_GET['wcv_message'] ) ) {
			Settings::add_message( stripslashes( $_GET['wcv_message'] ) );
		}
	}

	/**
	 * Menu - Vendors
	 */
	public function vendors_menu() {
		$vendors_page = add_submenu_page(
			'wc-vendors',
			wcv_get_vendor_name( false ),
			wcv_get_vendor_name( false ),
			'manage_woocommerce',
			'wcv-vendors',
			array(
				$this,
				'vendors_page',
			)
		);
	}

	/**
	 * Menu - Commissions
	 */
	public function commissions_menu() {
		$commissions_page = add_submenu_page(
			'wc-vendors',
			__( 'Commissions', 'wc-vendors' ),
			__( 'Commissions', 'wc-vendors' ),
			'manage_woocommerce',
			'wcv-commissions',
			array(
				$this,
				'commissions_page',
			)
		);
		add_action( "load-$commissions_page", array( $this, 'commission_screen_options' ) );

		return true;
	}

	/**
	 * Addons menu item.
	 */
	public function addons_menu() {
		add_submenu_page(
			'wc-vendors',
			__( 'WC Vendors Extensions', 'woocommerce' ),
			__( 'Extensions', 'wc-vendors' ),
			'manage_woocommerce',
			'wcv-addons',
			array(
				$this,
				'addons_page',
			)
		);
	}

	/**
	 * Adds screen options for the commissions page
	 *
	 * @access public
	 * @return bool
	 * @version 1.0.0
	 * @since 1.0.0
	 */
	public function commission_screen_options() {
		$option = 'per_page';

		$args = array(
			'label'   => __( 'Commissions', 'wc-vendors' ),
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
	 * @return mixed
	 * @version 1.0.0
	 * @since 1.0.0
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
	public function vendors_page() {
		// WCVendors_Admin_Vendors::output();
	}

	/**
	 * Commissions Page
	 */
	public function commissions_page() {
		Commission::output();
	}

	/**
	 * Settings Page
	 */
	public function settings_page() {
		Settings::output();
	}

	/**
	 *   Addons Page
	 */
	public function addons_page() {
		// WCVendors_Admin_Addons::output();
	}

}
