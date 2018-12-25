<?php
/**
 * WC Vendors Extensions Page
 *
 * @author   WooThemes
 * @category Admin
 * @package  WooCommerce/Admin
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
