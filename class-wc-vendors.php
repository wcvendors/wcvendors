<?php
/**
 * Plugin Name:          WC Vendors
 * Plugin URI:           https://www.wcvendors.com
 * Description:          Allow vendors to sell their own products and receive a commission for each sale.
 * Author:               WC Vendors
 * Author URI:           https://www.wcvendors.com
 * GitHub Plugin URI:    https://github.com/wcvendors/wcvendors
 *
 * Version:              2.0.3
 * Requires at least:    4.4.0
 * Tested up to:         4.9.5
 * WC requires at least: 3.0.0
 * WC tested up to: 	 3.3.5
 *
 * Text Domain:         wc-vendors
 * Domain Path:         /languages/
 *
 * @category            Plugin
 * @copyright           Copyright © 2012 Matt Gates
 * @copyright           Copyright © 2018 WC Vendors
 * @author              Matt Gates, WC Vendors
 * @package             WCVendors
 * @license     		GPL2

WC Vendors is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

WC Vendors is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WC Vendors. If not, see http://www.gnu.org/licenses/gpl-2.0.txt.

*/


/**
 *   Plugin activation hook
 */
function wcvendors_activate() {

	/**
	 *  Requires woocommerce to be installed and active
	 */
	if ( !class_exists( 'WooCommerce' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( __( 'WC Vendors requires WooCommerce to run. Please install WooCommerce and activate before attempting to activate again.', 'wc-vendors' ) );
	}
} // wcvendors_activate()

register_activation_hook( __FILE__, 'wcvendors_activate' );


/**
 * Required functions
 */
require_once trailingslashit( dirname( __FILE__ ) ) . 'classes/includes/class-functions.php';

/**
 * Check if WooCommerce is active
 */
if ( wcv_is_woocommerce_activated() ) {

	/* Define an absolute path to our plugin directory. */
	if ( !defined( 'wcv_plugin_dir' ) ) 		define( 'wcv_plugin_dir', trailingslashit( dirname( __FILE__ ) ) );
	if ( !defined( 'wcv_assets_url' ) ) 		define( 'wcv_assets_url', trailingslashit( plugins_url( 'assets', __FILE__ ) ) );
	if ( !defined( 'wcv_plugin_base' ) ) 		define( 'wcv_plugin_base', plugin_basename( __FILE__ ) );
	if ( !defined( 'wcv_plugin_dir_path' ) )	define( 'wcv_plugin_dir_path', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

	/**
	 * Main Product Vendor class
	 *
	 * @package WCVendors
	 */
	class WC_Vendors
	{

		public $version = '2.0.3';

		/**
		 * @var
		 */
		public static $pv_options;
		public static $id = 'wc_prd_vendor';

		/**
		 * Constructor.
		 */
		public function __construct()
		{

			// Load text domain
			add_action( 'plugins_loaded', array( $this, 'load_il8n' ) );

			$this->title = __( 'WC Vendors', 'wc-vendors' );

			$this->define_constants();

			// Install & upgrade
			add_action( 'admin_init', 						array( $this, 'check_install' ) );
			add_action( 'init', 							array( $this, 'maybe_flush_permalinks' ), 99 );
			add_action( 'wcvendors_flush_rewrite_rules', 	array( $this, 'flush_rewrite_rules' ) );
			add_action( 'admin_init', 						array( $this, 'wcv_required_ignore_notices' ) );

			add_action( 'plugins_loaded', array( $this, 'include_gateways' ) );
			add_action( 'plugins_loaded', array( $this, 'include_core' ) );
			add_action( 'init', 		  array( $this, 'include_init' ) );
			add_action( 'current_screen', array( $this, 'include_assets' ) );

			// Start a PHP session, if not yet started then destroy if logged in or out
			add_action( 'init', 		array( $this, 'init_session'), 1 );
			add_action( 'wp_logout', 	array( $this, 'destroy_session') );
			add_action( 'wp_login', 	array( $this, 'destroy_session') );

			// Legacy settings
			add_action( 'admin_init', 	array( 'WCVendors_Install', 'check_pro_version' ) );
			add_action( 'plugins_loaded', array( $this, 'load_legacy_settings' ) );

			// Show update notices
			$file   = basename( __FILE__ );
			$folder = basename( dirname( __FILE__ ) );
			$hook = "in_plugin_update_message-{$folder}/{$file}";
			add_action( $hook, array( $this, 'show_upgrade_notification') , 10, 2);

		}


		/**
		 *
		 */
		public function invalid_wc_version() {
			echo '<div class="error"><p>' . __( '<b>WC Vendors is inactive</b>. WC Vendors requires a minimum of WooCommerce 3.0.0 to operate.', 'wc-vendors' ) . '</p></div>';
		}

		/**
		 * Define WC Constants.
		 */
		private function define_constants() {

			$this->define( 'WCV_VERSION', $this->version );
			$this->define( 'WCV_TEMPLATE_BASE', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
			$this->define( 'WCV_ABSPATH_ADMIN', dirname( __FILE__ ) . '/classes/admin/');

		}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}


		/**
		 *  Start the session
		 */
		public function init_session(){

			 if ( !session_id() && is_user_logged_in() ) {
        		session_start();
    		 }

		} //init_session()

		public function destroy_session(){

			 if ( session_id() ) {
        		session_destroy();
    		 }

		} // destroy_session()


		/**
		 * Check whether install has ran before or not
		 *
		 * Run install if it hasn't.
		 *
		 * @return unknown
		 */
		public function check_install()	{

			if ( version_compare( WC_VERSION, '3.0.0', '<' ) ) {
				add_action( 'admin_notices', array( $this, 'invalid_wc_version' ) );
				deactivate_plugins( plugin_basename( __FILE__ ) );
				return false;
			}

		}


		/**
		 * Set static $pv_options to hold options class
		 */
		public function load_legacy_settings() {
			if ( empty( self::$pv_options ) ) {
				include_once( wcv_plugin_dir . 'classes/includes/class-sf-settings.php' );
				self::$pv_options = new SF_Settings_API();
			}
		}

		public function load_il8n() {
		    $locale = apply_filters( 'plugin_locale', get_locale(), 'wc-vendors' );
		    load_textdomain( 'wc-vendors', WP_LANG_DIR.'/wc-vendors/wc-vendors-'.$locale.'.mo');
			load_plugin_textdomain( 'wc-vendors', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );

		}

		/**
		 * Include core files
		 */
		public function include_core() {

			include_once( wcv_plugin_dir . 'classes/class-install.php' );
			include_once( wcv_plugin_dir . 'classes/class-queries.php');
			include_once( wcv_plugin_dir . 'classes/class-vendors.php');
			include_once( wcv_plugin_dir . 'classes/class-cron.php');
			include_once( wcv_plugin_dir . 'classes/class-commission.php');
			include_once( wcv_plugin_dir . 'classes/class-shipping.php');
			include_once( wcv_plugin_dir . 'classes/class-vendor-order.php');
			include_once( wcv_plugin_dir . 'classes/class-vendor-post-types.php');
			include_once( wcv_plugin_dir . 'classes/front/class-vendor-cart.php');
			include_once( wcv_plugin_dir . 'classes/front/dashboard/class-vendor-dashboard.php');
			include_once( wcv_plugin_dir . 'classes/front/class-vendor-shop.php');
			include_once( wcv_plugin_dir . 'classes/front/signup/class-vendor-signup.php');
			include_once( wcv_plugin_dir . 'classes/front/orders/class-orders.php');
			include_once( wcv_plugin_dir . 'classes/admin/emails/class-emails.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-vendor-applicants.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-admin-reports.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-wcv-commissions-page.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-wcv-admin-setup.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-wcv-admin-notices.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-wcv-admin-settings.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-admin-menus.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-wcv-admin-extensions.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-wcv-admin-help.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-setup-wizard.php');
			include_once( wcv_plugin_dir . 'classes/admin/class-vendor-admin-dashboard.php');
			include_once( wcv_plugin_dir . 'classes/includes/class-wcv-shortcodes.php');
			include_once( wcv_plugin_dir . 'classes/includes/wcv-update-functions.php');
			include_once( wcv_plugin_dir . 'classes/includes/wcv-template-functions.php');

			// Include


			if ( !function_exists( 'woocommerce_wp_text_input' ) && !is_admin() ) {
				include_once( WC()->plugin_path() . '/includes/admin/wc-meta-box-functions.php' );
			}

			new WCV_Vendors;
			new WCV_Vendor_Shop;
			new WCV_Vendor_Cart;
			new WCV_Commission;
			new WCV_Shipping;
			new WCV_Cron;
			new WCV_Orders;
			new WCV_Vendor_Dashboard;
			new WCV_Admin_Setup;
			new WCV_Vendor_Admin_Dashboard;
			new WCV_Admin_Reports;
			new WCV_Vendor_Applicants;
			new WCV_Emails;
			new WCV_Vendor_Signup;
			new WCV_Shortcodes;
		}


		/**
		 * These need to be initlized later in loading to fix interaction with other plugins that call current_user_can at the right time.
		 *
		 * @since 1.9.4
		 * @access public
		 */
		public function include_init(){

			require_once wcv_plugin_dir . 'classes/admin/class-vendor-reports.php';
			require_once wcv_plugin_dir . 'classes/admin/class-product-meta.php';
			require_once wcv_plugin_dir . 'classes/admin/class-admin-users.php';


			new WCV_Vendor_Reports;
			new WCV_Product_Meta;
			new WCV_Admin_Users;

		} // include_init()

		/**
		*	Load plugin assets
		*/
		public function include_assets(){

			$screen = get_current_screen();

			if ( in_array( $screen->id, array( 'edit-product' ) ) ) {
				wp_enqueue_script( 'wcv_quick-edit', wcv_assets_url. 'js/wcv-admin-quick-edit.js', array('jquery') );
			}

		}


		/**
		 * Include payment gateways
		 */
		public function include_gateways()
		{
			require_once wcv_plugin_dir . 'classes/gateways/PayPal_AdvPayments/paypal_ap.php';
			require_once wcv_plugin_dir . 'classes/gateways/PayPal_Masspay/class-paypal-masspay.php';
			require_once wcv_plugin_dir . 'classes/gateways/WCV_Gateway_Test/class-wcv-gateway-test.php';
		}

		/**
		 *  If the settings are updated and the vendor page link has changed update permalinks
		 *	@access public
		 *
		*/
		public function maybe_flush_permalinks() {
			if ( 'yes' === get_option( 'wcvendors_queue_flush_rewrite_rules' ) ) {
				$this->flush_rewrite_rules();
				update_option( 'wcvendors_queue_flush_rewrite_rules', 'no' );
			}
		}

		public function flush_rewrite_rules(){
			flush_rewrite_rules();
		}


		/**
		 * Add user meta to remember ignore notices
		 * @access public
         *
		 */
		public function wcv_required_ignore_notices(){
			global $current_user;
    		$current_user_id = $current_user->ID;

	        /* If user clicks to ignore the notice, add that to their user meta */
	        if ( isset( $_GET[ 'wcv_shop_ignore_notice' ] ) && '0' == $_GET[ 'wcv_shop_ignore_notice' ] ) {
	            add_user_meta( $current_user_id, 'wcv_shop_ignore_notice', 'true', true);
	    	}
			if ( isset($_GET['wcv_pl_ignore_notice']) && '0' == $_GET['wcv_pl_ignore_notice'] ) {
			 	add_user_meta( $current_user_id, 'wcv_pl_ignore_notice', 'true' , true);
			}

		}

		/**
		 * Class logger so that we can keep our debug and logging information cleaner
		 *
		 * @since 2.0.0
		 * @version 2.0.0
		 * @access public
		 *
		 * @param mixed - $data the data to go to the error log could be string, array or object
		 */
		public static function log( $data = '', $prefix = '' ){

			$trace 		= debug_backtrace( false, 2 );
			$caller 	= ( isset( $trace[ 1 ]['class'] ) ) ? $trace[ 1 ]['class'] : basename( $trace[ 1 ][ 'file' ] );

			if ( is_array( $data ) || is_object( $data ) ) {
				if ( $prefix ){
					error_log( '===========================' );
					error_log( $prefix );
					error_log( '===========================' );
				}
				error_log( $caller . ' : ' . print_r( $data, true ) );
			} else {
				if ( $prefix ){
					error_log( '===========================' );
					error_log( $prefix );
					error_log( '===========================' );
				}
				error_log( $caller  . ' : ' . $data );
			}

		} // log()


		/*
		* Upgrade notice displayed on the plugin screen
		*
		*/
		public function show_upgrade_notification( $args, $response ) {

			$new_version            = $response->new_version;
			$upgrade_notice 		= sprintf( __( 'WC Vendors 2.0 is a major update. This is not compatible with any of our existing extensions. You should test this update on a staging server before updating. Backup your site and update your theme and extensions, and <a href="%s">review update details here</a> before upgrading.', 'wc-vendors' ), 'https://docs.wcvendors.com/knowledge-base/upgrading-to-wc-vendors-2-0/');

			if ( version_compare( WCV_VERSION, $new_version, '<' ) && version_compare( $new_version, '2.0.0', '>=') ){
				echo '<h3>Important Upgrade Notice:</h3>';
				echo '<p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px">';
		        echo $upgrade_notice;
		        if ( !class_exists( 'WCVendors_Pro' ) ) echo '</p>';

		        if ( class_exists( 'WCVendors_Pro' ) ){

					if ( version_compare( WCV_PRO_VERSION, '1.5.0', '<' ) ){
						echo '<h3>WC Vendors Pro Notice</h3>';
						echo '<p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px">';
						$pro_upgrade = sprintf( __( 'WC Vendors Pro 1.5.0 is required to run WC Vendors 2.0.0. Your current version %s will be deactivated. Please upgrade to the latest version.', 'wc-vendors' ), WCV_PRO_VERSION );

						echo $pro_upgrade;
						// echo '</p>';
					}

				}

			}
		} // show_upgrade_notification()

	}

	$wc_vendors = new WC_Vendors;

}
