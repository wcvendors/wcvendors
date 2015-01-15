<?php

/**
 * My account views
 *
 * @author  Matt Gates <http://mgates.me>
 * @package ProductVendor
 */


class WCV_Vendor_Dashboard
{


	/**
	 * __construct()
	 */
	function __construct()
	{
		add_shortcode( 'wcv_shop_settings', array( $this, 'display_vendor_settings' ) );
		add_shortcode( 'wcv_vendor_dashboard', array( $this, 'display_vendor_products' ) );

		add_action( 'template_redirect', array( $this, 'check_access' ) );
		add_action( 'init', array( $this, 'save_vendor_settings' ) );
	}

	public function save_vendor_settings()
	{
		global $woocommerce;

		$user_id = get_current_user_id();

		if ( !empty( $_GET['wc_pv_mark_shipped'] ) ) {
			$order_id = $_GET['wc_pv_mark_shipped'];
			$shippers = (array) get_post_meta( $order_id, 'wc_pv_shipped', true );

			if( in_array($user_id, $shippers)) {
				foreach ($shippers as $key => $value) {
					if ( $value == $user_id ) {
						unset($shippers[$key]);
						wc_add_notice( __( 'Order unmarked shipped.', 'wcvendors' ), 'success');
						break;
					}
				}
			} else {
				$shippers[] = $user_id;
				$mails = $woocommerce->mailer()->get_emails();
				if ( !empty( $mails ) ) {
					$mails[ 'WC_Email_Notify_Shipped' ]->trigger( $order_id, $user_id );
				}
				wc_add_notice( __( 'Order marked shipped.', 'wcvendors' ), 'success' );
			}

			update_post_meta( $order_id, 'wc_pv_shipped', $shippers );
			return;
		}

		if ( isset( $_POST[ 'update_tracking' ] ) ) {
			$order_id   = (int) $_POST[ 'order_id' ];
			$product_id = (int) $_POST[ 'product_id' ];

			$tracking_provider        = woocommerce_clean( $_POST[ 'tracking_provider' ] );
			$custom_tracking_provider = woocommerce_clean( $_POST[ 'custom_tracking_provider' ] );
			$custom_tracking_link     = woocommerce_clean( $_POST[ 'custom_tracking_link' ] );
			$tracking_number          = woocommerce_clean( $_POST[ 'tracking_number' ] );
			$date_shipped             = woocommerce_clean( strtotime( $_POST[ 'date_shipped' ] ) );

			$order    = new WC_Order( $order_id );
			$products = $order->get_items();
			foreach ( $products as $key => $value ) {
				if ( $value[ 'product_id' ] == $product_id || $value[ 'variation_id' ] == $product_id ) {
					$order_item_id = $key;
					break;
				}
			}
			if ( $order_item_id ) {
				woocommerce_delete_order_item_meta( 2048, __( 'Tracking number', 'wcvendors' ) );
				woocommerce_add_order_item_meta( 2048, __( 'Tracking number', 'wcvendors' ), $tracking_number );

				$message = __( 'Success. Your tracking number has been updated.', 'wcvendors' );
				wc_add_notice( $message, 'success' );

				// Update order data
				update_post_meta( $order_id, '_tracking_provider', $tracking_provider );
				update_post_meta( $order_id, '_custom_tracking_provider', $custom_tracking_provider );
				update_post_meta( $order_id, '_tracking_number', $tracking_number );
				update_post_meta( $order_id, '_custom_tracking_link', $custom_tracking_link );
				update_post_meta( $order_id, '_date_shipped', $date_shipped );
			}

		}

		if ( empty( $_POST[ 'vendor_application_submit' ] ) ) {
			return false;
		}

		if ( !wp_verify_nonce( $_POST[ 'wc-product-vendor-nonce' ], 'save-shop-settings' ) ) {
			return false;
		}


		if ( isset( $_POST[ 'pv_paypal' ] ) ) {
			if ( !is_email( $_POST[ 'pv_paypal' ] ) ) {
				wc_add_notice( __( 'Your PayPal address is not a valid email address.', 'wcvendors' ), 'error' );
			} else {
				update_user_meta( $user_id, 'pv_paypal', $_POST[ 'pv_paypal' ] );
			}
		}

		if ( !empty( $_POST[ 'pv_shop_name' ] ) ) {
			$users = get_users( array( 'meta_key' => 'pv_shop_slug', 'meta_value' => sanitize_title( $_POST[ 'pv_shop_name' ] ) ) );
			if ( !empty( $users ) && $users[ 0 ]->ID != $user_id ) {
				wc_add_notice( __( 'That shop name is already taken. Your shop name must be unique.', 'wcvendors' ), 'error' ); 
			} else {
				update_user_meta( $user_id, 'pv_shop_name', $_POST[ 'pv_shop_name' ] );
				update_user_meta( $user_id, 'pv_shop_slug', sanitize_title( $_POST[ 'pv_shop_name' ] ) );
			}
		}

		if ( isset( $_POST[ 'pv_shop_description' ] ) ) {
			update_user_meta( $user_id, 'pv_shop_description', $_POST[ 'pv_shop_description' ] );
		}

		if ( isset( $_POST[ 'pv_seller_info' ] ) ) {
			update_user_meta( $user_id, 'pv_seller_info', $_POST[ 'pv_seller_info' ] );
		}

		do_action( 'wcvendors_shop_settings_saved', $user_id );

		if ( !wc_notice_count() ) {
			wc_add_notice( __( 'Settings saved.', 'wcvendors' ), 'success' );
		}
	}


	/**
	 *
	 */
	public function check_access()
	{
		$vendor_dashboard_page = WC_Vendors::$pv_options->get_option( 'vendor_dashboard_page' );
		$shop_settings_page    = WC_Vendors::$pv_options->get_option( 'shop_settings_page' );

		if ( ( is_page( $vendor_dashboard_page ) || is_page( $shop_settings_page ) ) && !is_user_logged_in() ) {
			wp_redirect( get_permalink( woocommerce_get_page_id( 'myaccount' ) ) );
			exit;
		}
	}


	/**
	 * [pv_recent_vendor_sales] shortcode
	 *
	 * @param array $atts
	 *
	 * @return unknown
	 */
	public function display_vendor_products( $atts )
	{
		global $start_date, $end_date;

		$start_date = !empty( $_SESSION[ 'PV_Session' ][ 'start_date' ] ) ? $_SESSION[ 'PV_Session' ][ 'start_date' ] : strtotime( date( 'Ymd', strtotime( date( 'Ym', current_time( 'timestamp' ) ) . '01' ) ) );
		$end_date   = !empty( $_SESSION[ 'PV_Session' ][ 'end_date' ] ) ? $_SESSION[ 'PV_Session' ][ 'end_date' ] : strtotime( date( 'Ymd', current_time( 'timestamp' ) ) );

		$can_view_orders = WC_Vendors::$pv_options->get_option( 'can_show_orders' );
		$settings_page   = get_permalink( WC_Vendors::$pv_options->get_option( 'shop_settings_page' ) );
		$can_submit      = WC_Vendors::$pv_options->get_option( 'can_submit_products' );
		if ( $can_submit ) $submit_link = admin_url( 'post-new.php?post_type=product' );

		if ( !$this->can_view_vendor_page() ) {
			return false;
		}

		extract( shortcode_atts( array(
									  'user_id'    => get_current_user_id(),
									  'datepicker' => true,
								 ), $atts ) );

		$vendor_products = WCV_Queries::get_commission_products( $user_id );
		$products = array();
		foreach ($vendor_products as $_product) {
			$products[] = $_product->ID;
		}

		$vendor_summary  = $this->format_product_details( $vendor_products );
		$order_summary   = WCV_Queries::get_orders_for_products( $products );
		$shop_page       = WCV_Vendors::get_vendor_shop_page( wp_get_current_user()->user_login );

		wp_enqueue_style( 'wcv_frontend_style', wcv_assets_url . 'css/wcv-frontend.css' );

		// WC Shipment Tracking Providers
		global $WC_Shipment_Tracking;

		$providers      = !empty( $WC_Shipment_Tracking->providers ) ? $WC_Shipment_Tracking->providers : false;
		$provider_array = array();

		if ( $providers ) {
			foreach ( $providers as $providerss ) {
				foreach ( $providerss as $provider => $format ) {
					$provider_array[ sanitize_title( $provider ) ] = urlencode( $format );
				}
			}
		}

		ob_start();
		do_action( 'wcvendors_before_dashboard' );

		wc_print_notices();
		wc_get_template( 'links.php', array(
													'shop_page'     => urldecode($shop_page),
													'settings_page' => $settings_page,
													'can_submit'    => $can_submit,
													'submit_link'   => $submit_link,
											   ), 'wc-product-vendor/dashboard/', wcv_plugin_dir . 'views/dashboard/' );

		if ( $can_view_sales = WC_Vendors::$pv_options->get_option( 'can_view_frontend_reports' ) ) {

		wc_get_template( 'reports.php', array(
													  'start_date'      => $start_date,
													  'end_date'        => $end_date,
													  'vendor_products' => $vendor_products,
													  'vendor_summary'  => $vendor_summary,
													  'datepicker'      => $datepicker,
													  'can_view_orders' => $can_view_orders,
												 ), 'wc-product-vendor/dashboard/', wcv_plugin_dir . 'views/dashboard/' );
		}

		wc_get_template( 'orders.php', array(
													  'start_date'      => $start_date,
													  'end_date'        => $end_date,
													  'vendor_products' => $vendor_products,
													  'order_summary'   => $order_summary,
													  'datepicker'      => $datepicker,
													  'providers'      => $providers,
													  'provider_array' => $provider_array,
													  'can_view_orders' => $can_view_orders,
												 ), 'wc-product-vendor/dashboard/', wcv_plugin_dir . 'views/dashboard/' );
		do_action( 'wcvendors_after_dashboard' );

		return ob_get_clean();
	}


	/**
	 * [pv_recent_vendor_sales] shortcode
	 *
	 * @param array $atts
	 *
	 * @return unknown
	 */
	public function display_vendor_settings( $atts )
	{
		global $woocommerce;

		if ( !$this->can_view_vendor_page() ) {
			return false;
		}

		extract( shortcode_atts( array(
									  'user_id'          => get_current_user_id(),
									  'paypal_address'   => true,
									  'shop_description' => true,
								 ), $atts ) );

		$description = get_user_meta( $user_id, 'pv_shop_description', true );
		$seller_info = get_user_meta( $user_id, 'pv_seller_info', true );
		$has_html    = get_user_meta( $user_id, 'pv_shop_html_enabled', true );
		$shop_page   = WCV_Vendors::get_vendor_shop_page( wp_get_current_user()->user_login );
		$global_html = WC_Vendors::$pv_options->get_option( 'shop_html_enabled' );

		ob_start();
		wc_get_template( 'settings.php', array(
													   'description'      => $description,
													   'global_html'      => $global_html,
													   'has_html'         => $has_html,
													   'paypal_address'   => $paypal_address,
													   'seller_info'      => $seller_info,
													   'shop_description' => $shop_description,
													   'shop_page'        => $shop_page,
													   'user_id'          => $user_id,
												  ), 'wc-product-vendor/dashboard/settings/', wcv_plugin_dir . 'views/dashboard/settings/' );

		return ob_get_clean();
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function can_view_vendor_page()
	{
		if ( !is_user_logged_in() ) {

			return false;

		} else if ( !WCV_Vendors::is_vendor( get_current_user_id() ) ) {

			wc_get_template( 'denied.php', array(), 'wc-product-vendor/dashboard/', wcv_plugin_dir . 'views/dashboard/' );

			return false;

		}

		return true;
	}


	/**
	 * Format products for easier displaying
	 *
	 * @param object $products
	 *
	 * @return array
	 */
	public function format_product_details( $products )
	{
		if ( empty( $products ) ) return false;

		$orders_page        = get_permalink( WC_Vendors::$pv_options->get_option( 'orders_page' ) );
		$default_commission = WC_Vendors::$pv_options->get_option( 'default_commission' );
		$total_qty          = $total_cost = 0;
		$data               = array(
			'products'   => array(),
			'total_qty'  => '',
			'total_cost' => '',
		);

		foreach ( $products as $product )
			$ids[ ] = $product->ID;

		$orders = WCV_Queries::sum_orders_for_products( $ids, array( 'vendor_id' => get_current_user_id() ) );

		if ( $orders )
			foreach ( $orders as $order_item ) {
				if ( $order_item->qty < 1 ) continue;

				$commission_rate = WCV_Commission::get_commission_rate( $order_item->product_id );
				$_product        = get_product( $order_item->product_id );
				$id              = !empty($_product->parent->id) ? $_product->parent->id : $order_item->product_id;

				$data[ 'products' ][$id] = array(
					'id'              => $id,
					'title'           => $_product->get_title(),
					'qty'             => !empty($data[ 'products' ][$id]) ? $data[ 'products' ][$id]['qty'] + $order_item->qty : $order_item->qty,
					'cost'            => !empty($data[ 'products' ][$id]) ? $data[ 'products' ][$id]['cost'] + $order_item->line_total : $order_item->line_total,
					'view_orders_url' => esc_url( add_query_arg( 'orders_for_product', $id, $orders_page ) ),
					'commission_rate' => $commission_rate,
				);

				$total_qty += $order_item->qty;
				$total_cost += $order_item->line_total;

			}

		$data[ 'total_qty' ]  = $total_qty;
		$data[ 'total_cost' ] = $total_cost;

		// Sort by product title
		if ( !empty( $data[ 'products' ] ) )
			usort( $data[ 'products' ], array( $this, 'sort_by_title' ) );

		return $data;
	}


	/**
	 * Sort an array by 'title'
	 *
	 * @param array $a
	 * @param array $b
	 *
	 * @return array
	 */
	private function sort_by_title( array $a, array $b )
	{
		return strcasecmp( $a[ 'title' ], $b[ 'title' ] );
	}


}
