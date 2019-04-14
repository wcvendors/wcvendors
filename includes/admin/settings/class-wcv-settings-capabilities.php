<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The capabilities settings class
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Settings
 * @package     WCVendors/Admin/Settings
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WCVendors_Settings_Capabilities', false ) ) :

/**
 * WC_Admin_Settings_General.
 */
class WCVendors_Settings_Capabilities extends WCVendors_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'capabilities';
		$this->label = __( 'Capabilities', 'wcvendors' );

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
			'product'	=> __( 'Products', 'wcvendors' ),
			'order'		=> __( 'Orders', 'wcvendors' ),
		);

		return apply_filters( 'wcvendors_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		if ( 'product' === $current_section ){ 

			$settings = apply_filters( 'wcvendors_settings_capabilities_product', array(

			array(
				'title'    => __( 'Add / Edit Product', 'wcvendors' ),
				'type'     => 'title',
				'desc'     => sprintf( __( 'Configure what product information a %s can access when creating or editing a product', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ),
				'id'       => 'product_add_options',
			),

			array(
					'title'    => __( 'Product Types', 'wcvendors' ),
					'desc'     => sprintf( __( 'This controls what product types a %s can create', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'       => 'wcvendors_capability_product_types',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => 'simple',
					'type'     => 'multiselect',
					'options'  => wc_get_product_types(), 
					'desc_tip' => true,
			), 

			array(
					'title'    => __( 'Product Type Options', 'wcvendors' ),
					'desc'     => sprintf( __( 'This controls what product type options a %s can use', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'       => 'wcvendors_capability_product_data_tabs',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => 'simple',
					'type'     => 'multiselect',
					'options'  => array(
						'virtual'  			=> __( 'Virtual', 'wcvendors' ),
						'downloadable'     	=> __( 'Downloadable', 'wcvendors' ),
					), 
					'desc_tip' => true,
			),

			array(
					'title'    => __( 'Product Data Tabs', 'wcvendors' ),
					'desc'     => sprintf( __( 'This controls what product data tabs a %s can use', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'       => 'wcvendors_capability_product_data_tabs',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => 'simple',
					'type'     => 'multiselect',
					'options'  => array(
						'general'  			=> __( 'General', 'wcvendors' ),
						'inventory'     	=> __( 'Inventory', 'wcvendors' ),
						'shipping' 			=> __( 'Shipping', 'wcvendors' ),
						'linked_product'	=> __( 'Linked Products', 'wcvendors' ),
						'attribute'			=> __( 'Attributes', 'wcvendors' ),
						'variations'		=> __( 'Variations', 'wcvendors' ),
						'advanced'			=> __( 'Advanced', 'wcvendors' ),
					),
					'desc_tip' => true,
			),


			array(
				'title'   => __( 'Featured Product', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to use the featured product option', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'      => 'wcvendors_capability_product_featured',
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'Duplicate Product', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to duplicate products', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'      => 'wcvendors_capability_product_duplicate',
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'SKU', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Hide sku field from %s', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'      => 'wcvendors_capability_product_sku',
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'Taxes', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Hide tax fields from %s', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'      => 'wcvendors_capability_product_taxes',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			

			array(
				'title'   => __( 'Import Products', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to import products', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'      => 'wcvendors_capability_product_import',
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'Export Products', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to export products', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'      => 'wcvendors_capability_product_export',
				'default' => 'no',
				'type'    => 'checkbox',
			),


			array( 'type' => 'sectionend', 'id' => 'product_add_options' ),
	
			) ); 	

		} elseif ( 'order' === $current_section ){ 

			$settings = apply_filters( 'wcvendors_settings_capabilities_order', array(

			array(
				'type'     => 'title',
				'desc'     => sprintf( __( 'Configure what order information a %s can view from an order', 'wcvendors' ), lcfirst( wcv_get_vendor_name( ) ) ),
				'id'       => 'order_view_options',
			),

			array(
				'title'   => __( 'Customer Billing Address', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Hide customer billing address fields from %s', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'      => 'wcvendors_capability_order_customer_billling',
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'Customer Shipping Address', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Hide the customer shipping fields from %s', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'      => 'wcvendors_capability_order_customer_shipping',
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'Customer Email', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Hide the customer email address from %s', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'      => 'wcvendors_capability_order_customer_email',
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'title'   => __( 'Customer Phone', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Hide the customer phone number from %s', 'wcvendors' ), lcfirst( wcv_get_vendor_name() ) ), 
				'id'      => 'wcvendors_capability_order_customer_phone',
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array( 'type' => 'sectionend', 'id' => 'order_view_options' ),

			) ); 	

		} else { 

			$settings = apply_filters( 'wcvendors_settings_capabilities_general', array(

			array(
				'title'    => __( 'Permissions', 'wcvendors' ),
				'type'     => 'title',
				'desc'     => sprintf( __( 'Enable or disable functionality for your %s', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
				'id'       => 'capabilities_options',
			),

			array( 'type' => 'sectionend', 'id' => 'capabilities_options' ),

			// Products 
			array(
				'title' 	=> __( 'Products', 'wcvendors' ),
				'type' 		=> 'title',
				'id' 		=> 'permissions_products_options',
			),

			array(
				'title'   => __( 'Submit Products', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to add/edit products', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ), 
				'id'      => 'wcvendors_capability_products_enabled',
				'default' => 'yes',
				'type'    => 'checkbox',
			),	

			array(
				'title'   => __( 'Edit Live Products', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to edit published (live) products', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ), 
				'id'      => 'wcvendors_capability_products_edit',
				'default' => 'yes',
				'type'    => 'checkbox',
			),	

			array(
				'title'   => __( 'Publish Approval', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to publish products directly to the marketplace without requiring approval.', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ), 
				'id'      => 'wcvendors_capability_products_live',
				'default' => 'yes',
				'type'    => 'checkbox',
			),	

			array( 'type' => 'sectionend', 'id' => 'permissions_products_options' ),

			// Orders 
			array(
				'title' 	=> __( 'Orders', 'wcvendors' ),
				'type' 		=> 'title',
				'id' 		=> 'permissions_orders_options',
			),

			array(
				'title'   => __( 'Manage Orders', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to manage orders', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ), 
				'id'      => 'wcvendors_capability_orders_enabled',
				'default' => 'yes',
				'type'    => 'checkbox',
			),	

			array(
				'title'   => __( 'Export Orders', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to export their orders to a CSV file', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ), 
				'id'      => 'wcvendors_capability_orders_export',
				'default' => 'yes',
				'type'    => 'checkbox',
			),	

			array(
				'title'   => __( 'View Order Notes', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to view order notes', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ), 
				'id'      => 'wcvendors_capability_order_read_notes',
				'default' => 'yes',
				'type'    => 'checkbox',
			),	

			array(
				'title'   => __( 'Add Order notes', 'wcvendors' ), 
				'desc'    => sprintf( __( 'Allow %s to add order notes.', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ), 
				'id'      => 'wcvendors_capability_order_update_notes',
				'default' => 'yes',
				'type'    => 'checkbox',
			),	

			array( 'type' => 'sectionend', 'id' => 'permissions_orders_options' ),


			// // Reports 
			// array(
			// 	'title' 	=> __( 'Reports', 'wcvendors' ),
			// 	'type' 		=> 'title',
			// 	'id' 		=> 'permissions_reports_options',
			// ),

			// array(
			// 	'title'   => __( 'View Reports', 'wcvendors' ), 
			// 	'desc'    => sprintf( __( 'Allow %s to view reports', 'wcvendors' ), lcfirst( wcv_get_vendor_name( false ) ) ), 
			// 	'id'      => 'wcvendors_capability_reports_enabled',
			// 	'default' => 'yes',
			// 	'type'    => 'checkbox',
			// ),	


			// array( 'type' => 'sectionend', 'id' => 'permissions_reports_options' ),

			) );

		}

		return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );
		
	}

	
}

endif;

return new WCVendors_Settings_Capabilities();
