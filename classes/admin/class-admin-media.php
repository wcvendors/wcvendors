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
class WCVendors_Admin_Media {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'bulk_actions-upload', [ $this, 'register_bulk_actions' ] );
		add_filter( 'handle_bulk_actions-upload', [ $this, 'bulk_action_handler' ], 10, 3 );
	}
 
	public function register_bulk_actions($bulk_actions) {
		$bulk_actions['assign_vendor'] = __( 'Assign vendor', 'wc-vendors');
		return $bulk_actions;
	}
 
	public function bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
	}
}

new WCVendors_Admin_Media();
