<?php
/**
 * Post Types
 *
 * Registers post types and taxonomies
 *
 * @class       WCV_Post_types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCV_Post_types Class
 */
class WCV_Post_types {

	/**
	 * Hook in methods.
	 */
	public static function init() {

		add_action( 'woocommerce_register_post_type', array( __CLASS__, 'register_shop_order_vendor' ) );
	}

	/**
	 * Register vendor order type
	 */
	public static function register_shop_order_vendor() {

		wc_register_order_type(
			'shop_order_vendor',
			apply_filters(
				'woocommerce_register_post_type_shop_order_vendor',
				array(
					'label'                            => sprintf( __( '%s Orders', 'wc-vendors' ), wcv_get_vendor_name() ),
					'capability_type'                  => 'shop_order',
					'public'                           => false,
					'hierarchical'                     => false,
					'supports'                         => false,
					'exclude_from_orders_screen'       => false,
					'add_order_meta_boxes'             => false,
					'exclude_from_order_count'         => true,
					'exclude_from_order_views'         => false,
					'exclude_from_order_reports'       => true,
					'exclude_from_order_sales_reports' => true,
					'class_name'                       => 'WC_Order_Vendor',
				)
			)
		);
	}
}

WCV_Post_types::init();
