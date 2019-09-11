<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Delete plugin data on deactivation
 *
 * @author      Lindeni Mahlalela, WC Vendors
 * @category    Settings
 * @package     WCVendors/Admin/Settings
 * @version     2.0.8
 */
class WCVendors_Uninstall {

	/**
	 * Check the uninstall options and delete the data
	 *
	 * @return void
	 * @package
	 * @since 2.0.8
	 */
	public static function uninstall() {

		if ( 'yes' == get_option( 'wcvendors_uninstall_delete_all_data' ) ) {
			self::delete_all();
		} else {
			if ( 'yes' == get_option( 'wcvendors_uninstall_delete_custom_table' ) ) {
				self::delete_table();
			}

			if ( 'yes' == get_option( 'wcvendors_uninstall_delete_custom_pages' ) ) {
				self::delete_pages();
			}

			if ( 'yes' == get_option( 'wcvendors_uninstall_delete_settings_options' ) ) {
				self::delete_options();
			}

			if ( 'yes' == get_option( 'wcvendors_uninstall_delete_vendor_roles' ) ) {
				self::remove_roles();
			}
		}

		self::flush_rewrite_rules();
	}

	/**
	 * Delete all plugin data at once
	 *
	 * @return void
	 * @since 2.0.8
	 */
	public static function delete_all() {

		self::remove_roles();
		self::delete_pages();
		self::delete_options();
		self::delete_table();
		WCV_Cron::remove_cron_schedule();
	}

	/**
	 * Remove custom roles
	 *
	 * @return void
	 * @since 2.0.8
	 */
	public static function remove_roles() {

		remove_role( 'pending_vendor' );
		remove_role( 'vendor' );
	}

	/**
	 * Delete custom pages created for this plugin
	 *
	 * @return void
	 * @since 2.0.8
	 */
	public static function delete_pages() {

		wp_delete_post( get_option( 'wcvendors_vendor_dashboard_page_id' ), true );
		wp_delete_post( get_option( 'wcvendors_shop_settings_page_id' ), true );
		wp_delete_post( get_option( 'wcvendors_product_orders_page_id' ), true );
		wp_delete_post( get_option( 'wcvendors_vendors_page_id' ), true );
	}

	/**
	 * Delete custom database table
	 *
	 * @return void
	 * @since 2.0.8
	 */
	public static function delete_table() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'pv_commission';

		$wpdb->query( "DROP TABLE $table_name" );
	}

	/**
	 * Delete all options
	 *
	 * @return void
	 * @since 2.0.8
	 */
	public static function delete_options() {

		include_once dirname( __FILE__ ) . '/admin/class-wcv-admin-settings.php';

		$settings = WCVendors_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}
			$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

			foreach ( $subsections as $subsection ) {
				foreach ( $section->get_settings( $subsection ) as $value ) {
					delete_option( $value['id'] );
				}
			}
		}

		delete_option( 'wcvendors_version' );
		delete_option( 'wcvendors_db_version' );
		delete_option( 'wcvendors_install_date' );
		delete_option( 'wcvendors_admin_notices' );
		delete_option( 'wcvendors_wizard_complete' );
		delete_option( 'wcvendors_queue_flush_rewrite_rules' );
		delete_option( 'wcvendors_admin_notice_email_updates' );
	}

	/**
	 * Flush rewrite rules
	 *
	 * @return void
	 * @since 2.0.8
	 */
	public static function flush_rewrite_rules() {

		flush_rewrite_rules();
	}
}
