<?php

namespace WCVendors\DataStores\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors Commission Data Store Interface.
 *
 * Functions that must be defined by commission classes.
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Interfaces
 * @package     WCVendors/Interfaces
 * @version     2.0.0
 */
interface VendorCommission {

	/**
	 * Get all commission data for the supplied comission ids or all of them
	 */
	public function get_commissions( $status = '', $limit = '', $offset = '' );

	/**
	 * Get all vendor commissions
	 */
	public function get_vendor_commissions( $vendor, $status = '', $limit = '', $offset = '' );

	/**
	 * Get all unpaid commissions
	 */
	public function get_due_commissions( $limit = '', $offset = '' );


	/**
	 * Get all paid commissions
	 */
	public function get_paid_commissions( $limit = '', $offset = '' );


	public function get_void_commissions( $limit = '', $offset = '' );

}
