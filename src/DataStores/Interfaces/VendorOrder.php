<?php

namespace WCVendors\DataStores\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vendor Order Data Store Interface
 *
 * Functions that must be defined by vendor order classes.
 *
 * @category Interface
 * @since    2.0.0
 * @package  WCVendors
 */
interface VendorOrder {

	/**
	 * Get the total for this order
	 */
	public function get_vendor_total( &$vendor_order );

}
