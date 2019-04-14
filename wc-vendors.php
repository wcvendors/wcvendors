<?php
/**
 * Plugin Name:         WC Vendors
 * Plugin URI:          https://www.wcvendors.com
 * Description:         Create your own marketplace and allow users to sign up and sell products on your store. All while taking a commission!
 * Author:              WC Vendors
 * Author URI:          https://www.wcvendors.com
 * GitHub Plugin URI:   https://github.com/wcvendors/wcvendors
 *
 * Version:              2.0.0
 * Requires at least:    4.4.0
 * Tested up to:         4.9.0
 * WC requires at least: 3.0.0
 * WC tested up to:      3.2.0
 *
 * Text Domain:         wcvendors
 * Domain Path:         /languages/
 *
 * @category            Plugin
 * @copyright           Copyright © 2018 Jamie Madden, WC Vendors
 * @author              Jamie Madden, WC Vendors
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

define( 'WC_VENDORS', '2.0.0' );


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class WC_Vendors {

	/**
	 * WC Vendors version.
	 *
	 * @var string
	 */
	public $version = '2.0.0';

	/**
	 * The single instance of the class.
	 *
	 * @var WC Vendors
	 * @since 2.0
	 */
	protected static $instance = null;

	/**
	 * @var WC_Logger Reference to logging class.
	 */
	private static $log;

	/**
	 * @var bool Enable debug logging.
	 */
	public static $enable_logging;

	/**
	 * Notices (array)
	 * @var array
	 */
	public $notices = array();

	/**
	 * Main WC Vendors Instance.
	 *
	 * Ensures only one instance of WC Vendors is loaded or can be loaded.
	 *
	 * @since 2.0
	 * @static
	 * @return WC Vendors - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * WC Vendors Constructor.
	 */
	public function __construct() {

		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		// self::$enable_logging = apply_filters( 'wcvendors_enable_logging', get_option( 'wcvendors_enable_logging' ) );
		self::$enable_logging = true;

		do_action( 'wcvendors_loaded' );
	}

	/**
	 * Cloning is forbidden.
	 * @since 2.0
	 */
	public function __clone() {
		wc_doing_it_wrong( __FUNCTION__, __( 'No cloning allowed', 'wcvendors' ), '2.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since 2.0
	 */
	public function __wakeup() {
		wc_doing_it_wrong( __FUNCTION__, __( 'No wakeup allowed', 'wcvenodrs' ), '2.0' );
	}

	/**
	 * Auto-load in-accessible properties on demand.
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( in_array( $key, array( 'payment_gateways' ) ) ) {
			return $this->$key();
		}
	}

	/**
	 * Hook into actions and filters.
	 * @since  2.0
	 */
	private function init_hooks() {

		register_activation_hook( __FILE__, array( 'WCVendors_Install', 'install' ) );

		add_action( 'admin_init', 		array( $this, 'check_environment' ) );
		add_action( 'admin_notices', 	array( $this, 'admin_notices' ), 15 );
		add_action( 'init', 			array( $this, 'init' ) );

		add_filter( 'woocommerce_data_stores', 	array( $this, 'add_data_stores' ) );
		add_filter( 'wc_order_types', 			array( $this, 'add_order_types' ), 10, 2 );

	}

	/**
	 * The backup sanity check, in case the plugin is activated in a weird way,
	 * or the environment changes after activation. Also handles upgrade routines.
	 */
	public function check_environment() {

		$environment_warning = self::get_environment_warning();

		if ( $environment_warning && is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			$this->add_admin_notice( 'bad_environment', 'error', $environment_warning );
		}
	}

	/**
	 * Checks the environment for compatibility problems.  Returns a string with the first incompatibility
	 * found or false if the environment has no problems.
	 */
	static function get_environment_warning() {

		if ( version_compare( phpversion(), WCV_MIN_PHP_VER, '<' ) ) {
			$message = __( 'WC Vendors - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'wcvendors' );
			return sprintf( $message, WCV_MIN_PHP_VER, phpversion() );
		}

		if ( ! defined( 'WC_VERSION' ) ) {
			return __( 'WC Vendors requires WooCommerce to be activated to work.', 'wcvendors' );
		}

		if ( version_compare( WC_VERSION, WCV_MIN_WC_VER, '<' ) ) {
			$message = __( 'WC Vendors - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'wcvendors' );
			return sprintf( $message, WCV_MIN_WC_VER, WC_VERSION );
		}

		return false;
	}

	/**
	 * Allow this class and other classes to add slug keyed notices (to avoid duplication)
	 */
	public function add_admin_notice( $slug, $class, $message ) {
		$this->notices[ $slug ] = array(
			'class'   => $class,
			'message' => $message,
		);
	}

	/**
	 * Display any notices we've collected thus far (e.g. for connection, disconnection)
	 */
	public function admin_notices() {

		foreach ( (array) $this->notices as $notice_key => $notice ) {
			echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
			echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
			echo '</p></div>';
		}
	}


	/**
	 * Define WCV Constants.
	 */
	private function define_constants() {

		$this->define( 'WCV_MIN_PHP_VER', '5.6.0' );
		$this->define( 'WCV_MIN_WC_VER', '3.0.0' );
		$this->define( 'WCV_PLUGIN_FILE', __FILE__ );
		$this->define( 'WCV_ABSPATH', dirname( __FILE__ ) . '/' );
		$this->define( 'WCV_ABSPATH_ADMIN', dirname( __FILE__ ) . '/includes/admin/' );
		$this->define( 'WCV_ABSPATH_FRONTEND', dirname( __FILE__ ) . '/includes/frontend/' );
		$this->define( 'WCV_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'WCV_VERSION', $this->version );
		$this->define( 'WCV_TEMPLATE_DEBUG_MODE', false );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get the template path.
	 * @return string
	 */
	public function template_path() {
		return apply_filters( 'wcvendors_template_path', 'wc-vendors/' );
	}

	/**
	 * Get Ajax URL.
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		/**
		* 	Interfaces
		*/
		include_once( WCV_ABSPATH . 'includes/interfaces/class-wcv-commission-data-store-interface.php' );
		include_once( WCV_ABSPATH . 'includes/interfaces/class-wcv-vendor-order-data-store-interface.php' );

		/*
		* Data stores
		*/
		include_once( WCV_ABSPATH . 'includes/data-stores/class-wcv-commission-data-store.php' );
		include_once( WCV_ABSPATH . 'includes/data-stores/class-wcv-vendor-order-data-store.php' );

		/**
		 * Core Classes
		 */
		include_once( WCV_ABSPATH . 'includes/wcv-core-functions.php' );
		include_once( WCV_ABSPATH . 'includes/wcv-vendor-functions.php' );
		include_once( WCV_ABSPATH . 'includes/class-wcv-post-types.php' );
		include_once( WCV_ABSPATH . 'includes/class-wcv-install.php' );

		include_once( WCV_ABSPATH . 'includes/class-wcv-order.php' );
		include_once( WCV_ABSPATH . 'includes/class-wcv-commission.php' );
		include_once( WCV_ABSPATH . 'includes/class-wcv-commission-factory.php' );
		include_once( WCV_ABSPATH . 'includes/class-wcv-vendor-order.php' );

		/**
			v1 code to be audited
		**/
		// require_once WCV_ABSPATH . 'classes/class-queries.php';
		// require_once WCV_ABSPATH . 'classes/class-vendors.php';
		// require_once WCV_ABSPATH . 'classes/class-cron.php';
		// require_once WCV_ABSPATH . 'classes/class-commission.php';
		// require_once WCV_ABSPATH . 'classes/class-shipping.php';
		// require_once WCV_ABSPATH . 'classes/class-vendor-order.php';
		// require_once WCV_ABSPATH . 'classes/class-vendor-post-types.php';
		// require_once WCV_ABSPATH . 'classes/includes/class-wcv-shortcodes.php';

		// // Include depreciated gateways
		// require_once WCV_ABSPATH . 'classes/gateways/PayPal_AdvPayments/paypal_ap.php';
		// require_once WCV_ABSPATH . 'classes/gateways/PayPal_Masspay/class-paypal-masspay.php';
		// require_once WCV_ABSPATH . 'classes/gateways/WCV_Gateway_Test/class-wcv-gateway-test.php';

		if ( $this->is_request( 'admin' ) ) {
			include_once( WCV_ABSPATH . 'includes/admin/class-wcv-admin.php' );
		}

		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}

	}

	/**
	 * Include required frontend files.
	 */
	public function frontend_includes() {

		/** Legacy **/
		/** @depreciated v2.0.0 **/
		// require_once WCV_ABSPATH . 'classes/front/class-vendor-cart.php';
		// require_once WCV_ABSPATH . 'classes/front/dashboard/class-vendor-dashboard.php';
		// require_once WCV_ABSPATH . 'classes/front/class-vendor-shop.php';
		// require_once WCV_ABSPATH . 'classes/front/signup/class-vendor-signup.php';
		// require_once WCV_ABSPATH . 'classes/front/orders/class-orders.php';

		/** New front end classes **/

	}

	/**
	 * Include required frontend files.
	 */
	public function admin_includes() {

	}

	/**
	 * Init WooCommerce when WordPress Initialises.
	 */
	public function init() {

		if ( self::get_environment_warning() ) {
			return;
		}

		// Before init action.
		do_action( 'before_wcvendors_init' );

		// Set up localisation.
		$this->load_plugin_textdomain();

		// Load class instances.


		// Session class, handles session data for users - can be overwritten if custom handler is needed.
		if ( $this->is_request( 'frontend' ) || $this->is_request( 'cron' ) ) {
			$session_class  = apply_filters( 'wcvendors_session_handler', 'WC_Session_Handler' );
			$this->session  = new $session_class();
		}

		// Classes/actions loaded for the frontend and for ajax requests.
		if ( $this->is_request( 'frontend' ) ) {
			// load front end things here.
		}

		// Init action.
		do_action( 'wcvendors_init' );
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {

		// Don't allow anything to execute if the environment isn't configured correctly
		if ( self::get_environment_warning() ) {
			return;
		}

		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/wc-vendors/wcvendors-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/wcvendors-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'wcvendors' );

		load_textdomain( 'wcvendors', WP_LANG_DIR . '/wc-vendors/wcvendors-' . $locale . '.mo' );
		load_plugin_textdomain( 'wcvendors', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Register data stores for WooCommerce 3.0+
	 *
	 * @since 1.0.0
	 */
	public static function add_data_stores( $data_stores ) {
		$data_stores[ 'shop-order-vendor' ]            = 'WCVendors_Vendor_Order_Data_Store_CPT';
		$data_stores[ 'vendor-product' ]               = 'WCVendors_Vendor_Product_Data_Store_CPT';
		$data_stores[ 'vendor-commission' ]            = 'WCVendors_Commission_Data_Store_CPT';
		return $data_stores;
	}

	/**
	 * Register order types
	 *
	 * order types are filtered based on views on the front end and admin
	 * and ensures that order types are only used when required.
	 * The internal vendor orders do not need to be referenced for meta boxes so disable it.
	 * This needs to be done here because the filter for order types is called after the internal woo filtering is compelted.
	 *
	 * @since 1.0.0
	 *
	 */
	public static function add_order_types( $order_types, $for ){

		if ( 'order-meta-boxes' != $for ){
			$order_types[] = 'shop-order-vendor';
		}

		return $order_types;
	}

	/**
	* Get the version of the plugin
	*
	* @return string $version
	*/
	public function get_version(){
		return $this->version;
	}

	/**
	 * Class logger so that we can keep our debug and logging information cleaner
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param mixed - $data the data to go to the error log could be string, array or object
	 */
	public function log( $data = '', $pre = '' ){

		$trace 	= debug_backtrace( false, 2 );
		$caller = ( isset( $trace[ 1 ] ) ) ? array_key_exists( 'class', $trace[ 1 ] ) ? $trace[ 1 ][ 'class' ] : '' : '';

		if ( self::$enable_logging ){

				if ( empty( self::$log ) ) {
					self::$log = new WC_Logger();
				}

				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					if ( is_array( $data ) || is_object( $data ) ) {

						// Write to the log file for viewing in wp-admin
						self::$log->add( 'wc-vendors', '== ' . $pre .' : ' . $caller . '==' );
						self::$log->add( 'wc-vendors', $caller . ' : ' . print_r( $data, true ) );
						self::$log->add( 'wc-vendors','====' );

						// Output to the error log
						error_log( '===================    ' . $pre .' : ' . $caller . '   ======================' );
						error_log( $caller . ' : ' . print_r( $data, true ) );
						error_log( '===============================================================');
					} else {
						// Write to the log file for viewing in wp-admin
						self::$log->add( 'wc-vendors', '==   '  . $pre .' : ' . $caller . ' ==' );
						self::$log->add( 'wc-vendors', $caller  . ' : ' . $data );
						self::$log->add( 'wc-vendors', '====');

						// Output to debugging log
						error_log( '===================    '  . $pre .' : ' . $caller . '   ======================' );
						error_log( $caller  . ' : ' . $data );
						error_log( '===============================================================');
					}
				}
		}
	}

} // End final class

/**
 * Main instance of WC Vendors
 *
 */
function WCVendors(){
	return WC_Vendors::instance();
}

add_action( 'plugins_loaded', 'WCVendors' );
