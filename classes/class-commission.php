<?php

/**
 * Commission functions
 *
 * @author  Matt Gates <http://mgates.me>
 * @package WCVendors
 */


class WCV_Commission {


	/**
	 * Constructor
	 */
	function __construct() {

		add_action( 'init', array( $this, 'check_order_complete' ) );
		add_action( 'init', array( $this, 'check_order_reverse'  ) );

		// Reverse the commission if the order is deleted
		add_action( 'deleted_post' , array( $this, 'commissions_table_sync' ), 10 );
		add_action( 'wp_trash_post', array( $this, 'commissions_table_sync' ), 10 );
	}


	/**
	 * Run actions when an order is reversed
	 */
	public function check_order_reverse() {

		foreach ( $this->get_completed_status() as $completed ) {
			foreach ( $this->get_reversed_status() as $reversed ) {
				add_action(
					"woocommerce_order_status_{$completed}_to_{$reversed}", array(
						'WCV_Commission',
						'reverse_due_commission',
					)
				);
			}
		}
	}


	/**
	 * Runs only on a manual order update by a human
	 */
	public function check_order_complete() {

		foreach ( $this->get_completed_status() as $completed ) {
			add_action( 'woocommerce_order_status_' . $completed, array( 'WCV_Commission', 'log_commission_due' ) );
		}
	}

	// get commission status's
	public static function commission_status() {

		return apply_filters(
			'wcvendors_commission_status', array(
				'due'      => __( 'Due', 'wc-vendors' ),
				'paid'     => __( 'Paid', 'wc-vendors' ),
				'reversed' => __( 'Reversed', 'wc-vendors' ),
			)
		);
	}

	/**
	 * return completed statuss
	 */
	public function get_completed_status() {

		return $completed_statuses = apply_filters(
			'wcvendors_completed_statuses', array(
				'completed',
				'processing',
			)
		);

	}


	/*
	* Return reversed status's
	*/
	public function get_reversed_status() {

		return $reverse_statuses = apply_filters(
			'wcvendors_reversed_statuses', array(
				'pending',
				'refunded',
				'cancelled',
				'failed',
			)
		);

	}


	/**
	 * Reverse commission for an entire order
	 *
	 * Only runs if the order has been logged in pv_commission table
	 *
	 * @param int $order_id
	 *
	 * @return unknown
	 */
	public static function reverse_due_commission( $order_id ) {

		global $wpdb;

		// Check if this order exists
		$count = WCV_Commission::count_commission_by_order( $order_id );
		if ( ! $count ) {
			return false;
		}

		// Deduct this amount from the vendor's total due
		$results    = WCV_Commission::sum_total_due_for_order( $order_id );
		$ids        = implode( ',', $results['ids'] );
		$table_name = $wpdb->prefix . 'pv_commission';

		$query   = "UPDATE `{$table_name}` SET `status` = '%s' WHERE id IN ({$ids})";
		$results = $wpdb->query( $wpdb->prepare( $query, 'reversed' ) );

		return $results;
	}


	/**
	 * Store all commission due for an order
	 *
	 * @return bool
	 *
	 * @param int $order_id
	 */
	public static function log_commission_due( $order_id ) {

		global $woocommerce;

		$order = wc_get_order( $order_id );
		$dues  = WCV_Vendors::get_vendor_dues_from_order( $order, false );

		foreach ( $dues as $vendor_id => $details ) {

			// Only process vendor commission
			if ( ! WCV_Vendors::is_vendor( $vendor_id ) ) {
				continue;
			}

			// See if they currently have an amount due
			$due = WCV_Vendors::count_due_by_vendor( $vendor_id, $order_id );
			if ( $due > 0 ) {
				continue;
			}

			// Get the dues in an easy format for inserting to our table
			$insert_due = array();

			foreach ( $details as $key => $detail ) {

				$product_id = $detail['product_id'];
				$order_date = $order->get_date_created();

				$insert_due[ $product_id ] = array(
					'order_id'       => $order_id,
					'vendor_id'      => $vendor_id,
					'product_id'     => $product_id,
					'total_due'      => ! empty( $insert_due[ $product_id ]['total_due'] )      ? ( $detail['commission'] + $insert_due[ $product_id ]['total_due'] )    : $detail['commission'],
					'total_shipping' => ! empty( $insert_due[ $product_id ]['total_shipping'] ) ? ( $detail['shipping'] + $insert_due[ $product_id ]['total_shipping'] ) : $detail['shipping'],
					'tax'            => ! empty( $insert_due[ $product_id ]['tax'] ) ? ( $detail['tax'] + $insert_due[ $product_id ]['tax'] ) : $detail['tax'],
					'qty'            => ! empty( $insert_due[ $product_id ]['qty'] ) ? ( $detail['qty'] + $insert_due[ $product_id ]['qty'] ) : $detail['qty'],
					'time'           => date( 'Y-m-d H:i:s', strtotime( $order_date ) ),
				);
			}

			if ( ! empty( $insert_due ) ) {
				WCV_Commission::insert_new_commission( array_values( $insert_due ) );
			}
		}

	}


	/**
	 * Add up the totals for an order for each vendor
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public static function sum_total_due_for_order( $order_id, $status = 'due' ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$query = "SELECT `id`, `total_due`, `total_shipping`, `tax`, `vendor_id`
							FROM `{$table_name}`
							WHERE `order_id` = %d
							AND `status` = %s";

		$results = $wpdb->get_results( $wpdb->prepare( $query, $order_id, 'due' ) );

		foreach ( $results as $commission ) {
			$commission_ids[] = $commission->id;

			$pay[ $commission->vendor_id ] = ! empty( $pay[ $commission->vendor_id ] )
				? ( $pay[ $commission->vendor_id ] + ( $commission->total_due + $commission->total_shipping + $commission->tax ) )
				: ( $commission->total_due + $commission->total_shipping + $commission->tax );
		}

		$return = array(
			'vendors' => $pay,
			'ids'     => $commission_ids,
		);

		return $return;
	}


	/**
	 * Return all commission outstanding with a 'due' status
	 *
	 * @return object
	 */
	public static function get_all_due() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';
		$where      = $wpdb->prepare( 'WHERE status = %s', 'due' );
		$where      = apply_filters( 'wcvendors_commission_all_due_where', $where );
		$query      = "SELECT id, vendor_id, total_due, total_shipping FROM `{$table_name}` $where";
		$query      = apply_filters( 'wcvendors_commission_all_due_sql', $query );
		$results    = $wpdb->get_results( $query );

		return $results;
	}


	/**
	 * Check if this order has commission logged already
	 *
	 * @param int $order_id
	 *
	 * @return int
	 */
	public static function count_commission_by_order( $order_id ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'pv_commission';

		if ( is_array( $order_id ) ) {
			$order_id = implode( ',', $order_id );
		}

		$query = "SELECT COUNT(order_id) AS order_count
				     FROM {$table_name}
				     WHERE order_id IN ($order_id)
				     AND status <> %s";
		$count = $wpdb->get_var( $wpdb->prepare( $query, 'reversed' ) );

		return $count;
	}

	/**
	 * Check the commission status for the order
	 *
	 * @param array  $order
	 * @param string $status
	 *
	 * @return int
	 */
	public static function check_commission_status( $order, $status ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$order_id   = $order['order_id'];
		$vendor_id  = $order['vendor_id'];
		$product_id = $order['product_id'];

		$query = "SELECT count(order_id) AS order_count
							FROM {$table_name}
							WHERE order_id = {$order_id}
							AND vendor_id = {$vendor_id}
							AND product_id = {$product_id}
							AND status = %s
		";

		return $wpdb->get_var( $wpdb->prepare( $query, $status ) );

	}


	/**
	 * Product's commission rate in percentage form
	 *
	 * Eg: 50 for 50%
	 *
	 * @param int $product_id
	 *
	 * @return float
	 */
	public static function get_commission_rate( $product_id ) {

		$commission = 0;

		$parent = get_post_ancestors( $product_id );
		if ( $parent ) {
			$product_id = $parent[0];
		}

		$vendor_id = WCV_Vendors::get_vendor_from_product( $product_id );

		$product_commission = get_post_meta( $product_id, 'pv_commission_rate', true );
		$vendor_commission  = WCV_Vendors::get_default_commission( $vendor_id );
		$default_commission = get_option( 'wcvendors_vendor_commission_rate' );

		if ( '' != $product_commission && false !== $product_commission ) {
			$commission = $product_commission;
		} elseif ( '' != $vendor_commission && false !== $vendor_commission ) {
			$commission = $vendor_commission;
		} elseif ( '' != $default_commission && false !== $default_commission ) {
			$commission = $default_commission;
		}

		return apply_filters( 'wcv_commission_rate_percent', $commission, $product_id );
	}


	/**
	 * Commission due for a product based on a rate and price
	 *
	 * @version 2.1.14
	 * @since   2.0.0
	 * @param float    $product_price The product price.
	 * @param int      $product_id    The product id.
	 * @param WC_Order $order         The WooCommerce order.
	 * @param int      $qty           The product quantity.
	 * @param mixed    $item          The order item. Optional, default array().
	 * @return float
	 */
	public static function calculate_commission( $product_price, $product_id, $order, $qty, $item = array() ) {

		$commission_rate = self::get_commission_rate( $product_id );
		$commission      = $product_price * ( $commission_rate / 100 );
		$commission      = round( $commission, 2 );

		return apply_filters( 'wcv_commission_rate', $commission, $product_id, $product_price, $order, $qty, $item );
	}


	/**
	 * Log commission to the pv_commission table
	 *
	 * Will either update or insert to the database
	 *
	 * @param array $orders
	 *
	 * @return unknown
	 */
	public static function insert_new_commission( $orders = array() ) {

		global $wpdb;

		if ( empty( $orders ) ) {
			return false;
		}

		$table = $wpdb->prefix . 'pv_commission';

		// Insert the time and default status 'due'
		foreach ( $orders as $key => $order ) {
			$orders[ $key ]['time']   = $order['time'];
			$orders[ $key ]['status'] = ( $order['total_due'] == 0 ) ? 'paid' : 'due';
		}

		foreach ( $orders as $key => $order ) {

			$where = array(
				'order_id'   => $order['order_id'],
				'product_id' => $order['product_id'],
				'vendor_id'  => $order['vendor_id'],
				'qty'        => $order['qty'],
			);
			// Is the commission already paid?
			$count = WCV_Commission::check_commission_status( $order, 'paid' );

			if ( 0 == $count ) {
				$format = array( '%d', '%d', '%d', '%f', '%f', '%f', '%f', '%s', '%s' );
				$update = $wpdb->update( $table, $order, $where, $format );
				if ( ! $update ) {
					$insert = $wpdb->insert( $table, $order, $format );
				}
			}
		}

		do_action( 'wcv_commissions_inserted', $orders );
	}


	/**
	 * Set commission to 'paid' for an entire order
	 *
	 * @access public
	 *
	 * @param mixed   $order_id   An array of Order IDs or an int.
	 * @param unknown $column_ids (optional)
	 *
	 * @return bool.
	 */
	public static function set_order_commission_paid( $order_id, $column_ids = false ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		if ( is_array( $order_id ) ) {
			$order_id = implode( ',', $order_id );
		}

		$query  = "UPDATE `{$table_name}` SET `status` = 'paid' WHERE order_id IN ($order_id)";
		$result = $wpdb->query( $query );

		return $result;
	}


	/**
	 * Set commission to 'paid' for an entire order
	 *
	 * @access public
	 *
	 * @param mixed $order_id An array of Order IDs or an int.
	 *
	 * @return bool.
	 */
	public static function set_vendor_commission_paid( $vendors ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		if ( is_array( $vendors ) ) {
			$vendors = implode( ',', $vendors );
		}

		$query  = "UPDATE `{$table_name}` SET `status` = 'paid' WHERE vendor_id IN ($vendors)";
		$result = $wpdb->query( $query );

		return $result;
	}


	/**
	 * Set commission to 'paid' for a specifc vendor
	 *
	 * @access public
	 *
	 * @param int $vendor_id  the vendor id
	 * @param int $product_id the product id
	 * @param int $order_id   the order id
	 *
	 * @return bool.
	 */
	public static function set_vendor_product_commission_paid( $vendor_id, $product_id, $order_id ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$query  = "UPDATE `{$table_name}` SET `status` = 'paid' WHERE vendor_id = $vendor_id AND order_id = $order_id AND product_id = $product_id";
		$result = $wpdb->query( $query );

		return $result;
	}

	/**
	 * If an order is deleted reverse the commissions rows
	 *
	 * @since   1.9.2
	 * @version 1.9.13
	 * @access  public
	 *
	 * @param int $order_id the order id
	 *
	 * @return bool.
	 */
	public function commissions_table_sync( $order_id ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';
		$query      = "UPDATE `{$table_name}` SET `status` = 'reversed' WHERE `order_id` = %d";
		$results    = $wpdb->query( $wpdb->prepare( $query, $order_id ) );

	} // commissions_table_sync()


	/**
	 * Get the commission total for a specific vendor.
	 *
	 * @since  1.9.6
	 * @access public
	 *
	 * @param int    $vendor_id the vendor id to search for
	 * @param string $status    the status to look for
	 *
	 * @return object $totals as an object
	 */
	public static function commissions_now( $vendor_id, $status = 'due', $inc_shipping = false, $inc_tax = false ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$sql = 'SELECT sum( `total_due` ) as total_due';

		if ( $inc_shipping ) {
			$sql .= ', sum( `total_shipping` ) as total_shipping';
		}
		if ( $inc_tax ) {
			$sql .= ', sum( `tax` ) as total_tax ';
		}

		$sql
			.= "
				FROM `{$table_name}`
				WHERE vendor_id = {$vendor_id}
				AND status = '{$status}'
			";

		$results = $wpdb->get_row( $sql );

		$commissions_now = array_filter( get_object_vars( $results ) );

		if ( empty( $commissions_now ) ) {
			$results = false;
		}

		return $results;

	} // commissions_now()


	/**
	 * Get the commission for a specific order, product and vendor
	 *
	 * @since  1.9.9
	 * @access public
	 *
	 * @param int $order_id   the order id to search for
	 * @param int $product_id the product id to search for
	 * @param int $vendor_id  the vendor id to search for
	 */
	public static function get_commission_due( $order_id, $product_id, $vendor_id ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$sql = 'SELECT total_due';

		$sql
			.= "
				FROM `{$table_name}`
				WHERE vendor_id = {$vendor_id}
				AND product_id = '{$product_id}'
				AND order_id = '{$order_id}'
			";

		$commission_due = $wpdb->get_var( $sql );

		return $commission_due;

	} // get_commission_due()


	/**
	 * Get the total due for all commissions
	 *
	 * @since  2.0.0
	 * @access public
	 */
	public static function get_totals( $status = 'due' ) {

		$total_due = 0;

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';
		$query
		            = "SELECT sum(total_due + total_shipping + tax) as total
					FROM `{$table_name}`
					WHERE status = %s";
		$results    = $wpdb->get_results( $wpdb->prepare( $query, $status ) );

		$totals = array_shift( $results )->total;

		return $totals;

	}

	/*
	* Get the summed total for each vendor based on the status
	* @since 2.0.3
	* @access public
	*/
	public static function get_sum_vendor_totals() {

		global $wpdb;

		$due      = array();
		$paid     = array();
		$reversed = array();

		$table_name = $wpdb->prefix . 'pv_commission';
		$query
		            = "SELECT `id`, `total_due`, `total_shipping`, `tax`, `vendor_id`, `status`
					FROM `{$table_name}`";

		$orderby    = ! empty( $_REQUEST['orderby'] ) ? esc_attr( $_REQUEST['orderby'] ) : 'time';
		$order      = ( ! empty( $_REQUEST['order'] ) && $_REQUEST['order'] == 'asc' ) ? 'ASC' : 'DESC';
		$com_status = ! empty( $_REQUEST['com_status'] ) ? esc_attr( $_REQUEST['com_status'] ) : '';
		$vendor_id  = ! empty( $_REQUEST['vendor_id'] ) ? esc_attr( $_REQUEST['vendor_id'] ) : '';
		$status_sql = '';
		$time_sql   = '';

		if ( ! empty( $_REQUEST['from_date'] ) && ! empty( $_REQUEST['to_date'] ) ) {
			$from_date = sanitize_text_field( wp_unslash( $_REQUEST['from_date'] ) );
			$to_date   = sanitize_text_field( wp_unslash( $_REQUEST['to_date'] ) );
			$time_sql  = " WHERE time BETWEEN '$from_date' AND '$to_date'";

			$query .= $time_sql;
		}

		if ( ! empty( $_GET['com_status'] ) ) {

			if ( $time_sql == '' ) {
				$status_sql
					= "
				WHERE status = '$com_status'
				";
			} else {
				$status_sql
					= "
				AND status = '$com_status'
				";
			}

			$query .= $status_sql;
		}

		if ( ! empty( $_GET['vendor_id'] ) ) {

			if ( '' == $time_sql && '' == $status_sql ) {
				$vendor_sql
					= "
				WHERE vendor_id = '$vendor_id'
				";
			} else {
				$vendor_sql
					= "
				AND vendor_id = '$vendor_id'
				";
			}

			$query .= $vendor_sql;
		}

		$results = $wpdb->get_results( $query );

		foreach ( $results as $commission ) {

			switch ( $commission->status ) {
				case 'due':
					$due[ $commission->vendor_id ] = ! empty( $due[ $commission->vendor_id ] ) ? ( $due[ $commission->vendor_id ] + ( $commission->total_due + $commission->total_shipping + $commission->tax ) ) : ( $commission->total_due + $commission->total_shipping + $commission->tax );
					break;
				case 'paid':
					$paid[ $commission->vendor_id ] = ! empty( $paid[ $commission->vendor_id ] ) ? ( $paid[ $commission->vendor_id ] + ( $commission->total_due + $commission->total_shipping + $commission->tax ) ) : ( $commission->total_due + $commission->total_shipping + $commission->tax );
					break;
				case 'reversed':
					$reversed[ $commission->vendor_id ] = ! empty( $reversed[ $commission->vendor_id ] ) ? ( $reversed[ $commission->vendor_id ] + ( $commission->total_due + $commission->total_shipping + $commission->tax ) ) : ( $commission->total_due + $commission->total_shipping + $commission->tax );
					break;
				default:
					// code...
					break;
			}
		}

		$sum_totals = array(
			'due'      => $due,
			'paid'     => $paid,
			'reversed' => $reversed,
		);

		return $sum_totals;

	}

}
