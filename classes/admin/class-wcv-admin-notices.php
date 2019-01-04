<?php
/**
 * Display notices in admin
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Admin_Notices Class.
 */
class WCVendors_Admin_Notices {

	/**
	 * Stores notices.
	 *
	 * @var array
	 */
	private static $notices = array();

	/**
	 * Array of notices - name => callback.
	 *
	 * @var array
	 */
	private static $core_notices
		= array(
			'install'        => 'install_notice',
			'update'         => 'update_notice',
			'template_files' => 'template_file_check_notice',
			'theme_support'  => 'theme_check_notice',
		);

	/**
	 * Constructor.
	 */
	public static function init() {

		self::$notices = get_option( 'wcvendors_admin_notices', array() );

		add_action( 'switch_theme'       , array( __CLASS__, 'reset_admin_notices' ) );
		add_action( 'wcvendors_installed', array( __CLASS__, 'reset_admin_notices' ) );
		add_action( 'wp_loaded'          , array( __CLASS__, 'hide_notices'        ) );
		add_action( 'shutdown'           , array( __CLASS__, 'store_notices'       ) );

		if ( current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_print_styles', array( __CLASS__, 'add_notices' ) );
		}
	}

	/**
	 * Store notices to DB
	 */
	public static function store_notices() {

		update_option( 'wcvendors_admin_notices', self::get_notices() );
	}

	/**
	 * Get notices
	 *
	 * @return array
	 */
	public static function get_notices() {

		return self::$notices;
	}

	/**
	 * Remove all notices.
	 */
	public static function remove_all_notices() {

		self::$notices = array();
	}

	/**
	 * Reset notices for themes when switched or a new version of WC is installed.
	 */
	public static function reset_admin_notices() {

		self::add_notice( 'template_files' );
	}

	/**
	 * Show a notice.
	 *
	 * @param string $name
	 */
	public static function add_notice( $name ) {

		self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );
	}

	/**
	 * Remove a notice from being displayed.
	 *
	 * @param  string $name
	 */
	public static function remove_notice( $name ) {

		self::$notices = array_diff( self::get_notices(), array( $name ) );
		delete_option( 'wcvendors_admin_notice_' . $name );
	}

	/**
	 * See if a notice is being shown.
	 *
	 * @param  string $name
	 *
	 * @return boolean
	 */
	public static function has_notice( $name ) {

		return in_array( $name, self::get_notices() );
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public static function hide_notices() {

		if ( isset( $_GET['wcv-hide-notice'] ) && isset( $_GET['_wcv_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_wcv_notice_nonce'], 'wcvendors_hide_notices_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'wc-vendors' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'wc-vendors' ) );
			}

			$hide_notice = sanitize_text_field( $_GET['wcv-hide-notice'] );
			self::remove_notice( $hide_notice );
			do_action( 'wcvendors_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Add notices + styles if needed.
	 */
	public static function add_notices() {

		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style( 'wcv-setup', wcv_assets_url . 'css/wcv-activation' . $suffix . '.css', WCV_VERSION );
			foreach ( $notices as $notice ) {
				if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'wcvendors_show_admin_notice', true, $notice ) ) {
					add_action( 'admin_notices', array( __CLASS__, self::$core_notices[ $notice ] ) );
				} else {
					add_action( 'admin_notices', array( __CLASS__, 'output_custom_notices' ) );
				}
			}
		}
	}

	/**
	 * Add a custom notice.
	 *
	 * @param string $name
	 * @param string $notice_html
	 */
	public static function add_custom_notice( $name, $notice_html ) {

		self::add_notice( $name );
		update_option( 'wcvendors_admin_notice_' . $name, wp_kses_post( $notice_html ) );
	}

	/**
	 * Output any stored custom notices.
	 */
	public static function output_custom_notices() {

		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				if ( empty( self::$core_notices[ $notice ] ) ) {
					$notice_html = get_option( 'wcvendors_admin_notice_' . $notice );

					if ( $notice_html ) {
						include 'views/notices/html-notice-custom.php';
					}
				}
			}
		}
	}

	/**
	 * If we need to update, include a message with the update button.
	 */
	public static function update_notice() {

		if ( version_compare( get_option( 'wcvendors_db_version' ), WCV_VERSION, '<' ) ) {
			$updater = new WCVendors_Background_Updater();
			if ( $updater->is_updating() || ! empty( $_GET['do_update_wcvendors'] ) ) {
				include 'views/notices/html-notice-updating.php';
			} else {
				include 'views/notices/html-notice-update.php';
			}
		} else {
			include 'views/notices/html-notice-updated.php';
		}
	}

	/**
	 * If we have just installed, show a message with the install pages button.
	 */
	public static function install_notice() {

		include 'views/notices/html-notice-install.php';
	}

	/**
	 * Show the Theme Check notice.
	 */
	public static function theme_check_notice() {

		if ( ! current_theme_supports( 'wcvendors' ) && ! in_array( get_option( 'template' ), wc_get_core_supported_themes() ) ) {
			include 'views/notices/html-notice-theme-support.php';
		} else {
			self::remove_notice( 'theme_support' );
		}
	}

	/**
	 * Show a notice highlighting bad template files.
	 */
	public static function template_file_check_notice() {

		$core_templates = WC_Admin_Status::scan_template_files( wcv_plugin_dir_path . '/templates' );
		$outdated       = false;

		foreach ( $core_templates as $file ) {

			$theme_file = false;
			if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
				$theme_file = get_stylesheet_directory() . '/' . $file;
			} elseif ( file_exists( get_stylesheet_directory() . '/wc-vendors/' . $file ) ) {
				$theme_file = get_stylesheet_directory() . '/wc-vendors/' . $file;
			} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
				$theme_file = get_template_directory() . '/' . $file;
			} elseif ( file_exists( get_template_directory() . '/wc-vendors/' . $file ) ) {
				$theme_file = get_template_directory() . '/wc-vendors/' . $file;
			}

			if ( false !== $theme_file ) {
				$core_version  = WC_Admin_Status::get_file_version( wcv_plugin_dir_path . '/templates/' . $file );
				$theme_version = WC_Admin_Status::get_file_version( $theme_file );

				if ( $core_version && $theme_version && version_compare( $theme_version, $core_version, '<' ) ) {
					$outdated = true;
					break;
				}
			}
		}

		if ( $outdated ) {
			include 'views/notices/html-notice-template-check.php';
		} else {
			self::remove_notice( 'template_files' );
		}
	}
}

WCVendors_Admin_Notices::init();
