<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin class handles all related functions for admin view
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */
class WCV_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );

		do_action( 'wcvendors_admin_loaded' );
	}

	/**
	 * Include any files required in Admin only
	 */
	public function includes() {

		include_once WCV_ABSPATH_ADMIN . 'wcv-admin-functions.php';
		include_once WCV_ABSPATH_ADMIN . 'class-wcv-admin-commission-table.php';
		include_once WCV_ABSPATH_ADMIN . 'class-wcv-admin-commission.php';
		include_once WCV_ABSPATH_ADMIN . 'class-wcv-admin-vendor.php';
		include_once WCV_ABSPATH_ADMIN . 'class-wcv-admin-post-types.php';
		include_once WCV_ABSPATH_ADMIN . 'class-wcv-admin-meta-boxes.php';
		include_once WCV_ABSPATH_ADMIN . 'class-wcv-admin-settings.php';
		include_once WCV_ABSPATH_ADMIN . 'class-wcv-admin-menus.php';
		include_once WCV_ABSPATH_ADMIN . 'class-wcv-admin-assets.php';
		include_once WCV_ABSPATH_ADMIN . 'class-wcv-admin-notices.php';

		// Setup/welcome
		if ( ! empty( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case 'wcv-setup':
					include_once WCV_ABSPATH_ADMIN . '/class-wcv-admin-setup-wizard.php';
					break;
			}
		}
	}

	/**
	 * Conditionally include files for the admin area
	 */
	public function conditional_includes() {

		if ( ! $screen = get_current_screen() ) {
			return;
		}

		switch ( $screen->id ) {
			case 'dashboard':
				break;
			case 'options-permalink':
				// include( 'class-wcv-admin-permalink-settings.php' );
				break;
			case 'users':
			case 'user':
			case 'profile':
			case 'user-edit':
				include 'class-wcv-admin-profile.php';
				break;
		}
	}

	/**
	 * Change the admin footer text on WooCommerce admin pages.
	 *
	 * @since  2.0.0
	 * @param  string $footer_text
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {

		if ( ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'wcv_get_screen_ids' ) ) {
			return $footer_text;
		}

		$current_screen = get_current_screen();
		$wcv_pages      = wcv_get_screen_ids();

		// Set only WCV pages.
		// $wcv_pages = array_diff( $current_screen, array( 'profile', 'user-edit' ) );
		// Check to make sure we're on a WC Vendors admin page.
		if ( isset( $current_screen->id ) && apply_filters( 'wcvendors_display_admin_footer_text', in_array( $current_screen->id, $wcv_pages ) ) ) {
			// Change the footer text
			if ( ! get_option( 'wcvendors_admin_footer_text_rated' ) ) {
				$footer_text = sprintf(
					/* translators: 1: WC Vendors 2:: five stars */
					__( 'If you like %1$s please leave us a %2$s rating. Your ratings help others choose the best product!. Thanks for your support', 'wcvendors' ),
					sprintf( '<strong>%s</strong>', esc_html__( 'WC Vendors', 'wcvendors' ) ),
					'<a href="https://wordpress.org/support/plugin/wc-vendors/reviews?rate=5#new-post" target="_blank" class="wcv-rating-link" data-rated="' . esc_attr__( 'Thanks for your support! - WC Vendors Team', 'wcvendors' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
				);
				wc_enqueue_js(
					"
					jQuery( 'a.wcv-rating-link' ).click( function() {
						jQuery.post( '" . WC()->ajax_url() . "', { action: 'wcvendors_rated' } );
						jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
					});
				"
				);
			} else {
				$footer_text = __( 'Thank you for creating your marketplace with WC Vendors.', 'wcvendors' );
			}
		}

		return $footer_text;
	}



}

return new WCV_Admin();
