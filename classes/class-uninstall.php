<?php

/**
 * Advanced uninstall settings
 *
 * @author      Lindeni Mahlalela, WC Vendors
 * @category    Settings
 * @package     WCVendors/Admin/Settings
 * @version     2.0.8
 */
class WCVendors_Uninstall{
    /**
     * Check the uninstall options and delete the data 
     *
     * @return void
     * @package
     * @since 2.0.8
     */
    public static function uninstall(){
        if ( get_option( 'wcvendors_uninstall_delete_all_data') == 'yes' ) {
            self::delete_all();
        }else{
            if ( get_option( 'wcvendors_uninstall_delete_custom_table') == 'yes' ) {
                self::delete_table();
            } 

            if ( get_option( 'wcvendors_uninstall_delete_custom_pages') == 'yes' ) {
                self::delete_pages();
            } 

            if ( get_option( 'wcvendors_uninstall_delete_settings_options') == 'yes' ) {
                self::delete_options();
            } 
        }

        self::remove_roles();
        self::flush_rewrite_rules();
    }

    /**
     * Delete all plugin data at once
     *
     * @return void
     * @since 2.0.8
     */
    public static function delete_all(){
        self::remove_roles();
        self::delete_pages();
        self::delete_table();
        self::delete_options();
    }
    
    /**
     * Remove custom roles
     *
     * @return void
     * @since 2.0.8
     */
    public static function remove_roles(){
        remove_role( 'pending_vendor' );
        remove_role( 'vendor' );
    }

    /**
     * Delete custom pages created for this plugin
     *
     * @return void
     * @since 2.0.8
     */
    public static function delete_pages(){
        $pages = array( 'vendors', 'vendor_dashboard', 'product_orders', 'shop_settings');
        foreach ( $pages as $page_name ) {
            $page = get_post( $page_name );
            wp_delete_post( $page->ID, true );
        }
    }

    /**
     * Delete custom database table
     *
     * @return void
     * @since 2.0.8
     */
    public static function delete_table(){
        global $wpdb;
        $table_name = $wpdb->prefix . "pv_commission";

        $wpdb->query("DROP TABLE $table_name");
    }

    /**
     * Delete all options
     *
     * @return void
     * @since 2.0.8
     */
    public static function delete_options() {

		include_once( dirname( __FILE__ ) . '/admin/class-wcv-admin-settings.php' );

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
    }

    /**
     * Flush rewrite rules
     *
     * @return void
     * @since 2.0.8
     */
    public static function flush_rewrite_rules(){
        flush_rewrite_rules();
    }
}