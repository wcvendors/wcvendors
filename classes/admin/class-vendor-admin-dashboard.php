<?php 
/**
 *  WC Vendor Admin Dashboard - Vendor WP-Admin Dashboard Pages
 * 
 * @author Jamie Madden <http://wcvendors.com / https://github.com/digitalchild>
 * @package WCVendors
 */

Class WCV_Vendor_Admin_Dashboard { 

	public $dashboard_error_msg; 

	function __construct(){ 
		// Add Shop Settings page 
		add_action( 'admin_menu', array( $this, 'vendor_dashboard_pages') ); 
		// Hook into init for form processing 
		add_action( 'admin_init', array( $this, 'save_shop_settings' ) );
		add_action( 'admin_head', array( $this, 'admin_enqueue_order_style') ); 
	}

	function vendor_dashboard_pages(){
        add_menu_page( __('Shop Settings', 'wcvendors'), __('Shop Settings', 'wcvendors'), 'manage_product', 'wcv-vendor-shopsettings', array( $this, 'settings_page' ) );
		$hook = add_menu_page( __( 'Orders', 'wcvendors' ), __( 'Orders', 'wcvendors' ), 'manage_product', 'wcv-vendor-orders', array( 'WCV_Vendor_Admin_Dashboard', 'orders_page' ) );
		add_action( "load-$hook", array( 'WCV_Vendor_Admin_Dashboard', 'add_options' ) );
	}
 
	function settings_page() {  
		$user_id = get_current_user_id(); 
		$paypal_address   = true; 
		$shop_description = true; 
		$description = get_user_meta( $user_id, 'pv_shop_description', true );
		$seller_info = get_user_meta( $user_id, 'pv_seller_info', true );
		$has_html    = get_user_meta( $user_id, 'pv_shop_html_enabled', true );
		$shop_page   = WCV_Vendors::get_vendor_shop_page( wp_get_current_user()->user_login );
		$global_html = WC_Vendors::$pv_options->get_option( 'shop_html_enabled' );
		include('views/html-vendor-settings-page.php'); 
	}

	function admin_enqueue_order_style() { 

		$screen 	= get_current_screen(); 
		$screen_id 	= $screen->id; 

		if ( 'wcv-vendor-orders' === $screen_id ){ 

			add_thickbox();
			wp_enqueue_style( 'admin_order_styles', wcv_assets_url . 'css/admin-orders.css' );
		}	
	}

	/** 
	*	Save shop settings 
	*/
	public function save_shop_settings()
	{
		$user_id = get_current_user_id();
		$error = false; 
		$error_msg = '';

		if (isset ( $_POST[ 'wc-vendors-nonce' ] ) ) { 

			if ( !wp_verify_nonce( $_POST[ 'wc-vendors-nonce' ], 'save-shop-settings-admin' ) ) {
				return false;
			}

			if ( !is_email( $_POST[ 'pv_paypal' ] ) ) {
				$error_msg .=  __( 'Your PayPal address is not a valid email address.', 'wcvendors' );
				$error = true; 
			} else {
				update_user_meta( $user_id, 'pv_paypal', $_POST[ 'pv_paypal' ] );
			}
		
			if ( !empty( $_POST[ 'pv_shop_name' ] ) ) {
				$users = get_users( array( 'meta_key' => 'pv_shop_slug', 'meta_value' => sanitize_title( $_POST[ 'pv_shop_name' ] ) ) );
				if ( !empty( $users ) && $users[ 0 ]->ID != $user_id ) {
					$error_msg .= __( 'That shop name is already taken. Your shop name must be unique.', 'wcvendors' ); 
					$error = true; 
				} else {
					update_user_meta( $user_id, 'pv_shop_name', $_POST[ 'pv_shop_name' ] );
					update_user_meta( $user_id, 'pv_shop_slug', sanitize_title( $_POST[ 'pv_shop_name' ] ) );
				}
			}

			if ( isset( $_POST[ 'pv_shop_description' ] ) ) {
				update_user_meta( $user_id, 'pv_shop_description', $_POST[ 'pv_shop_description' ] );
			}

			if ( isset( $_POST[ 'pv_seller_info' ] ) ) {
				update_user_meta( $user_id, 'pv_seller_info', $_POST[ 'pv_seller_info' ] );
			}

			do_action( 'wcvendors_shop_settings_admin_saved', $user_id );

			if ( ! $error ) {
				add_action( 'admin_notices', array( $this, 'add_admin_notice_success' ) ); 
			} else { 
				$this->dashboard_error_msg = $error_msg; 
				add_action( 'admin_notices', array( $this, 'add_admin_notice_error' ) );  	
			}
		}
	}

	/**
	 * Output a sucessful message after saving the shop settings
	 * 
	 * @since 1.9.9 
	 * @access public 
	 */
	public function add_admin_notice_success( ){ 

		echo '<div class="updated"><p>';
		echo __( 'Settings saved.', 'wcvendors' );
		echo '</p></div>';

	} // add_admin_notice_success() 

	/**
	 * Output an error message 
	 * 
	 * @since 1.9.9 
	 * @access public 
	 */
	public function add_admin_notice_error( ){ 

		echo '<div class="error"><p>';
		echo $this->dashboard_error_msg; 		
		echo '</p></div>';

	} // add_admin_notice_error()

	/**
	 *
	 *
	 * @param unknown $status
	 * @param unknown $option
	 * @param unknown $value
	 *
	 * @return unknown
	 */
	public static function set_table_option( $status, $option, $value )
	{
		if ( $option == 'orders_per_page' ) {
			return $value;
		}
	}


	/**
	 *
	 */
	public static function add_options()
	{
		global $WCV_Vendor_Order_Page;

		$args = array(
			'label'   => 'Rows',
			'default' => 10,
			'option'  => 'orders_per_page'
		);
		add_screen_option( 'per_page', $args );

		$WCV_Vendor_Order_Page = new WCV_Vendor_Order_Page();

	}


	/**
	 * HTML setup for the Orders Page 
	 */
	public static function orders_page()
	{
		global $woocommerce, $WCV_Vendor_Order_Page;

		$WCV_Vendor_Order_Page->prepare_items();

		?>
		<div class="wrap">

			<div id="icon-woocommerce" class="icon32 icon32-woocommerce-reports"><br/></div>
			<h2><?php _e( 'Orders', 'wcvendors' ); ?></h2>

			<form id="posts-filter" method="get">

				<input type="hidden" name="page" value="wcv-vendor-orders"/>
				<?php $WCV_Vendor_Order_Page->display() ?>

			</form>
			<div id="ajax-response"></div>
			<br class="clear"/>
		</div>

<?php }

} // End WCV_Vendor_Admin_Dashboard

if ( !class_exists( 'WP_List_Table' ) ) require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

/**
 * WCV Vendor Order Page 
 * 
 * @author Jamie Madden <http://wcvendors.com / https://github.com/digitalchild>
 * @package WCVendors 
 * @extends WP_List_Table
 */
class WCV_Vendor_Order_Page extends WP_List_Table
{

	public $index;

	/**
	 * can_view_comments
	 *  
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $can_view_comments    permission check for view comments
	 */
	public $can_view_comments;


	/**
	 * can_add_comments
	 *  
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $can_add_comments    permission check for add comments
	 */
	public $can_add_comments;


	/**
	 * __construct function.
	 *
	 * @access public
	 */
	function __construct()
	{
		global $status, $page;

		$this->index = 0;

		//Set parent defaults
		parent::__construct( array(
								  'singular' => __( 'order', 'wcvendors' ),
								  'plural'   => __( 'orders', 'wcvendors' ), 
								  'ajax'     => false
							 ) );

		$this->can_view_comments = WC_Vendors::$pv_options->get_option( 'can_view_order_comments' );
		$this->can_add_comments = WC_Vendors::$pv_options->get_option( 'can_submit_order_comments' );
	}


	/**
	 * column_default function.
	 *
	 * @access public
	 *
	 * @param unknown $item
	 * @param mixed   $column_name
	 *
	 * @return unknown
	 */
	function column_default( $item, $column_name )
	{
		global $wpdb;

		switch ( $column_name ) {
			case 'order_id' :
				return $item->order_id;
			case 'customer' : 
				return $item->customer; 
			case 'products' :
				return $item->products; 
			case 'total' : 
				return $item->total; 
			// case 'comments' : 
			// 	return $item->comments; 
			case 'date' : 
				return $item->date; 
			case 'status' : 
				return $item->status;
			default: 
				return apply_filters( 'wcvendors_vendor_order_page_column_default', '', $item, $column_name );
		}
	}


	/**
	 * column_cb function.
	 *
	 * @access public
	 *
	 * @param mixed $item
	 *
	 * @return unknown
	 */
	function column_cb( $item )
	{
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/
			'order_id',
			/*$2%s*/
			$item->order_id
		);
	}


	/**
	 * get_columns function.
	 *
	 * @access public
	 * @return unknown
	 */
	function get_columns()
	{
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'order_id'  => __( 'Order ID', 'wcvendors' ),
			'customer'  => __( 'Customer', 'wcvendors' ),
			'products'  => __( 'Products', 'wcvendors' ),
			'total' 	=> __( 'Total', 'wcvendors' ),
			// 'comments' 	=> __( 'Comments to Customer', 'wcvendors' ),
			'date'      => __( 'Date', 'wcvendors' ),
			'status'    => __( 'Shipped', 'wcvendors' ),
		);

		if ( !$this->can_view_comments ) unset( $columns['comments'] );

		return apply_filters( 'wcvendors_vendor_order_page_get_columns', $columns );

	}


	/**
	 * get_sortable_columns function.
	 *
	 * @access public
	 * @return unknown
	 */
	function get_sortable_columns()
	{
		$sortable_columns = array(
			'order_id'  	=> array( 'order_id', false ),
			'total'  		=> array( 'total', false ),
			'status'     	=> array( 'status', false ),
		);

		return $sortable_columns;
	}


	/**
	 * Get bulk actions
	 *
	 * @return unknown
	 */
	function get_bulk_actions()
	{
		$actions = array(
			'mark_shipped'     =>  apply_filters( 'wcvendors_mark_shipped_label', __( 'Mark shipped', 'wcvendors' ) ),
		);

		return $actions;
	}


	/**
	 * Process bulk actions
	 *
	 * @return unknown
	 */
	function process_bulk_action()
	{
		if ( !isset( $_GET[ 'order_id' ] ) ) return;

		if (is_array(  $_GET[ 'order_id' ] ) ) { 

			$items = array_map( 'intval', $_GET[ 'order_id' ] );
			switch ( $this->current_action() ) {
				case 'mark_shipped':

					$result = $this->mark_shipped( $items );

					if ( $result )
						echo '<div class="updated"><p>' . __( 'Orders marked shipped.', 'wcvendors' ) . '</p></div>';
					break;

				default:
					// code...
					break;
			}

		} else { 

			if ( !isset( $_GET[ 'action' ] ) ) return;
		}
		

	}


	/**
	 *  Mark orders as shipped 
	 *
	 * @param unknown $ids (optional)
	 *
	 * @return unknown
	 */
	public function mark_shipped( $ids = array() )
	{
		global $woocommerce;

		$user_id = get_current_user_id();

		if ( !empty( $ids ) ) {
			foreach ($ids as $order_id ) {
				$order = wc_get_order( $order_id );
				$vendors = WCV_Vendors::get_vendors_from_order( $order );
				$vendor_ids = array_keys( $vendors );
				if ( !in_array( $user_id, $vendor_ids ) ) {
					wp_die( __( 'You are not allowed to modify this order.', 'wcvendors' ) );
				}
				$shippers = (array) get_post_meta( $order_id, 'wc_pv_shipped', true );
				if( !in_array($user_id, $shippers)) {
					$shippers[] = $user_id;
					$mails = $woocommerce->mailer()->get_emails();
					if ( !empty( $mails ) ) {
						$mails[ 'WC_Email_Notify_Shipped' ]->trigger( $order_id, $user_id );
					}
					do_action('wcvendors_vendor_ship', $order_id, $user_id);
				}
				update_post_meta( $order_id, 'wc_pv_shipped', $shippers );
			}
			return true; 
		}
		return false; 
	}



	/**
	 *  Get Orders to display in admin 
	 *
	 * @return $orders
	 */
	function get_orders() { 

		$user_id = get_current_user_id(); 

		$orders = array(); 

		$vendor_products = $this->get_vendor_products( $user_id );

		$products = array();

		foreach ( $vendor_products as $_product ) {

			$products[] = $_product->ID;
		}

		$_orders   = $this->get_orders_for_vendor_products( $products );
		
		$model_id = 0; 

		if ( !empty( $_orders ) ) { 

			foreach ( $_orders as $order ) {

				$order 			= wc_get_order( $order->order_id );
				$order_id 		= ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $order->id : $order->get_id();  	
				$valid_items 	= WCV_Queries::get_products_for_order( $order_id );
				$valid 			= array();

				$items = $order->get_items();

				foreach ( $items as $order_item_id => $item) {
					if ( in_array( $item[ 'variation_id' ], $valid_items) || in_array( $item[ 'product_id' ], $valid_items ) ) {
						$valid[ $order_item_id ] = $item;
					}
				}

				$products = ''; 

				foreach ( $valid as $order_item_id => $item ) {
					
					$wc_product 		= new WC_Product( $item['product_id'] );

					$products 			.= '<strong>'. $item['qty'] . ' x ' . $item['name'] . '</strong><br />'; 
					
					if ( version_compare( WC_VERSION, '2.7', '<' ) ) { 
						$metadata = $order->has_meta( $order_item_id ); 
					} else { 
						$_item            	= $order->get_item( $order_item_id );
						$meta_data       	= $_item->get_meta_data();
					}

					if ( !empty( $metadata ) ) {

						$products .= '<table cellspacing="0" class="wcv_display_meta">';
						
						foreach ( $metadata as $meta ) {

							// Skip hidden core fields
							if ( in_array( $meta['meta_key'], apply_filters( 'woocommerce_hidden_order_itemmeta', array(
								'_qty',
								'_tax_class',
								'_product_id',
								'_variation_id',
								'_line_subtotal',
								'_line_subtotal_tax',
								'_line_total',
								'_line_tax',
								'_vendor_order_item_id', 
								'_vendor_commission', 
								WC_Vendors::$pv_options->get_option( 'sold_by_label' ), 
							) ) ) ) {
								continue;
							}

							// Skip serialised meta
							if ( is_serialized( $meta['meta_value'] ) ) {
								continue;
							}

							// Get attribute data
							if ( taxonomy_exists( wc_sanitize_taxonomy_name( $meta['meta_key'] ) ) ) {
								$term               = get_term_by( 'slug', $meta['meta_value'], wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
								$meta['meta_key']   = wc_attribute_label( wc_sanitize_taxonomy_name( $meta['meta_key'] ) );
								$meta['meta_value'] = isset( $term->name ) ? $term->name : $meta['meta_value'];
							} else {
								$meta['meta_key']   = apply_filters( 'woocommerce_attribute_label', wc_attribute_label( $meta['meta_key'], $wc_product ), $meta['meta_key'] );
							}

							$products .= '<tr><th>' . wp_kses_post( rawurldecode( $meta['meta_key'] ) ) . ':</th><td>' . rawurldecode( $meta['meta_value'] ) . '</td></tr>';
						}
						$products .= '</table>';
					}
													
				}

				$order_id 	= ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $order->id : $order->get_id();  	
				$shippers 	= (array) get_post_meta( $order_id, 'wc_pv_shipped', true );
				$shipped 	= in_array($user_id, $shippers) ? __( 'Yes', 'wcvendors' ) : __( 'No', 'wcvendors' ) ; 

				$sum = WCV_Queries::sum_for_orders( array( $order_id  ), array('vendor_id' =>get_current_user_id() ), false ); 
				$sum = reset( $sum ); 

				WC_Vendors::log( $sum ); 

				$total = $sum->line_total; 

				$comment_output = '';

				$order_date = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $order->order_date : $order->get_date_created(); 

				// Make sure the correct address is shown based on the WooCommerce options 
				$customer = $order->get_formatted_billing_address();
				if ( ( get_option( 'woocommerce_ship_to_billing_address_only' ) === 'no' ) && ( $order->get_formatted_shipping_address() ) ) {
			        $customer = $order->get_formatted_shipping_address();
				} 

				$order_items = array(); 
				$order_items[ 'order_id' ] 	= $order_id;
				$order_items[ 'customer' ]	= $customer;
				$order_items[ 'products' ] 	= $products; 
				$order_items[ 'total' ] 	= wc_price( $total );
				$order_items[ 'date' ] 		= date_i18n( wc_date_format(), strtotime( $order_date ) ); 
				$order_items[ 'status' ] 	= $shipped;

				$orders[] = (object) $order_items; 

				$model_id++;
			}
		}
		return $orders; 

	}


	/**
	 *  Get the vendor products sold 
	 *
	 * @param $user_id  - the user_id to get the products of 
	 *
	 * @return unknown
	 */
	public function get_vendor_products( $user_id )
	{
		global $wpdb;

		$vendor_products = array();
		$sql = '';

		$sql .= "SELECT product_id FROM {$wpdb->prefix}pv_commission WHERE vendor_id = {$user_id} AND status != 'reversed' GROUP BY product_id"; 

		$results = $wpdb->get_results( $sql );

		foreach ( $results as $value ) {
			$ids[ ] = $value->product_id;
		}

		if ( !empty( $ids ) ) {
			$vendor_products = get_posts( 
				array(
				   'numberposts' => -1,
				   'orderby'     => 'post_date',
				   'post_type'   => array( 'product', 'product_variation' ),
				   'order'       => 'DESC',
				   'include'     => $ids
			  	)
			);
		}

		return $vendor_products;
	}


	/**
	 * All orders for a specific product
	 *
	 * @param array $product_ids
	 * @param array $args (optional)
	 *
	 * @return object
	 */
	public function get_orders_for_vendor_products( array $product_ids, array $args = array() )
	{
		global $wpdb;

		if ( empty( $product_ids ) ) return false;

		$defaults = array(
			'status' => apply_filters( 'wcvendors_completed_statuses', array( 'completed', 'processing' ) ),
		);

		$args = wp_parse_args( $args, $defaults );


		$sql = "
			SELECT order_id
			FROM {$wpdb->prefix}pv_commission as order_items
			WHERE   product_id IN ('" . implode( "','", $product_ids ) . "')
			AND     status != 'reversed'
		";

		if ( !empty( $args[ 'vendor_id' ] ) ) {
			$sql .= "
				AND vendor_id = {$args['vendor_id']}
			";
		}

		$sql .= "
			GROUP BY order_id
			ORDER BY time DESC
		";

		$orders = $wpdb->get_results( $sql );

		return $orders;
	}



	/**
	 * prepare_items function.
	 *
	 * @access public
	 */
	function prepare_items()
	{

		
		/**
		 * Init column headers
		 */
		$this->_column_headers = $this->get_column_info();


		/**
		 * Process bulk actions
		 */
		$this->process_bulk_action();

		/**
		 * Get items
		 */
		
		$this->items = $this->get_orders();

		/**
		 * Pagination
		 */
	}


}