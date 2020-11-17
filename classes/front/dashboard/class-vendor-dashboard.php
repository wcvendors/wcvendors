<?php

/**
 * WCV Vendor Dashboard
 *
 * @author  Matt Gates <http://mgates.me>
 * @author  Jamie Madden <http://wcvendors.com>
 * @package WCVendors
 */


class WCV_Vendor_Dashboard {

	/**
	 * __construct()
	 */
	function __construct() {

		if ( is_admin() ) {
			return;
		}

		add_shortcode( 'wcv_shop_settings',        array( $this, 'display_vendor_settings' ) );
		add_shortcode( 'wcv_vendor_dashboard',     array( $this, 'display_vendor_products' ) );
		add_shortcode( 'wcv_vendor_dashboard_nav', array( $this, 'display_dashboard_nav' ) );

		add_action( 'template_redirect', array( $this, 'check_access' ) );
		add_action( 'template_redirect', array( $this, 'save_vendor_settings' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function enqueue_scripts() {

		global $post;
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}
		if (
			has_shortcode( $post->post_content, 'wcv_vendor_dashboard' )
			|| has_shortcode( $post->post_content, 'wcv_orders' )
			|| has_shortcode( $post->post_content, 'wcv_vendor_dashboard_nav' )
		) {
			wp_enqueue_style( 'wcv_frontend_style', wcv_assets_url . 'css/wcv-frontend.css' );			
		}
	}

	public function save_vendor_settings() {
		$user_id = get_current_user_id();

		if ( ! empty( $_GET['wc_pv_mark_shipped'] ) ) {

			$order_id   = $_GET['wc_pv_mark_shipped'];
			$order      = wc_get_order( $order_id );
			$vendors    = WCV_Vendors::get_vendors_from_order( $order );
			$vendor_ids = array_keys( $vendors );

			if ( ! in_array( $user_id, $vendor_ids ) ) {
				return;
			}

			$shippers = (array) get_post_meta( $order_id, 'wc_pv_shipped', true );

			// If not in the shippers array mark as shipped otherwise do nothing.
			if ( ! in_array( $user_id, $shippers ) ) {

				$shippers[] = $user_id;

				if ( ! empty( $mails ) ) {
					WC()->mailer()->emails['WC_Email_Notify_Shipped']->trigger( $order_id, $user_id );
				}

				do_action( 'wcvendors_vendor_ship', $order_id, $user_id, $order );

				wc_add_notice( __( 'Order marked shipped.', 'wc-vendors' ), 'success' );

				$shop_name = WCV_Vendors::get_vendor_shop_name( $user_id );
				$order->add_order_note( apply_filters( 'wcvendors_vendor_shipped_note', sprintf( __( '%s has marked as shipped. ', 'wc-vendors' ), $shop_name ), $user_id, $shop_name ) );

			} elseif ( false != ( $key = array_search( $user_id, $shippers ) ) ) {
				unset( $shippers[ $key ] ); // Remove user from the shippers array
			}

			update_post_meta( $order_id, 'wc_pv_shipped', $shippers );

			return;
		}

		if ( isset( $_POST['update_tracking'] ) ) {
			$order_id   = (int) $_POST['order_id'];
			$product_id = (int) $_POST['product_id'];

			$tracking_provider        = wc_clean( $_POST['tracking_provider'] );
			$custom_tracking_provider = wc_clean( $_POST['custom_tracking_provider_name'] );
			$custom_tracking_link     = wc_clean( $_POST['custom_tracking_url'] );
			$tracking_number          = wc_clean( $_POST['tracking_number'] );
			$date_shipped             = wc_clean( strtotime( $_POST['date_shipped'] ) );

			$order    = wc_get_order( $order_id );
			$products = $order->get_items();
			foreach ( $products as $key => $value ) {
				if ( $value['product_id'] == $product_id || $value['variation_id'] == $product_id ) {
					$order_item_id = $key;
					break;
				}
			}
			if ( $order_item_id ) {
				wc_delete_order_item_meta( $order_item_id, __( 'Tracking number', 'wc-vendors' ) );
				wc_add_order_item_meta( $order_item_id, __( 'Tracking number', 'wc-vendors' ), $tracking_number );

				$message = __( 'Success. Your tracking number has been updated.', 'wc-vendors' );
				wc_add_notice( $message, 'success' );

				// Update order data
				update_post_meta( $order_id, '_tracking_provider', $tracking_provider );
				update_post_meta( $order_id, '_custom_tracking_provider', $custom_tracking_provider );
				update_post_meta( $order_id, '_tracking_number', $tracking_number );
				update_post_meta( $order_id, '_custom_tracking_link', $custom_tracking_link );
				update_post_meta( $order_id, '_date_shipped', $date_shipped );
			}
		}

		if ( empty( $_POST['vendor_application_submit'] ) ) {
			return false;
		}

		if ( isset( $_POST['wc-product-vendor-nonce'] ) ) {

			if ( ! wp_verify_nonce( $_POST['wc-product-vendor-nonce'], 'save-shop-settings' ) ) {
				return false;
			}

			if ( isset( $_POST['pv_paypal'] ) && '' !== $_POST['pv_paypal'] ) {
				if ( ! is_email( $_POST['pv_paypal'] ) ) {
					wc_add_notice( __( 'Your PayPal address is not a valid email address.', 'wc-vendors' ), 'error' );
				} else {
					update_user_meta( $user_id, 'pv_paypal', $_POST['pv_paypal'] );
				}
			} else {
				update_user_meta( $user_id, 'pv_paypal', '' );
			}

			if ( ! empty( $_POST['pv_shop_name'] ) ) {
				$users = get_users(
					array(
						'meta_key'   => 'pv_shop_slug',
						'meta_value' => sanitize_title( $_POST['pv_shop_name'] ),
					)
				);
				if ( ! empty( $users ) && $users[0]->ID != $user_id ) {
					wc_add_notice( __( 'That shop name is already taken. Your shop name must be unique.', 'wc-vendors' ), 'error' );
				} else {
					update_user_meta( $user_id, 'pv_shop_name', $_POST['pv_shop_name'] );
					update_user_meta( $user_id, 'pv_shop_slug', sanitize_title( $_POST['pv_shop_name'] ) );
				}
			}

			if ( isset( $_POST['pv_shop_description'] ) ) {
				update_user_meta( $user_id, 'pv_shop_description', $_POST['pv_shop_description'] );
			} else {
				update_user_meta( $user_id, 'pv_shop_description', '' );
			}

			if ( isset( $_POST['pv_seller_info'] ) ) {
				update_user_meta( $user_id, 'pv_seller_info', $_POST['pv_seller_info'] );
			}

			// Bank details
			if ( isset( $_POST['wcv_bank_account_name'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_account_name', $_POST['wcv_bank_account_name'] );
			} else {
				delete_user_meta( $user_id, 'wcv_bank_account_name' );
			}
			if ( isset( $_POST['wcv_bank_account_number'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_account_number', $_POST['wcv_bank_account_number'] );
			} else {
				delete_user_meta( $user_id, 'wcv_bank_account_number' );
			}
			if ( isset( $_POST['wcv_bank_name'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_name', $_POST['wcv_bank_name'] );
			} else {
				delete_user_meta( $user_id, 'wcv_bank_name' );
			}
			if ( isset( $_POST['wcv_bank_routing_number'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_routing_number', $_POST['wcv_bank_routing_number'] );
			} else {
				delete_user_meta( $user_id, 'wcv_bank_routing_number' );
			}
			if ( isset( $_POST['wcv_bank_iban'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_iban', $_POST['wcv_bank_iban'] );
			} else {
				delete_user_meta( $user_id, 'wcv_bank_iban' );
			}
			if ( isset( $_POST['wcv_bank_bic_swift'] ) ) {
				update_user_meta( $user_id, 'wcv_bank_bic_swift', $_POST['wcv_bank_bic_swift'] );
			} else {
				delete_user_meta( $user_id, 'wcv_bank_bic_swift' );
			}

			do_action( 'wcvendors_shop_settings_saved', $user_id );

			if ( ! wc_notice_count() ) {
				wc_add_notice( __( 'Settings saved.', 'wc-vendors' ), 'success' );
			}
		}
	}

	/**
	 *
	 */
	public function check_access() {
		$vendor_dashboard_page = get_option( 'wcvendors_vendor_dashboard_page_id' );
		$shop_settings_page    = get_option( 'wcvendors_shop_settings_page_id' );

		if ( $vendor_dashboard_page && is_page( $vendor_dashboard_page ) || $shop_settings_page && is_page( $shop_settings_page ) ) {
			if ( ! is_user_logged_in() ) {
				wp_redirect( get_permalink( wc_get_page_id( 'myaccount' ) ), 303 );
				exit;
			}
		}

	} //check_access()

	/**
	 * [wcv_vendor_dashboard] shortcode
	 *
	 * @param array $atts
	 *
	 * @return unknown
	 */
	public function display_vendor_products( $atts ) {

		ob_start();

		global $start_date, $end_date;

		// Need to check if the session exists and if it doesn't create it 
		if ( null === WC()->session ) {
            $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
            // Prefix session class with global namespace if not already namespaced
            if ( false === strpos( $session_class, '\\' ) ) {
                $session_class = '\\' . $session_class;
            }
            WC()->session = new $session_class();
            WC()->session->init();
        }

		$start_date = WC()->session->get( 'wcv_order_start_date', strtotime( current_time( 'Y-M' ) . '-01' ) );
		$end_date   = WC()->session->get( 'wcv_order_end_date', current_time( 'timestamp' ) );

		$can_view_orders = wc_string_to_bool( get_option( 'wcvendors_capability_orders_enabled', 'no' ) );

		if ( ! $this->can_view_vendor_page() ) {
			wc_get_template( 'denied.php', array(), 'wc-vendors/dashboard/', wcv_plugin_dir . 'templates/dashboard/' );
			return ob_get_clean();
		}

		extract(
			shortcode_atts(
				array(
					'user_id'    => get_current_user_id(),
					'datepicker' => true,
				),
				$atts
			)
		);

		$vendor_products = WCV_Queries::get_commission_products( $user_id );
		$products        = array();
		foreach ( $vendor_products as $_product ) {
			$products[] = $_product->ID;
		}

		$vendor_summary = $this->format_product_details( $vendor_products );
		$order_summary  = WCV_Queries::get_orders_for_products( $products );

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

		do_action( 'wcvendors_before_dashboard' );

		if( function_exists ( 'wc_print_notices' ) ){ 
			wc_print_notices();
		}

		wc_get_template(
			'navigation.php',
			array(
				'items' => $this->get_nav_items()
			),
			'wc-vendors/dashboard/',
			wcv_plugin_dir . 'templates/dashboard/'
		);

		if ( $can_view_sales = get_option( 'wcvendors_capability_frontend_reports' ) ) {

			$can_view_address = wc_string_to_bool( get_option( 'wcvendors_capability_order_customer_shipping' ) );

			wc_get_template(
				'reports.php',
				array(
					'start_date'      => $start_date,
					'end_date'        => $end_date,
					'vendor_products' => $vendor_products,
					'vendor_summary'  => $vendor_summary,
					'datepicker'      => $datepicker,
					'can_view_orders' => $can_view_orders,
				),
				'wc-vendors/dashboard/',
				wcv_plugin_dir . 'templates/dashboard/'
			);
		}

		wc_get_template(
			'orders.php',
			array(
				'start_date'       => $start_date,
				'end_date'         => $end_date,
				'vendor_products'  => $vendor_products,
				'order_summary'    => $order_summary,
				'datepicker'       => $datepicker,
				'providers'        => $providers,
				'provider_array'   => $provider_array,
				'can_view_orders'  => $can_view_orders,
				'can_view_address' => $can_view_address,
			),
			'wc-vendors/dashboard/',
			wcv_plugin_dir . 'templates/dashboard/'
		);
		do_action( 'wcvendors_after_dashboard' );

		wc_enqueue_js( WCV_Vendor_dashboard::wc_st_js( $provider_array ) );

		return ob_get_clean();
	}

	/**
	 * Filterable dashboard navigation items.
	 *
	 * @return array
	 */
	public function get_nav_items() {

		$items = array(
			'shop_page'     => array(
				'url'   => urldecode( WCV_Vendors::get_vendor_shop_page( wp_get_current_user()->user_login ) ),
				'label' => esc_html__( 'View Your Store', 'wc-vendors' ),
			),
			'settings_page' => array(
				'url'   => get_permalink( get_option( 'wcvendors_shop_settings_page_id' ) ),
				'label' => esc_html__( 'Store Settings', 'wc-vendors' ),
			),
		);

		$can_submit = wc_string_to_bool( get_option( 'wcvendors_capability_products_enabled', 'no' ) );

		if ( $can_submit ) {
			$items['submit_link'] = array(
				'url'    => admin_url( 'post-new.php?post_type=product' ),
				'label'  => esc_html__( 'Add New Product', 'wc-vendors' ),
				'target' => '_top',
			);
			$items['edit_link']   = array(
				'url'    => admin_url( 'edit.php?post_type=product' ),
				'label'  => esc_html__( 'Edit Products', 'wc-vendors' ),
				'target' => '_top',
			);
		}

		return apply_filters( 'wcv_dashboard_nav_items', $items );
	}

	/**
	 * [wcv_vendor_dashboard_nav] shortcode.
	 *
	 * @return string
	 */
	public function display_dashboard_nav() {

		ob_start();

		wc_get_template(
			'navigation.php',
			array(
				'items' => $this->get_nav_items()
			),
			'wc-vendors/dashboard/',
			wcv_plugin_dir . 'templates/dashboard/'
		);

		return ob_get_clean();
	}

	/**
	 * [pv_recent_vendor_sales] shortcode
	 *
	 * @param array $atts
	 *
	 * @return unknown
	 */
	public function display_vendor_settings( $atts ) {
		global $woocommerce;

		ob_start();

		if ( ! $this->can_view_vendor_page() ) {
			return ob_get_clean();
		}

		extract(
			shortcode_atts(
				array(
					'user_id'          => get_current_user_id(),
					'paypal_address'   => true,
					'shop_description' => true,
				),
				$atts
			)
		);

		$description = get_user_meta( $user_id, 'pv_shop_description', true );
		$seller_info = get_user_meta( $user_id, 'pv_seller_info', true );
		$has_html    = get_user_meta( $user_id, 'pv_shop_html_enabled', true );
		$shop_page   = WCV_Vendors::get_vendor_shop_page( wp_get_current_user()->user_login );
		$global_html = wc_string_to_bool( get_option( 'wcvendors_display_shop_description_html', 'no' ) );

		wc_get_template(
			'settings.php',
			array(
				'description'      => $description,
				'global_html'      => $global_html,
				'has_html'         => $has_html,
				'paypal_address'   => $paypal_address,
				'seller_info'      => $seller_info,
				'shop_description' => $shop_description,
				'shop_page'        => $shop_page,
				'user_id'          => $user_id,
			),
			'wc-vendors/dashboard/settings/',
			wcv_plugin_dir . 'templates/dashboard/settings/'
		);

		return ob_get_clean();
	}

	/**
	 * Can the user view this page. 
	 *
	 * @version 2.2.1 
	 * 
	 * @return bool 
	 */
	public static function can_view_vendor_page() {
		if ( ! is_user_logged_in() || ! WCV_Vendors::is_vendor( get_current_user_id() ) ) {
			return false;
		} else { 
			return true;
		}
	}

	/**
	 * Format products for easier displaying
	 *
	 * @param object $products
	 *
	 * @return array
	 */
	public function format_product_details( $products ) {
		if ( empty( $products ) ) {
			return false;
		}

		$orders_page_id     = get_option( 'wcvendors_product_orders_page_id' );
		$orders_page        = get_permalink( $orders_page_id );
		$default_commission = get_option( 'wcvendors_vendor_commission_rate' );
		$total_qty          = $total_cost = 0;
		$data               = array(
			'products'   => array(),
			'total_qty'  => '',
			'total_cost' => '',
		);

		foreach ( $products as $product ) {
			$ids[] = $product->ID;
		}

		$orders = WCV_Queries::sum_orders_for_products( $ids, array( 'vendor_id' => get_current_user_id() ) );

		if ( $orders ) {
			foreach ( $orders as $order_item ) {
				if ( $order_item->qty < 1 ) {
					continue;
				}

				$commission_rate = WCV_Commission::get_commission_rate( $order_item->product_id );
				$_product        = wc_get_product( $order_item->product_id );
				$parent_id       = $_product->get_parent_id();
				$id              = ! empty( $parent_id ) ? $parent_id : $order_item->product_id;

				$data['products'][ $id ] = array(
					'id'              => $id,
					'title'           => $_product->get_title(),
					'qty'             => ! empty( $data['products'][ $id ] ) ? $data['products'][ $id ]['qty'] + $order_item->qty : $order_item->qty,
					'cost'            => ! empty( $data['products'][ $id ] ) ? $data['products'][ $id ]['cost'] + $order_item->line_total : $order_item->line_total,
					'view_orders_url' => esc_url( add_query_arg( 'orders_for_product', $id, $orders_page ) ),
					'commission_rate' => $commission_rate,
				);

				$total_qty  += $order_item->qty;
				$total_cost += $order_item->line_total;

			}
		}

		$data['total_qty']  = $total_qty;
		$data['total_cost'] = $total_cost;

		// Sort by product title
		if ( ! empty( $data['products'] ) ) {
			usort( $data['products'], array( $this, 'sort_by_title' ) );
		}

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
	private function sort_by_title( array $a, array $b ) {
		return strcasecmp( $a['title'], $b['title'] );
	}

	/**
	 *  Load the javascript for the WC Shipment Tracking form
	 */
	public static function wc_st_js( $provider_array ) {
		$js = "
			jQuery(function() {

				var providers = jQuery.parseJSON( '" . json_encode( $provider_array ) . "' );

				jQuery('#tracking_number').prop('readonly',true);
				jQuery('#date_shipped').prop('readonly',true);

				function updatelink( tracking, provider ) {

					var postcode = '32';
					postcode = encodeURIComponent(postcode);

					link = providers[provider];
					link = link.replace('%251%24s', tracking);
					link = link.replace('%252%24s', postcode);
					link = decodeURIComponent(link);
					return link;
				}

				jQuery('.tracking_provider, #tracking_number').unbind().change(function(){

					var form = jQuery(this).parent().parent().attr('id');

					var tracking = jQuery('#' + form + ' input#tracking_number').val();
					var provider = jQuery('#' + form + ' #tracking_provider').val();

					if ( providers[ provider ]) {
						link = updatelink(tracking, provider);
						jQuery('#' + form + ' #tracking_number').prop('readonly',false);
						jQuery('#' + form + ' #date_shipped').prop('readonly',false);
						jQuery('#' + form + ' .custom_tracking_url_field, #' + form + ' .custom_tracking_provider_name_field').hide();
					} else {
						jQuery('#' + form + ' .custom_tracking_url_field, #' + form + ' .custom_tracking_provider_name_field').show();
						link = jQuery('#' + form + ' input#custom_tracking_link').val();
					}

					if (link) {
						jQuery('#' + form + ' p.preview_tracking_link a').attr('href', link);
						jQuery('#' + form + ' p.preview_tracking_link').show();
					} else {
						jQuery('#' + form + ' p.preview_tracking_link').hide();
					}

				});

				jQuery('#custom_tracking_provider_name').unbind().click(function(){

					var form = jQuery(this).parent().parent().attr('id');

					jQuery('#' + form + ' #tracking_number').prop('readonly',false);
					jQuery('#' + form + ' #date_shipped').prop('readonly',false);

				});

			});
		";

		return $js;
	} // wc_st_js()

	/**
	 * Add custom wcvendors pro css classes
	 *
	 * @since    1.0.0
	 * @access   public
	 *
	 * @param array $classes - body css classes
	 *
	 * @return array $classes - body css classes
	 */
	public function body_class( $classes ) {

		$dashboard_page = get_option( 'wcvendors_vendor_dashboard_page_id' );
		$orders_page    = get_option( 'wcvendors_product_orders_page_id' );
		$shop_settings  = get_option( 'wcvendors_shop_settings_page_id' );
		$terms_page     = get_option( 'wcvendors_vendor_terms_page_id' );

		if ( is_page( $dashboard_page ) ) {
			$classes[] = 'wcvendors wcv-vendor-dashboard-page';
		}

		if ( is_page( $orders_page ) ) {
			$classes[] = 'wcvendors wcv-orders-page';
		}

		if ( is_page( $shop_settings ) ) {
			$classes[] = 'wcvendors wcv-shop-settings-page';
		}

		if ( is_page( $terms_page ) ) {
			$classes[] = 'wcvendors wcv-terms-page';
		}

		return $classes;

	} // body_class()
}
