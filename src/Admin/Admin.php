<?php

namespace WCVendors\Admin;

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
class Admin {

	/**
	 * Constructor
	 */
	public function init_hooks() {
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
		do_action( 'wcvendors_admin_loaded' );
	}

	/**
	 * Change the admin footer text on WooCommerce admin pages.
	 *
	 * @param string $footer_text
	 *
	 * @return string
	 * @since  2.0.0
	 */
	public function admin_footer_text( $footer_text ) {
		if ( ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'wcv_get_screen_ids' ) ) {
			return $footer_text;
		}

		$current_screen = get_current_screen();
		$wcv_pages      = wcv_get_screen_ids();

		// Set only WCV pages.
		// Check to make sure we're on a WC Vendors admin page.
		if ( isset( $current_screen->id ) && apply_filters( 'wcvendors_display_admin_footer_text', in_array( $current_screen->id, $wcv_pages ) ) ) {
			// Change the footer text
			if ( ! get_option( 'wcvendors_admin_footer_text_rated' ) ) {
				$footer_text = sprintf(
				/* translators: 1: WC Vendors 2:: five stars */
					__( 'If you like %1$s please leave us a %2$s rating. Your ratings help others choose the best product!. Thanks for your support', 'wc-vendors' ),
					sprintf( '<strong>%s</strong>', esc_html__( 'WC Vendors', 'wc-vendors' ) ),
					'<a href="https://wordpress.org/support/plugin/wc-vendors/reviews?rate=5#new-post" target="_blank" class="wcv-rating-link" data-rated="' . esc_attr__( 'Thanks for your support! - WC Vendors Team', 'wc-vendors' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
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
				$footer_text = __( 'Thank you for creating your marketplace with WC Vendors.', 'wc-vendors' );
			}
		}

		return $footer_text;
	}
}
