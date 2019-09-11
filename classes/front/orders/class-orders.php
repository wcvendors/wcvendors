<?php

/**
 * My account views
 *
 * @author  Matt Gates <http://mgates.me>, WC Vendors <http://wcvendors.com>
 * @package WCVendors
 */


class WCV_Orders {


	/**
	 * __construct()
	 */
	function __construct() {

		$this->can_view_orders        = wc_string_to_bool( get_option( 'wcvendors_capability_orders_enabled', 'no' ) );
		$this->can_export_csv         = wc_string_to_bool( get_option( 'wcvendors_capability_orders_export', 'no' ) );
		$this->can_view_emails        = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_email', 'no' ) );
		$this->can_view_name     	  = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_name', 'no' ) );
		$this->can_view_shipping_name = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_shipping_name', 'no' ) );
		$this->can_view_address       = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_shipping' ) );

		add_action( 'template_redirect', array( $this, 'check_access' ) );
		add_action( 'template_redirect', array( $this, 'process_export_orders' ) );
		add_shortcode( 'wcv_orders', array( $this, 'display_product_orders' ) );
	}


	/**
	 *
	 */
	public function check_access() {

		global $post;

		$orders_page = get_option( 'wcvendors_product_orders_page_id' );

		// Only if the orders page is set should we check access
		if ( $orders_page && is_page( $orders_page ) && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wcv_orders' ) && ! is_user_logged_in() ) {
			wp_redirect( get_permalink( wc_get_page_id( 'myaccount' ) ), 303 );
			exit;
		}

	} // check_access()


	/**
	 *  Processs export orders csv request
	 *
	 * @since 1.9.4
	 */
	public function process_export_orders() {

		if ( empty( $_GET['orders_for_product'] ) ) {

			return sprintf( __( 'You haven\'t selected a product\'s orders to view! Please go back to the %s Dashboard and click Show Orders on the product you\'d like to view.', 'wc-vendors' ), wcv_get_vendor_name() );

		} else {
			$this->product_id = ! empty( $_GET['orders_for_product'] ) ? (int) $_GET['orders_for_product'] : false;

			$products = array( $this->product_id );

			$_product = wc_get_product( $this->product_id );

			if ( is_object( $_product ) ) {

				$children = $_product->get_children();

				if ( ! empty( $children ) ) {
					$products = array_merge( $products, $children );
					$products = array_unique( $products );
				}
			}

			$this->orders = WCV_Queries::get_orders_for_products( $products, array( 'vendor_id' => get_current_user_id() ) );
		}

		if ( ! $this->orders ) {
			return __( 'No orders.', 'wc-vendors' );
		}

		if ( $this->can_export_csv && ! empty( $_POST['export_orders'] ) ) {
			$this->download_csv();
		}

	}  // process_export_orders()

	/**
	 *
	 *
	 * @return unknown
	 */
	public function download_csv() {

		if ( ! $this->orders ) {
			return false;
		}

		extract( WCV_Orders::format_order_details( $this->orders, $this->product_id ) );
		$headers = WCV_Orders::get_headers();

		// Export the CSV
		require_once wcv_plugin_dir . 'classes/front/orders/class-export-csv.php';
		WCV_Export_CSV::output_csv( $this->product_id, $headers, $body, $items );
	}


	/**
	 * Use views to display the Orders page
	 *
	 * @return html
	 */
	public function display_product_orders() {

		if ( ! WCV_Vendors::is_vendor( get_current_user_id() ) ) {
			ob_start();
			wc_get_template( 'denied.php', array(), 'wc-vendors/dashboard/', wcv_plugin_dir . 'templates/dashboard/' );

			return ob_get_clean();
		}

		if ( empty( $_GET['orders_for_product'] ) ) {

			return sprintf( __( 'You haven\'t selected a product\'s orders to view! Please go back to the %s Dashboard and click Show Orders on the product you\'d like to view.', 'wc-vendors' ), wcv_get_vendor_name() );

		} else {
			$this->product_id = ! empty( $_GET['orders_for_product'] ) ? (int) $_GET['orders_for_product'] : false;

			$products = array( $this->product_id );

			$_product = wc_get_product( $this->product_id );

			if ( is_object( $_product ) ) {

				$children = $_product->get_children();

				if ( ! empty( $children ) ) {
					$products = array_merge( $products, $children );
					$products = array_unique( $products );
				}
			}

			$this->orders = WCV_Queries::get_orders_for_products( $products, array( 'vendor_id' => get_current_user_id() ) );
		}

		if ( ! $this->orders ) {
			return __( 'No orders.', 'wc-vendors' );
		}

		if ( ! empty( $_POST['submit_comment'] ) ) {
			require_once wcv_plugin_dir . 'classes/front/orders/class-submit-comment.php';
			WCV_Submit_Comment::new_comment( $this->orders );
		}

		if ( isset( $_POST['mark_shipped'] ) ) {
			$order_id   = (int) $_POST['order_id'];
			$product_id = (int) $_POST['product_id'];
			exit;
		}

		$headers = WCV_Orders::get_headers();
		$all     = WCV_Orders::format_order_details( $this->orders, $this->product_id );

		wp_enqueue_script( 'pv_frontend_script', wcv_assets_url . 'js/front-orders.js' );

		$providers      = array();
		$provider_array = array();

		// WC Shipment Tracking Providers
		if ( class_exists( 'WC_Shipment_Tracking' ) ) {
			$WC_Shipment_Tracking = new WC_Shipment_Tracking();
			$providers            = ( method_exists( $WC_Shipment_Tracking, 'get_providers' ) ) ? $WC_Shipment_Tracking->get_providers() : $WC_Shipment_Tracking->providers;
			$provider_array       = array();
			foreach ( $providers as $all_providers ) {
				foreach ( $all_providers as $provider => $format ) {
					$provider_array[ sanitize_title( $provider ) ] = urlencode( $format );
				}
			}
		}

		// Show the Export CSV button
		if ( $this->can_export_csv ) {
			wc_get_template( 'csv-export.php', array(), 'wc-vendors/orders/', wcv_plugin_dir . 'templates/orders/' );
		}

		wc_get_template(
			'orders.php', array(
			'headers'        => $headers,
			'body'           => $all['body'],
			'items'          => $all['items'],
			'product_id'     => $all['product_id'],
			'providers'      => $providers,
			'provider_array' => $provider_array,
		), 'wc-vendors/orders/', wcv_plugin_dir . 'templates/orders/'
		);

	} // display_product_orders()


	/**
	 * Headers for the Orders page
	 *
	 * @return array
	 */
	public function get_headers() {

		$headers = array(
			'order'   => __( 'Order', 'wc-vendors' ),
			'product' => __( 'Product Title', 'wc-vendors' ),
			'name'    => __( 'Full name', 'wc-vendors' ),
			'address' => __( 'Address', 'wc-vendors' ),
			'city'    => __( 'City', 'wc-vendors' ),
			'state'   => __( 'State', 'wc-vendors' ),
			'zip'     => __( 'Zip', 'wc-vendors' ),
			'email'   => __( 'Email address', 'wc-vendors' ),
			'date'    => __( 'Date', 'wc-vendors' ),
		);

		if ( ! $this->can_view_emails ) {
			unset( $headers['email'] );
		}

		if ( ! $this->can_view_name ) {
			unset( $headers['name'] );
		}

		if ( ! $this->can_view_address ) {
			unset( $headers['address'] );
			unset( $headers['city'] );
			unset( $headers['state'] );
			unset( $headers['zip'] );
		}

		return $headers;
	}


	/**
	 * Format the orders with just the products we want
	 *
	 * @param object $orders
	 * @param int    $product_id
	 *
	 * @return array
	 */
	public function format_order_details( $orders, $product_id ) {

		$body    = $items = array();
		$product = wc_get_product( $product_id )->get_title();

		foreach ( $orders as $i => $order ) {
			$i          = $order->order_id;
			$order      = wc_get_order( $i );
			$order_date = $order->get_date_created();

			$shipping_first_name = $order->get_shipping_first_name();
			$shipping_last_name  = $order->get_shipping_last_name();
			$shipping_address_1  = $order->get_shipping_address_1();
			$shipping_city       = $order->get_shipping_city();
			$shipping_country    = $order->get_shipping_country();
			$shipping_state      = $order->get_shipping_state();
			$shipping_postcode   = $order->get_shipping_postcode();
			$billing_email       = $order->get_billing_email();
			$customer_note       = $order->get_customer_note();

			$body[ $order->get_order_number() ] = array(
				'order_number' => $order->get_order_number(),
				'product'      => $product,
				'name'         => $shipping_first_name . ' ' . $shipping_last_name,
				'address'      => $shipping_address_1,
				'city'         => $shipping_city,
				'state'        => $shipping_state,
				'zip'          => $shipping_postcode,
				'email'        => $billing_email,
				'date'         => date_i18n( wc_date_format(), strtotime( $order_date ) ),
				'comments'     => wptexturize( $customer_note ),
			);

			if ( ! $this->can_view_emails ) {
				unset( $body[ $i ]['email'] );
			}

			if ( ! $this->can_view_name ) {
				unset( $body[ $i ]['name'] );
			}

			if ( ! $this->can_view_address ) {
				unset( $body[ $i ]['address'] );
				unset( $body[ $i ]['city'] );
				unset( $body[ $i ]['state'] );
				unset( $body[ $i ]['zip'] );
			}

			$items[ $i ]['total_qty'] = 0;
			foreach ( $order->get_items() as $line_id => $item ) {

				if ( $item['product_id'] != $product_id && $item['variation_id'] != $product_id ) {
					continue;
				}

				$items[ $i ]['items'][]   = $item;
				$items[ $i ]['total_qty'] += $item['qty'];
			}
		}

		return array(
			'body'       => $body,
			'items'      => $items,
			'product_id' => $product_id,
		);
	}


	/**
	 * Verify the current user can view orders for a product
	 *
	 * @param int $product_id
	 */
	public function verify_order_access() {

		if ( ! is_user_logged_in() || empty( $this->product_id ) ) {
			wp_safe_redirect( apply_filters( 'woocommerce_get_myaccount_page_id', get_permalink( woocommerce_get_page_id( 'myaccount' ) ) ) );
			exit;
		}

		$product = get_post( $this->product_id );
		if ( empty( $product ) || $product->post_type != 'product' || get_current_user_id() != $product->post_author ) {
			wp_safe_redirect( apply_filters( 'woocommerce_get_myaccount_page_id', get_permalink( woocommerce_get_page_id( 'myaccount' ) ) ) );
			exit;
		}
	}

	/**
	 * Get the variation data for a product
	 *
	 * @since 1.9.4
	 * @return string variation_data
	 */
	public static function get_variation_data( $item_id ) {

		$_var_product     = new WC_Product_Variation( $item_id );
		$variation_data   = $_var_product->get_variation_attributes();
		$variation_detail = wc_get_formatted_variation( $variation_data, true );

		return $variation_detail;

	} // get_variation_data()

}
