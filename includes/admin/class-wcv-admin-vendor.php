<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The vendor admin class handles all vendor related functions in admin view
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */
class WCVendors_Admin_Vendor {

	/**
	 * constructor
	 */
	public function __construct() {

		// Load these filtered screens for the vendors only
		if ( wcv_is_vendor( get_current_user_id() ) ) {

			// filter media and products to only show objects owned by the vendor
			add_filter( 'parse_query', array( $this, 'filter_vendor' ) );
			add_action( 'ajax_query_attachments_args', array( $this, 'filter_media_modal' ) );

			// Disable wcvendors restrict admin
			add_action( 'admin_init', array( $this, 'prevent_admin_access' ) );
			add_filter( 'woocommerce_prevent_admin_access', array( $this, 'disable_admin_access' ) );

			// Remove Dashboard Widgets
			add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
		}

	}

	/**
	 * Filter admin queries to only show vendor posts ( custom/attachments/any )
	 */
	public function filter_vendor( $wp_query ) {
		$wp_query->set( 'author', get_current_user_id() );
	}

	/**
	 * Filter the media modal to only show the vendors attachments
	 */
	public function filter_media_modal( $query = array() ) {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$query['author'] = $user_id;
		}
		return $query;
	}

	/**
	 * Allow vendors to see wp-admin
	 * This is only for testing
	 */
	public function prevent_admin_access() {
		$permitted_user = ( current_user_can( 'edit_posts' ) || current_user_can( 'manage_wcvendors' ) || current_user_can( 'vendor' ) );

		if ( get_option( 'wcvendors_lock_down_admin' ) == 'yes' && ! is_ajax() && ! $permitted_user ) {
			wp_safe_redirect( get_permalink( wcvendors_get_page_id( 'myaccount' ) ) );
			exit;
		}
	}

	/**
	 * Disable the admin access restrictions from WooCommerce
	 */
	public function disable_admin_access() {
		return false;
	}

	/**
	 * Remove all dashboard widgets not relevant to a vendor
	 */
	public function remove_dashboard_widgets() {
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );      // Quick Press widget
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );      // Recent Drafts
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );      // WordPress.com Blog
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );      // Other WordPress News
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );    // Incoming Links
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );    // Plugins
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );     // Activity
	}

}

new WCVendors_Admin_Vendor();
