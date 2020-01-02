<?php
/**
 * Admin setup wziard
 *
 * @author      WooCommerce, Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCVendors_Admin_Setup_Wizard class.
 */
class WCVendors_Admin_Setup_Wizard {

	/**
	 * Current step
	 *
	 * @var string
	 */
	private $step = '';

	/**
	 * Steps for the setup wizard
	 *
	 * @var array
	 */
	private $steps = array();

	/**
	 * Actions to be executed after the HTTP response has completed
	 *
	 * @var array
	 */
	private $deferred_actions = array();

	/**
	 * Hook in tabs.
	 */
	public function __construct() {

		if ( apply_filters( 'wcv_enable_setup_wizard', true ) && current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menus' ) );
			add_action( 'admin_init', array( $this, 'setup_wizard' ) );
			add_action( 'admin_head', array( $this, 'hide_setup_wizard' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {

		add_dashboard_page( '', '', 'manage_options', 'wcv-setup', '' );
	}

	/**
	 * Hide the setup wizard menu item.
	 */
	public function hide_setup_wizard() {

		remove_submenu_page( 'index.php', 'wcv-setup' );
	}

	/**
	 * Show the setup wizard.
	 */
	public function setup_wizard() {

		if ( empty( $_GET['page'] ) || 'wcv-setup' !== $_GET['page'] ) {
			return;
		}
		$default_steps = array(
			'store_setup'  => array(
				'name'    => __( 'Start', 'wc-vendors' ),
				'view'    => array( $this, 'wcv_setup_general' ),
				'handler' => array( $this, 'wcv_setup_general_save' ),
			),
			'capabilities' => array(
				'name'    => __( 'Capabilities', 'wc-vendors' ),
				'view'    => array( $this, 'wcv_setup_capabilities' ),
				'handler' => array( $this, 'wcv_setup_capabilities_save' ),
			),
			'pages'        => array(
				'name'    => __( 'Pages', 'wc-vendors' ),
				'view'    => array( $this, 'wcv_setup_pages' ),
				'handler' => array( $this, 'wcv_setup_pages_save' ),
			),
			'ready'        => array(
				'name'    => __( 'Ready!', 'wc-vendors' ),
				'view'    => array( $this, 'wcv_setup_ready' ),
				'handler' => '',
			),
		);

		$this->steps = apply_filters( 'wcv_setup_wizard_steps', $default_steps );
		$this->step  = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
		$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_register_script( 'selectWoo', WC()->plugin_url() . '/assets/js/selectWoo/selectWoo.full' . $suffix . '.js', array( 'jquery' ), '1.0.0' );
		wp_register_script(
			'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array(
			'jquery',
			'selectWoo',
		), WC_VERSION
		);
		wp_localize_script(
			'wc-enhanced-select', 'wc_enhanced_select_params', array(
				'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'wc-vendors' ),
				'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'wc-vendors' ),
				'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'wc-vendors' ),
				'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'wc-vendors' ),
				'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'wc-vendors' ),
				'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'wc-vendors' ),
				'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'wc-vendors' ),
				'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'wc-vendors' ),
				'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'wc-vendors' ),
				'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'wc-vendors' ),
				'ajax_url'                  => admin_url( 'admin-ajax.php' ),
				'search_products_nonce'     => wp_create_nonce( 'search-products' ),
				'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
			)
		);
		// @todo fix the select2 styles in our admin.css
		wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );

		wp_enqueue_style(
			'wcv-setup', wcv_assets_url . 'css/wcv-setup.css', array(
			'dashicons',
			'install',
		), WCV_VERSION
		);
		wp_register_script(
			'wcv-setup', wcv_assets_url . 'js/admin/wcv-setup.js', array(
			'jquery',
			'wc-enhanced-select',
			'jquery-blockui',
			'wp-util',
		), WCV_VERSION
		);

		if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
		}

		ob_start();
		$this->setup_wizard_header();
		$this->setup_wizard_steps();
		$this->setup_wizard_content();
		$this->setup_wizard_footer();
		exit;
	}

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @param string $step slug (default: current step).
	 *
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 * @since 2.0.0
	 */
	public function get_next_step_link( $step = '' ) {

		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );
		if ( end( $keys ) === $step ) {
			return admin_url();
		}

		$step_index = array_search( $step, $keys );
		if ( false === $step_index ) {
			return '';
		}

		return add_query_arg( 'step', $keys[ $step_index + 1 ], remove_query_arg( 'activate_error' ) );
	}

	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {

		include WCV_ABSPATH_ADMIN . 'views/setup/header.php';
	}

	/**
	 * Setup Wizard Footer.
	 */
	public function setup_wizard_footer() {

		include WCV_ABSPATH_ADMIN . 'views/setup/footer.php';
	}

	/**
	 * Output the steps.
	 */
	public function setup_wizard_steps() {

		$output_steps = $this->steps;
		include WCV_ABSPATH_ADMIN . 'views/setup/steps.php';
	}

	/**
	 * Output the content for the current step.
	 */
	public function setup_wizard_content() {

		echo '<div class="wcv-setup-content">';
		if ( ! empty( $this->steps[ $this->step ]['view'] ) ) {
			call_user_func( $this->steps[ $this->step ]['view'], $this );
		}
		echo '</div>';
	}

	/**
	 * Helper method to retrieve the current user's email address.
	 *
	 * @return string Email address
	 */
	protected function get_current_user_email() {

		$current_user = wp_get_current_user();
		$user_email   = $current_user->user_email;

		return $user_email;
	}

	/**
	 * Helper method to retrieve the current user's firt name
	 *
	 * @return string Email address
	 */
	protected function get_current_user_firstname() {

		$current_user = wp_get_current_user();
		$first_name   = $current_user->user_firstname;

		return $first_name;
	}

	/**
	 * Initial "marketplace setup" step.
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_general() {

		$allow_registration = get_option( 'wcvendors_vendor_allow_registration', 'yes' );
		$manual_approval    = get_option( 'wcvendors_vendor_approve_registration', 'no' );
		$vendor_taxes       = get_option( 'wcvendors_vendor_give_taxes', 'no' );
		$vendor_shipping    = get_option( 'wcvendors_vendor_give_shipping', 'no' );
		$commission_rate    = get_option( 'wcvendors_vendor_commission_rate', '' );

		include WCV_ABSPATH_ADMIN . 'views/setup/general.php';
	}

	/**
	 * Save initial marketplace settings.
	 */
	public function wcv_setup_general_save() {

		check_admin_referer( 'wcv-setup' );

		$allow_registration = isset( $_POST['wcv_vendor_allow_registration'] ) ? sanitize_text_field( $_POST['wcv_vendor_allow_registration'] ) : '';
		$manual_approval    = isset( $_POST['wcv_vendor_approve_registration'] ) ? sanitize_text_field( $_POST['wcv_vendor_approve_registration'] ) : '';
		$vendor_taxes       = isset( $_POST['wcv_vendor_give_taxes'] ) ? sanitize_text_field( $_POST['wcv_vendor_give_taxes'] ) : '';
		$vendor_shipping    = isset( $_POST['wcv_vendor_give_shipping'] ) ? sanitize_text_field( $_POST['wcv_vendor_give_shipping'] ) : '';
		$commission_rate    = sanitize_text_field( $_POST['wcv_vendor_commission_rate'] );

		update_option( 'wcvendors_vendor_allow_registration', $allow_registration );
		update_option( 'wcvendors_vendor_approve_registration', $manual_approval );
		update_option( 'wcvendors_vendor_give_taxes', $vendor_taxes );
		update_option( 'wcvendors_vendor_give_shipping', $vendor_shipping );
		update_option( 'wcvendors_vendor_commission_rate', $commission_rate );

		WCVendors_Install::create_pages();
		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * General setup
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_capabilities() {

		$products_enabled  = get_option( 'wcvendors_capability_products_enabled', 'yes' );
		$live_products     = get_option( 'wcvendors_capability_products_edit', 'yes' );
		$products_approval = get_option( 'wcvendors_capability_products_live', 'yes' );
		$orders_enabled    = get_option( 'wcvendors_capability_orders_enabled', 'yes' );
		$export_orders     = get_option( 'wcvendors_capability_orders_export', 'yes' );
		$view_order_notes  = get_option( 'wcvendors_capability_order_read_notes', 'yes' );
		$add_order_notes   = get_option( 'wcvendors_capability_order_update_notes', 'yes' );

		include WCV_ABSPATH_ADMIN . 'views/setup/capabilities.php';
	}

	/**
	 * Save capabilities settings.
	 */
	public function wcv_setup_capabilities_save() {

		check_admin_referer( 'wcv-setup' );

		$products_enabled  = isset( $_POST['wcv_capability_products_enabled'] ) ? sanitize_text_field( $_POST['wcv_capability_products_enabled'] ) : '';
		$live_products     = isset( $_POST['wcv_capability_products_edit'] ) ? sanitize_text_field( $_POST['wcv_capability_products_edit'] ) : '';
		$products_approval = isset( $_POST['wcv_capability_products_live'] ) ? sanitize_text_field( $_POST['wcv_capability_products_live'] ) : '';
		$orders_enabled    = isset( $_POST['wcv_capability_orders_enabled'] ) ? sanitize_text_field( $_POST['wcv_capability_orders_enabled'] ) : '';
		$export_orders     = isset( $_POST['wcv_capability_orders_export'] ) ? sanitize_text_field( $_POST['wcv_capability_orders_export'] ) : '';
		$view_order_notes  = isset( $_POST['wcv_capability_order_read_notes'] ) ? sanitize_text_field( $_POST['wcv_capability_order_read_notes'] ) : '';
		$add_order_notes   = isset( $_POST['wcv_capability_order_update_notes'] ) ? sanitize_text_field( $_POST['wcv_capability_order_update_notes'] ) : '';

		update_option( 'wcvendors_capability_products_enabled', $products_enabled );
		update_option( 'wcvendors_capability_products_edit', $live_products );
		update_option( 'wcvendors_capability_products_live', $products_approval );
		update_option( 'wcvendors_capability_orders_enabled', $orders_enabled );
		update_option( 'wcvendors_capability_orders_export', $export_orders );
		update_option( 'wcvendors_capability_order_read_notes', $view_order_notes );
		update_option( 'wcvendors_capability_order_update_notes', $add_order_notes );

		// Update actual role
		$args = array(
			'assign_product_terms'      => $products_enabled,
			'edit_products'             => $products_enabled || $live_products,
			'edit_product'              => $products_enabled || $live_products,
			'edit_published_products'   => $live_products,
			'delete_published_products' => $live_products,
			'delete_products'           => $live_products,
			'manage_product'            => $products_enabled,
			'publish_products'          => $products_approval,
			'delete_posts'              => true,
			'read'                      => true,
			'read_products'             => $live_products || $products_enabled,
			'upload_files'              => true,
			'import'                    => true,
			'view_woocommerce_reports'  => false,
		);

		remove_role( 'vendor' );
		add_role( 'vendor', sprintf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ), $args );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Initial "marketplace setup" step.
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_pages() {

		$vendor_dashboard_page_id = get_option( 'wcvendors_vendor_dashboard_page_id' );
		$shop_settings_page_id    = get_option( 'wcvendors_shop_settings_page_id' );
		$product_orders_page_id   = get_option( 'wcvendors_product_orders_page_id' );
		$vendors_page_id          = get_option( 'wcvendors_vendors_page_id' );
		$terms_page_id            = get_option( 'wcvendors_vendor_terms_page_id' );

		include WCV_ABSPATH_ADMIN . 'views/setup/pages.php';
	}

	/**
	 * Initial "marketplace setup" step.
	 * Vendor registration, taxes and shipping
	 */
	public function wcv_setup_pages_save() {

		$vendor_dashboard_page_id = sanitize_text_field( $_POST['wcvendors_vendor_dashboard_page_id'] );
		$shop_settings_page_id    = sanitize_text_field( $_POST['wcvendors_shop_settings_page_id'] );
		$product_orders_page_id   = sanitize_text_field( $_POST['wcvendors_product_orders_page_id'] );
		$vendors_page_id          = sanitize_text_field( $_POST['wcvendors_vendors_page_id'] );
		$terms_page_id            = sanitize_text_field( $_POST['wcvendors_vendor_terms_page_id'] );

		update_option( 'wcvendors_vendor_dashboard_page_id', $vendor_dashboard_page_id );
		update_option( 'wcvendors_shop_settings_page_id', $shop_settings_page_id );
		update_option( 'wcvendors_product_orders_page_id', $product_orders_page_id );
		update_option( 'wcvendors_vendors_page_id', $vendors_page_id );
		update_option( 'wcvendors_vendor_terms_page_id', $terms_page_id );

		wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;

	}

	/**
	 * Final step.
	 */
	public function wcv_setup_ready() {

		WCVendors_Admin_Notices::remove_notice( 'install' );
		WCVendors_Install::update_db_version();
		flush_rewrite_rules();

		update_option( 'wcvendors_wizard_complete', current_time( 'mysql' ) ); 

		$user_email = $this->get_current_user_email();
		$first_name = $this->get_current_user_firstname();
		$forums     = 'https://wordpress.org/support/plugin/wc-vendors';
		$docs_url   = 'https://docs.wcvendors.com/?utm_source=setup_wizard&utm_medium=plugin&utm_campaign=setup_complete';
		$help_text  = sprintf(
		/* translators: %1$s: link to videos, %2$s: link to docs */
			__( 'Don\'t forget to check our <a href="%1$s" target="_blank">documentation</a> to learn more about setting up WC Vendors and if you need help, be sure to visit our <a href="%2$s" target="_blank">free support forums</a>.', 'wc-vendors' ),
			$docs_url,
			$forums
		);

		include WCV_ABSPATH_ADMIN . 'views/setup/ready.php';
	}
}

new WCVendors_Admin_Setup_Wizard();
