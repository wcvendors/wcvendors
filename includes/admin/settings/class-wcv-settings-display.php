<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The display settings class
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Settings
 * @package     WCVendors/Admin/Settings
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCVendors_Settings_Display', false ) ) :

/**
 * WC_Admin_Settings_General.
 */
class WCVendors_Settings_Display extends WCVendors_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'display';
		$this->label = __( 'Display', 'wcvendors' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''          => __( 'General', 'wcvendors' ),
			'labels'	=> __( 'Labels', 'wcvendors' ),
		);

		return apply_filters( 'wcvendors_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		if ( 'labels' === $current_section ){ 

			$settings = apply_filters( 'wcvendors_settings_display_labels', array(

				// Shop Display Options 
				array(
					'title'    => __( '', 'wcvendors' ),
					'type'     => 'title',
					'desc'     => sprintf( __( 'Labels are shown on the front end, in orders and emails.', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
					'id'       => 'shop_options',
				),

				array(
					'title'   	=> __( 'Sold by', 'wcvendors' ), 
					'desc'    	=> __( 'Enable sold by labels', 'wcvendors' ), 
					'desc_tip' 	=> sprintf( __( 'This enables the sold by labels used to show which %s shop the product belongs to', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
					'id'      	=> 'wcvendors_display_label_sold_by_enable',
					'default' 	=> 'yes',
					'type'    	=> 'checkbox',
				), 

				array(
					'title'    	=> __( 'Sold by label', 'wcvendors' ),
					'desc_tip'  => __( 'The sold by label', 'wcvendors' ),
					'id'       	=> 'wcvendors_label_sold_by',
					'type'     	=> 'text',
					'default'	=> __( 'Sold By', 'wcvendors' ), 
				),

				array(
					'title'   	=> sprintf( __( '%s Store Info', 'wcvendors' ), wcv_get_vendor_name() ), 
					'desc'    	=> sprintf( __( 'Enable %s store info tab on the single product page', 'wcvendors' ), wcv_get_vendor_name() ), 
					'id'      	=> 'wcvendors_label_store_info_enable',
					'default' 	=> 'yes',
					'type'    	=> 'checkbox',
				), 

				array(
					'title'    	=> sprintf( __( '%s store Info label', 'wcvendors' ), wcv_get_vendor_name() ), 
					'desc'  => sprintf( __( 'The %s store info label', 'wcvendors' ), lcfirst( wcv_get_vendor_name( ) ) ), 
					'id'       	=> 'wcvendors_display_label_store_info',
					'type'     	=> 'text',
					'default'	=> __( 'Store Info', 'wcvendors' ), 
				),

				array( 'type' => 'sectionend', 'id' => 'shop_options' ),

			) ); 

		} else { 

			$settings = apply_filters( 'wcvendors_settings_display_general', array(

				//  General Options 
				array(
					'title'    	=> __( 'Pages', 'wcvendors' ),
					'type'     	=> 'title',
					'desc'		=> sprintf( __( 'These pages used on the front end by %s.', 'wcvendors'), lcfirst( wcv_get_vendor_name( false ) ) ), 
					'id'       	=> 'page_options',
				),
				array(
					'title'    	=> __( 'Dashboard', 'wcvendors' ),
					'id'       	=> 'wcvendors_dashboard_page_id',
					'type'     	=> 'single_select_page',
					'default'  	=> '',
					'class'    	=> 'wc-enhanced-select-nostd',
					'css'      	=> 'min-width:300px;',
					'desc' 	   	=> sprintf(  __( '<br />This sets the page used to display the front end %s dashboard. This page should contain the following shortcode. <code>[wcvendors_dashboard]</code>', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ), 
				), 
				array(
					'title'    	=> sprintf( __( '%s', 'wcvendors' ), ucfirst( wcv_get_vendor_name( false ) ) ),
					'id'       	=> 'wcvendors_vendors_page_id',
					'type'     	=> 'single_select_page',
					'default'  	=> '',
					'class'    	=> 'wc-enhanced-select-nostd',
					'css'      	=> 'min-width:300px;',
					'desc' 	   	=> sprintf(  __( '<br />This sets the page used to display a paginated list of all %1$s stores. Your %1$s stores will be available at <code>%2$s/page-slug/store-name/</code><br />This page should contain the following shortcode. <code>[wcvendors_stores]</code>', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ), esc_html( home_url() ) ), 
				), 
				array(
					'title'    	=> __( 'Terms and Conditions', 'wcvendors' ),
					'id'       	=> 'wcvendors_vendor_terms_page_id',
					'type'     	=> 'single_select_page',
					'default'  	=> '',
					'class'    	=> 'wc-enhanced-select-nostd',
					'css'      	=> 'min-width:300px;',
					'desc'     	=> sprintf(  __( '<br />This sets the page used to display the terms and conditions when a %s signs up.', 'wcvendors' ), lcfirst( wcv_get_vendor_name( ) ) ), 
				), 

				array( 'type' => 'sectionend', 'id' => 'page_options' ),

				// Shop Settings 
				array(
					'title'    	=> __( 'Store Settings', 'wcvendors' ),
					'type'     	=> 'title',
					'desc'		=> __( 'These are the settings for the individual vendor stores.', 'wcvendors' ),
					'id'       	=> 'shop_options',
				),

				array(
					'title'   	=> __( 'Shop Header', 'wcvendors' ), 
					'desc'    	=> sprintf( __( 'Enable %s shop headers', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
					'desc_tip' 	=> sprintf( __( 'This enables the %s shop header template and disables the shop description text.', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
					'id'      	=> 'wcvendors_display_shop_headers',
					'default' 	=> 'no',
					'type'    	=> 'checkbox',
				), 

				array(
					'title'   	=> __( 'Shop HTML', 'wcvendors' ), 
					'desc'    	=> sprintf( __( 'Allow HTML in %s shop desription', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
					'desc_tip' 	=> sprintf( __( 'Enable HTML for a %s shop description. You can enable or disable this per vendor by editing the vendors user account.', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ), 
					'id'      	=> 'wcvendors_display_shop_description_html',
					'default' 	=> 'no',
					'type'    	=> 'checkbox',
				),

				array(
					'title'    => __( 'Display Name', 'wcvendors' ),
					'id'       => 'wcvendors_display_shop_display_name', 
					'desc_tip' => sprintf( __( 'Select what will be used to display the %s name throughout the marketplace.', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
					'default'  => 'shop_name',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'options'  => array(
						'display_name'  => __( 'Display name', 'wcvendors' ),
						'shop_name'     => __( 'Shop name', 'wcvendors' ),
						'user_login' 	=> sprintf( __( '%s Username', 'wcvendors' ), wcv_get_vendor_name() ), 
						'user_email'	=> sprintf( __( '%s Email', 'wcvendors' ), wcv_get_vendor_name() ), 
					),
				),

				array( 'type' => 'sectionend', 'id' => 'shop_options' ),

			) ); 

		}

		return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );

	}


}

endif;

return new WCVendors_Settings_Display();
