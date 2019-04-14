<?php
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
interface WCVendors_Vendor_Order_Data_Store_Interface {

	/**
	 * Get the total for this order
	 */
	public function get_vendor_total( &$vendor_order ); 

}