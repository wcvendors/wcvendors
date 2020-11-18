<?php
/**
 * WC Vendors Go Pro Page
 *
 * @author   WC Vendors
 * @category Admin
 * @package  WCVendors/Admin
 * @version  2.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors_Admin_GoPro Class.
 */
class WCVendors_Admin_GoPro {

	public static function output() {
		include_once dirname( __FILE__ ) . '/views/html-admin-page-go-pro.php';
	}

}
