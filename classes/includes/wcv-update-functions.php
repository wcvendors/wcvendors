<?php
/**
 * WC Vendors Updates
 *
 * Functions for updating data, used by the background updater.
 *
 * @package WCVendors/Functions
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* Map WC Vendors version one settings to version two settings
*
* @since 2.0.0
*/
function wcv_migrate_settings(){

	$version_one = get_option( 'wc_prd_vendor_options', null );
	$mappings = wcv_get_settings_mapping();

	if ( is_null( $version_one ) ) return;

	foreach ( $version_one as $setting => $value ) {

		if ( array_key_exists( $setting, $mappings ) ){

			$value = maybe_unserialize( $value );

			if ( $setting == 'hide_product_misc' && ! empty( $value ) ){

				update_option( 'wcvendors_capability_product_featured', $value[ 'featured' ] );
				update_option( 'wcvendors_capability_product_duplicate', $value[ 'duplicate' ] );
				update_option( 'wcvendors_capability_product_sku', $value[ 'sku' ] );
				update_option( 'wcvendors_capability_product_taxes', $value[ 'taxes' ] );

			} else {

 				if ( $value == 1 ) $value = 'yes';

				update_option( $mappings[ $setting ], $value );
			}

		}
	}
}

/**
* Settings Map
*
* @since 2.0.0
*/
function wcv_get_settings_mapping(){

	return apply_filters( 'wcvendors_settings_mappings', array(
		'hide_product_types'       => 'wcvendors_capability_product_types',
		'hide_product_type_options'       => 'wcvendors_capability_product_type_options',
		'hide_product_panel'       => 'wcvendors_capability_product_data_tabs',
		'hide_product_misc'      => 'wcvendors_capability_order_customer_billling',
		'can_submit_products'      => 'wcvendors_capability_products_enabled',
		'can_edit_published_products'      => 'wcvendors_capability_products_edit',
		'can_submit_live_products'      => 'wcvendors_capability_products_live',
		'can_show_orders'      => 'wcvendors_capability_orders_enabled',
		'can_export_csv'      => 'wcvendors_capability_orders_export',
		'can_view_order_emails'      => 'wcvendors_capability_order_customer_email',
		'can_view_order_comments'      => 'wcvendors_capability_order_read_notes',
		'can_submit_order_comments'      => 'wcvendors_capability_order_update_notes',
		'can_view_frontend_reports'      => 'wcvendors_capability_frontend_reports',
		'default_commission'       => 'wcvendors_vendor_commission_rate',
		'sold_by'      	=> 'wcvendors_display_label_sold_by_enable',
		'sold_by_label'       	=> 'wcvendors_label_sold_by',
		'seller_info_label'       	=> 'wcvendors_display_label_store_info',
		'vendor_dashboard_page'       	=> 'wcvendors_vendor_dashboard_page_id',
		'shop_settings_page'       	=> 'wcvendors_shop_settings_page_id',
		'product_orders_page'       	=> 'wcvendors_product_orders_page_id',
		'terms_to_apply_page'       => 'wcvendors_vendor_terms_page_id',
		'shop_headers_enabled'      => 'wcvendors_display_shop_headers',
		'shop_html_enabled'      	=> 'wcvendors_display_shop_description_html',
		'vendor_display_name'       => 'wcvendors_display_shop_display_name',
		'vendor_shop_permalink'		=> 'wcvendors_vendor_shop_permalink',
		'product_page_css'			=> 'wcvendors_display_advanced_stylesheet',
		'show_vendor_registration'  => 'wcvendors_vendor_allow_registration',
		'manual_vendor_registration' => 'wcvendors_vendor_approve_registration',
		'give_tax' => 'wcvendors_vendor_give_taxes',
		'give_shipping' => 'wcvendors_vendor_give_shipping',
		'instapay'		=> 'wcvendors_payments_paypal_instantpay_enable',
		'schedule'	=> 'wcvendors_payments_paypal_schedule',
		'mail_mass_pay_results' => 'wcvendors_payments_paypal_email_enable'

	) );
}

/**
* Finish Settings update
*
* @since 2.0.0
*/
function wcv_update_200_db_version(){
	WCVendors_Install::update_db_version( '2.0.0' );
}
