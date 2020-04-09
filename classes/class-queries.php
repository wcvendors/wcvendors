<?php

class WCV_Queries {

	/**
	 *
	 *
	 * @param unknown $user_id
	 *
	 * @return unknown
	 */

	public static function get_commission_products( $user_id ) {
		global $wpdb;

		$dates           = self::orders_within_range();
		$vendor_products = array();
		$sql             = '';

		$sql .= "SELECT product_id FROM {$wpdb->prefix}pv_commission WHERE vendor_id = {$user_id} ";

		if ( ! empty( $dates ) ) {
			$sql .= "AND time >= '" . $dates['after'] . "' AND time <= '" . $dates['before'] . "'";
		}

		$sql .= " AND status != 'reversed' GROUP BY product_id";

		$results = $wpdb->get_results( $sql );

		foreach ( $results as $value ) {
			$ids[] = $value->product_id;
		}

		if ( ! empty( $ids ) ) {
			$vendor_products = get_posts(
				array(
					'numberposts' => -1,
					'orderby'     => 'post_date',
					'post_type'   => array( 'product', 'product_variation' ),
					'order'       => 'DESC',
					'include'     => $ids,
				)
			);
		}

		return $vendor_products;
	}

	/**
	 *
	 *
	 * @param unknown $order_id
	 *
	 * @return unknown
	 */


	public static function get_products_for_order( $order_id ) {
		global $wpdb;

		$vendor_products = array();

		$results = $wpdb->get_results(
			"
			SELECT product_id
			FROM {$wpdb->prefix}pv_commission
			WHERE order_id = {$order_id}
			AND     status != 'reversed'
			AND     vendor_id = " . get_current_user_id() . '
			GROUP BY product_id'
		);

		foreach ( $results as $value ) {
			$ids[] = $value->product_id;
		}

		return $ids;
	}

	/**
	 * All orders for a specific product
	 *
	 * @param array $product_ids
	 * @param array $args (optional)
	 *
	 * @return object
	 */
	public static function get_orders_for_products( array $product_ids, array $args = array() ) {
		global $wpdb;

		if ( empty( $product_ids ) ) {
			return false;
		}

		$dates = self::orders_within_range();

		$defaults = array(
			'status' => apply_filters( 'wcvendors_completed_statuses', array( 'completed', 'processing' ) ),
			'dates'  => array(
				'before' => $dates['before'],
				'after'  => $dates['after'],
			),
		);

		$args = wp_parse_args( $args, $defaults );

		$sql = "
			SELECT order_id
			FROM {$wpdb->prefix}pv_commission as order_items
			WHERE   product_id IN ('" . implode( "','", $product_ids ) . "')
			AND 	time >= '" . $args['dates']['after'] . "'
			AND 	time <= '" . $args['dates']['before'] . "'
			AND     status != 'reversed'
		";

		if ( ! empty( $args['vendor_id'] ) ) {
			$sql .= "
				AND vendor_id = {$args['vendor_id']}
			";
		}

		$sql .= '
			GROUP BY order_id
			ORDER BY time DESC
		';

		$orders = $wpdb->get_results( $sql );

		return $orders;
	}

	/**
	 * Sum of orders for a specific product
	 *
	 * @param array $product_ids
	 * @param array $args (optional)
	 *
	 * @return object
	 */
	public static function sum_orders_for_products( array $product_ids, array $args = array() ) {
		global $wpdb;

		$dates = self::orders_within_range();

		$defaults = array(
			'status' => apply_filters( 'wcvendors_completed_statuses', array( 'completed', 'processing' ) ),
			'dates'  => array(
				'before' => $dates['before'],
				'after'  => $dates['after'],
			),
		);

		foreach ( $product_ids as $id ) {
			$posts = get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => 'product_variation',
					'post_parent' => $id,
				)
			);

			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					$product_ids[] = $post->ID;
				}
			}
		}

		$args = wp_parse_args( $args, $defaults );

		$sql = "
			SELECT COUNT(order_id) as total_orders,
			       SUM(total_due + total_shipping + tax) as line_total,
			       SUM(qty) as qty,
			       product_id

			FROM {$wpdb->prefix}pv_commission

			WHERE   product_id IN ('" . implode( "','", $product_ids ) . "')
			AND     time >= '" . $args['dates']['after'] . "'
			AND     time <= '" . $args['dates']['before'] . "'
			AND     status != 'reversed'
		";

		if ( ! empty( $args['vendor_id'] ) ) {
			$sql .= "
				AND vendor_id = {$args['vendor_id']}
			";
		}

		$sql .= '
			GROUP BY product_id
			ORDER BY time DESC;
		';

		$orders = $wpdb->get_results( $sql );

		return $orders;
	}

	/**
	 * Sum of orders for a specific order
	 *
	 * @param array $order_ids
	 * @param array $args (optional)
	 *
	 * @return object
	 */
	public static function sum_for_orders( array $order_ids, array $args = array(), $date_range = true ) {
		global $wpdb;

		$dates = ( $date_range ) ? self::orders_within_range() : array();

		$defaults = array(
			'status' => apply_filters( 'wcvendors_completed_statuses', array( 'completed', 'processing' ) ),
		);

		$args = wp_parse_args( $args, $defaults );

		$sql = "
			SELECT COUNT(order_id) as total_orders,
			       SUM(total_due + total_shipping + tax) as line_total,
			       SUM(qty) as qty,
			       product_id

			FROM {$wpdb->prefix}pv_commission

			WHERE   order_id IN ('" . implode( "','", $order_ids ) . "')
			AND     status != 'reversed'
		";

		if ( ! empty( $dates ) ) {
			$sql .= "
				AND     time >= '" . $dates['after'] . "'
				AND     time <= '" . $dates['before'] . "'
			";
		}

		if ( ! empty( $args['vendor_id'] ) ) {
			$sql .= "
				AND vendor_id = {$args['vendor_id']}
			";
		}

		$sql .= '
			GROUP BY order_id
			ORDER BY time DESC;
		';

		$orders = $wpdb->get_results( $sql );

		return $orders;
	}

	/**
	 * Orders for range filter function
	 *
	 * @return array
	 */
	public static function orders_within_range() {
		global $start_date, $end_date;

		if ( ! empty( $_POST['start_date'] ) ) {
			WC()->session->set( 'wcv_order_start_date', strtotime( sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) ) );
		}

		if ( ! empty( $_POST['end_date'] ) ) {
			WC()->session->set( 'wcv_order_end_date', strtotime( sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) ) );
		}

		$start_date = WC()->session->get( 'wcv_order_start_date', strtotime( current_time( 'Y-M' ) . '-01' ) );
		$end_date   = WC()->session->get( 'wcv_order_end_date', strtotime( current_time( 'mysql' ) ) );

		$after  = gmdate( 'Y-m-d', $start_date );
		$before = gmdate( 'Y-m-d', strtotime( '+1 day', $end_date ) );

		return apply_filters(
			'wcvendors_orders_date_range',
			array(
				'after'  => $after,
				'before' => $before,
			)
		);
	}

}
