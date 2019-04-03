<?php

/**
 * Order vendor_order
 *
 * @class    WC_Order_Vendor
 */
class WC_Order_Vendor extends WC_Order {

	/** @public string Order type */
	public $order_type = 'vendor_order';

	/** @var string Date */
	public $date;

	/**
	 * Init/load the vendor_order object. Called from the constructor.
	 *
	 * @param  string|int|object|WC_Order_Vendor $vendor_order Vendor Order to init
	 *
	 * @uses   WP_POST
	 */
	protected function init( $vendor_order ) {

		if ( is_numeric( $vendor_order ) ) {
			$this->id   = absint( $vendor_order );
			$this->post = get_post( $vendor_order );
			$this->get_vendor_order( $this->id );
		} elseif ( $vendor_order instanceof WC_Order_Vendor ) {
			$this->id   = absint( $vendor_order->id );
			$this->post = $vendor_order->post;
			$this->get_vendor_order( $this->id );
		} elseif ( isset( $vendor_order->ID ) ) {
			$this->id   = absint( $vendor_order->ID );
			$this->post = $vendor_order;
			$this->get_vendor_order( $this->id );
		}
	}

	/**
	 * Gets an vendor_order from the database
	 *
	 * @since 2.2
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function get_vendor_order( $id = 0 ) {

		if ( ! $id ) {
			return false;
		}

		if ( $result = get_post( $id ) ) {
			$this->populate( $result );

			return true;
		}

		return false;
	}

	/**
	 * Populates a vendor_order from the loaded post data
	 *
	 * @param mixed $result
	 */
	public function populate( $result ) {

		// Standard post data
		$this->id            = $result->ID;
		$this->date          = $result->post_date;
		$this->modified_date = $result->post_modified;
		$this->reason        = $result->post_excerpt;
	}

}
