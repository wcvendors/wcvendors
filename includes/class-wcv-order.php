<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The order class process orders from WooCommerce
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Classes
 * @package     WCVendors/Classes
 * @version     2.0.0
 */
class WCVendors_Order {

	public function __construct() {

		// Orders required to be created when the order is processed otherwise
		// the correct data will not be available for the child order creation
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'create_vendor_orders' ), 10, 2 );

		// Move these the commissions class
		// add_action( 'woocommerce_order_status_processing', 						array( $this, 'process' ), 10, 2 );
		// add_action( 'woocommerce_order_status_completed', 						array( $this, 'process' ), 10, 2 );
		add_action( 'wcvendors_commission_added', array( $this, 'add_commission_order_note' ) );
		add_filter( 'woocommerce_order_actions', array( $this, 'add_order_actions' ) );

		// Order view screen
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_item_meta' ) );

		// Order actions
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_vendor_id_line_item_meta' ), 10, 4 );
		add_action( 'woocommerce_order_action_wcvendors_manual_create_commission', array( $this, 'process_manual_create_commission_action' ) );
		add_action( 'woocommerce_order_action_wcvendors_manual_create_vendor_orders', array( $this, 'process_manual_create_vendor_order_action' ) );
		// add_action( 'woocommerce_checkout_create_order_tax_item',                array( $this, 'create_tax_line_item'), 10, 3 );
		// Synch Changes
		add_action( 'woocommerce_order_edit_status', array( $this, 'mark_order_status' ), 10, 2 );
		add_action( 'delete_post', array( $this, 'delete_post' ) );
		add_action( 'wp_trash_post', array( $this, 'trash_post' ) );
		add_action( 'untrashed_post', array( $this, 'untrash_post' ) );
		add_action( 'before_delete_post', array( $this, 'delete_order_items' ) );

		// Add order item
		// Delete order Item
		// Refund order Item
	}

	/**
	 * Commission is processed when an order is_paid() state
	 */
	public function process( $order_id ) {

		$order = new WC_Order( $order_id );

		$commission_calculated = false;
		$commission_logged     = $order->get_meta( '_wcvendors_commission_logged', true );
		$vendor_orders         = wcvendors_get_vendor_orders_from_order( $order_id );

		if ( $vendor_orders ) {

			// loop through vendor orders
			// Create commissision object
			foreach ( $vendor_orders as $vendor_order_data ) {

				$vendor_order = new WCVendors_Vendor_Order( $vendor_order_data->ID );
				$commission   = new WCVendors_Commission();

				$commission->set_order_id( $vendor_order->get_parent_id() );
				$commission->set_order_date( $vendor_order->get_date_created() );
				$commission->set_order_item_ids( $vendor_order->get_order_item_ids() );
				$commission->set_vendor_order_id( $vendor_order->get_id() );
				$commission->set_vendor_id( $vendor_order->get_vendor_id() );
				$commission->set_vendor_name( wcvendors_get_vendor_display_name( $vendor_order->get_vendor_id() ) );

				$commission->calculate_totals();

				$commission->save();

			}
		}
	}

	/**
	 * Create a vendor specific order for the parent order_id
	 */
	public function create_vendor_orders( $order_id, $data ) {

		WCVendors()->log( $data );
		WCVendors()->log( $order_id );

		$order = new WC_Order( $order_id );

		$vendor_line_items = array();

		foreach ( $order->get_items() as $order_item_product ) {
			$vendor_id = wc_get_order_item_meta( $order_item_product->get_id(), '_vendor_id', true );
			if ( wcv_is_vendor( $vendor_id ) ) {
				$vendor_line_items[ $vendor_id ][ $order_item_product->get_id() ] = $order_item_product;
			}
		}

		foreach ( $vendor_line_items as $vendor_id => $items ) {
			if ( ! empty( $items ) ) {
				$vendor_order = $this->create_vendor_order(
					array(
						'parent_order' => $order,
						'vendor_id'    => $vendor_id,
						'items'        => $items,
						'data'         => $data,
					)
				);
			}
		}

	}

	/**
	 * Add order note to state commission added for order
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param object $order
	 * @return bool
	 */
	public function add_commission_order_note( $order ) {
		$note = __( 'Commission data generated.', 'wcvendors' );
		$order->add_order_note( $note );
		return true;
	}

	/**
	 * Adds order actions related to WC Vendors
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 * @param array $actions
	 * @return array $actions
	 */
	public function add_order_actions( $actions ) {

		if ( ! isset( $_REQUEST['post'] ) ) {
			return $actions;
		}

		$actions['wcvendors_manual_create_commission']    = __( 'Re-Generate Vendor Commissions', 'wcvendors' );
		$actions['wcvendors_manual_create_vendor_orders'] = __( 'Re-Generate Vendor Orders', 'wcvendors' );
		return $actions;
	}

	/**
	 * Create a new vendor order programmatically
	 *
	 * Returns a new WCVendors_Vendor_Order object on success which can then be used to add additional data.
	 *
	 * @since
	 * @param array $args
	 * @return WC_Order_Vendor|WP_Error
	 */
	public function create_vendor_order( $args = array() ) {

		$defaults = array(
			'parent_order' => 0,
			'vendor_id'    => 0,
			'vendor_order' => 0,
			'items'        => array(),
			'data'         => array(),
		);

		$order_args = wp_parse_args( $args, $defaults );

		extract( $order_args );

		$vendor_order = new WCVendors_Vendor_Order();
		$vendor_order->set_parent_order( $parent_order );
		$vendor_order->set_vendor_id( $vendor_id );
		$vendor_order->add_items( $items );
		$vendor_order->set_data( $data );
		$vendor_order->calculate_totals();
		$vendor_order->save();

		do_action( 'wcvendors_vendor_order_created', $vendor_order->get_id(), $vendor_order, $order_args );

		return $vendor_order;

	}

	/**
	 * Add the vendor id to the order. This will remove the need to query the underlying product later on
	 *
	 * @version 2.0.0
	 * @since 2.0.0
	 * @param WC_Order_Item_Product $item
	 * @param int                   $cart_item_key
	 * @param array                 $values - values from the order item product
	 * @param WC_Order              $order the order
	 *
	 * @todo make the item meta hidden for some reason it is not hidden...
	 */
	public function add_vendor_id_line_item_meta( $item, $cart_item_key, $values, $order ) {
		$product   = $item->get_product();
		$vendor_id = get_post_field( 'post_author', $product->get_id() );
		$item->add_meta_data( '_vendor_id', $vendor_id, true );
	}


	 /*
	 |--------------------------------------------------------------------------
	 | Orders View Screen
	 |--------------------------------------------------------------------------
	 |
	 | Functions on the order view screen
	 |
	 */

	 /**
	  * Process the manual create commission action
	  *
	  * @access public
	  * @since 2.0.0
	  * @version 2.0.0
	  * @param object $order
	  * @return bool
	  */
	public function process_manual_create_commission_action( $order ) {

		$order_id = $order->get_id();
		$this->process( $order_id );

		return true;
	}

	public function hide_item_meta( $hidden_order_itemmeta ) {

		$wcvendors_order_item_meta = array(
			'_vendor_id',
		);

		$hidden_order_itemmeta = array_merge( $hidden_order_itemmeta, $wcvendors_order_item_meta );

		return $hidden_order_itemmeta;

	}

	/**
	 *
	 *
	 */
	public function process_manual_create_vendor_order_action( $order ) {

		WCVendors()->log( __function__ );

	}

	/*
	 |--------------------------------------------------------------------------
	 | Sync changes
	 |--------------------------------------------------------------------------
	 |
	 | Functions for syncing changes from the parent order to the relevant child orders
	 |
	 */

	/**
	 *  Sync the parent order status with the vendor child orders
	 */
	public function mark_order_status( $order_id, $status ) {

		$args = array(
			'post_parent' => $order_id,
			'post_type'   => 'shop_order_vendor',
			'numberposts' => -1,
			'post_status' => 'any',
		);

		$vendor_orders = get_children( $args );

		foreach ( $vendor_orders as $vendor_order ) {
			$order = wc_get_order( $vendor_order->ID );

			if ( $order ) {
				$order->update_status( $status, '', true );
			}
		}
	}


	/**
	 * Removes vendor orders belonging to parent shop order
	 *
	 * @version 2.0.0
	 * @since 2.0.0
	 *
	 * @param mixed $id ID of post being deleted
	 */
	public function delete_post( $id ) {
		if ( ! current_user_can( 'delete_posts' ) || ! $id ) {
			return;
		}

		$post_type = get_post_type( $id );

		switch ( $post_type ) {
			case 'shop_order':
				global $wpdb;

				$vendor_orders = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'shop_order_vendor' AND post_parent = %d", $id ) );

				if ( ! is_null( $vendor_orders ) ) {
					foreach ( $vendor_orders as $vendor_order ) {
						wp_delete_post( $vendor_order->ID, true );
					}
				}
				break;
		}
	}

	/**
	 * woocommerce_trash_post function.
	 *
	 * @param mixed $id
	 */
	public function trash_post( $id ) {
		if ( ! $id ) {
			return;
		}

		$post_type = get_post_type( $id );

		// If this is an order, trash any refunds too.
		if ( in_array( $post_type, wc_get_order_types( 'order-count' ) ) ) {
			global $wpdb;

			$vendor_orders = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'shop_order_vendor' AND post_parent = %d", $id ) );

			foreach ( $vendor_orders as $vendor_order ) {
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'trash' ), array( 'ID' => $vendor_order->ID ) );
			}
		}
	}

	/**
	 * woocommerce_untrash_post function.
	 *
	 * @param mixed $id
	 */
	public function untrash_post( $id ) {
		if ( ! $id ) {
			return;
		}

		$post_type = get_post_type( $id );

		if ( in_array( $post_type, wc_get_order_types( 'order-count' ) ) ) {
			global $wpdb;

			$vendor_orders = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'shop_order_vendor' AND post_parent = %d", $id ) );

			foreach ( $vendor_orders as $vendor_order ) {
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'wc-completed' ), array( 'ID' => $vendor_order->ID ) );
			}
		}
	}

	/**
	 * Remove item meta on permanent deletion.
	 */
	public function delete_order_items( $id ) {
		global $wpdb;

		$post_type = get_post_type( $id );

		if ( $post_type === 'shop_order_vendor' ) {
			do_action( 'wcvendors_delete_order_items', $id );

			$wpdb->query(
				"
				DELETE {$wpdb->prefix}woocommerce_order_items, {$wpdb->prefix}woocommerce_order_itemmeta
				FROM {$wpdb->prefix}woocommerce_order_items
				JOIN {$wpdb->prefix}woocommerce_order_itemmeta ON {$wpdb->prefix}woocommerce_order_items.order_item_id = {$wpdb->prefix}woocommerce_order_itemmeta.order_item_id
				WHERE {$wpdb->prefix}woocommerce_order_items.order_id = '{$id}';
				"
			);

			do_action( 'wcvendors_deleted_order_items', $id );
		}
	}

}
new WCVendors_Order();
