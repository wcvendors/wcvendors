<?php
/**
 * The order class process orders from WooCommerce
 *
 * @package WCVendors
 */

namespace WCVendors;

use Exception;
use WC_Order;
use WC_Order_Item_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The order class process orders from WooCommerce
 */
class OrderHandler {

	/**
	 * Log instance.
	 *
	 * @var Logger $logger Log instance.
	 */
	private $logger;

	/**
	 * OrderHandler constructor.
	 *
	 * @param Logger $logger Log instance.
	 */
	public function __construct( $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Init WP hooks.
	 */
	public function init_hooks() {

		// Orders required to be created when the order is processed otherwise
		// the correct data will not be available for the child order creation.
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'create_vendor_orders' ) );

		// Move these the commissions class.
		 add_action( 'woocommerce_order_status_processing', array( $this, 'process' ), 10, 2 );
		 add_action( 'woocommerce_order_status_completed', array( $this, 'process' ), 10, 2 );
		// add_action( 'wcvendors_commission_added', array( $this, 'add_commission_order_note' ) );
		// add_filter( 'woocommerce_order_actions', array( $this, 'add_order_actions' ) );

		// Order view screen.
		// add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_item_meta' ) );

		// Order actions.
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_vendor_id_line_item_meta' ) );
		// add_action(
			// 'woocommerce_order_action_wcvendors_manual_create_commission',
			// array(
				// $this,
				// 'process_manual_create_commission_action',
			// )
		// );
		// add_action(
			// 'woocommerce_order_action_wcvendors_manual_create_vendor_orders',
			// array(
				// $this,
				// 'process_manual_create_vendor_order_action',
			// )
		// );
		// add_action( 'woocommerce_checkout_create_order_tax_item', array( $this, 'create_tax_line_item'), 10, 3 );

		// Sync Changes.
		add_action( 'woocommerce_order_edit_status', array( $this, 'mark_order_status' ), 10, 2 );
		// add_action( 'delete_post', array( $this, 'delete_post' ) );
		// add_action( 'wp_trash_post', array( $this, 'trash_post' ) );
		// add_action( 'untrashed_post', array( $this, 'untrash_post' ) );

		// Add order item.
		// Delete order Item.
		// Refund order Item.
	}

	/**
	 * Create a vendor specific order for the parent order_id.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @throws Exception Order creation exception.
	 */
	public function create_vendor_orders( $order_id ) {

		$this->logger->log( __( 'Creating vendor order #', 'wc-vendors' ) . $order_id );

		$order = wc_get_order( $order_id );

		$vendors = array();

		foreach ( $order->get_items() as $item_product ) {
			$vendor_id = $item_product->get_meta( '_vendor_id' );
			if ( wcv_is_vendor( $vendor_id ) ) {
				$vendors[] = $vendor_id;
			}
		}

		$vendors = array_unique( $vendors );

		foreach ( $vendors as $vendor_id ) {
			$vendor_order = new VendorOrder();
			$vendor_order->set_parent_id( $order->get_id() );
			$vendor_order->set_parent_order( $order );
			$vendor_order->set_vendor_id( $vendor_id );
			$vendor_order->calculate_totals();
			$vendor_order->save();
		}

	}

	/**
	 * Add the vendor id to the order. This will remove the need to query the underlying product later on.
	 *
	 * @param WC_Order_Item_Product $item Order item.
	 *
	 * @todo make the item meta hidden for some reason it is not hidden...
	 */
	public function add_vendor_id_line_item_meta( $item ) {
		$product   = $item->get_product();
		$vendor_id = get_post_field( 'post_author', $product->get_id() );
		$item->add_meta_data( '_vendor_id', $vendor_id, true );
	}

	/**
	 * Add order note to state commission added for order
	 *
	 * @param object $order Order object.
	 *
	 * @return bool
	 */
	public function add_commission_order_note( $order ) {
		$note = __( 'Commission data generated.', 'wc-vendors' );
		$order->add_order_note( $note );

		return true;
	}

	/**
	 * Adds order actions related to WC Vendors
	 *
	 * @param array $actions Order actions.
	 *
	 * @return array $actions
	 */
	public function add_order_actions( $actions ) {

		if ( ! isset( $_REQUEST['post'] ) ) {
			return $actions;
		}

		$actions['wcvendors_manual_create_commission']    = __( 'Re-Generate Vendor Commissions', 'wc-vendors' );
		$actions['wcvendors_manual_create_vendor_orders'] = __( 'Re-Generate Vendor Orders', 'wc-vendors' );

		return $actions;
	}

	/**
	 * Process the manual create commission action
	 *
	 * @param object $order Order object.
	 *
	 * @return bool
	 */
	public function process_manual_create_commission_action( $order ) {

		$order_id = $order->get_id();
		$this->process( $order_id );

		return true;
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
	 * Commission is processed when an order is_paid() state.
	 *
	 * @param int $order_id Order ID.
	 */
	public function process( $order_id ) {

		$order         = wc_get_order( $order_id );
		$vendor_orders = wc_get_orders(
			array(
				'parent' => $order_id,
				'type'   => 'shop_order_vendor',
			)
		);

		foreach ( $vendor_orders as $vendor_order ) {
			$vendor_order->update_status( $order->get_status() );
		}

		// $commission_calculated = false;
		// $commission_logged     = $order->get_meta( '_wcvendors_commission_logged', true );
		// $vendor_orders         = wcvendors_get_vendor_orders_from_order( $order_id ); // @todo missing function

		// if ( $vendor_orders ) {

			// loop through vendor orders.
			// Create commission object.
			// foreach ( $vendor_orders as $vendor_order_data ) {

				// $vendor_order = new VendorOrder( $vendor_order_data->ID );
				// $commission   = new Commission();

				// $commission->set_order_id( $vendor_order->get_parent_id() );
				// $commission->set_order_date( $vendor_order->get_date_created() );
				// $commission->set_order_item_ids( $vendor_order->get_order_item_ids() );
				// $commission->set_vendor_order_id( $vendor_order->get_id() );
				// $commission->set_vendor_id( $vendor_order->get_vendor_id() );
				// $commission->set_vendor_name( wcvendors_get_vendor_display_name( $vendor_order->get_vendor_id() ) ); // @todo missing function

				// $commission->calculate_totals();

				// $commission->save();
			// }
		// }
	}

	/**
	 * Get hidden item meta.
	 *
	 * @param array $hidden_order_itemmeta Hidden order item meta.
	 *
	 * @return array
	 */
	public function hide_item_meta( $hidden_order_itemmeta ) {

		$wcvendors_order_item_meta = array(
			'_vendor_id',
		);

		$hidden_order_itemmeta = array_merge( $hidden_order_itemmeta, $wcvendors_order_item_meta );

		return $hidden_order_itemmeta;

	}

	/**
	 * Create vendor order when parent order is manually created.
	 *
	 * @param WC_Order $order Order object.
	 */
	public function process_manual_create_vendor_order_action( $order ) {

		$this->logger->log( __function__ );

	}

	/**
	|--------------------------------------------------------------------------
	| Sync changes
	|--------------------------------------------------------------------------
	|
	| Functions for syncing changes from the parent order to the relevant child orders
	|
	 **/

	/**
	 *  Sync the parent order status with the vendor child orders
	 *
	 * @param int    $order_id Order ID.
	 * @param string $status Order status.
	 */
	public function mark_order_status( $order_id, $status ) {

		$args = array(
			'post_parent' => $order_id,
			'post_type'   => 'shop_order_vendor',
			'numberposts' => - 1,
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
	 * Removes vendor orders belonging to parent shop order.
	 *
	 * @param mixed $id ID of post being deleted.
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
	 * Function woocommerce_trash_post.
	 *
	 * @param int $id Order ID.
	 */
	public function trash_post( $id ) {
		if ( ! $id ) {
			return;
		}

		$post_type = get_post_type( $id );

		// If this is an order, trash any refunds too.
		if ( in_array( $post_type, wc_get_order_types( 'order-count' ), true ) ) {
			global $wpdb;

			$vendor_orders = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'shop_order_vendor' AND post_parent = %d", $id ) );

			foreach ( $vendor_orders as $vendor_order ) {
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'trash' ), array( 'ID' => $vendor_order->ID ) );
			}
		}
	}

	/**
	 * Function: woocommerce_untrash_post.
	 *
	 * @param int $id Order ID.
	 */
	public function untrash_post( $id ) {
		if ( ! $id ) {
			return;
		}

		$post_type = get_post_type( $id );

		if ( in_array( $post_type, wc_get_order_types( 'order-count' ), true ) ) {
			global $wpdb;

			$vendor_orders = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = 'shop_order_vendor' AND post_parent = %d", $id ) );

			foreach ( $vendor_orders as $vendor_order ) {
				$wpdb->update( $wpdb->posts, array( 'post_status' => 'wc-completed' ), array( 'ID' => $vendor_order->ID ) );
			}
		}
	}
}
