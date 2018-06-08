<?php
/**
 * Add vendor signup option on WooCommerce My Account Page
 *
 * @author Lindeni Mahlalela <https://lindeni.co.za>
 * @package WCVendors
 */
class WCV_Account_Links extends WCV_Vendor_Signup{

    public $terms_page;

    /**
     * Constructor 
     *
     * @description adds the action hooks and gets the terms page
     * @package
     * @since
     */
    function __construct(){
        $this->terms_page = get_option( 'wcvendors_vendor_terms_page_id' );

        add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_menu_items') );
        add_action( 'init', array( $this, 'add_rewrite_endpoint' ) );
        add_action( 'woocommerce_account_become-a-vendor_endpoint', array( $this, 'render_vendor_signup' ) );
        add_filter( 'query_vars', array( $this, 'query_vars'), 0 );
        add_action( 'after_switch_theme', array( $this, 'flush_rewrite_rules') );
    }

    /**
     * Add accounts menu item
     *
     * @param array $items
     * @return void
     * @description Add Become a Vendor Link to my accounts navigation
     * @package
     * @since
     */
    function add_account_menu_items( $items ) {
        $add_items = array( 
            'become-a-vendor' => __( 'Become a Vendor', 'wc-vendors')
        );

        //slice the array so the logout link goes at the end of the list
        $first_part = array_slice( $items, 0, count( $items ) - 1, true);
        $last_part = array_slice( $items, count( $items ) - 1, true);

        //put the arrays together putting the logout link at the end
        $items = $first_part + $add_items + $last_part;

        return $items;
    }

    /**
     * Add the become-a-vendor in the global query object
     *
     * @param array $vars
     * @return void
     */
    function query_vars( $vars ){
        $vars[] = 'become-a-vendor';
        return $vars;
    }

    /**
     * Flushes rewrite rules when a theme is changed
     *
     * @return void
     */
    function flush_rewrite_rules(){
        flush_rewrite_rules();
    }

    /**
     * Add rewrite endpoint
     *
     * @return void
     */
    function add_rewrite_endpoint(){
        add_rewrite_endpoint( 'become-a-vendor', EP_PAGES );
    }

    /**
     * Render the become a vendor signup page in the my account page
     *
     * @return void
     */
    function render_vendor_signup(){
        if ( ! class_exists( 'WCV_Vendor_Signup' ) ) {
            include_once( wcv_plugin_dir . 'classes/front/signup/class-vendor-signup.php');
        }

        if ( isset( $_POST['apply_for_vendor'] ) ) {
            self::apply_form_dashboard();
        }
        
        require_once( wcv_plugin_dir . 'templates/dashboard/denied.php');
    }
}