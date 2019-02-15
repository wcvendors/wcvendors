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
			$this->label = __( 'Capabilities', 'wc-vendors' );

			parent::__construct();
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				''        => __( 'General', 'wc-vendors' ),
				'product' => __( 'Products', 'wc-vendors' ),
				'order'   => __( 'Orders', 'wc-vendors' ),
			);

			return apply_filters( 'wcvendors_get_sections_' . $this->id, $sections );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {

			if ( 'product' === $current_section ) {

				$settings = apply_filters(
					'wcvendors_settings_capabilities_product', array(

						array(
							'title' => __( 'Add / Edit Product', 'wc-vendors' ),
							'type'  => 'title',
							'desc'  => sprintf( __( 'Configure what product information to hide from the %s when creating or editing a product', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ),
							'id'    => 'product_add_options',
						),

						array(
							'title'    => __( 'Product Types', 'wc-vendors' ),
							'desc'     => sprintf( __( 'This controls what product types are hidden from the %s', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ),
							'id'       => 'wcvendors_capability_product_types',
							'class'    => 'wc-enhanced-select',
							'css'      => 'min-width:300px;',
							'type'     => 'multiselect',
							'options'  => wc_get_product_types(),
							'desc_tip' => true,
						),

						array(
							'title'    => __( 'Product Type Options', 'wc-vendors' ),
							'desc'     => sprintf( __( 'This controls what product type options are hidden from the %s', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ),
							'id'       => 'wcvendors_capability_product_type_options',
							'class'    => 'wc-enhanced-select',
							'css'      => 'min-width:300px;',
							'type'     => 'multiselect',
							'options'  => array(
								'virtual'      => __( 'Virtual', 'wc-vendors' ),
								'downloadable' => __( 'Downloadable', 'wc-vendors' ),
							),
							'desc_tip' => true,
						),

						array(
							'title'    => __( 'Product Data Tabs', 'wc-vendors' ),
							'desc'     => sprintf( __( 'This controls what product data tabs will be hidden from the %s', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ),
							'id'       => 'wcvendors_capability_product_data_tabs',
							'class'    => 'wc-enhanced-select',
							'css'      => 'min-width:300px;',
							'type'     => 'multiselect',
							'options'  => apply_filters('wcvendors_capability_product_data_tabs', array(
								'general'        => __( 'General', 'wc-vendors' ),
								'inventory'      => __( 'Inventory', 'wc-vendors' ),
								'shipping'       => __( 'Shipping', 'wc-vendors' ),
								'linked_product' => __( 'Linked Products', 'wc-vendors' ),
								'attribute'      => __( 'Attributes', 'wc-vendors' ),
								'variations'     => __( 'Variations', 'wc-vendors' ),
								'advanced'       => __( 'Advanced', 'wc-vendors' ),
							) ),
							'desc_tip' => true,
						),

						array(
							'title'   => __( 'Featured Product', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to use the featured product option', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_product_featured',
							'default' => 'no',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Duplicate Product', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to duplicate products', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_product_duplicate',
							'default' => 'no',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'SKU', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Hide sku field from %s', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_product_sku',
							'default' => 'no',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Taxes', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Hide tax fields from %s', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_product_taxes',
							'default' => 'no',
							'type'    => 'checkbox',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'product_add_options',
						),

					)
				);

			} elseif ( 'order' === $current_section ) {

				$settings = apply_filters(
					'wcvendors_settings_capabilities_order', array(

						array(
							'type' => 'title',
							'desc' => sprintf( __( 'Configure what order information a %s can view from an order', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ),
							'id'   => 'order_view_options',
						),

						array(
							'title'   => __( 'View Order Notes', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to view order notes', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_order_read_notes',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Add Order notes', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to add order notes.', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_order_update_notes',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Customer Name', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to view customer name fields', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_order_customer_name',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Customer Shipping Name', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to view customer shipping name fields', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_order_customer_shipping_name',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Customer Billing Address', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to view customer billing address fields', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_order_customer_billing',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Customer Shipping Address', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to view the customer shipping fields', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_order_customer_shipping',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Customer Email', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to view the customer email address', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_order_customer_email',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Customer Phone', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to view the customer phone number', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_order_customer_phone',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'order_view_options',
						),

					)
				);

			} else {

				$settings = apply_filters(
					'wcvendors_settings_capabilities_general', array(

						array(
							'title' => __( 'Permissions', 'wc-vendors' ),
							'type'  => 'title',
							'desc'  => sprintf( __( 'Enable or disable functionality for your %s', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'    => 'capabilities_options',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'capabilities_options',
						),

						// Products
						array(
							'title' => __( 'Products', 'wc-vendors' ),
							'type'  => 'title',
							'id'    => 'permissions_products_options',
						),

						array(
							'title'   => __( 'Submit Products', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to add/edit products', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_products_enabled',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Edit Live Products', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to edit published (live) products', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_products_edit',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Publish Approval', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to publish products directly to the marketplace without requiring approval.', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_products_live',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'permissions_products_options',
						),

						// Orders
						array(
							'title' => __( 'Orders', 'wc-vendors' ),
							'type'  => 'title',
							'id'    => 'permissions_orders_options',
						),

						array(
							'title'   => __( 'View Orders', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to view orders', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_orders_enabled',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Export Orders', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %s to export their orders to a CSV file', 'wc-vendors' ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_orders_export',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'title'   => __( 'Front End Sales Reports', 'wc-vendors' ),
							'desc'    => sprintf( __( 'Allow %1$s to view sales table on the frontend on the %2$s dashboard page.', 'wc-vendors' ), wcv_get_vendor_name( false, false ), wcv_get_vendor_name( false, false ) ),
							'id'      => 'wcvendors_capability_frontend_reports',
							'default' => 'yes',
							'type'    => 'checkbox',
						),

						array(
							'type' => 'sectionend',
							'id'   => 'permissions_orders_options',
						),

					)
				);

			}

			return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );

		}


	}

endif;

return new WCVendors_Settings_Capabilities();
