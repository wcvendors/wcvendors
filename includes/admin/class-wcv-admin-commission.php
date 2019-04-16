<?php
/**
 * Commissions page
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin/
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors Admin Commission Class.
 */
class WCVendors_Admin_Commission {

	public static function output() {

		$commissions_table = new WCVendors_Admin_Commission_Table();
		$commissions_table->prepare_items();
		include_once 'views/html-admin-page-commissions.php';
		return true;
	}

}
