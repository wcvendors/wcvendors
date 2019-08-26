<?php

/**
 * Add vendor signup option on WooCommerce My Account Page
 *
 * @author  Lindeni Mahlalela <https://lindeni.co.za>
 * @package WCVendors
 */
class WCV_Account_Links extends WCV_Vendor_Signup {

	public $terms_page;

	/**
	 * Constructor
	 *
	 * @description adds the action hooks and gets the terms page
	 * @package
	 * @since
	 */
	public function __construct() {

		// Only enable this if registration for vendors is enabled
		if ( ! wc_string_to_bool( get_option( 'wcvendors_vendor_allow_registration', 'no' ) ) ) {
			return;
		}

		$show_become_a_vendor_link = wc_string_to_bool( get_option( 'wcvendors_become_a_vendor_my_account_link_visibility' ) );

		if ( WCV_Vendors::is_vendor( get_current_user_id() ) || ! $show_become_a_vendor_link ) {
			return;
		}

		$this->terms_page = get_option( 'wcvendors_vendor_terms_page_id' );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_menu_items' ) );
		add_action( 'woocommerce_account_become-a-vendor_endpoint', array( $this, 'render_vendor_signup' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ), 0 );
		add_action( 'wcvendors_flush_rewrite_rules', array( $this, 'flush_rewrite_rules' ) );
	}

	/**
	 * Add accounts menu item
	 *
	 * @param array $items
	 *
	 * @return void
	 * @description Add Become a Vendor Link to my accounts navigation
	 * @package
	 * @since
	 */
	public function add_account_menu_items( $items ) {

		$become_a_vendor_label = __( get_option( 'wcvendors_label_become_a_vendor', __( 'Become a', 'wc-vendors' ) ), 'wc-vendors' );

		$add_items = apply_filters(
			'wcv_become_a_vendor_string',
			array(
				'become-a-vendor' => sprintf( __( '%s %s', 'wc-vendors' ), $become_a_vendor_label, wcv_get_vendor_name() )
			)
		);

		// slice the array so the logout link goes at the end of the list.
		$first_part = array_slice( $items, 0, count( $items ) - 1, true );
		$last_part  = array_slice( $items, count( $items ) - 1, true );
		// put the arrays together putting the logout link at the end.
		$items = $first_part + $add_items + $last_part;

		return $items;
	}

	/**
	 * Add the become-a-vendor in the global query object
	 *
	 * @param array $vars
	 *
	 * @return void
	 */
	public function query_vars( $vars ) {

		$vars[] = 'become-a-vendor';

		return $vars;
	}

	/**
	 * Flushes rewrite rules when a Theme / WC Vendors settings are changed
	 *
	 * @return void
	 */
	public function flush_rewrite_rules() {

		flush_rewrite_rules();
	}

	/**
	 * Render the become a vendor signup page in the my account page
	 * If the current user is already a vendor, hide the signup form and show a message
	 *
	 * @return void
	 */
	public function render_vendor_signup() {

		if ( WCV_Vendors::is_vendor( get_current_user_id() ) ) {
			echo '<div class="woocommerce-message" role="alert"><p>' . __( 'You are already an approved vendor, no need to apply', 'wc-vendors' ) . '</p></div>';
		} else {

			if ( ! class_exists( 'WCV_Vendor_Signup' ) ) {
				include_once wcv_plugin_dir . 'classes/front/signup/class-vendor-signup.php';
			}

			if ( isset( $_POST['apply_for_vendor'] ) ) {
				self::apply_form_dashboard();
			}

			require_once wcv_plugin_dir . 'templates/dashboard/denied.php';
		}
	}
}
