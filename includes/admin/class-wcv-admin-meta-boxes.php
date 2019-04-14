<?php
/**
 * WooCommerce Meta Boxes
 *
 * Sets up the write panels used by products and orders (custom post types).
 *
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WC_Admin_Meta_Boxes.
 */
class WCVendors_Admin_Meta_Boxes {

	public function __construct( ){ 

		// Vendor meta box 
		add_action( 'add_meta_boxes',			array( $this, 'rename_meta_boxes' ) ); 
		add_filter( 'wp_dropdown_users_args', 	array( $this, 'filter_product_author_dd'), 10, 2 ); 

		//Commission product data tabs
		add_action( 'woocommerce_product_data_tabs', 		array( $this, 'add_commission_tab' ) );
		add_action( 'woocommerce_product_data_panels', 		array( $this, 'add_commission_panel' ) );
		add_action( 'woocommerce_process_product_meta', 	array( $this, 'save' ), 10, 2 ); 
	
	}

	/**
	*	Rename meta boxes on the post edit screen 
	*/ 
	public function rename_meta_boxes(){ 
		global $post; 		

		// Rename the author box to vendor 
		if ( isset( $post ) && post_type_supports( 'product', 'author' ) ) {
			remove_meta_box( 'authordiv', 'product', 'normal' );
    		add_meta_box( 'authordiv', __( 'Vendor', 'wcvendors' ), 'post_author_meta_box', 'product', 'side', 'high' );
		} 	
	}

	/**
	* Hook into the drop down menu for author and add vendors 
	*
	*/ 	
	public function filter_product_author_dd( $query_args, $r ){ 

		global $post;

		if ( empty( $post ) ) return $query_args;

		// return if not on the product page 
		if ( $post->post_type != 'product' ) return $query_args;
		// Get the require roles 
		$query_args[ 'role__in' ] = apply_filters( 'wcvendors_filter_product_author_dd', array( 'vendor', 'administrator' ) ); 
		// unset who which defaults to 'author'
		unset( $query_args[ 'who' ] ); 
		return $query_args;  
	}

	public function add_commission_tab( $tabs ){ 
		
		$tabs[ 'commission' ] = array(
				'label'    => __( 'Commission', 'wcvendors' ),
				'target'   => 'commission_product_data',
				'class'    => array( 'show_if_simple', 'show_if_variable', 'show_if_grouped', 'show_if_external' ),
				'priority' => 80,
		); 

		return $tabs; 
	}

	/**
	**	Output the product commission data panel
	*/ 
	public function add_commission_panel(){ 

		global $post; 

		$thepostid      = $post->ID;
		$product_object = $thepostid ? wc_get_product( $thepostid ) : new WC_Product;

		include( WCV_ABSPATH_ADMIN . 'views/meta-boxes/html-product-data-commission-panel.php' ); 
	}

	/**
	*	Save the data panel 
	*
	*/ 
	public function save( $post_id, $post ){ 

		$product_type 	= empty( $_POST['product-type'] ) ? WC_Product_Factory::get_product_type( $post_id ) : sanitize_title( stripslashes( $_POST['product-type'] ) );
		$classname    	= WC_Product_Factory::get_product_classname( $post_id, $product_type ? $product_type : 'simple' );
		$product      	= new $classname( $post_id );

		if ( isset( $_POST[ '_wcv_commission_rate' ] ) ){ 
			$product->update_meta_data( '_wcv_commission_rate', wc_clean( $_POST[ '_wcv_commission_rate' ] ) ); 
		} else { 
			$product->delete_meta_data( '_wcv_commission_rate' ); 
		}

		$product->save(); 

		do_action( 'wcvendors_admin_meta_boxes_save', $product ); 
	}

} 

new WCVendors_Admin_Meta_Boxes(); 