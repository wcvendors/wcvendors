<?php
/**
 * Vendor Order Data Store.
 *
 * @package WCVendors/DataStores
 */

namespace WCVendors\DataStores;

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
	 * @param \WCVendors\VendorOrder $vendor_order Vendor order object.
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
	 * @param \WCVendors\VendorOrder $vendor_order Vendor order object.
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
}

