<?php
/**
 * Install class on activation.
 *
 * @author  Jamie Madden
 * @package WCVendors
 */


class WCVendors_Install {

	/**
	 * Updates to be run
	 */
	private static $db_updates
		= array(
			'2.0.0'  => array(
				'wcv_migrate_settings',
				'wcv_enable_legacy_emails',
				'wcv_update_200_db_version',
			),
			'2.0.11' => array(
				'wcv_add_hide_become_a_vendor_link_option',
				'wcv_add_terms_and_conditions_visibility_option',
			),
			'2.1.1'  => array(
				'wcv_redirect_wp_registration_to_woocommerce_myaccount',
			),
			'2.1.4'  => array(
				'wcv_can_view_customer_shipping_name_option',
			),
			'2.1.6'  => array(
				'wcv_add_vendor_caps',
			),
		);


	/**
	 * Background update class.
	 *
	 * @var object
	 */
	private static $background_updater;

	/**
	 * Checks if install is requierd
	 *
	 * @return unknown
	 */
	public static function init() {

		add_action( 'init', array( __CLASS__, 'check_version' ) );
		add_action( 'admin_init', array( __CLASS__, 'check_pro_version' ) );
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'plugin_action_links_' . wcv_plugin_base, array( __CLASS__, 'plugin_action_links' ) );
		add_action( 'wcvendors_update_options_display', array( __CLASS__, 'maybe_flush_rewrite_rules' ) );

	} // init()

	/**
	 * Check WC Vendors version and run the updater if required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {

		global $wc_vendors;
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'wcvendors_version' ) !== $wc_vendors->version ) {
			self::install();
			do_action( 'wcvendors_updated' );
		}
	}

	/**
	 * Check WC Vendors version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_pro_version() {

		if ( class_exists( 'WCVendors_Pro' ) ) {

			if ( version_compare( WCV_PRO_VERSION, '1.5.0', '<' ) ) {

				if ( is_plugin_active( 'wc-vendors-pro/wcvendors-pro.php' ) ) {
					$notice = sprintf( __( 'WC Vendors Pro %s or below detected. WC Vendors Pro 1.5.0 is required for WC Vendors 2.0.0 and above. WC Vendors Pro has been deactivated.' ), WCV_PRO_VERSION );
					WCVendors_Admin_Notices::add_custom_notice( 'pro_update', $notice );
					deactivate_plugins( 'wc-vendors-pro/wcvendors-pro.php' );
				}
			}
		}
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public static function install_actions() {

		if ( ! empty( $_GET['do_update_wcvendors'] ) ) {
			self::update();
			WCVendors_Admin_Notices::add_notice( 'update' );
		}
		if ( ! empty( $_GET['force_update_wcvendors'] ) ) {
			self::update();
			wp_safe_redirect( admin_url( 'admin.php?page=wcv-settings' ) );
			exit;
		}
	}


	/**
	 * Grouped functions for installing the WC Vendor plugin
	 */
	public static function install() {

		// Check if we are not already running this routine.
		if ( wc_string_to_bool( get_transient( 'wcvendors_installing' ) ) ) {
			return;
		}

		// Ensure needed classes are loaded
		include_once dirname( __FILE__ ) . '/admin/class-wcv-admin-notices.php';

		// If we made it till here nothing is running yet, lets set the transient now.
		set_transient( 'wcvendors_installing', 'yes', MINUTE_IN_SECONDS * 10 );
		wc_maybe_define_constant( 'WCV_INSTALLING', true );

		// Clear the cron
		wp_clear_scheduled_hook( 'pv_schedule_mass_payments' );

		self::remove_admin_notices();
		self::create_roles();
		self::create_tables();
		self::create_options();
		self::add_install_date();
		self::maybe_run_setup_wizard();
		self::update_wcv_version();
		self::maybe_update_db_version();

		delete_transient( 'wcvendors_installing' );

		do_action( 'wcvendors_flush_rewrite_rules' );
		do_action( 'wcvendors_installed' );

	}

	/**
	 * Add the new vendor role
	 *
	 * @return bool
	 */
	public static function create_roles() {

		remove_role( 'pending_vendor' );
		add_role(
			'pending_vendor',
			sprintf( __( 'Pending %s', 'wc-vendors' ), wcv_get_vendor_name() ),
			array(
				'read'         => true,
				'edit_posts'   => false,
				'delete_posts' => false,
			)
		);

		remove_role( 'vendor' );
		$can_add         = wc_string_to_bool( get_option( 'wcvendors_capability_products_enabled', 'yes' ) );
		$can_edit        = wc_string_to_bool( get_option( 'wcvendors_capability_products_edit', 'yes' ) );
		$can_submit_live = wc_string_to_bool( get_option( 'wcvendors_capability_products_live', 'yes' ) );
		add_role(
			'vendor',
			sprintf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ),
			array(
				'assign_product_terms'      => $can_add,
				'edit_products'             => $can_add || $can_edit,
				'edit_product'              => $can_add || $can_edit,
				'edit_published_products'   => $can_edit,
				'delete_published_products' => $can_edit,
				'delete_products'           => $can_edit,
				'delete_posts'              => true,
				'manage_product'            => $can_add,
				'publish_products'          => $can_submit_live,
				'read'                      => true,
				'read_products'             => $can_edit || $can_add,
				'upload_files'              => true,
				'import'                    => true,
				'view_woocommerce_reports'  => false,
			)
		);

		self::create_capabilities();
	}

	/**
	 * Create the new capabilities for vendors
	 *
	 * @since 2.1.6
	 */
	public static function create_capabilities() {

		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		$capabilities = array();
		$all_cap      = self::get_vendor_caps();

		foreach ( $all_cap as $key => $cap ) {
			$capabilities = array_merge( $capabilities, array_keys( $cap ) );
		}

		foreach ( $capabilities as $key => $capability ) {
			$wp_roles->add_cap( 'vendor', $capability );
			$wp_roles->add_cap( 'administrator', $capability );
			$wp_roles->add_cap( 'shop_manager', $capability );
		}
	}


	/**
	 * Create the pv_commission table
	 */
	public static function create_tables() {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql
			= "CREATE TABLE $table_name (
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
	 * Create pages that the plugin relies on, storing page IDs in variables.
	 */
	public static function create_pages() {

		$vendor_dashboard_page_id = wc_create_page(
			esc_sql( _x( 'vendor_dashboard', 'Page slug', 'wc-vendors' ) ),
			'wcvendors_vendor_dashboard_page_id',
			sprintf( _x( '%s Dashboard', 'Page title', 'wc-vendors' ), wcv_get_vendor_name( true ) ),
			'[wcv_vendor_dashboard]',
			''
		);

		$vendor_page_id = wc_create_page(
			esc_sql( _x( 'vendors', 'Page slug', 'wc-vendors' ) ),
			'wcvendors_vendors_page_id',
			sprintf( _x( '%s', 'Page title', 'wc-vendors' ), wcv_get_vendor_name( false ) ),
			'[wcv_vendorslist]',
			''
		);

		$pages = apply_filters(
			'wcvendors_create_pages', array(
				'shop_settings'  => array(
					'name'    => _x( 'shop_settings', 'Page slug', 'wc-vendors' ),
					'title'   => _x( 'Shop Settings', 'Page title', 'wc-vendors' ),
					'parent'  => $vendor_dashboard_page_id,
					'content' => '[wcv_shop_settings]',
				),
				'product_orders' => array(
					'name'    => _x( 'product_orders', 'Page slug', 'wc-vendors' ),
					'title'   => _x( 'Orders', 'Page title', 'wc-vendors' ),
					'parent'  => $vendor_dashboard_page_id,
					'content' => '[wcv_orders]',
				),
			)
		);

		foreach ( $pages as $key => $page ) {
			wc_create_page( esc_sql( $page['name'] ), 'wcvendors_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? $page['parent'] : '' );
		}
	}

	/**
	 * Reset any notices added to admin.
	 *
	 * @since 2.0.0
	 */
	private static function remove_admin_notices() {

		WCVendors_Admin_Notices::remove_all_notices();
	}

	/**
	 * Is this a brand new WC install?
	 *
	 * @return boolean
	 * @since 2.0.0
	 */
	public static function is_new_install() {

		return is_null( get_option( 'wcvendors_version', null ) ) && is_null( get_option( 'wcvendors_db_version', null ) ) && is_null( get_option( 'wc_prd_vendor_options', null ) );
	}

	/**
	 * See if we need the wizard or not.
	 *
	 * @since 2.0.0
	 */
	public static function maybe_run_setup_wizard() {

		if ( apply_filters( 'wcvendors_enable_setup_wizard', self::is_new_install() ) ) {
			WCVendors_Admin_Notices::add_notice( 'install' );
		}
	}


	/**
	 * Get list of DB update callbacks.
	 *
	 * @return array
	 * @since  2.0.0
	 */
	public static function get_db_update_callbacks() {

		return self::$db_updates;
	}

	/**
	 * Is a DB update needed?
	 *
	 * @return boolean
	 * @since 2.0.0
	 */
	private static function needs_db_update() {

		global $wc_vendors;
		$current_db_version = get_option( 'wcvendors_db_version', null );
		$version_one        = get_option( 'wc_prd_vendor_options', null );
		$updates            = self::get_db_update_callbacks();

		if ( ! is_null( $version_one ) && is_null( $current_db_version ) ) {
			return true;
		} else {
			return ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( $updates ) ), '<' );
		}

	}

	/**
	 * Init background updates
	 */
	public static function init_background_updater() {

		include_once dirname( __FILE__ ) . '/includes/class-wcv-background-updater.php';
		self::$background_updater = new WCVendors_Background_Updater();
	}


	/**
	 * See if we need to show or run database updates during install.
	 *
	 * @since 2.0.0
	 */
	private static function maybe_update_db_version() {

		if ( self::needs_db_update() ) {
			if ( apply_filters( 'wcvendors_enable_auto_update_db', false ) ) {
				self::init_background_updater();
				self::update();
			} else {
				WCVendors_Admin_Notices::add_notice( 'update' );
			}
		} else {
			self::update_db_version();
		}
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {

		include_once dirname( __FILE__ ) . '/admin/class-wcv-admin-settings.php';

		$settings = WCVendors_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}
			$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

			foreach ( $subsections as $subsection ) {
				foreach ( $section->get_settings( $subsection ) as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string $version
	 */
	public static function update_db_version( $version = null ) {

		global $wc_vendors;
		delete_option( 'wcvendors_db_version' );
		add_option( 'wcvendors_db_version', is_null( $version ) ? $wc_vendors->version : $version );
	}


	/**
	 * Update WC version to current.
	 */
	private static function update_wcv_version() {

		global $wc_vendors;
		delete_option( 'wcvendors_version' );
		add_option( 'wcvendors_version', $wc_vendors->version );
	}


	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {

		$current_db_version = get_option( 'wcvendors_db_version' );
		$logger             = wc_get_logger();
		$update_queued      = false;

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {

			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					$logger->info(
						sprintf( 'Queuing %s - %s', $version, $update_callback ),
						array( 'source' => 'wcvendors_db_updates' )
					);
					self::$background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}

		if ( $update_queued ) {
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Add an install date option so we can track when the plugin was installed
	 *
	 */
	private static function add_install_date() {
		if ( self::is_new_install() ) {
			add_option( 'wcvendors_install_date', current_time( 'Y-m-d' ) );
		}
	}


	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links
	 *
	 * @return    array
	 * @since 2.0.0
	 */
	public static function plugin_action_links( $links ) {

		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wcv-settings' ) . '" aria-label="' . esc_attr__( 'View WC Vendors settings', 'wc-vendors' ) . '">' . esc_html__( 'Settings', 'wc-vendors' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta
	 * @param mixed $file Plugin Base file
	 *
	 * @return    array
	 */
	public static function plugin_row_meta( $links, $file ) {

		if ( wcv_plugin_base == $file ) {
			$row_meta = array(
				'docs'         => '<a href="' . esc_url( apply_filters( 'wcvendors_docs_url', 'https://docs.wcvendors.com/' ) ) . '" aria-label="' . esc_attr__( 'View WC Vendors documentation', 'wc-vendors' ) . '">' . esc_html__( 'Docs', 'wc-vendors' ) . '</a>',
				'free-support' => '<a href="' . esc_url( apply_filters( 'wcvendors_free_support_url', 'https://wordpress.org/plugins/wc-vendors' ) ) . '" aria-label="' . esc_attr__( 'Visit community forums', 'wc-vendors' ) . '">' . esc_html__( 'Free support', 'wc-vendors' ) . '</a>',
				'support'      => '<a href="' . esc_url( apply_filters( 'wcvendors_support_url', 'https://www.wcvendors.com/product/wc-vendors-pro/?utm_source=plugin&utm_medium=settings_page&utm_campaign=premium_support' ) ) . '" aria-label="' . esc_attr__( 'Buy premium customer support', 'wc-vendors' ) . '">' . esc_html__( 'Premium support', 'wc-vendors' ) . '</a>',
				'pro'          => '<strong><a href="https://www.wcvendors.com/product/wc-vendors-pro/?utm_source=plugin&utm_medium=settings_page&utm_campaign=upgrade_promo" target="_blank">' . __( 'Upgrade to Pro', 'wc-vendors' ) . '</a></strong>',
			);

			if ( class_exists( 'WCVendors_Pro' ) ) {
				unset( $row_meta['pro'] );
			}

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Flush rules if the event is queued.
	 *
	 * @since 2.0.0
	 */
	public static function maybe_flush_rewrite_rules() {

		if ( wc_string_to_bool( get_option( 'wcvendors_queue_flush_rewrite_rules', 'no' ) ) ) {
			update_option( 'wcvendors_queue_flush_rewrite_rules', 'no' );
			flush_rewrite_rules();
		}
	}

	/**
	 * Define the new capabilities for vendors
	 *
	 * @since 2.1.6
	 */
	public static function get_vendor_caps() {

		$capabilities = array(
			'vendor'    => array(
				'wcv_vendor_enabled'  => __( 'Vendor is enabled', 'wc-vendors' ),
				'wcv_vendor_verified' => __( 'Vendor is verified', 'wc-vendors' ),
				'wcv_vendor_trusted'  => __( 'Vendor is trusted', 'wc-vendors' ),
				'wcv_manage_products' => __( 'Manage Products', 'wc-vendors' ),
				'wcv_manage_orders'   => __( 'Manage orders', 'wc-vendors' ),
				'wcv_manage_coupons'  => __( 'Manage coupons', 'wc-vendors' ),
				'wcv_manage_ratings'  => __( 'Manage ratings', 'wc-vendors' ),
				'wcv_manage_settings' => __( 'Manage Store Settings', 'wc-vendors' ),
				'wcv_manage_shipping' => __( 'Manage Store Shipping', 'wc-vendors' ),
				'wcv_view_store'      => __( 'View Store', 'wc-vendors' ),
			),
			'dashboard' => array(
				'wcv_view_sales_overview'        => __( 'View sales overview', 'wc-vendors' ),
				'wcv_view_sales_report_chart'    => __( 'View sales report chart', 'wc-vendors' ),
				'wcv_view_vendor_notice'         => __( 'View vendor notices', 'wc-vendors' ),
				'wcv_view_order_report'          => __( 'View order report', 'wc-vendors' ),
				'wcv_view_order_overview'        => __( 'View order overview', 'wc-vendors' ),
				'wcv_view_review_reports'        => __( 'View ratings report', 'wc-vendors' ),
				'wcv_view_product_status_report' => __( 'View product status report', 'wc-vendors' ),
			),
			'product'   => array(
				'wcv_add_product'            => __( 'Add product', 'wc-vendors' ),
				'wcv_edit_product'           => __( 'Edit product', 'wc-vendors' ),
				'wcv_edit_product_published' => __( 'Edit published product', 'wc-vendors' ),
				'wcv_publish_product'        => __( 'Publish product directly without approval', 'wc-vendors' ),
				'wcv_delete_product'         => __( 'Delete product', 'wc-vendors' ),
				'wcv_duplicate_product'      => __( 'Duplicate product', 'wc-vendors' ),
				'wcv_featured_product'       => __( 'Featured product', 'wc-vendors' ),
				'wcv_view_product'           => __( 'View product', 'wc-vendors' ),
				'wcv_import_product'         => __( 'Import product', 'wc-vendors' ),
				'wcv_export_product'         => __( 'Export product', 'wc-vendors' )
			),
			'order'     => array(
				'wcv_view_order'          => __( 'View order', 'wc-vendors' ),
				'wcv_add_order_note'      => __( 'Add order notes', 'wc-vendors' ),
				'wcv_view_order_note'     => __( 'View order notes', 'wc-vendors' ),
				'wcv_manage_order_export' => __( 'Export orders', 'wc-vendors' ),
				'wcv_manage_order_status' => __( 'Manage order status', 'wc-vendors' ),
				'wcv_view_name'           => __( 'View customer name', 'wc-vendors' ),
				'wcv_view_phone'          => __( 'View customer phone number', 'wc-vendors' ),
				'wcv_view_shipping_name'  => __( 'View customer shipping name', 'wc-vendors' ),
				'wcv_view_shipping'       => __( 'View customer shipping address fields', 'wc-vendors' ),
				'wcv_view_billing'        => __( 'View customer billing address fields', 'wc-vendors' ),
				'wcv_view_email'          => __( 'View customer shipping name', 'wc-vendors' ),
			),
			'coupon'    => array(
				'wcv_add_coupon'    => __( 'Add coupon', 'wc-vendors' ),
				'wcv_edit_coupon'   => __( 'Edit coupon', 'wc-vendors' ),
				'wcv_delete_coupon' => __( 'Delete coupon', 'wc-vendors' ),
			),
			'report'    => array(
				'wcv_view_overview_report'    => __( 'View overview report', 'wc-vendors' ),
				'wcv_view_daily_sale_report'  => __( 'View daily sales report', 'wc-vendors' ),
				'wcv_view_top_selling_report' => __( 'View top selling report', 'wc-vendors' ),
				'wcv_view_top_earning_report' => __( 'View top earning report', 'wc-vendors' ),
				'wcv_view_statement_report'   => __( 'View statement report', 'wc-vendors' )
			),
			'menu'      => array(
				'wcv_view_overview_menu'       => __( 'View order menu', 'wc-vendors' ),
				'wcv_view_dashboard_menu'      => __( 'View dashboard menu', 'wc-vendors' ),
				'wcv_view_product_menu'        => __( 'View product menu', 'wc-vendors' ),
				'wcv_view_order_menu'          => __( 'View order menu', 'wc-vendors' ),
				'wcv_view_coupon_menu'         => __( 'View order menu', 'wc-vendors' ),
				'wcv_view_ratings_menu'        => __( 'View ratings menu', 'wc-vendors' ),
				'wcv_view_store_settings_menu' => __( 'View store settings menu', 'wc-vendors' ),
			),
		);

		return apply_filters( 'wcv_get_vendor_caps', $capabilities );

	}
}

WCVendors_Install::init();
