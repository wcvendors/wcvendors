<?php
/**
 * WC Vendors Template functions
 *
 * Functions for templates
 *
 * @package WCVendors/Functions
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! function_exists( 'wcv_get_vendor_order_items' ) ) {
	/**
	 * Get HTML for the order items to be shown in emails.
	 *
	 * @param WC_Order $order Order object.
	 * @param array    $args  Arguments.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	function wcv_get_vendor_order_items( $order, $args = array() ) {

		ob_start();

		$defaults = array(
			'show_sku'       => false,
			'show_image'     => false,
			'image_size'     => array( 32, 32 ),
			'plain_text'     => false,
			'sent_to_admin'  => false,
			'sent_to_vendor' => false,
			'totals_display' => 'both',
			'vendor_items'   => array(),
			'vendor_id'      => 0,
		);

		$args     = wp_parse_args( $args, $defaults );
		$template = $args['plain_text'] ? 'emails/plain/vendor-order-items.php' : 'emails/vendor-order-items.php';

		wc_get_template(
			$template, apply_filters(
			'wcvendors_vendor_order_items_args', array(
				'order'          => $order,
				'items'          => $args['vendor_items'],
				'show_sku'       => $args['show_sku'],
				'show_image'     => $args['show_image'],
				'image_size'     => $args['image_size'],
				'plain_text'     => $args['plain_text'],
				'sent_to_admin'  => $args['sent_to_admin'],
				'sent_to_vendor' => $args['sent_to_vendor'],
				'totals_display' => $args['totals_display'],
				'vendor_id'      => $args['vendor_id'],
			)
		), 'woocommerce', WCV_TEMPLATE_BASE
		);

		return apply_filters( 'wcvendors_vendor_order_items_table', ob_get_clean(), $order );
	}
}


if ( ! function_exists( 'wcv_get_vendor_item_totals' ) ) {
	/**
	 * Get the Vendor order item total rows
	 *
	 * @param WC_Order $order Order object.
	 * @param array    $args  Arguments.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	function wcv_get_vendor_item_totals( $order, $items, $vendor_id, $email, $totals_display = 'both' ) {

		$product_subtotal    = 0;
		$commission_subtotal = 0;
		$commission_total    = 0;
		$tax                 = 0;
		$shipping            = 0;
		$total               = 0;
		$total_rows          = array();

		$vendor_commissions = WCV_Vendors::get_vendor_dues_from_order( $order );

		// Get vendor commission information
		foreach ( $vendor_commissions as $commission ) {

			if ( $vendor_id == $commission['vendor_id'] ) {

				$commission_subtotal += $commission['commission'];
				$shipping            = $commission['shipping'];
				$tax                 = $commission['tax'];
				$commission_total    += $commission['total'];
			}
		}

		// Commission subtotal
		if ( 'commission' === $totals_display || 'both' === $totals_display ) {
			// Commission Subtotal
			$total_rows['commission_subtotal'] = array(
				'label' => __( 'Commission subtotal:', 'wc-vendors' ),
				'value' => wc_price( $commission_subtotal, array( 'currency' => $order->get_currency() ) ),
			);
		}

		// Product subtotals
		if ( 'product' === $totals_display || 'both' === $totals_display ) {

			foreach ( $items as $item ) {
				$product_subtotal += $item->get_subtotal();
			}

			$total_rows['product_subtotal'] = array(
				'label' => __( 'Product subtotal:', 'wc-vendors' ),
				'value' => wc_price( $product_subtotal, array( 'currency' => $order->get_currency() ) ),
			);
		}

		// Shipping
		if ( wc_string_to_bool( get_option( 'wcvendors_vendor_give_shipping', 'no' ) ) ) {
			$total_rows['shipping'] = array(
				'label' => __( 'Shipping:', 'wc-vendors' ),
				'value' => wc_price( $shipping, array( 'currency' => $order->get_currency() ) ),
			);
		}

		// Tax
		if ( wc_string_to_bool( get_option( 'wcvendors_vendor_give_taxes', 'no' ) ) ) {
			$total_rows['tax'] = array(
				'label' => __( 'Tax:', 'wc-vendors' ),
				'value' => wc_price( $tax, array( 'currency' => $order->get_currency() ) ),
			);
		}

		// Payment Method
		if ( 'yes' == $email->get_option( 'payment_method' ) ) {
			$total_rows['payment_method'] = array(
				'label' => __( 'Payment method:', 'wc-vendors' ),
				'value' => $order->get_payment_method_title(),
			);
		}

		// Commission total
		if ( 'both' === $totals_display || 'commission' === $totals_display ) {
			// Commission Subtotal
			$total_rows['commission_total'] = array(
				'label' => __( 'Commission total:', 'wc-vendors' ),
				'value' => wc_price( $commission_total, array( 'currency' => $order->get_currency() ) ),
			);
		}

		// Product totals
		if ( 'both' === $totals_display || 'product' === $totals_display ) {
			$product_total = $product_subtotal + $shipping + $tax;

			$total_rows['product_total'] = array(
				'label' => __( 'Product total:', 'wc-vendors' ),
				'value' => wc_price( $product_total, array( 'currency' => $order->get_currency() ) ),
			);
		}

		if ( 'none' === $totals_display ) {
			$total_rows = array();
		}

		return apply_filters( 'wcvendors_get_vendor_item_totals', $total_rows, $order, $items, $vendor_id, $totals_display );

	} // wcv_get_vendor_item_totals
}

if ( ! function_exists( 'is_wcv_pro_active' ) ) {
	/**
	 * Check if WC Vendors Pro is active
	 *
	 * @since 2.1.4
	 * @return bool True if active false otherwis
	 */
	function is_wcv_pro_active() {
		if ( defined( 'WCV_PRO_PLUGIN_FILE' ) ) {
			return true;
		}

		return false;
	}
}


if ( ! function_exists( 'wcv_get_sold_by_link' )  ){
	/**
	 * Get the vendor sold by URL
	 *
	 * @param int $vendor_id - vendor's id
	 * @param string $css_class - optional css class
	 */
	function wcv_get_sold_by_link( $vendor_id, $css_class = '' ){
		$class 		= isset( $css_class ) ? 'class="' . $css_class . '"' : '';
		$sold_by 	= WCV_Vendors::is_vendor( $vendor_id )
			? sprintf( '<a href="%s" %s>%s</a>', WCV_Vendors::get_vendor_shop_page( $vendor_id ), $class, WCV_Vendors::get_vendor_sold_by( $vendor_id ) )
			: get_bloginfo( 'name' );

		return apply_filters( 'wcv_sold_by_link', $sold_by, $vendor_id );

	}
}


if ( ! function_exists( 'wcv_get_vendor_sold_by') ){
	function wcv_get_vendor_sold_by( $vendor_id ){

		$sold_by_label     = __( get_option( 'wcvendors_label_sold_by' ), 'wc-vendors' );
		$sold_by_separator = __( get_option( 'wcvendors_label_sold_by_separator' ), 'wc-vendors' );
		$sold_by           = wcv_get_sold_by_link( $vendor_id, 'wcvendors_cart_sold_by_meta' );

		$vendor_sold_by = sprintf(
			apply_filters( 'wcvendors_cart_sold_by_meta_template', '%1$s %2$s %3$s', get_the_ID(), $vendor_id ),
			apply_filters( 'wcvendors_cart_sold_by_meta', $sold_by_label, get_the_ID(), $vendor_id ),
			apply_filters( 'wcvendors_cart_sold_by_meta_separator', $sold_by_separator, get_the_ID(), $vendor_id ),
			$sold_by
		);

		return $vendor_sold_by;
	}
}
