<?php
/**
 * Admin setup
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class WCV_Admin_Setup {

	public function __construct() {

		// add_action( 'admin_menu', 											array( 'WCV_Admin_Setup', 'menu' ), 10 );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'add_vendor_details'), 10, 2 );
		add_action( 'woocommerce_admin_order_actions_end'                , array( $this, 'append_actions' )   , 10, 1 );
		add_filter( 'woocommerce_debug_tools'                            , array( $this, 'wcvendors_tools' )          );

		add_filter( 'admin_footer_text'                    , array( $this, 'admin_footer_text' ), 1   );
		add_action( 'admin_init'                           , array( $this, 'export_commissions' )     );
		add_action( 'admin_init'                           , array( $this, 'export_sum_commissions' ) );
		add_action( 'admin_init'                           , array( $this, 'mark_all_paid' ) );
		add_filter( 'woocommerce_screen_ids'               , array( $this, 'wcv_screen_ids' )         );
		add_action( 'wcvendors_update_options_capabilities', array( $this, 'update_vendor_role' )     );

		add_filter( 'woocommerce_inventory_settings', 		array ( $this, 'add_vendor_stock_notification' ) );
	}

	public function add_vendor_details( $order ) {

		$actions = $this->append_actions( $order, true );

		if ( empty( $actions['wc_pv_shipped']['name'] ) ) {
			return;
		}

		echo '<h4>' . __( 'Vendors shipped', 'wc-vendors' ) . '</h4><br/>';
		echo $actions['wc_pv_shipped']['name'];
	}

	public function append_actions( $order, $order_page = false ) {

		global $woocommerce;

		$order_id = $order->get_id();

		$authors = WCV_Vendors::get_vendors_from_order( $order );
		$authors = $authors ? array_keys( $authors ) : array();
		if ( empty( $authors ) ) {
			return false;
		}

		$shipped = (array) get_post_meta( $order_id, 'wc_pv_shipped', true );
		$string  = '</br></br>';

		foreach ( $authors as $author ) {
			$string .= in_array( $author, $shipped ) ? '&#10004; ' : '&#10005; ';
			$string .= WCV_Vendors::get_vendor_shop_name( $author );
			$string .= '</br>';
		}

		$response = array(
			'url'       => '#',
			'name'      => __( 'Vendors Shipped', 'wc-vendors' ) . $string,
			'action'    => 'wc_pv_shipped',
			'image_url' => wcv_assets_url . '/images/icons/truck.png',
		);

		if ( ! $order_page ) {
			printf( '<a class="button tips %s" href="%s" data-tip="%s"><img style="width:16px;height:16px;" src="%s"></a>', $response['action'], $response['url'], $response['name'], $response['image_url'] );
		} else {
			echo $response['name'];
		}

		return $response;
	}


	/**
	 * Add tools to the woocommerce status tools page
	 *
	 * @since  1.9.2
	 * @access public
	 */
	public function wcvendors_tools( $tools ) {

		$tools['reset_wcvendor_roles'] = array(
			'name'     => __( 'Reset WC Vendors roles ', 'wc-vendors' ),
			'button'   => __( 'Reset WC Vendor Roles', 'wc-vendors' ),
			'desc'     => __( 'This will reset the wcvendors roles ( vendor & pending_vendor ), back to the default capabilities.', 'wc-vendors' ),
			'callback' => array( 'WCV_Admin_Setup', 'reset_vendor_roles' ),
		);

		$tools['reset_wcvendors'] = array(
			'name'     => __( 'Reset WC Vendors ', 'wc-vendors' ),
			'button'   => __( 'Reset WC Vendors Settings', 'wc-vendors' ),
			'desc'     => __( 'This will reset wcvendors back to defaults. This DELETES ALL YOUR Settings.', 'wc-vendors' ),
			'callback' => array( 'WCV_Admin_Setup', 'reset_wcvendors' ),
		);

		$tools['remove_suborders'] = array(
			'name'     => __( 'Remove orphaned sub orders', 'wc-vendors' ),
			'button'   => __( 'Remove orphaned sub orders', 'wc-vendors' ),
			'desc'     => __( 'This will remove all orphaned sub orders ', 'wc-vendors' ),
			'callback' => array( 'WCV_Admin_Setup', 'remove_orphaned_orders' ),
		);

		return $tools;

	} // wcvendors_tools()

	/**
	 * Reset the vendor roles
	 *
	 * @since  1.9.2
	 * @access public
	 */
	public static function reset_vendor_roles() {

		$can_add         = wc_string_to_bool( get_option( 'wcvendors_capability_products_enabled', 'no' ) );
		$can_edit        = wc_string_to_bool( get_option( 'wcvendors_capability_products_edit'   , 'no' ) );
		$can_submit_live = wc_string_to_bool( get_option( 'wcvendors_capability_products_live'   , 'no' ) );

		$args = array(
			'assign_product_terms'      => $can_add,
			'edit_products'             => $can_add || $can_edit,
			'edit_product'              => $can_add || $can_edit,
			'edit_published_products'   => $can_edit,
			'delete_published_products' => $can_edit,
			'delete_products'           => $can_edit,
			'manage_product'            => $can_add,
			'publish_products'          => $can_submit_live,
			'delete_posts'              => true,
			'read'                      => true,
			'read_products'             => $can_edit || $can_add,
			'upload_files'              => true,
			'import'                    => true,
			'view_woocommerce_reports'  => false,
		);

		remove_role( 'vendor' );
		add_role( 'vendor', sprintf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ), $args );

		remove_role( 'pending_vendor' );
		add_role(
			'pending_vendor',
			sprintf( __( 'Pending %s', 'wc-vendors' ), wcv_get_vendor_name() ),
			array(
				'read'         => true,
				'edit_posts'   => false,
				'delete_posts' => false,
			)
		);

		// Reset the capabilities
		WCVendors_Install::create_capabilities();

		echo '<div class="updated inline"><p>' . __( 'WC Vendor roles successfully reset.', 'wc-vendors' ) . '</p></div>';

	} // reset_vendor_roles()


	/**
	 * Reset wcvendors
	 *
	 * @since  1.9.2
	 * @access public
	 */
	public static function reset_wcvendors() {

		delete_option( WC_Vendors::$id . '_options' );
		echo '<div class="updated inline"><p>' . __( 'WC Vendors was successfully reset. All settings have been reset.', 'wc-vendors' ) . '</p></div>';

	} // reset_wcvendors()


	/**
	 *  Clean up orphaned Vendor sub orders that do not have parent posts
	 *
	 * @since 2.1.13
	 */
	public static function remove_orphaned_orders(){

		$args = array(
			'post_status' => 'any',
			'numberposts' => -1,
			'post_type' => 'shop_order_vendor',
			'fields' 	=> array( 'ID', 'post_parent' ),
		);

		$vendor_sub_orders = get_posts( $args );

		if ( empty( $vendor_sub_orders ) ) return;

		foreach ( $vendor_sub_orders as $vendor_sub_order ) {
			if ( ! get_post_status( $vendor_sub_order->post_parent ) ){
				wp_delete_post( $vendor_sub_order->ID, true );
			}
		}

		echo '<div class="updated inline"><p>' . __( 'Orphaned sub orders have been removed.', 'wc-vendors' ) . '</p></div>';
	}


	/*
	*	Export commissions via csv
	*/
	public function export_commissions() {

		// prepare the items to export
		if ( isset( $_GET['action'], $_GET['nonce'] ) && wp_verify_nonce( wp_unslash( $_GET['nonce'] ), 'export_commissions' ) && 'export_commissions' === wp_unslash( $_GET['action'] ) ) {

			include_once 'class-wcv-commissions-csv-exporter.php';

			$exporter = new WCV_Commissions_CSV_Export();

			$date = gmdate( 'Y-M-d' );

			if ( ! empty( $_GET['com_status'] ) ) { // WPCS: input var ok.
				$exporter->set_filename( 'wcv_commissions_' . wp_unslash( $_GET['com_status'] ) . '-' . $date . '.csv' ); // WPCS: input var ok, sanitization ok.
			} else {
				$exporter->set_filename( 'wcv_commissions-' . $date . '.csv' ); // WPCS: input var ok, sanitization ok.
			}

			$exporter->export();
		}

	}

	/*
	*	Export sum commissions via csv
	*/
	public function export_sum_commissions() {

		// prepare the items to export
		if ( isset( $_GET['action'], $_GET['nonce'] ) && wp_verify_nonce( wp_unslash( $_GET['nonce'] ), 'export_commission_totals' ) && 'export_commission_totals' === wp_unslash( $_GET['action'] ) ) {

			include_once 'class-wcv-commissions-sum-csv-exporter.php';

			$exporter = new WCV_Commissions_Sum_CSV_Export();

			$date = gmdate( 'Y-M-d' );

			if ( ! empty( $_GET['com_status'] ) ) { // WPCS: input var ok.
				$exporter->set_filename( 'wcv_commissions_sum_' . wp_unslash( $_GET['com_status'] ) . '-' . $date . '.csv' ); // WPCS: input var ok, sanitization ok.
			} else {
				$exporter->set_filename( 'wcv_commissions_sum-' . $date . '.csv' ); // WPCS: input var ok, sanitization ok.
			}

			$exporter->export();
		}

	}

	/**
	 * Mark all commissions that are due as paid this is triggered by the Mark All Paid button on the commissions screen
	 *
	 * @since 2.1.10
	 * @version 2.1.10
	 */
	public function mark_all_paid() {

		// set all
		if ( isset( $_GET['action'], $_GET['nonce'] ) && wp_verify_nonce( wp_unslash( $_GET['nonce'] ), 'mark_all_paid' ) && 'mark_all_paid' === wp_unslash( $_GET['action'] ) ) {

			global $wpdb;
			$table_name = $wpdb->prefix . 'pv_commission';
			$query  = "UPDATE `{$table_name}` SET `status` = 'paid' WHERE `status` = 'due'";
			$result = $wpdb->query( $query );
			if ( $result ) add_action( 'admin_notices', array( $this, 'mark_all_paid__success' ) );

		}

	}


	public function mark_all_paid__success() {
    	echo '<div class="notice notice-success is-dismissible"><p>' . __( 'All commissions marked as paid.', 'wc-vendors' ) .'</p></div>';
	}



	/**
	 * Add wc vendors screens to woocommerce screen ids to utilise js and css assets from woocommerce.
	 *
	 * @since 2.0.0
	 */
	public function wcv_screen_ids( $screen_ids ) {

		$screen = get_current_screen();

		$wcv_screen_ids = wcv_get_screen_ids();
		$screen_ids     = array_merge( $wcv_screen_ids, $screen_ids );

		return $screen_ids;
	}


	/**
	 * Change the admin footer text on WooCommerce admin pages.
	 *
	 * @since  2.0.0
	 *
	 * @param  string $footer_text
	 *
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {

		if ( ! current_user_can( 'manage_woocommerce' ) || ! function_exists( 'wcv_get_screen_ids' ) ) {
			return $footer_text;
		}
		$current_screen = get_current_screen();
		$wcv_pages      = wcv_get_screen_ids();

		// Set only WC pages.
		// $wcv_pages = array_diff( $wcv_pages, array( 'profile', 'user-edit' ) );
		// Check to make sure we're on a WooCommerce admin page.
		if ( isset( $current_screen->id ) && apply_filters( 'wcvendors_display_admin_footer_text', in_array( $current_screen->id, $wcv_pages ) ) ) {
			// Change the footer text
			$footer_text = sprintf(
			/* translators: 1: WooCommerce 2:: five stars */
				__( 'If you like %1$s please leave us a %2$s rating. A huge thanks in advance!', 'wc-vendors' ),
				sprintf( '<strong>%s</strong>', esc_html__( 'WC Vendors', 'wc-vendors' ) ),
				'<a href="https://wordpress.org/support/plugin/wc-vendors/reviews?rate=5#new-post" target="_blank" class="wcv-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'wc-vendors' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}

	/**
	 * Update the vendor role based on the capabilities saved.
	 */
	public function update_vendor_role() {

		$can_add         = wc_string_to_bool( get_option( 'wcvendors_capability_products_enabled', 'no' ) );
		$can_edit        = wc_string_to_bool( get_option( 'wcvendors_capability_products_edit'   , 'no' ) );
		$can_submit_live = wc_string_to_bool( get_option( 'wcvendors_capability_products_live'   , 'no' ) );

		$args = array(
			'assign_product_terms'      => $can_add,
			'edit_products'             => $can_add || $can_edit,
			'edit_product'              => $can_add || $can_edit,
			'edit_published_products'   => $can_edit,
			'delete_published_products' => $can_edit,
			'delete_products'           => $can_edit,
			'delete_posts'              => true,
			'manage_product'            => $can_add,
			'publish_products'          => $can_submit_live,
			'read'                      => true,
			'read_products'             => $can_edit || $can_add,
			'upload_files'              => true,
			'import'                    => true,
			'view_woocommerce_reports'  => false,
		);

		remove_role( 'vendor' );
		add_role( 'vendor', sprintf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ), $args );

	}

	/**
	* Add options to disable vendor low / no stock notifications
	*
	* @since 2.1.10
	* @version 2.1.10
	*
	*/
	public function add_vendor_stock_notification( $options ){
		$new_options = array();

		foreach ( $options as $option ) {
		 	if ( $option['id'] == 'woocommerce_stock_email_recipient' ){
		 		// Low stock
		 		$new_options[] = array(
					'title'         => sprintf( __( '%s Notifications', 'wc-vendors' ), wcv_get_vendor_name() ),
					'desc'          => sprintf( __( 'Enable %s low stock notifications', 'wc-vendors' ),  wcv_get_vendor_name( true, false) ),
					'id'            => 'wcvendors_notify_low_stock',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'checkboxgroup' => 'start',
					'class'         => 'manage_stock_field',
				);
				// No Stock
		 		$new_options[] = array(
					'desc'          => sprintf( __( 'Enable %s out of stock notifications', 'wc-vendors' ),  wcv_get_vendor_name( true, false) ),
					'id'            => 'wcvendors_notify_no_stock',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'checkboxgroup' => 'middle',
					'class'         => 'manage_stock_field',
				);
		 		// Back order
				$new_options[] = array(
					'desc'          => sprintf( __( 'Enable %s backorder stock notifications', 'wc-vendors' ),  wcv_get_vendor_name( true, false) ),
					'id'            => 'wcvendors_notify_backorder_stock',
					'default'       => 'yes',
					'type'          => 'checkbox',
					'checkboxgroup' => 'end',
					'class'         => 'manage_stock_field',
				);

		 	}
		 	$new_options[] = $option;
		}
		return $new_options;
	}

}
