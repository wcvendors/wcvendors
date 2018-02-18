<?php

/**
 * Vendor functions
 *
 * @author  Matt Gates <http://mgates.me>, WC Vendors <http://wcvendors.com>
 * @package WCVendors
 */
	

class WCV_Vendors
{

	/**
	 * Constructor
	 */
	function __construct()
	{
		add_action( 'woocommerce_checkout_order_processed',  array( __CLASS__, 'create_child_orders' ), 10, 1 );
	}

	/**
	 * Retrieve all products for a vendor
	 *
	 * @param int $vendor_id
	 *
	 * @return object
	 */
	public static function get_vendor_products( $vendor_id )
	{
		$args = array(
			'numberposts' => -1,
			'post_type'   => 'product',
			'author'      => $vendor_id,
			'post_status' => 'publish',
		);

		$args = apply_filters( 'pv_get_vendor_products_args', $args );

		return get_posts( $args );
	}

	public static function get_default_commission( $vendor_id )
	{
		return get_user_meta( $vendor_id, 'pv_custom_commission_rate', true );
	}


	/**
	 * Vendor IDs and PayPal addresses from an order
	 *
	 * @param object  $order
	 * @param unknown $items (optional)
	 *
	 * @return array
	 */
	public static function get_vendors_from_order( $order, $items = false )
	{
		if ( !$order ) return;
		if ( !$items ) $items = $order->get_items();

		$vendors = array();
		foreach ( $items as $key => $product ) {

			$author = WCV_Vendors::get_vendor_from_product( $product[ 'product_id' ] );

			// Only store the vendor authors
			if ( !WCV_Vendors::is_vendor( $author ) ) continue;

			$vendors[ $author ] = the_author_meta( 'author_paypal', $author );
		}

		return apply_filters( 'pv_vendors_from_order', $vendors, $order );
	}


	/**
	 *
	 *
	 * @param unknown $order
	 * @param unknown $group (optional)
	 *
	 * @return unknown
	 */
	public static function get_vendor_dues_from_order( $order, $group = true )
	{
		global $woocommerce;

		$give_tax       	= WC_Vendors::$pv_options->get_option( 'give_tax' );
		$give_shipping 		= WC_Vendors::$pv_options->get_option( 'give_shipping' );
		$receiver      		= array();
		$shipping_given 	= 0;
		$tax_given      	= 0;

		WCV_Shipping::$pps_shipping_costs = array();

		foreach ( $order->get_items() as $key => $product ) {

			$product_id 				= !empty( $product[ 'variation_id' ] ) ? $product[ 'variation_id' ] : $product[ 'product_id' ];
			$author     				= WCV_Vendors::get_vendor_from_product( $product_id );
			$give_tax_override 			= get_user_meta( $author, 'wcv_give_vendor_tax', true ); 
			$give_shipping_override 	= get_user_meta( $author, 'wcv_give_vendor_shipping', true ); 
			$is_vendor  				= WCV_Vendors::is_vendor( $author );
			$commission 				= $is_vendor ? WCV_Commission::calculate_commission( $product[ 'line_subtotal' ], $product_id, $order, $product[ 'qty' ] ) : 0;
			$tax        				= !empty( $product[ 'line_tax' ] ) ? (float) $product[ 'line_tax' ] : 0;
			$order_id 					= ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $order->id : $order->get_id();  	
			
			// Check if shipping is enabled
			if ( get_option('woocommerce_calc_shipping') === 'no' ) { 
				$shipping = 0; $shipping_tax = 0; 
			} else {
				$shipping_costs = WCV_Shipping::get_shipping_due( $order_id, $product, $author, $product_id );
				$shipping = $shipping_costs['amount']; 
				$shipping_tax = $shipping_costs['tax']; 
			}
	
			$_product = new WC_Product( $product['product_id'] ); 

			// Add line item tax and shipping taxes together 
			$total_tax = ( $_product->is_taxable() ) ? (float) $tax + (float) $shipping_tax : 0; 

			// Tax override on a per vendor basis
			if ( $give_tax_override ) $give_tax = true; 
			// Shipping override 
			if ( $give_shipping_override ) $give_shipping = true; 

			if ( $is_vendor ) {

				$shipping_given += $give_shipping ? $shipping : 0;
				$tax_given += $give_tax ? $total_tax : 0;

				$give = 0;
				$give += !empty( $receiver[ $author ][ 'total' ] ) ? $receiver[ $author ][ 'total' ] : 0;
				$give += $give_shipping ? $shipping : 0;
				$give += $commission;
				$give += $give_tax ? $total_tax : 0;

				if ( $group ) {

					$receiver[ $author ] = array(
						'vendor_id'  => (int) $author,
						'commission' => !empty( $receiver[ $author ][ 'commission' ] ) ? $receiver[ $author ][ 'commission' ] + $commission : $commission,
						'shipping'   => $give_shipping ? ( !empty( $receiver[ $author ][ 'shipping' ] ) ? $receiver[ $author ][ 'shipping' ] + $shipping : $shipping) : 0,
						'tax'        => $give_tax ? ( !empty( $receiver[ $author ][ 'tax' ] ) ? $receiver[ $author ][ 'tax' ] + $total_tax : $total_tax ) : 0,
						'qty'        => !empty( $receiver[ $author ][ 'qty' ] ) ? $receiver[ $author ][ 'qty' ] + $product[ 'qty' ] : $product[ 'qty' ],
						'total'      => $give,
					);

				} else {

					$receiver[ $author ][ $key ] = array(
						'vendor_id'  => (int) $author,
						'product_id' => $product_id,
						'commission' => $commission,
						'shipping'   => $give_shipping ? $shipping : 0,
						'tax'        => $give_tax ? $total_tax : 0,
						'qty'        => $product[ 'qty' ],
						'total'      => ($give_shipping ? $shipping : 0) + $commission + ( $give_tax ? $total_tax : 0 ),
					);

				}

			}

			$admin_comm = $product[ 'line_subtotal' ] - $commission;

			if ( $group ) {
				$receiver[ 1 ] = array(
					'vendor_id'  => 1,
					'qty'        => !empty( $receiver[ 1 ][ 'qty' ] ) ? $receiver[ 1 ][ 'qty' ] + $product[ 'qty' ] : $product[ 'qty' ],
					'commission' => !empty( $receiver[ 1 ][ 'commission' ] ) ? $receiver[ 1 ][ 'commission' ] + $admin_comm : $admin_comm,
					'total'      => !empty( $receiver[ 1 ] ) ? $receiver[ 1 ][ 'total' ] + $admin_comm : $admin_comm,
				);
			} else {
				$receiver[ 1 ][ $key ] = array(
					'vendor_id'  => 1,
					'product_id' => $product_id,
					'commission' => $admin_comm,
					'shipping'   => 0,
					'tax'        => 0,
					'qty'        => $product[ 'qty' ],
					'total'      => $admin_comm,
				);
			}

		}
		
		// Add remainders on end to admin
		$discount = $order->get_total_discount();
		$shipping 	= round( ( $order->get_total_shipping() - $shipping_given ), 2 );
		$tax 		= round( $order->get_total_tax() - $tax_given, 2); 
		$total    	= ( $tax + $shipping ) - $discount;

		if ( $group ) {
			$r_total = round( $receiver[ 1 ][ 'total' ], 2 ) ; 
			$receiver[ 1 ][ 'commission' ] = round( $receiver[ 1 ][ 'commission' ], 2 )  - round( $discount, 2 );
			$receiver[ 1 ][ 'shipping' ]   = $shipping;
			$receiver[ 1 ][ 'tax' ]        = $tax;
			$receiver[ 1 ][ 'total' ] 	   = $r_total + round( $total, 2 );
		} else {
			$r_total = round( $receiver[ 1 ][ $key ][ 'total' ], 2 ); 
			$receiver[ 1 ][ $key ][ 'commission' ] = round( $receiver[ 1 ][ $key ][ 'commission' ], 2 ) - round( $discount, 2 );
			$receiver[ 1 ][ $key ][ 'shipping' ]   = ( $order->get_total_shipping() - $shipping_given );
			$receiver[ 1 ][ $key ][ 'tax' ]        = $tax;
			$receiver[ 1 ][ $key ][ 'total' ] 	   = $r_total + round( $total, 2 );
		}

		// Reset the array keys
		// $receivers = array_values( $receiver );

		return apply_filters( 'wcv_vendor_dues', $receiver, $order, $group );
	}


	/**
	 * Return the PayPal address for a vendor
	 *
	 * If no PayPal is set, it returns the vendor's email
	 *
	 * @param int $vendor_id
	 *
	 * @return string
	 */
	public static function get_vendor_paypal( $vendor_id )
	{
		$paypal = get_user_meta( $vendor_id, $meta_key = 'pv_paypal', true );
		$paypal = !empty( $paypal ) ? $paypal : get_the_author_meta( 'user_email', $vendor_id, false );

		return $paypal;
	}


	/**
	 * Check if a vendor has an amount due for an order already
	 *
	 * @param int $vendor_id
	 * @param int $order_id
	 *
	 * @return int
	 */
	public static function count_due_by_vendor( $vendor_id, $order_id )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . "pv_commission";

		$query = "SELECT COUNT(*)
					FROM {$table_name}
					WHERE vendor_id = %s
					AND order_id = %s
					AND status = %s";
		$count = $wpdb->get_var( $wpdb->prepare( $query, $vendor_id, $order_id, 'due' ) );

		return $count;
	}


	/**
	 * All commission due for a specific vendor
	 *
	 * @param int $vendor_id
	 *
	 * @return int
	 */
	public static function get_due_orders_by_vendor( $vendor_id )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . "pv_commission";

		$query   = "SELECT *
					FROM {$table_name}
					WHERE vendor_id = %s
					AND status = %s";
		$results = $wpdb->get_results( $wpdb->prepare( $query, $vendor_id, 'due' ) );

		return $results;
	}


	/**
	 *
	 *
	 * @param unknown $product_id
	 *
	 * @return unknown
	 */
	public static function get_vendor_from_product( $product_id )
	{
		// Make sure we are returning an author for products or product variations only 
		if ( 'product' === get_post_type( $product_id ) || 'product_variation' === get_post_type( $product_id ) ) { 
			$parent = get_post_ancestors( $product_id );
			if ( $parent ) $product_id = $parent[ 0 ];

			$post = get_post( $product_id );
			$author = $post ? $post->post_author : 1;
			$author = apply_filters( 'pv_product_author', $author, $product_id );
		} else { 
			$author = -1; 
		}
		return $author;
	}


	/**
	 * Checks whether the ID provided is vendor capable or not
	 *
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public static function is_vendor( $user_id )
	{
		$user = get_userdata( $user_id ); 

		$vendor_roles = apply_filters( 'wcvendors_vendor_roles', array( 'vendor') ); 
		
		if (is_object($user)) { 

			foreach ($vendor_roles as $role ) {
				$is_vendor = is_array( $user->roles ) ? in_array( $role , $user->roles ) : false;	
			}
			
		} else { 
			$is_vendor = false; 
		}

		return apply_filters( 'pv_is_vendor', $is_vendor, $user_id );
	}


	/**
	 * Grabs the vendor ID whether a username or an int is provided
	 * and returns the vendor_id if it's actually a vendor
	 *
	 * @param unknown $input
	 *
	 * @return unknown
	 */
	public static function get_vendor_id( $input )
	{
		if ( empty( $input ) ) {
			return false;
		}

		$users = get_users( array( 'meta_key' => 'pv_shop_slug', 'meta_value' => sanitize_title( $input ) ) );

		if ( !empty( $users ) && count( $users ) == 1 ) {
			$vendor = $users[ 0 ];
		} else {
			$int_vendor = is_numeric( $input );
			$vendor     = !empty( $int_vendor ) ? get_userdata( $input ) : get_user_by( 'login', $input );
		}

		if ( $vendor ) {
			$vendor_id = $vendor->ID;
			if ( self::is_vendor( $vendor_id ) ) {
				return $vendor_id;
			}
		}

		return false;
	}


	/**
	 * Retrieve the shop page for a specific vendor
	 *
	 * @param unknown $vendor_id
	 *
	 * @return string
	 */
	public static function get_vendor_shop_page( $vendor_id )
	{
		$vendor_id = self::get_vendor_id( $vendor_id );
		if ( !$vendor_id ) return;

		$slug   = get_user_meta( $vendor_id, 'pv_shop_slug', true );
		$vendor = !$slug ? get_userdata( $vendor_id )->user_login : $slug;

		if ( get_option( 'permalink_structure' ) ) {
			$permalink = trailingslashit( WC_Vendors::$pv_options->get_option( 'vendor_shop_permalink' ) );

			return trailingslashit( home_url( sprintf( '/%s%s', $permalink, $vendor ) ) );
		} else {
			return esc_url( add_query_arg( array( 'vendor_shop' => $vendor ), get_post_type_archive_link( 'product' ) ) );
		}
	}


	/**
	 * Retrieve the shop name for a specific vendor
	 *
	 * @param unknown $vendor_id
	 *
	 * @return string
	 */
	public static function get_vendor_shop_name( $vendor_id )
	{
		$vendor_id = self::get_vendor_id( $vendor_id );
		$name      = $vendor_id ? get_user_meta( $vendor_id, 'pv_shop_name', true ) : false;
		$shop_name = ( ! $name && $vendor = get_userdata( $vendor_id ) ) ? $vendor->user_login : $name;

		return $shop_name;
	}


	/**
	 *
	 *
	 * @param unknown $user_id
	 *
	 * @return unknown
	 */
	public static function is_pending( $user_id )
	{
		$user = get_userdata( $user_id );

		$role       = !empty( $user->roles ) ? array_shift( $user->roles ) : false;
		$is_pending = ( $role == 'pending_vendor' );

		return $is_pending;
	}

	/* 
	* 	Is this a vendor product ? 
	* 	@param uknown $role 
	*/ 
	public static function is_vendor_product($role) { 
		return ($role === 'Vendor') ? true : false; 
	}

	/* 
	*	Is this the vendors shop archive page ? 
	*/
	public static function is_vendor_page() { 

		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );

		return $vendor_id ? true : false; 

	} // is_vendor_page()

	/* 
	*	Is this a vendor single product page ? 
	*/
	public static function is_vendor_product_page($vendor_id) { 

		$vendor_product = WCV_Vendors::is_vendor_product( wcv_get_user_role($vendor_id) ); 
		return $vendor_product ? true : false; 

	} // is_vendor_product_page()

	public static function get_vendor_sold_by( $vendor_id ){ 

		$vendor_display_name = WC_Vendors::$pv_options->get_option( 'vendor_display_name' ); 
		$vendor =  get_userdata( $vendor_id ); 

		switch ($vendor_display_name) {
			case 'display_name':
				$display_name = $vendor->display_name;
				break;
			case 'user_login': 
				$display_name = $vendor->user_login;
				break;
			case 'user_email': 
				$display_name = $vendor->user_email;
				break;

			default:
				$display_name = WCV_Vendors::get_vendor_shop_name( $vendor_id ); 
				break;
		}

		return $display_name;

	} // get_vendor_sold_by()

	/**
	 * Split order into vendor orders (when applicable) after checkout
	 *
	 * @since 
	 * @param int $order_id
	 * @return void
	 */
	public static function create_child_orders ( $order_id ) {

		$order = wc_get_order( $order_id );
		$items = $order->get_items();
		$vendor_items = array();

		foreach ($items as $item_id => $item) {
			if ( isset($item['product_id']) && $item['product_id'] !== 0 ) {
				// check if product is from vendor
				$product_author = get_post_field( 'post_author', $item['product_id'] );
				if (WCV_Vendors::is_vendor( $product_author ) ) {
					$vendor_items[ $product_author ][ $item_id ] = array (
						'item_id'		=> $item_id,
						'qty'			=> $item['qty'],
						'total'			=> $item['line_total'],
						'subtotal'		=> $item['line_subtotal'],
						'tax'			=> $item['line_tax'],
						'subtotal_tax'	=> $item['line_subtotal_tax'],
						'tax_data'		=> maybe_unserialize( $item['line_tax_data'] ),
						'commission'	=> WCV_Commission::calculate_commission( $item['line_subtotal'], $item['product_id'], $order, $item['qty'] ),
					);
				}
			}
		}

		foreach ($vendor_items as $vendor_id => $items) {
			if (!empty($items)) {
				$vendor_order = WCV_Vendors::create_vendor_order( array(
					'order_id'   => $order_id,
					'vendor_id'  => $vendor_id,
					'line_items' => $items
				) );
			}
		}
	}

	/**
	 * Create a new vendor order programmatically
	 *
	 * Returns a new vendor_order object on success which can then be used to add additional data.
	 *
	 * @since 
	 * @param array $args
	 * @return WC_Order_Vendor|WP_Error
	 */
	public static function create_vendor_order( $args = array() ) {
		$default_args = array(
			'vendor_id'       => null,
			'order_id'        => 0,
			'vendor_order_id' => 0,
			'line_items'      => array(),
			'date'            => current_time( 'mysql', 0 )
		);

		$args              = wp_parse_args( $args, $default_args );
		$vendor_order_data = array();

		if ( $args['vendor_order_id'] > 0 ) {
			$updating                = true;
			$vendor_order_data['ID'] = $args['vendor_order_id'];
		} else {
			$updating                           = false;
			$vendor_order_data['post_type']     = 'shop_order_vendor';
			$vendor_order_data['post_status']   = 'wc-completed';
			$vendor_order_data['ping_status']   = 'closed';
			$vendor_order_data['post_author']   = get_current_user_id();
			$vendor_order_data['post_password'] = uniqid( 'vendor_' ); // password = 20 char max! (uniqid = 13)
			$vendor_order_data['post_parent']   = absint( $args['order_id'] );
			$vendor_order_data['post_title']    = sprintf( __( 'Vendor Order &ndash; %s', 'woocommerce' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) );
			$vendor_order_data['post_date']     = $args['date'];
		}

		if ( $updating ) {
			$vendor_order_id = wp_update_post( $vendor_order_data );
		} else {
			$vendor_order_id = wp_insert_post( apply_filters( 'woocommerce_new_vendor_order_data', $vendor_order_data ), true );
		}

		if ( is_wp_error( $vendor_order_id ) ) {
			return $vendor_order_id;
		}

		if ( ! $updating ) {
			// Store vendor ID
			update_post_meta( $vendor_order_id, '_vendor_id', $args['vendor_id'] );

			// Get vendor order object
			$vendor_order = wc_get_order( $vendor_order_id );
			$order        = wc_get_order( $args['order_id'] );

			$order_currency = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $order->get_order_currency() : $order->get_currency(); 

			// Order currency is the same used for the parent order
			update_post_meta( $vendor_order_id, '_order_currency', $order_currency );

			if ( sizeof( $args['line_items'] ) > 0 ) {
				$order_items = $order->get_items( array( 'line_item', 'fee', 'shipping' ) );

				foreach ( $args['line_items'] as $vendor_order_item_id => $vendor_order_item ) {
					if ( isset( $order_items[ $vendor_order_item_id ] ) ) {
						if ( empty( $vendor_order_item['qty'] ) && empty( $vendor_order_item['total'] ) && empty( $vendor_order_item['tax'] ) ) {
							continue;
						}

						// Prevents errors when the order has no taxes
						if ( ! isset( $vendor_order_item['tax'] ) ) {
							$vendor_order_item['tax'] = array();
						}

						switch ( $order_items[ $vendor_order_item_id ]['type'] ) {
							case 'line_item' :
								$line_item_args = array(
									'totals' => array(
										'subtotal'     => $vendor_order_item['subtotal'],
										'total'        => $vendor_order_item['total'],
										'subtotal_tax' => $vendor_order_item['subtotal_tax'],
										'tax'          => $vendor_order_item['tax'],
										'tax_data'     => $vendor_order_item['tax_data'],
									)
								);
								$new_item_id = $vendor_order->add_product( $order->get_product_from_item( $order_items[ $vendor_order_item_id ] ), isset( $vendor_order_item['qty'] ) ? $vendor_order_item['qty'] : 0, $line_item_args );
								wc_add_order_item_meta( $new_item_id, '_vendor_order_item_id', $vendor_order_item_id );
								wc_add_order_item_meta( $new_item_id, '_vendor_commission', $vendor_order_item['commission'] );
							break;
							case 'shipping' :
								$shipping        = new stdClass();
								$shipping->label = $order_items[ $vendor_order_item_id ]['name'];
								$shipping->id    = $order_items[ $vendor_order_item_id ]['method_id'];
								$shipping->cost  = $vendor_order_item['total'];
								$shipping->taxes = $vendor_order_item['tax'];

								$new_item_id = $vendor_order->add_shipping( $shipping );
								wc_add_order_item_meta( $new_item_id, '_vendor_order_item_id', $vendor_order_item_id );
							break;
							case 'fee' :
								$fee            = new stdClass();
								$fee->name      = $order_items[ $vendor_order_item_id ]['name'];
								$fee->tax_class = $order_items[ $vendor_order_item_id ]['tax_class'];
								$fee->taxable   = $fee->tax_class !== '0';
								$fee->amount    = $vendor_order_item['total'];
								$fee->tax       = array_sum( $vendor_order_item['tax'] );
								$fee->tax_data  = $vendor_order_item['tax'];

								$new_item_id = $vendor_order->add_fee( $fee );
								wc_add_order_item_meta( $new_item_id, '_vendor_order_item_id', $vendor_order_item_id );
							break;
						}
					}
				}
				$vendor_order->update_taxes();
			}

			$vendor_order->calculate_totals( false );

			do_action( 'woocommerce_vendor_order_created', $vendor_order_id, $args );
		}

		// Clear transients
		wc_delete_shop_order_transients( $args['order_id'] );

		return new WC_Order_Vendor( $vendor_order_id );
	}


	/**
	 * Get vendor orders
	 *
	 * @return array
	 */
	public static function get_vendor_orders( $order_id ) {
		$vendor_orders    = array();
		$vendor_order_ids = get_posts(
			array(
				'post_type'      => 'shop_order_vendor',
				'post_parent'    => $order_id,
				'posts_per_page' => -1,
				'post_status'    => 'any',
				'fields'         => 'ids'
			)
		);

		foreach ( $vendor_order_ids as $vendor_order_id ) {
			$vendor_orders[] = new WC_Order_Vendor( $vendor_order_id );
		}

		return $vendor_orders;

	} // get_vendor_orders()

	/**
	 * Find the parent product id if the variation has been deleted 
	 * 
	 * @since 1.9.13
	 * @access public 
	 */
	public static function find_parent_id_from_order( $order_id, $product_id ){ 

		global $wpdb; 

		$order_item_id_sql = "SELECT `order_item_id` FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = $order_id AND `order_item_type` = 'line_item'"; 

		$order_item_ids = $wpdb->get_results( $order_item_id_sql ); 

		foreach ( $order_item_ids as $key => $order_item ) {

			$item_product_id 	= get_metadata( 'order_item', $order_item->order_item_id, '_product_id', true );
			$item_variation_id 	= get_metadata( 'order_item', $order_item->order_item_id, '_variation_id', true );			

			if ( $item_variation_id  == $product_id ){
				return $item_product_id; 
			}
		}

		return $product_id; 

	}

} // WCV_Vendors 
