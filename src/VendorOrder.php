<?php
/**
 * The vendor order class handles storage of a vendor order data. The data is stored in a custom post type
 *
 * @package WCVendors
 */

namespace WCVendors;

use Exception;
use WC_Data_Exception;
use WC_Data_Store;
use WC_Order;
use WC_Order_Item_Tax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The vendor order class handles storage of a vendor order data. The data is stored in a custom post type
 */
class VendorOrder extends WC_Order {
	/**
	 * Current order object.
	 *
	 * @var null|VendorOrder $order Current order object;
	 */
	protected $order = null;

	/**
	 * Which data store to load. WC 3.0+ property.
	 *
	 * @var string
	 */
	protected $data_store_name = 'shop-order-vendor';

	/**
	 * This is the name of this object type. WC 3.0+ property.
	 *
	 * @var string
	 */
	protected $object_type = 'shop_order_vendor';

	/**
	 * Parent order of this vendor order.
	 *
	 * @var WC_Order $parent_order Parent order object.
	 */
	private $parent_order;

	/**
	 * Extra data for this object. Name value pairs (name + default value).
	 *
	 * WC 3.0+ property.
	 *
	 * @var array
	 */
	protected $extra_data = array(
		// Extra data with getters/setters.
		'vendor_id'      => 0,
		'commission'     => 0,
		'order_item_ids' => array(),
	);

	/**
	 * Initialize the vendor order object.
	 *
	 * @param int|VendorOrder $vendor_order Vendor order ID or object.
	 *
	 * @throws Exception WooCommerce data exception.
	 */
	public function __construct( $vendor_order = 0 ) {

		parent::__construct( $vendor_order );

		if ( is_numeric( $vendor_order ) && $vendor_order > 0 ) {
			$this->set_id( $vendor_order );
		} elseif ( $vendor_order instanceof self ) {
			$this->set_id( $vendor_order->get_id() );
		} elseif ( ! empty( $vendor_order->ID ) ) {
			$this->set_id( $vendor_order->ID );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = WC_Data_Store::load( $this->data_store_name );

		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/**
	 * Getters
	 */

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'shop_order_vendor';
	}

	/**
	 * Get vendor id.
	 *
	 * @param string $context Context, view or edit.
	 *
	 * @return mixed
	 */
	public function get_vendor_id( $context = 'view' ) {
		return $this->get_prop( 'vendor_id', $context );
	}

	/**
	 * Get parent order of vendor order.
	 *
	 * @return WC_Order
	 */
	public function get_parent_order() {
		if ( ! is_object( $this->parent_order ) ) {
			$this->parent_order = new WC_Order( $this->get_parent_id() );
		}

		return $this->parent_order;
	}

	/**
	 * Setters
	 */

	/**
	 * Adds an order item to this order. The order item will not persist until save.
	 *
	 * @param \WC_Order_Item $item Order item object (product, shipping, fee, coupon, tax).
	 * @return false|void
	 */
	public function add_item( $item ) {
	}

	/**
	 * Set vendor id.
	 *
	 * @param int $vendor_id Vendor ID.
	 */
	public function set_vendor_id( $vendor_id ) {
		$this->set_prop( 'vendor_id', $vendor_id );
	}

	/**
	 * Set parent order of vendor order.
	 *
	 * @param WC_Order $order WC Order object.
	 */
	public function set_parent_order( $order ) {
		$this->parent_order = $order;
	}

	/**
	 * Vendor order doesn't need payment.
	 *
	 * @return bool
	 */
	public function needs_payment() {
		return false;
	}

	/**
	 * We don't process order item of vendor order.
	 *
	 * @return bool
	 */
	public function needs_processing() {
		return false;
	}
}
