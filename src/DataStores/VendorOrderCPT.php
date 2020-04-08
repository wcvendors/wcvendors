<?php
/**
 * Vendor Order Data Store.
 *
 * @package WCVendors/DataStores
 */

namespace WCVendors\DataStores;

use WCVendors\VendorOrder;
use WC_Order_Data_Store_CPT;
use WC_Order_Data_Store_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vendor Order Data Store.
 */
class VendorOrderCPT extends WC_Order_Data_Store_CPT implements WC_Order_Data_Store_Interface {
	/**
	 * Create the vendor order in the database
	 *
	 * @param VendorOrder $vendor_order Vendor order object.
	 */
	public function create( &$vendor_order ) {
		$vendor_order->set_version( WCV_VERSION );
		$vendor_order->set_date_created( time() );

		$id = wp_insert_post(
			apply_filters(
				'woocommerce_new_order_data',
				array(
					'post_date'     => gmdate( 'Y-m-d H:i:s', $vendor_order->get_date_created( 'edit' )->getOffsetTimestamp() ),
					'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $vendor_order->get_date_created( 'edit' )->getTimestamp() ),
					'post_type'     => $vendor_order->get_type( 'edit' ),
					'post_status'   => $this->get_post_status( $vendor_order ),
					'ping_status'   => 'closed',
					'post_author'   => $vendor_order->get_vendor_id(),
					'post_title'    => $this->get_post_title(),
					'post_password' => wc_generate_order_key(),
					'post_parent'   => $vendor_order->get_parent_id( 'edit' ),
					'post_excerpt'  => $this->get_post_excerpt( $vendor_order ),
				)
			),
			true
		);

		if ( $id && ! is_wp_error( $id ) ) {
			$vendor_order->set_id( $id );
			$this->update_post_meta( $vendor_order );
			$vendor_order->save_meta_data();
			$vendor_order->apply_changes();
			$this->clear_caches( $vendor_order );
		}
		do_action( 'wcvendors_new_vendor_order', $vendor_order->get_id() );
	}

	/**
	 * Get a title for the new post type.
	 *
	 * @return string
	 */
	protected function get_post_title() {
		// @codingStandardsIgnoreStart
		/* translators: %s: Order date */
		return sprintf( __( 'Vendor order &ndash; %s', 'woocommerce' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) );
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Helper method that updates all the post meta for an order based on
	 * it's settings in the WC_Order class.
	 *
	 * @param VendorOrder $vendor_order Vendor order object.
	 */
	protected function update_post_meta( &$vendor_order ) {
		$updated_props     = array();
		$meta_key_to_props = array(
			'_cart_discount'      => 'discount_total',
			'_cart_discount_tax'  => 'discount_tax',
			'_order_shipping'     => 'shipping_total',
			'_order_shipping_tax' => 'shipping_tax',
			'_order_tax'          => 'cart_tax',
			'_order_total'        => 'total',
			'_order_version'      => 'version',
			'_prices_include_tax' => 'prices_include_tax',
		);

		$props_to_update = $this->get_props_to_update( $vendor_order, $meta_key_to_props );

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $vendor_order->{"get_$prop"}( 'edit' );
			$value = is_string( $value ) ? wp_slash( $value ) : $value;

			if ( 'prices_include_tax' === $prop ) {
				$value = $value ? 'yes' : 'no';
			}

			$updated = $this->update_or_delete_post_meta( $vendor_order, $meta_key, $value );

			if ( $updated ) {
				$updated_props[] = $prop;
			}
		}

		do_action( 'wcvendors_order_object_updated_props', $vendor_order, $updated_props );
	}

	/**
	 * Read order items of a specific type from the database for this order.
	 *
	 * @param  VendorOrder $vendor_order Order object.
	 * @param  string      $type         Order item type.
	 * @return array
	 */
	public function read_items( $vendor_order, $type ) {
		global $wpdb;

		// Get from cache if available.
		$items = 0 < $vendor_order->get_id() ? wp_cache_get( 'order-items-' . $vendor_order->get_id(), 'orders' ) : false;

		if ( false === $items ) {
			$items = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT order_item_type, items.order_item_id, order_id, order_item_name
					FROM {$wpdb->prefix}woocommerce_order_items AS items
					INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS meta ON items.order_item_id = meta.order_item_id
					WHERE order_id = %d
					AND meta.meta_key = '_vendor_id'
					AND meta.meta_value = %s
					ORDER BY order_item_id;
					",
					$vendor_order->get_parent_id(),
					$vendor_order->get_vendor_id()
				)
			);
			foreach ( $items as $item ) {
				wp_cache_set( 'item-' . $item->order_item_id, $item, 'order-items' );
			}
			if ( 0 < $vendor_order->get_id() ) {
				wp_cache_set( 'order-items-' . $vendor_order->get_id(), $items, 'orders' );
			}
		}

		$items = wp_list_filter( $items, array( 'order_item_type' => $type ) );

		if ( ! empty( $items ) ) {
			$items = array_map( array( 'WC_Order_Factory', 'get_order_item' ), array_combine( wp_list_pluck( $items, 'order_item_id' ), $items ) );
		} else {
			$items = array();
		}

		return $items;
	}

	/**
	 * Read order data. Can be overridden by child classes to load other props.
	 *
	 * @param VendorOrder $vendor_order Order object.
	 * @param object      $post_object Post object.
	 * @since 3.0.0
	 */
	protected function read_order_data( &$vendor_order, $post_object ) {
		$id        = $vendor_order->get_id();
		$parent_id = $vendor_order->get_parent_id();

		$vendor_order->set_props(
			array(
				'currency'           => get_post_meta( $id, '_order_currency', true ),
				'discount_total'     => get_post_meta( $id, '_cart_discount', true ),
				'discount_tax'       => get_post_meta( $id, '_cart_discount_tax', true ),
				'shipping_total'     => get_post_meta( $id, '_order_shipping', true ),
				'shipping_tax'       => get_post_meta( $id, '_order_shipping_tax', true ),
				'cart_tax'           => get_post_meta( $id, '_order_tax', true ),
				'total'              => get_post_meta( $id, '_order_total', true ),
				'version'            => get_post_meta( $id, '_order_version', true ),
				'prices_include_tax' => metadata_exists( 'post', $id, '_prices_include_tax' ) ? 'yes' === get_post_meta( $id, '_prices_include_tax', true ) : 'yes' === get_option( 'woocommerce_prices_include_tax' ),
			)
		);

		// Gets extra data associated with the order if needed.
		foreach ( $vendor_order->get_extra_data_keys() as $key ) {
			$function = 'set_' . $key;
			if ( is_callable( array( $vendor_order, $function ) ) ) {
				$vendor_order->{$function}( get_post_meta( $vendor_order->get_id(), '_' . $key, true ) );
			}
		}

		$date_completed = get_post_meta( $parent_id, '_date_completed', true );
		$date_paid      = get_post_meta( $parent_id, '_date_paid', true );

		if ( ! $date_completed ) {
			$date_completed = get_post_meta( $parent_id, '_completed_date', true );
		}

		if ( ! $date_paid ) {
			$date_paid = get_post_meta( $parent_id, '_paid_date', true );
		}

		$vendor_order->set_props(
			array(
				'order_key'            => get_post_meta( $parent_id, '_order_key', true ),
				'customer_id'          => get_post_meta( $parent_id, '_customer_user', true ),
				'billing_first_name'   => get_post_meta( $parent_id, '_billing_first_name', true ),
				'billing_last_name'    => get_post_meta( $parent_id, '_billing_last_name', true ),
				'billing_company'      => get_post_meta( $parent_id, '_billing_company', true ),
				'billing_address_1'    => get_post_meta( $parent_id, '_billing_address_1', true ),
				'billing_address_2'    => get_post_meta( $parent_id, '_billing_address_2', true ),
				'billing_city'         => get_post_meta( $parent_id, '_billing_city', true ),
				'billing_state'        => get_post_meta( $parent_id, '_billing_state', true ),
				'billing_postcode'     => get_post_meta( $parent_id, '_billing_postcode', true ),
				'billing_country'      => get_post_meta( $parent_id, '_billing_country', true ),
				'billing_email'        => get_post_meta( $parent_id, '_billing_email', true ),
				'billing_phone'        => get_post_meta( $parent_id, '_billing_phone', true ),
				'shipping_first_name'  => get_post_meta( $parent_id, '_shipping_first_name', true ),
				'shipping_last_name'   => get_post_meta( $parent_id, '_shipping_last_name', true ),
				'shipping_company'     => get_post_meta( $parent_id, '_shipping_company', true ),
				'shipping_address_1'   => get_post_meta( $parent_id, '_shipping_address_1', true ),
				'shipping_address_2'   => get_post_meta( $parent_id, '_shipping_address_2', true ),
				'shipping_city'        => get_post_meta( $parent_id, '_shipping_city', true ),
				'shipping_state'       => get_post_meta( $parent_id, '_shipping_state', true ),
				'shipping_postcode'    => get_post_meta( $parent_id, '_shipping_postcode', true ),
				'shipping_country'     => get_post_meta( $parent_id, '_shipping_country', true ),
				'payment_method'       => get_post_meta( $parent_id, '_payment_method', true ),
				'payment_method_title' => get_post_meta( $parent_id, '_payment_method_title', true ),
				'transaction_id'       => get_post_meta( $parent_id, '_transaction_id', true ),
				'customer_ip_address'  => get_post_meta( $parent_id, '_customer_ip_address', true ),
				'customer_user_agent'  => get_post_meta( $parent_id, '_customer_user_agent', true ),
				'created_via'          => get_post_meta( $parent_id, '_created_via', true ),
				'date_completed'       => $date_completed,
				'date_paid'            => $date_paid,
				'cart_hash'            => get_post_meta( $parent_id, '_cart_hash', true ),
				'customer_note'        => $post_object->post_excerpt,
				'vendor_id'            => $post_object->post_author,
			)
		);
	}
}

