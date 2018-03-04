<?php
/**
 * Install class on activation.
 *
 * @author  Matt Gates, Jamie Madden
 * @package WCVendors
 */


class WCV_Install
{

	/**
	 * Checks if install is requierd
	 *
	 * @return unknown
	 */
	public static function init() {

		$db_version = WC_Vendors::$pv_options->get_option( 'db_version' );

		// Initial Install
		if ( version_compare( $db_version, '1.0', '<' ) ) {
			self::install();
			// WC_Vendors::$pv_options->update_option( 'db_version', '2.0.0' );
		}

	} // init()


	/**
	 * Grouped functions for installing the WC Vendor plugin
	 */
	public static function install() {

		// Clear the cron
		wp_clear_scheduled_hook( 'pv_schedule_mass_payments' );

		self::create_roles();
		self::create_tables();
		// self::maybe_run_setup_wizard();

	}

	/**
	 * Add the new Vendor role
	 *
	 * @return bool
	 */
	public static function create_roles() {
		remove_role( 'pending_vendor' );
		add_role( 'pending_vendor', __( 'Pending Vendor', 'wcvendors' ), array(
																					  'read'         => true,
																					  'edit_posts'   => false,
																					  'delete_posts' => false
																				 ) );

		remove_role( 'vendor' );
		add_role( 'vendor', __( 'Vendor', 'wcvendors') , array(
										   'assign_product_terms'     => true,
										   'edit_products'            => true,
										   'edit_product'             => true,
										   'edit_published_products'  => false,
										   'manage_product'           => true,
										   'publish_products'         => false,
										   'delete_posts'			  => true,
										   'read'                     => true,
										   'upload_files'             => true,
										   'view_woocommerce_reports' => false,
									  ) );
	}


	/**
	 * Create the pv_commission table
	 */
	public static function create_tables() {
		global $wpdb;

		$table_name = $wpdb->prefix . "pv_commission";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			product_id bigint(20) NOT NULL,
			order_id bigint(20) NOT NULL,
			vendor_id bigint(20) NOT NULL,
			total_due decimal(20,2) NOT NULL,
			qty BIGINT( 20 ) NOT NULL,
			total_shipping decimal(20,2) NOT NULL,
			tax decimal(20,2) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'due',
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			UNIQUE KEY id (id)
		);";

		dbDelta( $sql );
	}


	/**
	 * Create a page
	 *
	 * @access public
	 * @return void
	 *
	 * @param mixed  $slug         Slug for the new page
	 * @param mixed  $option       Option name to store the page's ID
	 * @param string $page_title   (optional) (default: '') Title for the new page
	 * @param string $page_content (optional) (default: '') Content for the new page
	 * @param int    $post_parent  (optional) (default: 0) Parent for the new page
	 */
	public static function create_page( $slug, $page_title = '', $page_content = '', $post_parent = 0 )
	{
		global $wpdb;

		$page_id = WC_Vendors::$pv_options->get_option( $slug . '_page' );

		if ( $page_id > 0 && get_post( $page_id ) ) {
			return $page_id;
		}

		$page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;", $slug ) );
		if ( $page_found ) {
			if ( !$page_id ) {
				WC_Vendors::$pv_options->update_option( $slug . '_page', $page_found );

				return $page_found;
			}

			return $page_id;
		}

		$page_data = array(
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_author'    => 1,
			'post_name'      => $slug,
			'post_title'     => $page_title,
			'post_content'   => $page_content,
			'post_parent'    => $post_parent,
			'comment_status' => 'closed'
		);

		$page_id = wp_insert_post( $page_data );

		WC_Vendors::$pv_options->update_option( $slug . '_page', $page_id );

		return $page_id;
	}


	/**
	 * Create all pages
	 */
	public static function create_pages(){

		$vendor_page_id = self::create_page( 'vendor_dashboard', __( 'Vendor Dashboard', 'wcvendors' ), '[wcv_vendor_dashboard]' );
		self::create_page( 'product_orders', __( 'Orders', 'wcvendors' ), '[wcv_orders]', $vendor_page_id );
		self::create_page( 'shop_settings', __( 'Shop Settings', 'wcvendors' ), '[wcv_shop_settings]', $vendor_page_id );
	}


	/**
	 * Is this a brand new WC install?
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	public static function is_new_install() {
		return empty( WC_Vendors::$pv_options->get_option( 'db_version' ) );
	}

	/**
	 * See if we need the wizard or not.
	 *
	 * @since 2.0.0
	 */
	public static function maybe_run_setup_wizard() {
		if ( apply_filters( 'wcvendors_enable_setup_wizard', self::is_new_install() ) ) {
			add_action( 'admin_notices', array( 'WCV_INSTALL', 'setup_wizard' ) );
		}
	}


	public static function setup_wizard(){
		include( 'includes/views/html-notice-install.php' );
	}


}
