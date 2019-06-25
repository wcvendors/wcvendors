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
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 100 );
		add_action( 'admin_footer', [ $this, 'output_vendor_select_box' ] );
		add_action( 'admin_notices', [ $this, 'bulk_actions_admin_notice' ] );
	}

	public function register_bulk_actions( $bulk_actions ) {
		/* translators: %s is vendor string */
		$bulk_actions['assign_vendor'] = sprintf( __( 'Assign %s', 'wc-vendors' ), wcv_get_vendor_name() );
		return $bulk_actions;
	}

	public function bulk_action_handler( $redirect_to, $doaction, $attachment_ids ) {

		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-media' ) ) {
			return $redirect_to;
		}

		if ( $doaction != 'assign_vendor' ) {
			return $redirect_to;
		}

		$vendor_to_assign = 0;
		if ( isset( $_REQUEST['vendor'] ) && $_REQUEST['vendor'] && $_REQUEST['vendor'] != '-1' ) {
			$vendor_to_assign = $_REQUEST['vendor'];
		} elseif ( isset( $_REQUEST['vendor2'] ) && $_REQUEST['vendor2'] && $_REQUEST['vendor2'] != '-1' ) {
			$vendor_to_assign = $_REQUEST['vendor2'];
		}

		if ( ! $vendor_to_assign ) {
			return $redirect_to;
		}

		foreach ( $attachment_ids as $attachment_id ) {
			wp_update_post(
				[
					'ID'          => $attachment_id,
					'post_author' => $vendor_to_assign,
				]
			);
		}

		$redirect_to = add_query_arg( 'assigned_attachment', count( $attachment_ids ), $redirect_to );
		$redirect_to = add_query_arg( 'vendor_id', $vendor_to_assign, $redirect_to );

		return $redirect_to;
	}

	public function enqueue_scripts() {
		if ( ! $this->is_upload_page() ) {
			return;
		}

		wp_enqueue_style(
			'wcv-admin-media-bulk-actions-select2',
			wcv_assets_url . 'css/select2.min.css',
			[],
			'4.0.3'
		);
		wp_enqueue_script(
			'wcv-admin-media-bulk-actions-select2',
			wcv_assets_url . 'js/select2.min.js',
			[ 'jquery' ],
			'4.0.3',
			true
		);

		wp_enqueue_script(
			'wcv-admin-media-bulk-actions',
			wcv_assets_url . 'js/admin/wcv-admin-media-bulk-actions.js',
			[ 'wcv-admin-media-bulk-actions-select2' ],
			'1.0.0',
			true
		);
	}

	public function is_upload_page() {
		$current_screen = get_current_screen();

		if ( $current_screen->id != 'upload' ) {
			return false;
		}

		return true;
	}

	public function output_vendor_select_box() {
		if ( ! $this->is_upload_page() ) {
			return;
		}

		wp_dropdown_users(
			[
				'show_option_none' => __( 'None', 'wc-vendors' ),
				'name'             => 'vendor',
				'id'               => 'vendor',
				'class'            => 'hidden assign-vendor',
				'role'             => 'vendor',
			]
		);
	}

	public function bulk_actions_admin_notice() {
		if ( ! isset( $_REQUEST['assigned_attachment'] ) || ! isset( $_REQUEST['vendor_id'] ) ) {
			return;
		}
		$count     = intval( $_REQUEST['assigned_attachment'] );
		$vendor_id = intval( $_REQUEST['vendor_id'] );
		$vendor    = new WP_User( $vendor_id );

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			sprintf(
				/* translators: %d number of assigned media files. %s vendor display name */
				_n( '%1$d media file has been assigned to %2$s.', '%1$d media files have been assigned to %2$s.', $count, 'wc-vendor' ),
				esc_attr( number_format_i18n( $count ) ),
				esc_html( $vendor->display_name )
			)
		);

	}
}

new WCVendors_Admin_Media();
