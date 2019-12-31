<?php
/**
 * The capabilities settings class.
 *
 * @package WCVendors/Admin/Settings
 * @phpcs:disable WordPress.WP.I18n
 */

namespace WCVendors\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The capabilities settings class.
 */
class Capabilities extends Base {

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
	 * @param string $current_section Current section ID.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {

		if ( 'product' === $current_section ) {

			$settings = array(

				array(
					'title' => __( 'Add / Edit Product', 'wc-vendors' ),
					'type'  => 'title',
					'desc'  => sprintf( __( 'Configure what product information a %s can access when creating or editing a product', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'    => 'product_add_options',
				),
				array(
					'title'    => __( 'Product Types', 'wc-vendors' ),
					'desc'     => sprintf( __( 'This controls what product types a %s can create', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'       => 'wcvendors_capability_product_types',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => 'simple',
					'type'     => 'multiselect',
					'options'  => wc_get_product_types(),
					'desc_tip' => true,
				),
				array(
					'title'    => __( 'Product Type Options', 'wc-vendors' ),
					'desc'     => sprintf( __( 'This controls what product type options a %s can use', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'       => 'wcvendors_capability_product_data_tabs',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => 'simple',
					'type'     => 'multiselect',
					'options'  => array(
						'virtual'      => __( 'Virtual', 'wc-vendors' ),
						'downloadable' => __( 'Downloadable', 'wc-vendors' ),
					),
					'desc_tip' => true,
				),
				array(
					'title'    => __( 'Product Data Tabs', 'wc-vendors' ),
					'desc'     => sprintf( __( 'This controls what product data tabs a %s can use', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'       => 'wcvendors_capability_product_data_tabs',
					'class'    => 'wc-enhanced-select',
					'css'      => 'min-width:300px;',
					'default'  => 'simple',
					'type'     => 'multiselect',
					'options'  => array(
						'general'        => __( 'General', 'wc-vendors' ),
						'inventory'      => __( 'Inventory', 'wc-vendors' ),
						'shipping'       => __( 'Shipping', 'wc-vendors' ),
						'linked_product' => __( 'Linked Products', 'wc-vendors' ),
						'attribute'      => __( 'Attributes', 'wc-vendors' ),
						'variations'     => __( 'Variations', 'wc-vendors' ),
						'advanced'       => __( 'Advanced', 'wc-vendors' ),
					),
					'desc_tip' => true,
				),
				array(
					'title'   => __( 'Featured Product', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to use the featured product option', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'      => 'wcvendors_capability_product_featured',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Duplicate Product', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to duplicate products', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'      => 'wcvendors_capability_product_duplicate',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'SKU', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Hide sku field from %s', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'      => 'wcvendors_capability_product_sku',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Taxes', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Hide tax fields from %s', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'      => 'wcvendors_capability_product_taxes',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Import Products', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to import products', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'      => 'wcvendors_capability_product_import',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Export Products', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to export products', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'      => 'wcvendors_capability_product_export',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'product_add_options',
				),
			);

		} elseif ( 'order' === $current_section ) {

			$settings = array(
				array(
					'type' => 'title',
					'desc' => sprintf( __( 'Configure what order information a %s can view from an order', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'   => 'order_view_options',
				),
				array(
					'title'   => __( 'Customer Billing Address', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Hide customer billing address fields from %s', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'      => 'wcvendors_capability_order_customer_billling',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Customer Shipping Address', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Hide the customer shipping fields from %s', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'      => 'wcvendors_capability_order_customer_shipping',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Customer Email', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Hide the customer email address from %s', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'      => 'wcvendors_capability_order_customer_email',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Customer Phone', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Hide the customer phone number from %s', 'wc-vendors' ), lcfirst( wcv_get_vendor_name() ) ),
					'id'      => 'wcvendors_capability_order_customer_phone',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'order_view_options',
				),
			);

		} else {

			$settings = array(
				array(
					'title' => __( 'Permissions', 'wc-vendors' ),
					'type'  => 'title',
					'desc'  => sprintf( __( 'Enable or disable functionality for your %s', 'wc-vendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'    => 'capabilities_options',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'capabilities_options',
				),
				// Products.
				array(
					'title' => __( 'Products', 'wc-vendors' ),
					'type'  => 'title',
					'id'    => 'permissions_products_options',
				),
				array(
					'title'   => __( 'Submit Products', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to add/edit products', 'wc-vendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_capability_products_enabled',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Edit Live Products', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to edit published (live) products', 'wc-vendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_capability_products_edit',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Publish Approval', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to publish products directly to the marketplace without requiring approval.', 'wc-vendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_capability_products_live',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'permissions_products_options',
				),

				// Orders.
				array(
					'title' => __( 'Orders', 'wc-vendors' ),
					'type'  => 'title',
					'id'    => 'permissions_orders_options',
				),
				array(
					'title'   => __( 'Manage Orders', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to manage orders', 'wc-vendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_capability_orders_enabled',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Export Orders', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to export their orders to a CSV file', 'wc-vendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_capability_orders_export',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'View Order Notes', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to view order notes', 'wc-vendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_capability_order_read_notes',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Add Order notes', 'wc-vendors' ),
					'desc'    => sprintf( __( 'Allow %s to add order notes.', 'wc-vendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
					'id'      => 'wcvendors_capability_order_update_notes',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'permissions_orders_options',
				),

				// phpcs:disable
				// // Reports
				// array(
				// 'title'     => __( 'Reports', 'wc-vendors' ),
				// 'type'      => 'title',
				// 'id'        => 'permissions_reports_options',
				// ),
				// array(
				// 'title'   => __( 'View Reports', 'wc-vendors' ),
				// 'desc'    => sprintf( __( 'Allow %s to view reports', 'wc-vendors' ), lcfirst( wcv_get_vendor_name( false ) ) ),
				// 'id'      => 'wcvendors_capability_reports_enabled',
				// 'default' => 'yes',
				// 'type'    => 'checkbox',
				// ),
				// array( 'type' => 'sectionend', 'id' => 'permissions_reports_options' ),
				// phpcs:enable
			);

		}

		return apply_filters( 'wcvendors_get_settings_' . $this->id, $settings, $current_section );

	}


}

