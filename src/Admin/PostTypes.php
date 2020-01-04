<?php
/**
 * WC Vendors Post Types Admin
 *
 * @package WCVendors/Admin
 */

namespace WCVendors\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * PostTypes Class.
	 *
	 * Hook into the WooCommerce custom post types and add vendor and marketplace extensions
	 */
class PostTypes {

	/**
	 * WP hooks.
	 */
	public function init_hooks() {

		// Show pending item counts.
		add_filter( 'add_menu_classes', array( $this, 'pending_items_count' ) );

		// Allow products to have author which is used to store the vendor owner.
		add_post_type_support( 'product', 'author' );

		// Quick and Bulk Edit.
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit' ), 10, 2 );
		add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit' ), 10, 2 );

		// Quick and Bulk Edit Save.
		add_action( 'save_post', array( $this, 'bulk_and_quick_edit_hook' ), 10, 2 );
		add_action( 'wcvendors_product_bulk_and_quick_edit', array( $this, 'bulk_and_quick_edit_save_post' ), 10, 2 );

		// Product Columns.
		add_filter( 'manage_product_posts_columns', array( $this, 'product_columns' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_product_column' ), 10, 2 );

	}

	/**
	 * Show the pending product count in the menu for admins and vendors.
	 *
	 * @param array $menu Current menus.
	 */
	public function pending_items_count( $menu ) {
		// Pending vendors.
		$users                 = count_users();
		$pending_vendors_count = ! empty( $users['avail_roles']['vendor_pending'] ) ? $users['avail_roles']['vendor_pending'] : '';

		// Draft products from vendors pending review.
		$products               = wp_count_posts( 'product', 'readable' );
		$pending_products_count = ! empty( $products->pending ) ? $products->pending : '';

		foreach ( $menu as $menu_key => $menu_data ) {
			if ( 'users.php' === $menu_data[2] && ! empty( $pending_vendors_count ) ) {
				$menu[ $menu_key ][0] .= ' <span class="update-plugins count-' . $pending_vendors_count . '" title="' . esc_attr__( 'Products awaiting review', 'wc-vendors' ) . '"><span class="plugin-count">' . number_format_i18n( $pending_vendors_count ) . '</span></span>';
			}
			if ( 'edit.php?post_type=product' === $menu_data[2] && ! empty( $products->pending ) ) {
				$menu[ $menu_key ][0] .= ' <span class="update-plugins count-' . $pending_products_count . '" title="' . esc_attr__( 'Products awaiting review', 'wc-vendors' ) . '"><span class="plugin-count">' . number_format_i18n( $pending_products_count ) . '</span></span>';
			}
		}
		return $menu;
	}

	/**
	 *  Change the author column to vendor and re-arrage the column.
	 *
	 * @param  array $columns Existing columns.
	 * @return array
	 */
	public function product_columns( $columns ) {

		$new_order = array();
		// remove the author column.
		unset( $columns['author'] );
		// re order the array move vendor to before date.
		foreach ( $columns as $key => $value ) {

			if ( 'date' === $key ) {
				$new_order['author'] = wcv_get_vendor_name();
			}

			$new_order[ $key ] = $value;
		}

		return $new_order;
	}

	/**
	 * Add a hidden column that will store the vendor id for the post for quick edit
	 *
	 * @since 2.0.0
	 * @version 3.0.0
	 * @param string $column - the column that is currently being rendered.
	 * @param int    $post_id - the post id for the row.
	 */
	public function render_product_column( $column, $post_id ) {

		$vendor_id = get_post_field( 'post_author', $post_id );
		$product   = wc_get_product( $post_id );

		switch ( $column ) {
			case 'name':
				include 'views/html-admin-product-column.php';
				break;
			case $column:
				apply_filters( 'wcvendors_rendor_vendor_columns_' . $column, $column, $post_id );
				break;
			default:
				apply_filters( 'wcvendors_rendor_vendor_columns', $column, $post_id );
				break;
		}

	}

	/**
	 * Display the vendor drop down on the quick edit screen
	 *
	 * @param string $column_name Column name.
	 * @param string $post_type Post type.
	 */
	public function quick_edit( $column_name, $post_type ) {
		if ( 'price' !== $column_name || 'product' !== $post_type ) {
			return;
		}

		include WCV_ABSPATH_ADMIN . 'views/html-admin-quick-edit-product.php';
	}

	/**
	 * Display the vendor drop down on the quick edit screen
	 *
	 * @param string $column_name Column name.
	 * @param string $post_type Post type.
	 */
	public function bulk_edit( $column_name, $post_type ) {
		if ( 'price' !== $column_name || 'product' !== $post_type ) {
			return;
		}
		include WCV_ABSPATH_ADMIN . 'views/html-admin-bulk-edit-product.php';
	}

	/**
	 * Offers a way to hook into save post without causing an infinite loop
	 * when quick/bulk saving product info.
	 *
	 * @since 2.0.0
	 * @param int     $post_id Product ID.
	 * @param WP_Post $post Product as post object.
	 */
	public function bulk_and_quick_edit_hook( $post_id, $post ) {
		remove_action( 'save_post', array( $this, 'bulk_and_quick_edit_hook' ) );
		do_action( 'wcvendors_product_bulk_and_quick_edit', $post_id, $post );
		add_action( 'save_post', array( $this, 'bulk_and_quick_edit_hook' ), 10, 2 );
	}

	/**
	 * Quick and bulk edit saving.
	 *
	 * @param int     $post_id Product ID.
	 * @param WP_Post $post Product as post object.
	 * @return int
	 */
	public function bulk_and_quick_edit_save_post( $post_id, $post ) {

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Don't save revisions and autosaves.
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}

		// Check post type is product.
		if ( 'product' !== $post->post_type ) {
			return $post_id;
		}

		// Check user permission.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Check nonces.
		if ( ! isset( $_REQUEST['wcvendors_quick_edit_nonce'] ) && ! isset( $_REQUEST['wcvendors_bulk_edit_nonce'] ) ) {
			return $post_id;
		}
		if ( isset( $_REQUEST['wcvendors_quick_edit_nonce'] ) && ! wp_verify_nonce( wp_unslash( sanitize_key( $_REQUEST['wcvendors_quick_edit_nonce'] ) ), 'wcvendors_quick_edit_nonce' ) ) {
			return $post_id;
		}
		if ( isset( $_REQUEST['wcvendors_bulk_edit_nonce'] ) && ! wp_verify_nonce( wp_unslash( sanitize_key( $_REQUEST['wcvendors_bulk_edit_nonce'] ) ), 'wcvendors_bulk_edit_nonce' ) ) {
			return $post_id;
		}

		// Get the product and save.
		$product = wc_get_product( $post );

		if ( ! empty( $_REQUEST['wcvendors_quick_edit'] ) ) {
			$this->quick_edit_save( $post_id, $product );
		} else {
			$this->bulk_edit_save( $post_id, $product );
		}

		return $post_id;
	}

	/**
	 * Quick edit.
	 *
	 * @param integer    $post_id Product ID.
	 * @param WC_Product $product Product object.
	 */
	private function quick_edit_save( $post_id, $product ) {

		// If commission is enabled then process.
		if ( apply_filters( 'wcvendors_quick_edit_commission', true ) ) {

			if ( isset( $_REQUEST['_wcv_commission_rate'] ) ) {
				$product->update_meta_data( '_wcv_commission_rate', wc_clean( $_REQUEST['_wcv_commission_rate'] ) );
			} else {
				$product->delete_meta_data( '_wcv_commission_rate' );
			}

			$product->save();

		}

		do_action( 'wcvendors_quick_edit_save', $post_id, $product );
	}

	/**
	 * Bulk edit.
	 *
	 * @param integer    $post_id Product ID.
	 * @param WC_Product $product Product object.
	 */
	private function bulk_edit_save( $post_id, $product ) {

		// Update the vendor.
		if ( isset( $_REQUEST['post_author-vendor'] ) ) {
			$update_args = array(
				'ID'          => $post_id,
				'post_author' => wc_clean( $_REQUEST['post_author-vendor'] ),
			);
			wp_update_post( $update_args );
		}

		// If the commission is enabled then process
		if ( apply_filters( 'wcvendors_quick_edit_commission', true ) ) {

			if ( isset( $_REQUEST['_wcv_commission_rate'] ) ) {
				$product->update_meta_data( '_wcv_commission_rate', wc_clean( $_REQUEST['_wcv_commission_rate'] ) );
			} else {
				$product->delete_meta_data( '_wcv_commission_rate' );
			}
			$product->save();
		}

		do_action( 'wcvendors_bulk_edit_save', $post_id, $product );

	}

}
