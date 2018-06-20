<?php

class WCVendors_Uninstall{

    public function __construct(){
        //TODO: Get uninstall options, check them one by one and remove as required
        //
    }
    
    private static function remove_roles(){
        remove_role( 'pending_vendor' );
        remove_role( 'vendor' );
    }

    private static function remove_pages(){

    }

    private static function delete_table(){
        global $wpdb;
        $table_name = $wpdb->prefix . "pv_commission";

        $wpdb->query("DROP TABLE $table_name");
    }

    private static function delete_options() {

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
	}
}