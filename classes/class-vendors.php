<?php

/**
 * Vendor functions
 *
 * @author  Matt Gates <http://mgates.me>, WC Vendors <http://wcvendors.com>
 * @package WCVendors
 */


class WCV_Vendors {

	/**
	 * Constructor
	 */
	function __construct() {

		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'create_child_orders' ), 10, 1 );
		add_filter( 'init', array( $this, 'add_rewrite_rules' ), 0 );
		add_action( 'delete_post', array( $this, 'remove_child_orders' ), 10, 1 );
	}

	/**
	 * Retrieve all products for a vendor
	 *
	 * @param int $vendor_id
	 *
	 * @return object
	 */
	public static function get_vendor_products( $vendor_id ) {

		$args = array(
			'numberposts' => -1,
			'post_type'   => 'product',
			'author'      => $vendor_id,
			'post_status' => 'publish',
		);

		$args = apply_filters( 'pv_get_vendor_products_args', $args );

		return get_posts( $args );
	}

	public static function get_default_commission( $vendor_id ) {

		return get_user_meta( $vendor_id, 'pv_custom_commission_rate', true );
	}


	/**
	 * Get vendors from an order including all user meta and vendor items filtered and grouped
	 *
	 * @param object  $order
	 * @param unknown $items (optional)
	 *
	 * @return array $vendors
	 * @version 2.0.0
	 */
	public static function get_vendors_from_order( $order, $items = false ) {

		$vendors      = array();
		$vendor_items = array();

		if ( is_a( $order, 'WC_Order' ) ) {

			// Only loop through order items if there isn't an error
			if ( is_array( $order->get_items() ) || is_object( $order->get_items() ) ) {

				foreach ( $order->get_items() as $item_id => $order_item ) {

					if ( 'line_item' === $order_item->get_type() ) {

						$product_id = ( $order_item->get_variation_id() ) ? $order_item->get_variation_id() : $order_item->get_product_id();
						$vendor_id  = self::get_vendor_from_product( $product_id );

						if ( ! self::is_vendor( $vendor_id ) ) {
							continue;
						}

						if ( array_key_exists( $vendor_id, $vendors ) ) {
							$vendors[ $vendor_id ]['line_items'][ $order_item->get_id() ] = $order_item;
						} else {
							$vendor_details        = array(
								'vendor'     => get_userdata( $vendor_id ),
								'line_items' => array( $order_item->get_id() => $order_item ),
							);
							$vendors[ $vendor_id ] = $vendor_details;
						}
					}
				}
			} else {
				$vendors = array();
			}
		}

		// legacy filter left in place
		$vendors = apply_filters( 'pv_vendors_from_order', $vendors, $order );

		return apply_filters( 'wcvendors_get_vendors_from_order', $vendors, $order );

	} // get_vendors_from_order()


	/**
	 *
	 *
	 * @param unknown $order
	 * @param unknown $group (optional)
	 *
	 * @return unknown
	 */
	public static function get_vendor_dues_from_order( $order, $group = true ) {

		global $woocommerce;

		$give_tax       = 'yes' == get_option( 'wcvendors_vendor_give_taxes', 'no' ) ? true : false;
		$give_shipping  = 'yes' == get_option( 'wcvendors_vendor_give_shipping', 'no' ) ? true : false;
		$receiver       = array();
		$shipping_given = 0;
		$tax_given      = 0;

		WCV_Shipping::$pps_shipping_costs = array();

		foreach ( $order->get_items() as $key => $order_item ) {

			$product_id             = ! empty( $order_item['variation_id'] ) ? $order_item['variation_id'] : $order_item['product_id'];
			$author                 = WCV_Vendors::get_vendor_from_product( $product_id );
			$give_tax_override      = get_user_meta( $author, 'wcv_give_vendor_tax', true );
			$give_shipping_override = get_user_meta( $author, 'wcv_give_vendor_shipping', true );
			$is_vendor              = WCV_Vendors::is_vendor( $author );
			$commission             = $is_vendor ? WCV_Commission::calculate_commission( $order_item['line_subtotal'], $product_id, $order, $order_item['qty'], $order_item ) : 0;
			$tax                    = ! empty( $order_item['line_tax'] ) ? (float) $order_item['line_tax'] : 0;
			$order_id               = $order->get_id();

			// Check if shipping is enabled
			if ( 'no' === get_option( 'woocommerce_calc_shipping' ) ) {
				$shipping     = 0;
				$shipping_tax = 0;
			} else {
				$shipping_costs = WCV_Shipping::get_shipping_due( $order_id, $order_item, $author, $product_id );
				$shipping       = $shipping_costs['amount'];
				$shipping_tax   = $shipping_costs['tax'];
			}

			$_product = new WC_Product( $order_item['product_id'] );

			// Add line item tax and shipping taxes together
			$total_tax = ( $_product->is_taxable() ) ? (float) $tax + (float) $shipping_tax : 0;

			// Tax override on a per vendor basis
			if ( $give_tax_override ) {
				$give_tax = true;
			}
			// Shipping override
			if ( $give_shipping_override ) {
				$give_shipping = true;
			}

			if ( $is_vendor ) {

				$shipping_given += $give_shipping ? $shipping : 0;
				$tax_given      += $give_tax ? $total_tax : 0;

				$give = 0;
				$give += ! empty( $receiver[ $author ]['total'] ) ? $receiver[ $author ]['total'] : 0;
				$give += $give_shipping ? $shipping : 0;
				$give += $commission;
				$give += $give_tax ? $total_tax : 0;

				if ( $group ) {

					$receiver[ $author ] = array(
						'vendor_id'  => (int) $author,
						'commission' => ! empty( $receiver[ $author ]['commission'] ) ? $receiver[ $author ]['commission'] + $commission : $commission,
						'shipping'   => $give_shipping ? ( ! empty( $receiver[ $author ]['shipping'] ) ? $receiver[ $author ]['shipping'] + $shipping : $shipping ) : 0,
						'tax'        => $give_tax ? ( ! empty( $receiver[ $author ]['tax'] ) ? $receiver[ $author ]['tax'] + $total_tax : $total_tax ) : 0,
						'qty'        => ! empty( $receiver[ $author ]['qty'] ) ? $receiver[ $author ]['qty'] + $order_item['qty'] : $order_item['qty'],
						'total'      => $give,
					);

				} else {

					$receiver[ $author ][ $key ] = array(
						'vendor_id'  => (int) $author,
						'product_id' => $product_id,
						'commission' => $commission,
						'shipping'   => $give_shipping ? $shipping : 0,
						'tax'        => $give_tax ? $total_tax : 0,
						'qty'        => $order_item['qty'],
						'total'      => ( $give_shipping ? $shipping : 0 ) + $commission + ( $give_tax ? $total_tax : 0 ),
					);

				}
			}

			$admin_comm = $order_item['line_subtotal'] - $commission;

			if ( $group ) {
				$receiver[1] = array(
					'vendor_id'  => 1,
					'qty'        => ! empty( $receiver[1]['qty'] ) ? $receiver[1]['qty'] + $order_item['qty'] : $order_item['qty'],
					'commission' => ! empty( $receiver[1]['commission'] ) ? $receiver[1]['commission'] + $admin_comm : $admin_comm,
					'total'      => ! empty( $receiver[1] ) ? $receiver[1]['total'] + $admin_comm : $admin_comm,
				);
			} else {
				$receiver[1][ $key ] = array(
					'vendor_id'  => 1,
					'product_id' => $product_id,
					'commission' => $admin_comm,
					'shipping'   => 0,
					'tax'        => 0,
					'qty'        => $order_item['qty'],
					'total'      => $admin_comm,
				);
			}
		}

		// Add remainders on end to admin
		$discount = $order->get_total_discount();
		$shipping = round( ( $order->get_total_shipping() - $shipping_given ), 2 );
		$tax      = round( $order->get_total_tax() - $tax_given, 2 );
		$total    = ( $tax + $shipping ) - $discount;

		if ( $group ) {
			$r_total                   = round( $receiver[1]['total'], 2 );
			$receiver[1]['commission'] = round( $receiver[1]['commission'], 2 ) - round( $discount, 2 );
			$receiver[1]['shipping']   = $shipping;
			$receiver[1]['tax']        = $tax;
			$receiver[1]['total']      = $r_total + round( $total, 2 );
		} else {
			$r_total                           = round( $receiver[1][ $key ]['total'], 2 );
			$receiver[1][ $key ]['commission'] = round( $receiver[1][ $key ]['commission'], 2 ) - round( $discount, 2 );
			$receiver[1][ $key ]['shipping']   = ( $order->get_total_shipping() - $shipping_given );
			$receiver[1][ $key ]['tax']        = $tax;
			$receiver[1][ $key ]['total']      = $r_total + round( $total, 2 );
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
	public static function get_vendor_paypal( $vendor_id ) {

		$paypal = get_user_meta( $vendor_id, $meta_key = 'pv_paypal', true );
		$paypal = ! empty( $paypal ) ? $paypal : get_the_author_meta( 'user_email', $vendor_id, false );

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
	public static function count_due_by_vendor( $vendor_id, $order_id ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$query
			   = "SELECT COUNT(*)
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
	public static function get_due_orders_by_vendor( $vendor_id ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$query
			     = "SELECT *
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
	public static function get_vendor_from_product( $product_id ) {

		// Make sure we are returning an author for products or product variations only
		if ( 'product' === get_post_type( $product_id ) || 'product_variation' === get_post_type( $product_id ) ) {
			$parent = get_post_ancestors( $product_id );
			if ( $parent ) {
				$product_id = $parent[0];
			}

			$post   = get_post( $product_id );
			$author = $post ? $post->post_author : 1;
			$author = apply_filters( 'pv_product_author', $author, $product_id );
		} else {
			$author = - 1;
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
	public static function is_vendor( $user_id ) {

		$user         = get_userdata( $user_id );
		$vendor_roles = apply_filters( 'wcvendors_vendor_roles', array( 'vendor' ) );
		$is_vendor    = false;

		if ( is_object( $user ) && is_array( $user->roles ) ) {

			foreach ( $vendor_roles as $role ) {
				if ( in_array( $role, $user->roles ) ) {
					$is_vendor = true;
					break;
				}
			}
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
	public static function get_vendor_id( $input ) {

		if ( empty( $input ) ) {
			return false;
		}

		$users = get_users(
			array(
				'meta_key'   => 'pv_shop_slug',
				'meta_value' => sanitize_title( $input ),
			)
		);

		if ( ! empty( $users ) && 1 == count( $users ) ) {
			$vendor = $users[0];
		} else {
			$int_vendor = is_numeric( $input );
			$vendor     = ! empty( $int_vendor ) ? get_userdata( $input ) : get_user_by( 'login', $input );
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
	public static function get_vendor_shop_page( $vendor_id ) {

		$vendor_id = self::get_vendor_id( $vendor_id );
		if ( ! $vendor_id ) {
			return;
		}

		$slug   = get_user_meta( $vendor_id, 'pv_shop_slug', true );
		$vendor = ! $slug ? get_userdata( $vendor_id )->user_login : $slug;

		if ( get_option( 'permalink_structure' ) ) {
			$permalink = trailingslashit( get_option( 'wcvendors_vendor_shop_permalink' ) );

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
	public static function get_vendor_shop_name( $vendor_id ) {

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
	public static function is_pending( $user_id ) {

		$user 	= get_userdata( $user_id );
		$roles 	= $user->roles;

		if ( is_array( $roles ) ){
			$is_pending = in_array( 'pending_vendor', $roles );
		} else {
			$is_pending = ( 'pending_vendor' == $role );
		}
		return $is_pending;
	}

	/*
	* 	Is this a vendor product ?
	* 	@param uknown $role
	*/
	public static function is_vendor_product( $role ) {

		return ( 'vendor' === $role ) ? true : false;
	}

	/**
	 * Is this the vendors shop archive page or a single vendor product?
	 *
	 * @return boolean
	 * @since   2.1.3
	 * @version 2.1.3
	 */
	public static function is_vendor_page() {

		global $post;

		$vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
		$vendor_id   = WCV_Vendors::get_vendor_id( $vendor_shop );

		if ( ! $vendor_id && is_a( $post, 'WC_Product' ) ) {
			if ( self::is_vendor( $post->post_author ) ) {
				$vendor_id = $post->post_author;
			}
		}

		return $vendor_id ? true : false;

	} // is_vendor_page()

	/*
	*	Is this a vendor single product page ?
	*/
	public static function is_vendor_product_page( $vendor_id ) {

		$vendor_product = WCV_Vendors::is_vendor_product( wcv_get_user_role( $vendor_id ) );

		return $vendor_product ? true : false;

	} // is_vendor_product_page()

	public static function get_vendor_sold_by( $vendor_id ) {

		$vendor_display_name = get_option( 'wcvendors_display_shop_display_name' );
		$vendor              = get_userdata( $vendor_id );

		switch ( $vendor_display_name ) {
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
	 *
	 * @param int $order_id
	 *
	 * @return void
	 */
	public static function create_child_orders( $order_id ) {

		$order        = wc_get_order( $order_id );
		$items        = $order->get_items();
		$vendor_items = array();

		foreach ( $items as $item_id => $item ) {
			if ( isset( $item['product_id'] ) && 0 !== $item['product_id'] ) {
				// check if product is from vendor
				$product_author = get_post_field( 'post_author', $item['product_id'] );
				if ( WCV_Vendors::is_vendor( $product_author ) ) {
					$vendor_items[ $product_author ][ $item_id ] = array(
						'item_id'      => $item_id,
						'qty'          => $item['qty'],
						'total'        => $item['line_total'],
						'subtotal'     => $item['line_subtotal'],
						'tax'          => $item['line_tax'],
						'subtotal_tax' => $item['line_subtotal_tax'],
						'tax_data'     => maybe_unserialize( $item['line_tax_data'] ),
						'commission'   => WCV_Commission::calculate_commission( $item['line_subtotal'], $item['product_id'], $order, $item['qty'], $item ),
					);
				}
			}
		}

		foreach ( $vendor_items as $vendor_id => $items ) {
			if ( ! empty( $items ) ) {
				$vendor_order = WCV_Vendors::create_vendor_order(
					array(
						'order_id'   => $order_id,
						'vendor_id'  => $vendor_id,
						'line_items' => $items,
					)
				);
			}
		}
	}

	/**
	 * Create a new vendor order programmatically
	 *
	 * Returns a new vendor_order object on success which can then be used to add additional data.
	 *
	 * @since
	 *
	 * @param array $args
	 *
	 * @return WC_Order_Vendor|WP_Error
	 */
	public static function create_vendor_order( $args = array() ) {

		$default_args = array(
			'vendor_id'       => null,
			'order_id'        => 0,
			'vendor_order_id' => 0,
			'line_items'      => array(),
			'date'            => current_time( 'mysql', 0 ),
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
			$vendor_order_data['post_title']    = sprintf( __( '%1$s Order &ndash; %2$s', 'wc-vendors' ), wcv_get_vendor_name(), strftime( _x( '%1$b %2$d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'wc-vendors' ) ) );
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

			$order_currency = $order->get_currency();

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
							case 'line_item':
								$line_item_args = array(
									'totals' => array(
										'subtotal'     => $vendor_order_item['subtotal'],
										'total'        => $vendor_order_item['total'],
										'subtotal_tax' => $vendor_order_item['subtotal_tax'],
										'tax'          => $vendor_order_item['tax'],
										'tax_data'     => $vendor_order_item['tax_data'],
									),
								);
								$line_item = new WC_Order_Item_Product( $vendor_order_item_id ); 
								$new_item_id    = $vendor_order->add_product( $line_item->get_product(), isset( $vendor_order_item['qty'] ) ? $vendor_order_item['qty'] : 0, $line_item_args );
								wc_add_order_item_meta( $new_item_id, '_vendor_order_item_id', $vendor_order_item_id );
								wc_add_order_item_meta( $new_item_id, '_vendor_commission', $vendor_order_item['commission'] );
								break;
							case 'shipping':
								$shipping        = new stdClass();
								$shipping->label = $order_items[ $vendor_order_item_id ]['name'];
								$shipping->id    = $order_items[ $vendor_order_item_id ]['method_id'];
								$shipping->cost  = $vendor_order_item['total'];
								$shipping->taxes = $vendor_order_item['tax'];

								$new_item_id = $vendor_order->add_shipping( $shipping );
								wc_add_order_item_meta( $new_item_id, '_vendor_order_item_id', $vendor_order_item_id );
								break;
							case 'fee':
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
				'fields'         => 'ids',
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
	 * @since  1.9.13
	 * @access public
	 */
	public static function find_parent_id_from_order( $order_id, $product_id ) {

		global $wpdb;

		$order_item_id_sql = "SELECT `order_item_id` FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = $order_id AND `order_item_type` = 'line_item'";

		$order_item_ids = $wpdb->get_results( $order_item_id_sql );

		foreach ( $order_item_ids as $key => $order_item ) {

			$item_product_id   = get_metadata( 'order_item', $order_item->order_item_id, '_product_id', true );
			$item_variation_id = get_metadata( 'order_item', $order_item->order_item_id, '_variation_id', true );

			if ( $item_variation_id == $product_id ) {
				return $item_product_id;
			}
		}

		return $product_id;

	}

	/**
	 * Remove child orders if the parent order is deleted
	 *
	 * @since 2.1.13
	 * @access public
	 */
	public function remove_child_orders( $post_id ){

		$post_type = get_post_type( $post_id ); 

		if ( 'shop_order' !== $post_type ) return; 

		$child_orders = get_children(
			array(
				'post_parent' 	=> $post_id,
				'post_type' 	=> 'shop_order_vendor'
			)
		);

		if ( empty( $child_orders ) ) return;

		foreach ( $child_orders as $child_order ) {
			wp_delete_post( $child_order->ID, true );
		}
	}

	/**
	 * Moved to vendors class
	 *
	 * @version 2.2.0
	 * @since 2.0.9
	 */
	public static function add_rewrite_rules() {

		$permalink = untrailingslashit( get_option( 'wcvendors_vendor_shop_permalink' ) );

		// Remove beginning slash
		if ( '/' == substr( $permalink, 0, 1 ) ) {
			$permalink = substr( $permalink, 1, strlen( $permalink ) );
		}

		add_rewrite_tag( '%vendor_shop%', '([^&]+)' );

		add_rewrite_rule( $permalink . '/page/([0-9]+)', 'index.php?pagename='.$permalink.'&paged=$matches[1]', 'top' );
		add_rewrite_rule( $permalink . '/([^/]*)/page/([0-9]+)', 'index.php?post_type=product&vendor_shop=$matches[1]&paged=$matches[2]', 'top' );
		add_rewrite_rule( $permalink . '/([^/]*)', 'index.php?post_type=product&vendor_shop=$matches[1]', 'top' );
	}

} // WCV_Vendors
