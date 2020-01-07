<?php
/**
 * Installation related functions and actions.
 *
 * @package WCVendors
 */

namespace WCVendors;

use WP_Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors_Install Class.
 */
class Install {

	/** @var object Background update class */
	private $background_updater;

	/** Updates to be run **/
	private $db_updates = array(
		'2.0.0' => array(
			'_updates',
		),
	);

	public function init_hooks() {
		add_action( 'init', array( $this, 'check_version' ) );
		add_action( 'admin_init', array( $this, 'install_actions' ) );
		add_filter( 'plugin_action_links_' . WCV_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Check WC Vendors version and run the updater is required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'wcvendors_version' ) !== WCV_VERSION ) {
			$this->install();
			do_action( 'wcvendors_updated' );
		}
	}

	/**
	 * Install WC Vendors
	 */
	public function install() {

		if ( ! defined( 'WCV_INSTALLING' ) ) {
			define( 'WCV_INSTALLING', true );
		}

		$this->create_options();
		$this->create_tables();
		$this->create_roles();

		// $this->create_cron_jobs();
		// $this->create_files();
		// Queue upgrades/setup wizard
		$current_wcv_version = get_option( 'wcvendors_version', null );
		$current_db_version  = get_option( 'wcvendors_db_version', null );

		// WC_Admin_Notices::remove_all_notices();

		// No versions? This is a new install :)
		if ( is_null( $current_wcv_version ) && is_null( $current_db_version ) && apply_filters( 'wcvendors_enable_setup_wizard', true ) ) {
			Admin\Notices::add_notice( 'install' );
			set_transient( '_wcv_activation_redirect', 1, 30 );

			// No page? Let user run wizard again..
		} elseif ( ! get_option( 'wcvendors_dashboard_page_id' ) ) {
			Admin\Notices::add_notice( 'install' );
		}

		if ( ! is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( $this->db_updates ) ), '<' ) ) {
			Admin\Notices::add_notice( 'update' );
		} else {
			$this->update_db_version();
		}

		$this->update_wcv_version();

		// Flush rules after install
		do_action( 'wcvendors_flush_rewrite_rules' );

		// Trigger action
		do_action( 'wcvendors_installed' );
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private function create_options() {
		$settings = Admin\Settings::get_settings_pages();

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
	 * Create tables
	 */
	private function create_tables() {

		global $wpdb;
		$wpdb->hide_errors();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $this->get_schema() );

	}

	/**
	 * Get the Db schema for wc vendors
	 */
	private function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$schema = "CREATE TABLE {$wpdb->prefix}wcvendors_commissions (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			order_id bigint(20) NOT NULL, 
			vendor_id bigint(20) NOT NULL,
			vendor_name varchar(255) NOT NULL, 
			vendor_order_id bigint(20) NOT NULL,
			product_id bigint(20) NOT NULL,
			variation_id bigint(20) NOT NULL, 
			order_item_id bigint(20) NOT NULL, 
			product_qty bigint( 20 ) NOT NULL,
			total_shipping decimal(20,2) NOT NULL,
			shipping_tax decimal(20,2) NOT NULL,
			tax decimal(20,2) NOT NULL,
			fees decimal(20,2) NOT NULL,
			total_due decimal(20,2) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'due',
			commission_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			commission_rate varchar(8) NOT NULL DEFAULT '',
			commission_fee varchar(8) NOT NULL DEFAULT '',
			paid_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			paid_status varchar(20) NOT NULL DEFAULT '',
			paid_via varchar(255) NOT NULL DEFAULT '', 
			PRIMARY KEY  (id)
			) $collate;
		";

		return $schema;
	}

	/**
	 * Create roles and capabilities.
	 */
	public function create_roles() {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}

		// Pending Vendor
		add_role(
			'pending_vendor',
			__( 'Pending Vendor', 'wc-vendors' ),
			array(
				'read'         => true,
				'edit_posts'   => false,
				'delete_posts' => false,
			)
		);

		// Vendor
		add_role(
			'vendor',
			__( 'Vendor', 'wc-vendors' ),
			array(
				'assign_product_terms'     => true,
				'edit_products'            => true,
				'edit_product'             => true,
				'edit_published_products'  => false,
				'manage_product'           => true,
				'publish_products'         => false,
				'delete_posts'             => true,
				'read'                     => true,
				'upload_files'             => true,
				'view_woocommerce_reports' => false,
			)
		);

		// Add new capabilities to vendors
		$capabilities = $this->get_core_capabilities();

		foreach ( $capabilities as $cap_group ) {
			foreach ( $cap_group as $cap ) {
				$wp_roles->add_cap( 'vendor', $cap );
			}
		}

	}

	/**
	 * Get capabilities for WC Vendors - these are assigned to vendors during installation or reset.
	 *
	 * @return array
	 */
	private function get_core_capabilities() {

		$capabilities = array();

		$capabilities['vendor'] = array(
			'is_vendor',
		);

		$capability_types = array( 'vendor_product', 'vendor_shop_order' );

		foreach ( $capability_types as $capability_type ) {

			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

			);
		}

		return $capabilities;
	}

	/**
	 * Update DB version to current.
	 *
	 * @param string $version
	 */
	public function update_db_version( $version = null ) {
		update_option( 'wcvendors_db_version', is_null( $version ) ? WCV_VERSION : $version );
	}

	/**
	 * Update WC version to current.
	 */
	private function update_wcv_version() {
		update_option( 'wcvendors_version', WCV_VERSION );
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public function install_actions() {
		// if ( ! empty( $_GET['do_update_wcvendors'] ) ) {
		// $this->update();
		// WCVendors_Admin_Notices::add_notice( 'update' );
		// }
	}

	/**
	 * wcvendors_remove_roles function.
	 */
	public function remove_roles() {
		remove_role( 'pending_vendor' );
		remove_role( 'vendor' );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links
	 *
	 * @return  array
	 */
	public function plugin_action_links( $links ) {
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
	 * @return  array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( WCV_PLUGIN_BASENAME == $file ) {
			$row_meta = array(
				'docs'         => '<a href="' . esc_url( apply_filters( 'wcvendors_docs_url', 'https://docs.wc-vendors.com/' ) ) . '" aria-label="' . esc_attr__( 'View WC Vendors documentation', 'wc-vendors' ) . '">' . esc_html__( 'Docs', 'wc-vendors' ) . '</a>',
				'free-support' => '<a href="' . esc_url( apply_filters( 'wcvendors_free_support_url', 'https://wordpress.org/plugins/wc-vendors' ) ) . '" aria-label="' . esc_attr__( 'Visit community forums', 'wc-vendors' ) . '">' . esc_html__( 'Free support', 'wc-vendors' ) . '</a>',
				'support'      => '<a href="' . esc_url( apply_filters( 'wcvendors_support_url', 'https://wc-vendors.com/support/' ) ) . '" aria-label="' . esc_attr__( 'Visit premium customer support', 'wc-vendors' ) . '">' . esc_html__( 'Paid support', 'wc-vendors' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Create pages that the plugin relies on, storing page IDs in variables.
	 */
	public function create_pages() {

		$pages = apply_filters(
			'wcvendors_create_pages',
			array(
				'dashboard'    => array(
					'name'    => _x( 'dashboard', 'Page slug', 'wc-vendors' ),
					'title'   => _x( 'Dashboard', 'Page title', 'wc-vendors' ),
					'content' => '[' . apply_filters( 'wcvendors_dashboard_shortcode_tag', 'wcvendors_dashboard' ) . ']',
				),
				'vendors'      => array(
					'name'    => _x( 'vendors', 'Page slug', 'wc-vendors' ),
					'title'   => _x( 'Vendors', 'Page title', 'wc-vendors' ),
					'content' => '[' . apply_filters( 'wcvendors_stores_shortcode_tag', 'wcvendors_stores' ) . ']',
				),
				'vendor_terms' => array(
					'name'    => _x( 'vendor-terms', 'Page slug', 'wc-vendors' ),
					'title'   => _x( 'Vendor Terms & Conditions', 'Page title', 'wc-vendors' ),
					'content' => '',
				),
			)
		);

		foreach ( $pages as $key => $page ) {
			wc_create_page( esc_sql( $page['name'] ), 'wcvendors_' . $key . '_page_id', $page['title'], $page['content'], ! empty( $page['parent'] ) ? wc_get_page_id( $page['parent'] ) : '' );
		}
	}

	/**
	 * See if we need the wizard or not.
	 *
	 * @since 3.2.0
	 */
	private function maybe_enable_setup_wizard() {
		if ( apply_filters( 'wcvendors_enable_setup_wizard', $this->is_new_install() ) ) {
			// WCVendors_Admin_Notices::add_notice( 'install' );
			set_transient( '_wcv_activation_redirect', 1, 30 );
		}
	}

	/**
	 * Is this a brand new WC Vendors install?
	 *
	 * @return boolean
	 * @since 3.2.0
	 */
	private function is_new_install() {
		return is_null( get_option( 'wcvendors_version', null ) ) && is_null( get_option( 'wcvendors_db_version', null ) );
	}

}
