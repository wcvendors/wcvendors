<?php
/**
 * Manage the Vendor Order detail.
 *
 * @package WCVendors/Admin
 */

namespace WCVendors\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage the Vendor Order detail.
 */
class VendorOrdersMetaBoxes {

	/**
	 * Initialize WP hooks.
	 */
	public function init_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 30 );
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 10 );
	}

	/**
	 * Add WC Meta boxes.
	 */
	public function add_meta_boxes() {
		$type                  = 'shop_order_vendor';
			$order_type_object = get_post_type_object( $type );
			/* Translators: %s order type name. */
			add_meta_box( 'woocommerce-order-data', sprintf( __( '%s data', 'wc-vendors' ), $order_type_object->labels->singular_name ), 'WC_Meta_Box_Order_Data::output', $type, 'normal', 'high' );
			add_meta_box( 'woocommerce-order-items', __( 'Items', 'wc-vendors' ), 'WC_Meta_Box_Order_Items::output', $type, 'normal', 'high' );
	}

	/**
	 * Remove bloat.
	 */
	public function remove_meta_boxes() {
		remove_meta_box( 'postexcerpt', 'product', 'normal' );
		remove_meta_box( 'product_shipping_classdiv', 'product', 'side' );
		remove_meta_box( 'commentsdiv', 'product', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'product', 'side' );
		remove_meta_box( 'commentstatusdiv', 'product', 'normal' );
		remove_meta_box( 'woothemes-settings', 'shop_coupon', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'shop_coupon', 'normal' );
		remove_meta_box( 'slugdiv', 'shop_coupon', 'normal' );
		remove_meta_box( 'commentsdiv', 'shop_order_vendor', 'normal' );
		remove_meta_box( 'woothemes-settings', 'shop_order_vendor', 'normal' );
		remove_meta_box( 'commentstatusdiv', 'shop_order_vendor', 'normal' );
		remove_meta_box( 'slugdiv', 'shop_order_vendor', 'normal' );
		remove_meta_box( 'submitdiv', 'shop_order_vendor', 'side' );
	}
}
