<?php
/**
 * WC Vendors Extensions Page
 *
 * @author   WC Vendors
 * @category Admin
 * @package  WCVendors/Admin
 * @version  2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Admin_Addons Class.
 */
class WCVendors_Admin_Extensions {

	public static function output() {
		include_once dirname( __FILE__ ) . '/views/html-admin-page-extensions.php';
	}

}
