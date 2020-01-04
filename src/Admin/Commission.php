<?php
/**
 * Commissions page
 *
 * @package WCVendors/Admin
 */

namespace WCVendors\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors Admin Commission Class.
 */
class Commission {

	/**
	 * Output the commissions table.
	 */
	public static function output() {
		$commissions_table = new CommissionTable();
		$commissions_table->prepare_items();
		include_once 'views/html-admin-page-commissions.php';
		return true;
	}
}
