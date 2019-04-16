<?php
/**
 * Post Types
 *
 * Registers post types
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Classes
 * @package     WCVendors/Classes
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors_Post_types Class.
 */
class WCVendors_Post_types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_action( 'wcvendors_flush_rewrite_rules', array( __CLASS__, 'flush_rewrite_rules' ) );
	}


	/**
	 * Register wcvendors post types.
	 */
	public static function register_post_types() {
		if ( ! is_blog_installed() || post_type_exists( 'shop_order_vendor' ) ) {
			return;
		}

		// Vendor Order
		wc_register_order_type(
			'shop_order_vendor',
			apply_filters(
				'wcvendors_register_post_type_shop_order_vendor',
				array(
					'labels'                           => array(
						'name'               => __( 'Vendor Orders', 'wcvendors' ),
						'singular_name'      => __( 'Vendor Order', 'wcvendors' ),
						'add_new'            => _x( 'Add Vendor Order', 'custom post type setting', 'wcvendors' ),
						'add_new_item'       => _x( 'Add New Vendor Order', 'custom post type setting', 'wcvendors' ),
						'edit'               => _x( 'Edit', 'custom post type setting', 'wcvendors' ),
						'edit_item'          => _x( 'Edit Vendor Order', 'custom post type setting', 'wcvendors' ),
						'new_item'           => _x( 'New Vendor Order', 'custom post type setting', 'wcvendors' ),
						'view'               => _x( 'View Vendor Order', 'custom post type setting', 'wcvendors' ),
						'view_item'          => _x( 'View Vendor Order', 'custom post type setting', 'wcvendors' ),
						'search_items'       => __( 'Search Vendor Orders', 'wcvendors' ),
						'not_found'          => __( 'No Vendor Orders Found', 'wcvendors' ),
						'not_found_in_trash' => _x( 'No Vendor orders found in trash', 'custom post type setting', 'wcvendors' ),
						'parent'             => _x( 'Parent Orders', 'custom post type setting', 'wcvendors' ),
						'menu_name'          => __( 'Vendor Orders', 'wcvendors' ),
					),
					'description'                      => __( 'This is where vendor orders are stored.', 'wcvendors' ),
					'public'                           => false,
					'show_ui'                          => true,
					'capability_type'                  => 'shop_order',
					'map_meta_cap'                     => true,
					'publicly_queryable'               => false,
					'exclude_from_search'              => true,
					'show_in_menu'                     => current_user_can( 'manage_woocommerce' ) ? 'wcvendors' : true,
					'hierarchical'                     => false,
					'show_in_nav_menus'                => false,
					'rewrite'                          => false,
					'query_var'                        => false,
					'supports'                         => array( 'title', 'comments', 'custom-fields' ),
					'has_archive'                      => false,

					// wc_register_order_type() params
					'exclude_from_orders_screen'       => false,
					'add_order_meta_boxes'             => false,
					'exclude_from_order_count'         => true,
					'exclude_from_order_views'         => true,
					'exclude_from_order_reports'       => true,
					'exclude_from_order_sales_reports' => true,
					'class_name'                       => 'WCVendors_Vendor_Order',
				)
			)
		);

	}

	/**
	 * Flush rewrite rules.
	 */
	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}
}

WCVendors_Post_types::init();
