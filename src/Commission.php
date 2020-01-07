<?php
/**
 * The commission class for a single commission
 *
 * @package WCVendors
 */

namespace WCVendors;

use WC_Data;
use WC_Data_Store;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The commission class for a single commission
 */
class Commission extends WC_Data {


	public $object_type = 'vendor-commission';
	public $data_store_name = 'vendor-commission';
	protected $id = null;
	protected $data = array(
		'order_id'          => 0,
		'vendor_id'         => 0,
		'vendor_order_id'   => 0,
		'product_id'        => 0,
		'variation_id'      => 0,
		'order_item_id'     => 0,
		'product_qty'       => 0,
		'total_shipping'    => 0,
		'shipping_tax'      => 0,
		'tax'               => 0,
		'total_due'         => 0,
		'fees'              => 0,
		'commission_status' => 'due',
		'commission_date'   => null,
		'commission_rate'   => 0,
		'commission_fee'    => 0,
		'paid_date'         => null,
		'paid_status'       => '',
		'paid_via'          => '',
	);


	/**
	 * Constructor for commission.
	 *
	 * @param int|object $commission Commission ID to load from the DB or commission object.
	 */
	public function __construct( $commission = null ) {

		if ( is_numeric( $commission ) && ! empty( $commission ) ) {
			$this->set_id( $commission );
		} elseif ( is_object( $commission ) ) {
			$this->set_id( $commission->commission_id );
		} elseif ( 0 === $commission || '0' === $commission ) {
			$this->set_id( 0 );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = WC_Data_Store::load( $this->data_store_name );

		if ( false === $this->get_object_read() ) {
			$this->data_store->read( $this );
		}

		parent::__construct();
	}

	/*
	 |--------------------------------------------------------------------------
	 | Getters
	 |--------------------------------------------------------------------------
	 */


	/**
	 *   Get the order_id.
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_order_id( $context = 'view' ) {
		return $this->get_prop( 'order_id', $context );
	}

	/**
	 *   Get the vendor_id.
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_vendor_id( $context = 'view' ) {
		return $this->get_prop( 'vendor_id', $context );
	}

	/**
	 *   Get the vendor_name
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_vendor_name( $context = 'view' ) {
		return $this->get_prop( 'vendor_name', $context );
	}

	/**
	 *   Get the vendor_order_id
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_vendor_order_id( $context = 'view' ) {
		return $this->get_prop( 'vendor_order_id', $context );
	}

	/**
	 *   Get the product_id
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_product_id( $context = 'view' ) {
		return $this->get_prop( 'product_id', $context );
	}

	/**
	 *   Get the variation id.
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_variation_id( $context = 'view' ) {
		return $this->get_prop( 'variation_id', $context );
	}

	/**
	 *   Get the product_qty
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_product_qty( $context = 'view' ) {
		return $this->get_prop( 'product_qty', $context );
	}

	/**
	 *   Get the total_shipping
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_total_shipping( $context = 'view' ) {
		return $this->get_prop( 'total_shipping', $context );
	}

	/**
	 *   Get the shipping_tax
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_shipping_tax( $context = 'view' ) {
		return $this->get_prop( 'shipping_tax', $context );
	}

	/**
	 *   Get the tax
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_tax( $context = 'view' ) {
		return $this->get_prop( 'tax', $context );
	}

	/**
	 *   Get the fees
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_fees( $context = 'view' ) {
		return $this->get_prop( 'fees', $context );
	}

	/**
	 *   Get the total_due
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_total_due( $context = 'view' ) {
		return $this->get_prop( 'total_due', $context );
	}

	/**
	 *   Get the status
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_status( $context = 'view' ) {
		return $this->get_prop( 'status', $context );
	}

	/**
	 *   Get the commission_date
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_commission_date( $context = 'view' ) {
		return $this->get_prop( 'commission_date', $context );
	}

	/**
	 *   Get the commission_rate
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_commission_rate( $context = 'view' ) {
		return $this->get_prop( 'commission_rate', $context );
	}

	/**
	 *   Get the commission_fee
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_commission_fee( $context = 'view' ) {
		return $this->get_prop( 'commission_fee', $context );
	}

	/**
	 *   Get the paid_date
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_paid_date( $context = 'view' ) {
		return $this->get_prop( 'paid_date', $context );
	}

	/**
	 *   Get the paid_status
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_paid_status( $context = 'view' ) {
		return $this->get_prop( 'paid_status', $context );
	}

	/**
	 *   Get the paid_via
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_paid_via( $context = 'view' ) {
		return $this->get_prop( 'paid_via', $context );
	}

	/**
	 *   Set the order_id.
	 *
	 * @param int $value Order ID.
	 */
	public function set_order_id( $value ) {
		return $this->set_prop( 'order_id', $value );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Set the vendor_id.
	 *
	 * @param int $value Vendor ID.
	 */
	public function set_vendor_id( $value ) {
		return $this->set_prop( 'vendor_id', $value );
	}

	/**
	 *   Set the vendor_name
	 *
	 * @param string $value Vendor name.
	 */
	public function set_vendor_name( $value ) {
		return $this->set_prop( 'vendor_name', $value );
	}

	/**
	 *   Set the vendor_order_id
	 *
	 * @param int $value Vendor order ID.
	 */
	public function set_vendor_order_id( $value ) {
		return $this->set_prop( 'vendor_order_id', $value );
	}

	/**
	 *   Set the product_id
	 *
	 * @param int $value Product ID.
	 */
	public function set_product_id( $value ) {
		return $this->set_prop( 'product_id', $value );
	}

	/**
	 *   Set the
	 *
	 * @param int $value Variation ID.
	 */
	public function set_variation_id( $value ) {
		return $this->set_prop( 'variation_id', $value );
	}

	/**
	 *   Set the order_item_id
	 *
	 * @param int $value Order item ID.
	 */
	public function set_order_item_id( $value ) {
		return $this->set_prop( 'order_item_id', $value );
	}

	/**
	 *   Set the product_qty
	 *
	 * @param int $value Product quantity.
	 */
	public function set_product_qty( $value ) {
		return $this->set_prop( 'product_qty', $value );
	}

	/**
	 *   Set the total_shipping
	 */
	public function set_total_shipping( $value ) {
		return $this->set_prop( 'total_shipping', $value );
	}

	/**
	 *   Set the shipping_tax
	 */
	public function set_shipping_tax( $value ) {
		return $this->set_prop( 'shipping_tax', $value );
	}

	/**
	 *   Set the tax
	 */
	public function set_tax( $value ) {
		return $this->set_prop( 'tax', $value );
	}

	/**
	 *   Set the fees
	 */
	public function set_fees( $value ) {
		return $this->set_prop( 'fees', $value );
	}

	/**
	 *   Set the status
	 */
	public function set_status( $value ) {
		return $this->set_prop( 'status', $value );
	}

	/**
	 *   Set the commission_date
	 */
	public function set_commission_date( $value ) {
		return $this->set_prop( 'commission_date', $value );
	}

	/**
	 *   Set the commission_rate
	 */
	public function set_commission_rate( $value ) {
		return $this->set_prop( 'commission_rate', $value );
	}

	/**
	 *   Set the commission_fee
	 */
	public function set_commission_fee( $value ) {
		return $this->set_prop( 'commission_fee', $value );
	}

	/**
	 *   Set the paid_date
	 */
	public function set_paid_date( $value ) {
		return $this->set_prop( 'paid_date', $value );
	}

	/**
	 *   Set the paid_status
	 */
	public function set_paid_status( $value ) {
		return $this->set_prop( 'paid_status', $value );
	}

	/**
	 *   Set the paid_via
	 */
	public function set_paid_via( $value ) {
		return $this->set_prop( 'paid_via', $value );
	}

	/**
	 *  Calculate the total commission
	 */
	public function calculate_totals() {

		// $this->apply_changes();
		$commission   = 0;
		$shipping     = 0;
		$shipping_tax = 0;
		$tax          = 0;
		$total_due    = 0;

		$item = WC_Order_Factory::get_order_item( $this->get_order_item_id() );

		// Calculate the commission for the product amount. This will include the qty multiplier if required
		$commission += $this->calculate_item_commission( $item->get_subtotal(), $item->get_quantity() );

		// There is no commission on taxes. These are pass through to the vendor
		$tax += $item->get_total_tax();

		$total = (float) $commission + (float) $tax;

		$this->set_commission( $commission );
		$this->set_total_due( $total );

		// $this->save();
	}

	/**
	 *   Get the order_item_id
	 *
	 * @param string $context Context view or edit;
	 *
	 * @return mixed
	 */
	public function get_order_item_id( $context = 'view' ) {
		return $this->get_prop( 'order_item_id', $context );
	}


	/*
	|--------------------------------------------------------------------------
	| Other Methods
	|--------------------------------------------------------------------------
	*/

	/**
	 *   Set the total_due
	 */
	public function set_total_due( $value ) {
		return $this->set_prop( 'total_due', $value );
	}

	/**
	 * Calculate the commission for the single order item
	 */
	public function calculate_commission( $item_total, $item_qty ) {

		$commission_type      = 'global';
		$commission_rate      = '50';
		$commission_rate_type = 'percent';

		$item_commission = 0;

		switch ( $commission_rate_type ) {
			default:
				$commission      = $item_total * ( $commission_rate / 100 );
				$commission      = round( $commission, 2 );
				$item_commission = $commission * $item_qty;
				break;
		}

		return $item_commission;
	}

}
