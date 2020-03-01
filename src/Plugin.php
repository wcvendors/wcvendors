<?php
/**
 * Init classes and setup our plugin.
 *
 * @package WCVendors
 */

namespace WCVendors;

/**
 * Init classes and setup our plugin.
 */
class Plugin {
	/**
	 * Plugin constructor.
	 */
	public function __construct() {
		if ( $this->get_environment_warning() ) {
			add_action(
				'admin_notices',
				function () {
					printf(
						'<div class="notice notice-error"><p>%s</p></div>',
						esc_html( $this->get_environment_warning() )
					);
				}
			);
		}

		do_action( 'before_wcvendors_init' );

		$this->load_textdomain();
		$this->init_hooks();
		$this->init_classes();

		do_action( 'after_wcvendors_init' );
	}

	/**
	 * Load the plugin text domain.
	 */
	private function load_textdomain() {
		load_plugin_textdomain( 'wc-vendors', false, WCV_PLUGIN_BASENAME . '/languages' );
	}

	/**
	 * We need to register order type and data store early.
	 */
	private function init_hooks() {
		add_filter( 'woocommerce_data_stores', array( $this, 'add_data_stores' ) );
		add_filter( 'wc_order_types', array( $this, 'add_order_types' ), 10, 2 );
	}

	/**
	 * Init required classes for this plugin to function.
	 */
	private function init_classes() {
		$logger = new Logger();
		( new PostTypes() )->init_hooks();
		( new Install() )->init_hooks();
		( new OrderHandler( $logger ) )->init_hooks();

		$this->init_admin_classes();

		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_includes();
		}
	}

	/**
	 * Init admin classes.
	 */
	private function init_admin_classes() {
		Admin\Notices::init();
		( new Admin\Admin() )->init_hooks();
		( new Admin\Assets() )->init_hooks();
		( new Admin\Menus() )->init_hooks();
		( new Admin\PermalinkSettings() )->init_hooks();
		( new Admin\PostTypes() )->init_hooks();
		( new Admin\Vendor() )->init_hooks();
		( new Admin\MetaBoxes() )->init_hooks();
	}

	/**
	 * Init frontend classes. 
	 */
	private function frontend_includes(){ 

		( new Front\Registration() )->init_hooks(); 
	}

	/**
	 * Register data stores for WooCommerce 3.0+
	 *
	 * @param array $data_stores WooCommerce Data store classes.
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function add_data_stores( $data_stores ) {
		$data_stores['shop-order-vendor'] = new DataStores\VendorOrder();
		$data_stores['vendor-commission'] = new DataStores\VendorCommission();

		return $data_stores;
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Register order types
	 *
	 * Order types are filtered based on views on the front end and admin
	 * and ensures that order types are only used when required.
	 * The internal vendor orders do not need to be referenced for meta boxes so disable it.
	 * This needs to be done here because the filter for order types is called after
	 * the internal woo filtering is completed.
	 *
	 * @param array  $order_types WooCommerce order types.
	 * @param string $for View id.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function add_order_types( $order_types, $for ) {

		if ( 'order-meta-boxes' !== $for ) {
			$order_types[] = 'shop-order-vendor';
		}

		return $order_types;
	}

	/**
	 * Checks the environment for compatibility problems.  Returns a string with the first incompatibility
	 * found or false if the environment has no problems.
	 *
	 * @noinspection PhpUndefinedConstantInspection
	 */
	private function get_environment_warning() {
		$output = '';

		if ( version_compare( phpversion(), WCV_MIN_PHP_VER, '<' ) ) {
			/* translators: %1$s: the minimum PHP version, %2$s: the current PHP version. */
			$message = __( 'WC Vendors - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'wc-vendors' );
			$output  = sprintf( $message, WCV_MIN_PHP_VER, phpversion() );
		}

		if ( ! defined( 'WC_VERSION' ) ) {
			$output = __( 'WC Vendors requires WooCommerce to be activated to work.', 'wc-vendors' );
		}

		if ( version_compare( WC_VERSION, WCV_MIN_WC_VER, '<' ) ) {
			/* translators: %1$s: the minimum WooCommerce version, %2$s: the current WooCommerce version. */
			$message = __( 'WC Vendors - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'wc-vendors' );
			$output  = sprintf( $message, WCV_MIN_WC_VER, WC_VERSION );
		}

		return $output;
	}
}
