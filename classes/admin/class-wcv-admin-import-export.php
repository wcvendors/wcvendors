<?php

/**
 * Add vendor id to the import and export of products for admins
 *
 * Allow vendor details to be exported and imported via the product screen as admins.
 *
 * @package    WCVendors
 * @subpackage WCVendors/admin
 * @author     Jamie Madden <support@wcvendors.com>
 */

class WCV_Admin_Import_Export {

	/**
	 * The version of this plugin.
	 *
	 * @since    2.1.15
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Script suffix for debugging
	 *
	 * @since    2.1.15
	 * @access   private
	 * @var      string $suffix script suffix for including minified file versions
	 */
	private $suffix;

	/**
	 * Is the plugin in debug mode
	 *
	 * @since    2.1.15
	 * @access   private
	 * @var      bool $debug plugin is in debug mode
	 */
	private $debug;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.1.15
	 *
	 * @param    string $wcvendors_pro The name of this plugin.
	 * @param    string $version       The version of this plugin.
	 */
	public function __construct() {
		add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'add_column_to_importer' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'add_column_to_mapping_screen') );
		add_filter( 'woocommerce_product_export_column_names', array( $this, 'add_export_column') );
		add_filter( 'woocommerce_product_export_product_default_columns', array( $this, 'add_export_column' ));
		add_filter( 'woocommerce_product_export_product_column_vendor_id', array( $this, 'add_export_data'), 10, 2 );
		add_filter( 'woocommerce_product_import_inserted_product_object', array( $this, 'process_import'), 10, 2 );
		add_filter( 'woocommerce_product_import_process_item_data', array( $this, 'check_product_owner' ) );
		add_filter( 'woocommerce_new_product_data', array( $this, 'avoid_empty_product' ) );
	}

	/**
	 * Register the 'Custom Column' column in the importer.
	 *
	 * @since    2.1.15
	 * @param array $options
	 * @return array $options
	 */
	public function add_column_to_importer( $options ) {

		// column slug => column name
		$options['vendor_id'] = __( 'Vendor ID', 'wc-vendors');

		return apply_filters( 'wcv_csv_product_import_mapping_options', $options );
	}

	/**
	 * Add automatic mapping support for 'Custom Column'.
	 * This will automatically select the correct mapping for columns named 'Custom Column' or 'custom column'.
	 *
	 * @since    2.1.15
	 * @param array $columns
	 * @return array $columns
	 */
	public function add_column_to_mapping_screen( $columns ) {

		// potential column name => column slug
		$columns['Vendor ID'] = 'vendor_id';

		return $columns;
	}

	/**
	 * Process the data read from the CSV file.
	 * This just saves the value in meta data, but you can do anything you want here with the data.
	 *
	 * @since    2.1.15
	 * @param WC_Product $object - Product being imported or updated.
	 * @param array      $data   - CSV data read for the product.
	 * @return WC_Product $object
	 */
	function process_import( $object, $data ) {

		if ( ! $this->is_product_author( $data ) ) {
			return $object;
		}

		if ( is_a( $object, 'WC_Product' ) || is_a( $object, 'WC_Product_Variation' ) ) {

			$post = array(
				'ID'          => $object->get_id(),
				'post_author' => isset( $data['vendor_id'] ) ? $data['vendor_id'] : get_current_user_id(),
			);

			$update = wp_update_post( $post );

		}

		return $object;
	}

	/**
	 * Check product owner
	 *
	 * Check who owns the product. If the current user is not the owner of the product,
	 * create a new product for the current user, or do nothing.
	 *
	 * @param data $data The product raw data.
	 * @return object
	 * @version 2.2.1
	 * @since   2.2.1
	 */
	public function check_product_owner( $data ) {
		
		if ( ! $this->is_product_author( $data ) ) {
			return array();
		}

		return $data;
	}

	/**
	 * Avoid creating an empty product
	 *
	 * @param bool  $data Product args.
	 * @return bool
	 * @version 2.2.1
	 * @since   2.2.1
	 */
	public function avoid_empty_product( $data ) {
		if ( ! $this->is_product_author( $data ) && isset( $data['post_title'] ) && __( 'Product', 'woocommerce' ) === $data['post_title'] ) {
			return array();
		}

		return $data;
	}

	/**
	 * Check if the current user is the product vendor
	 *
	 * @param array $data The product data.
	 * @return boolean
	 * @version 2.2.1
	 * @since   2.2.1
	 */
	public function is_product_author( $data ) {
		$vendor_id = get_current_user_id();
		
		if ( ( isset( $data['vendor_id'] ) && $vendor_id == $data['vendor_id'] ) || is_admin() ) {
			return true;
		}

		return false;
	}

	/**
	 * Add the custom column to the exporter and the exporter column menu.
	 *
	 * @since    2.1.15
	 * @param array $columns
	 * @return array $columns
	 */
	public function add_export_column( $columns ) {

		// column slug => column name
		$columns['vendor_id'] = 'Vendor ID';

		return $columns;
	}

	/**
	 * Provide the data to be exported for one item in the column.
	 *
	 * @since    2.1.15
	 * @param mixed      $value (default: '')
	 * @param WC_Product $product
	 * @return mixed $value - Should be in a format that can be output into a text file (string, numeric, etc).
	 */
	function add_export_data( $value, $product ) {
		$vendor_id = WCV_Vendors::get_vendor_from_product( $product->get_id() );

		return $vendor_id;
	}
}
