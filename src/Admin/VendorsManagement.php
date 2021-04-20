<?php
/**
 * Vendors Management page
 *
 * @package WCVendors/Admin
 */

namespace WCVendors\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class VendorsManagement{
    public static function output(){
        $vendors_mamagement_table = new VendorsManagementTable();
        $vendors_mamagement_table->prepare_items();
        include_once('views/html-admin-page-vendors-manager.php');
       return true;
    }
}