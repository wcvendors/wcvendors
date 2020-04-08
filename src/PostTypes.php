<?php
/**
 * Post Types
 *
 * Registers post types
 *
 * @package     WCVendors
 */

namespace WCVendors;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors_Post_types Class.
 */
class PostTypes {

	/**
	 * Hook in methods.
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'register_post_types' ), 5 );
		add_action( 'wcvendors_flush_rewrite_rules', array( $this, 'flush_rewrite_rules' ) );
	}


	/**
	 * Register wcvendors post types.
	 */
	public function register_post_types() {
		if ( ! is_blog_installed() || post_type_exists( 'shop_order_vendor' ) ) {
			return;
		}

		// Vendor order post type.
		wc_register_order_type(
			'shop_order_vendor',
			apply_filters(
				'wcvendors_register_post_type_shop_order_vendor',
				array(
					'labels'                           => array(
						'name'               => __( 'Vendor Orders', 'wc-vendors' ),
						'singular_name'      => __( 'Vendor Order', 'wc-vendors' ),
						'add_new'            => _x( 'Add Vendor Order', 'custom post type setting', 'wc-vendors' ),
						'add_new_item'       => _x( 'Add New Vendor Order', 'custom post type setting', 'wc-vendors' ),
						'edit'               => _x( 'Edit', 'custom post type setting', 'wc-vendors' ),
						'edit_item'          => _x( 'Edit Vendor Order', 'custom post type setting', 'wc-vendors' ),
						'new_item'           => _x( 'New Vendor Order', 'custom post type setting', 'wc-vendors' ),
						'view'               => _x( 'View Vendor Order', 'custom post type setting', 'wc-vendors' ),
						'view_item'          => _x( 'View Vendor Order', 'custom post type setting', 'wc-vendors' ),
						'search_items'       => __( 'Search Vendor Orders', 'wc-vendors' ),
						'not_found'          => __( 'No Vendor Orders Found', 'wc-vendors' ),
						'not_found_in_trash' => _x( 'No Vendor orders found in trash', 'custom post type setting', 'wc-vendors' ),
						'parent'             => _x( 'Parent Orders', 'custom post type setting', 'wc-vendors' ),
						'menu_name'          => __( 'Vendor Orders', 'wc-vendors' ),
					),
					'description'                      => __( 'This is where vendor orders are stored.', 'wc-vendors' ),
					'public'                           => false,
					'show_ui'                          => true,
					'capability_type'                  => 'shop_order',
					'capabilities' => array(
						'create_posts' => false,
					),
					'map_meta_cap'                     => true,
					'publicly_queryable'               => false,
					'exclude_from_search'              => true,
					'show_in_menu'                     => current_user_can( 'manage_woocommerce' ) ? 'wc-vendors' : true,
					'hierarchical'                     => false,
					'show_in_nav_menus'                => false,
					'rewrite'                          => false,
					'query_var'                        => false,
					'supports'                         => array( 'title', 'comments', 'custom-fields' ),
					'has_archive'                      => false,
					// wc_register_order_type() params.
					'exclude_from_orders_screen'       => false,
					'add_order_meta_boxes'             => true,
					'exclude_from_order_count'         => true,
					'exclude_from_order_views'         => true,
					'exclude_from_order_reports'       => true,
					'exclude_from_order_sales_reports' => true,
					'class_name'                       => 'WCVendors\\VendorOrder',
				)
			)
		);

	}

	/**
	 * Flush rewrite rules.
	 */
	public function flush_rewrite_rules() {
		flush_rewrite_rules();
	}
}
