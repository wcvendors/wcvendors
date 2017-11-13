<?php

class WCV_Admin_Setup
{
	/**
	 * WC > Referrals menu
	 */

	public function __construct()
	{
		add_filter( 'set-screen-option', array( 'WCV_Admin_Setup', 'set_table_option' ), 10, 3 );
		add_action( 'admin_menu', array( 'WCV_Admin_Setup', 'menu' ) );

		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'add_vendor_details' ), 10, 2 );
		add_action( 'woocommerce_admin_order_actions_end', array( $this, 'append_actions' ), 10, 1 );

		add_filter( 'woocommerce_debug_tools', array( $this, 'wcvendors_tools' ) ); 

		add_action( 'admin_head', array( $this, 'commission_table_header_styles' ) ); 
	}


	public function add_vendor_details( $order )
	{
		$actions = $this->append_actions( $order, true );

		if (empty( $actions['wc_pv_shipped']['name'] )) {
			return;
		}

		echo '<h4>' . __('Vendors shipped', 'wcvendors') . '</h4><br/>';
		echo $actions['wc_pv_shipped']['name'];
	}

	public function append_actions( $order, $order_page = false )
	{
		global $woocommerce;

		$order_id = ( version_compare( WC_VERSION, '2.7', '<' ) ) ? $order->id : $order->get_id();  	

		$authors = WCV_Vendors::get_vendors_from_order( $order );
		$authors = $authors ? array_keys( $authors ) : array();
		if ( empty( $authors ) ) return false;

		$shipped = (array) get_post_meta( $order_id, 'wc_pv_shipped', true );
		$string = '</br></br>';

		foreach ($authors as $author ) {
			 $string .= in_array( $author, $shipped ) ? '&#10004; ' : '&#10005; ';
			 $string .= WCV_Vendors::get_vendor_shop_name( $author );
			 $string .= '</br>';
		}

		$response = array(
			'url'       => '#',
			'name'      => __('Vendors Shipped', 'wcvendors') . $string,
			'action'    => 'wc_pv_shipped',
			'image_url' => wcv_assets_url . '/images/icons/truck.png',
		);

		if ( ! $order_page ) {
			printf( '<a class="button tips %s" href="%s" data-tip="%s"><img style="width:16px;height:16px;" src="%s"></a>', $response['action'], $response['url'], $response['name'], $response['image_url'] );
		} else {
			echo $response['name'];
		}

		return $response;
	}


	/**
	 * Add the commissions sub menu 
	 *
	 * @since 1.0.0 
	 * @access public 
	 *
	 */
	public static function menu()
	{
		$hook = add_submenu_page(
			'woocommerce',
			__( 'Commission', 'wcvendors' ), __( 'Commission', 'wcvendors' ),
			'manage_woocommerce',
			'pv_admin_commissions',
			array( 'WCV_Admin_Setup', 'commissions_page' )
		);

		add_action( "load-$hook", array( 'WCV_Admin_Setup', 'add_options' ) );

		add_action( "admin_print_styles-$hook", 	array( 'WCV_Admin_Setup', 'commission_enqueue_style' ) );
		add_action( "admin_print_scripts-$hook", 	array( 'WCV_Admin_Setup', 'commission_my_enqueue_script' ) );



	} // menu() 


	/**
	 * Add tools to the woocommerce status tools page 
	 * 
	 * @since 1.9.2
	 * @access public 
	 */
	public function wcvendors_tools( $tools ){ 

		$tools[ 'reset_wcvendor_roles' ] = array(
				'name'    => __( 'Reset WC Vendors roles ', 'wcvendors' ),
				'button'  => __( 'Reset WC Vendor Roles', 'wcvendors' ),
				'desc'    => __( 'This will reset the wcvendors roles ( vendor & pending_vendor ), back to the default capabilities.', 'wcvendors' ),
				'callback' => array( 'WCV_Admin_Setup', 'reset_vendor_roles' )
			); 

		$tools[ 'reset_wcvendors' ] = array(
				'name'    => __( 'Reset WC Vendors ', 'wcvendors' ),
				'button'  => __( 'Reset WC Vendors Settings', 'wcvendors' ),
				'desc'    => __( 'This will reset wcvendors back to defaults. This DELETES ALL YOUR Settings.', 'wcvendors' ),
				'callback' => array( 'WCV_Admin_Setup', 'reset_wcvendors' )
			); 

		return $tools; 

	} // wcvendors_tools() 

	/**
	 * Reset the vendor roles 
	 * 
	 * @since 1.9.2 
	 * @access public
	 */
	public static function reset_vendor_roles(){ 

		$can_add          = WC_Vendors::$pv_options->get_option( 'can_submit_products' );
		$can_edit         = WC_Vendors::$pv_options->get_option( 'can_edit_published_products' );
		$can_submit_live  = WC_Vendors::$pv_options->get_option( 'can_submit_live_products' );
		$can_view_reports = WC_Vendors::$pv_options->get_option( 'can_view_backend_reports' );

		$args = array(
			'assign_product_terms'      => $can_add,
			'edit_products'             => $can_add || $can_edit,
			'edit_published_products'   => $can_edit,
			'delete_published_products' => $can_edit,
			'delete_products'           => $can_edit,
			'manage_product'            => $can_add,
			'publish_products'          => $can_submit_live,
			'read'                      => true,
			'read_products'             => $can_edit || $can_add,
			'upload_files'              => true,
			'import'                    => true,
			'view_woocommerce_reports'  => false,
		);

		remove_role( 'vendor' );
		add_role( 'vendor', __('Vendor', 'wcvendors'), $args );

		remove_role( 'pending_vendor'); 
		add_role( 'pending_vendor', __( 'Pending Vendor', 'wcvendors' ), array(
																							  'read'         => true,
																							  'edit_posts'   => false,
																							  'delete_posts' => true
																						 ) );

		echo '<div class="updated inline"><p>' . __( 'WC Vendor roles successfully reset.', 'wcvendors' ) . '</p></div>';

	} // reset_vendor_roles() 


	/**
	 * Reset wcvendors
	 * 
	 * @since 1.9.2 
	 * @access public
	 */	
	public static function reset_wcvendors(){ 

		delete_option( WC_Vendors::$id . '_options' ); 
		echo '<div class="updated inline"><p>' . __( 'WC Vendors was successfully reset. All settings have been reset.', 'wcvendors' ) . '</p></div>';

	} // reset_wcvendors() 


	public static function commission_enqueue_style(){ 

		wp_enqueue_style( 'commissions_select2_css', wcv_assets_url . 'css/select2.min.css' );

	} //commission_enqueue_style() 

	public static function commission_my_enqueue_script(){ 

		$select2_args = apply_filters( 'wcvendors_select2_commission_args', array( 
			'placeholder' => __( 'Select a Vendor', 'wcvendors' ), 
			'allowclear' => true, 
		) ); 

		wp_enqueue_script( 'commissions_select2_styles_js', wcv_assets_url. 'js/select2.min.js', array('jquery') );
		
		wp_register_script( 'commissions_select2_load_js', wcv_assets_url. 'js/wcv-commissions.js', array('jquery') );
		wp_localize_script( 'commissions_select2_load_js', 'wcv_commissions_select', $select2_args ); 
		wp_enqueue_script( 'commissions_select2_load_js' );

	}

	/**
	 * Load styles for the commissions table page 
	 */
	public function commission_table_header_styles() { 

	    $page = ( isset( $_GET[ 'page' ] ) ) ? esc_attr( $_GET[ 'page' ] ) : false;

	    // Only load the styles on the license table page 
	   
	    if ( 'pv_admin_commissions' !== $page ) return;

	    echo '<style type="text/css">';
	    echo '.wp-list-table .column-product_id { width: 20%; }';
	    echo '.wp-list-table .column-vendor_id { width: 15%; }';
	    echo '.wp-list-table .column-order_id { width: 8%; }';
	    echo '.wp-list-table .column-total_due { width: 10%;}';
	    echo '.wp-list-table .column-total_shipping { width: 10%;}';
	    echo '.wp-list-table .column-tax { width: 10%;}';
	    echo '.wp-list-table .column-totals { width: 10%;}';
	    echo '.wp-list-table .column-status { width: 5%;}';
	    echo '.wp-list-table .column-time { width: 10%;}';
	    echo '</style>';
	  
	} //table_header_styles()

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
		if ( $option == 'commission_per_page' ) {
			return $value;
		}
	}


	/**
	 *
	 */
	public static function add_options()
	{
		global $PV_Admin_Page;

		$args = array(
			'label'   => 'Rows',
			'default' => 10,
			'option'  => 'commission_per_page'
		);
		add_screen_option( 'per_page', $args );

		$PV_Admin_Page = new WCV_Admin_Page();

	}


	/**
	 * HTML setup for the WC > Commission page
	 */
	public static function commissions_page()
	{
		global $woocommerce, $PV_Admin_Page;

		

		?>

		<div class="wrap">

			<div id="icon-woocommerce" class="icon32 icon32-woocommerce-reports"><br/></div>
			<h2><?php _e( 'Commission', 'wcvendors' ); ?></h2>

			<form id="posts-filter" method="get">

			<?php 
				$page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );
				$paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );

				printf( '<input type="hidden" name="page" value="%s" />', $page );
				printf( '<input type="hidden" name="paged" value="%d" />', $paged );
			?>

			<input type="hidden" name="page" value="pv_admin_commissions"/>
				
			<?php $PV_Admin_Page->prepare_items(); ?>
			<?php $PV_Admin_Page->display() ?>

			</form>
			<div id="ajax-response"></div>
			<br class="clear"/>
		</div>
	<?php
	}


}


if ( !class_exists( 'WP_List_Table' ) ) require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

/**
 * WC_Simple_Referral_Admin class.
 *
 * @extends WP_List_Table
 */
class WCV_Admin_Page extends WP_List_Table
{

	public $index;


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
								  'singular' => 'commission',
								  'plural'   => 'commissions',
								  'ajax'     => false
							 ) );
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
			case 'id' :
				return $item->id;
			case 'vendor_id' :
				$user = get_userdata( $item->vendor_id );
				return '<a href="' . admin_url( 'user-edit.php?user_id=' . $item->vendor_id ) . '">' . WCV_Vendors::get_vendor_shop_name( $item->vendor_id ) . '</a>';
			case 'total_due' :
				return wc_price( $item->total_due );
			case 'total_shipping': 
				return wc_price($item->total_shipping );
			case 'tax': 
				return wc_price( $item->tax );
			case 'totals' :
				$totals = ( wc_tax_enabled() ) ? $item->total_due + $item->total_shipping + $item->tax :  $item->total_due + $item->total_shipping; 
				return wc_price( $totals );
			case 'product_id' :
				$parent = get_post_ancestors( $item->product_id );
				$product_id = $parent ? $parent[ 0 ] : $item->product_id;
				$wcv_total_sales = get_post_meta( $product_id, 'total_sales', true );
				if ( ! get_post_status( $product_id ) ) { 
					error_log('deleted product id: ' . $product_id );
					$product_id = WCV_Vendors::find_parent_id_from_order( $item->order_id, $product_id ); 
				}
                return '<a href="' . admin_url( 'post.php?post=' . $product_id . '&action=edit' ) . '">' . get_the_title( $product_id ) . '</a> (<span title="' . get_the_title( $product_id ) .' has sold ' . $wcv_total_sales . ' times total.">' . $wcv_total_sales  . '</span>)';
			case 'order_id' :
				$order = wc_get_order( $item->order_id );
				if ( $order ) {
					return '<a href="' . admin_url( 'post.php?post=' . $item->order_id . '&action=edit' ) . '">' . $order->get_order_number() . '</a>';
				} else { 
					return $item->order_id; 
				}
			case 'status' :
				return $item->status;
			case 'time' :
				return date_i18n( get_option( 'date_format' ), strtotime( $item->time ) );
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
			'id',
			/*$2%s*/
			$item->id
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
			'cb'         		=> '<input type="checkbox" />',
			'product_id' 		=> __( 'Product', 'wcvendors' ),
			'order_id'   		=> __( 'Order ID', 'wcvendors' ),
			'vendor_id' 	    => __( 'Vendor', 'wcvendors' ),
			'total_due'  		=> __( 'Commission', 'wcvendors' ),
			'total_shipping'  	=> __( 'Shipping', 'wcvendors' ),
			'tax'  				=> __( 'Tax', 'wcvendors' ),
			'totals'  			=> __( 'Total', 'wcvendors' ),
			'status'     		=> __( 'Status', 'wcvendors' ),
			'time'       		=> __( 'Date', 'wcvendors' ),
		);

		if ( ! wc_tax_enabled() ) unset( $columns[ 'tax'] ); 

		return $columns;
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
			'time'       		=> array( 'time', true ),
			'product_id' 		=> array( 'product_id', false ),
			'order_id'   		=> array( 'order_id', false ),
			'total_due' 		=> array( 'total_due', false ),
			'total_shipping'	=> array( 'total_shipping', false ), 
			'tax'				=> array( 'tax', false ), 
			'totals' 			=> array( 'totals', false ),
			'status'    		=> array( 'status', false ),
			'vendor_id' 		=> array( 'vendor_id', false ),
			'status'    		=> array( 'status', false ),
		);

		if ( ! wc_tax_enabled() ) unset( $sortable_columns[ 'tax'] ); 

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
			'mark_paid'     => __( 'Mark paid', 'wcvendors' ),
			'mark_due'      => __( 'Mark due', 'wcvendors' ),
			'mark_reversed' => __( 'Mark reversed', 'wcvendors' ),
			// 'delete' => __('Delete', 'wcvendors'),
		);
		
		$actions = apply_filters('wcv_edit_bulk_actions', $actions);

		return $actions;
	}


	/**
	 *
	 */
	function extra_tablenav( $which )
	{
		if ( $which == 'top' ) {
			?><div class="alignleft actions" style="width: 70%;">
			<?php
			// Months drop down 
			$this->months_dropdown( 'commission' );
			
			// commission status drop down 
			$this->status_dropdown( 'commission' );
			
			// Vendor drop down 
			$this->vendor_dropdown( 'commission' );
			
			submit_button( __( 'Filter' ), false, false, false, array( 'id' => "post-query-submit", 'name' => 'do-filter' ) );
			?></div>
			<?php
		}
	}


	/**
	 * Display a monthly dropdown for filtering items
	 *
	 * @since  3.1.0
	 * @access protected
	 *
	 * @param unknown $post_type
	 */
	function months_dropdown( $post_type )
	{
		global $wpdb, $wp_locale;

		$table_name = $wpdb->prefix . "pv_commission";

		$months = $wpdb->get_results( "
			SELECT DISTINCT YEAR( time ) AS year, MONTH( time ) AS month
			FROM $table_name
			ORDER BY time DESC
		" );

		$month_count = count( $months );

		if ( !$month_count || ( 1 == $month_count && 0 == $months[ 0 ]->month ) )
			return;

		$m = isset( $_GET[ 'm' ] ) ? (int) $_GET[ 'm' ] : 0;
		?>
		<select name="m" id="filter-by-date">
			<option<?php selected( $m, 0 ); ?> value='0'><?php _e( 'Show all dates', 'wcvendors' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year )
					continue;

				$month = zeroise( $arc_row->month, 2 );
				$year  = $arc_row->year;

				printf( "<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					/* translators: 1: month name, 2: 4-digit year */
					sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
				);
			}
			?>
		</select>
		
	<?php
	}

	/**
	 * Display a status dropdown for filtering items
	 *
	 * @since  3.1.0
	 * @access protected
	 *
	 * @param unknown $post_type
	 */
	function status_dropdown( $post_type )
	{
		$com_status = isset( $_GET[ 'com_status' ] ) ? $_GET[ 'com_status' ] : '';
		?>
		<select name="com_status">
			<option<?php selected( $com_status, '' ); ?> value=''><?php _e( 'Show all Statuses', 'wcvendors' ); ?></option>
			<option<?php selected( $com_status, 'due' ); ?> value="due"><?php _e( 'Due', 'wcvendors' ); ?></option>
			<option<?php selected( $com_status, 'paid' ); ?> value="paid"><?php _e( 'Paid', 'wcvendors' ); ?></option>
			<option<?php selected( $com_status, 'reversed' ); ?> value="reversed"><?php _e( 'Reversed', 'wcvendors' ); ?></option>
		</select>
	<?php
	}

	/**
	 * Display a vendor dropdown for filtering commissions
	 *
	 * @since  1.9.2
	 * @access public
	 *
	 * @param unknown $post_type
	 */
	public function vendor_dropdown( $post_type ){ 

		$user_args 			= array( 'fields' => array( 'ID', 'display_name' ) );
		$vendor_id 			= isset( $_GET[ 'vendor_id' ] ) ? $_GET[ 'vendor_id' ] : '';
		$new_args           = $user_args;
		$new_args[ 'role' ] = 'vendor';
		$users              = get_users( $new_args );

		// Generate the drop down 
		$output = '<select style="width: 30%;" name="vendor_id" id="vendor_id" class="select2">'; 
		$output .= "<option></option>";
		foreach ( (array) $users as $user ) {
			$select = selected( $user->ID, $vendor_id, false );
			$output .= "<option value='$user->ID' $select>$user->display_name</option>";
		}
		$output .= '</select>';

		echo $output; 

	} // vendor_dropdown() 



	/**
	 * Process bulk actions
	 *
	 * @return unknown
	 */
	function process_bulk_action()
	{
		if ( !isset( $_GET[ 'id' ] ) ) return;

		$items = array_map( 'intval', $_GET[ 'id' ] );
		$ids   = implode( ',', $items );

		switch ( $this->current_action() ) {
			case 'mark_paid':
				$result = $this->mark_paid( $ids );

				if ( $result )
					echo '<div class="updated"><p>' . __( 'Commission marked paid.', 'wcvendors' ) . '</p></div>';
				break;

			case 'mark_due':
				$result = $this->mark_due( $ids );

				if ( $result )
					echo '<div class="updated"><p>' . __( 'Commission marked due.', 'wcvendors' ) . '</p></div>';
				break;

			case 'mark_reversed':
				$result = $this->mark_reversed( $ids );

				if ( $result )
					echo '<div class="updated"><p>' . __( 'Commission marked reversed.', 'wcvendors' ) . '</p></div>';
				break;

			default:
				// code...
				do_action('wcv_edit_process_bulk_actions', $this->current_action(), $ids);
				break;
		}

	}


	/**
	 *
	 *
	 * @param unknown $ids (optional)
	 *
	 * @return unknown
	 */
	public function mark_paid( $ids = array() )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . "pv_commission";

		$query  = "UPDATE `{$table_name}` SET `status` = 'paid' WHERE id IN ($ids) AND `status` = 'due'";
		$result = $wpdb->query( $query );

		return $result;
	}


	/**
	 *
	 *
	 * @param unknown $ids (optional)
	 *
	 * @return unknown
	 */
	public function mark_reversed( $ids = array() )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . "pv_commission";
		$query  = "UPDATE `{$table_name}` SET `status` = 'reversed' WHERE id IN ($ids)";
		$result = $wpdb->query( $query );

		return $result; 

	}


	/**
	 *
	 *
	 * @param unknown $ids (optional)
	 *
	 * @return unknown
	 */
	public function mark_due( $ids = array() )
	{
		global $wpdb;

		$table_name = $wpdb->prefix . "pv_commission";

		$query  = "UPDATE `{$table_name}` SET `status` = 'due' WHERE id IN ($ids)";
		$result = $wpdb->query( $query );

		return $result;
	}


	/**
	 * cubrid_prepare(conn_identifier, prepare_stmt)_items function.
	 *
	 * @access public
	 */
	function prepare_items()
	{
		global $wpdb;

		$_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );

		$per_page     = $this->get_items_per_page( 'commission_per_page', 10 );
		$current_page = $this->get_pagenum();

		$orderby = !empty( $_REQUEST[ 'orderby' ] ) ? esc_attr( $_REQUEST[ 'orderby' ] ) : 'time';
		$order   = ( !empty( $_REQUEST[ 'order' ] ) && $_REQUEST[ 'order' ] == 'asc' ) ? 'ASC' : 'DESC';
		$com_status = !empty( $_REQUEST[ 'com_status' ] ) ? esc_attr( $_REQUEST[ 'com_status' ] ) : '';
		$vendor_id = !empty( $_REQUEST[ 'vendor_id' ] ) ? esc_attr( $_REQUEST[ 'vendor_id' ] ) : '';
		$status_sql = '';
		$time_sql = ''; 

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
		$sql = "SELECT COUNT(id) FROM {$wpdb->prefix}pv_commission";

		if ( !empty( $_GET[ 'm' ] ) ) {

			$year  = substr( $_GET[ 'm' ], 0, 4 );
			$month = substr( $_GET[ 'm' ], 4, 2 );

			$time_sql = "
				WHERE MONTH(`time`) = '$month'
				AND YEAR(`time`) = '$year'
			";

			$sql .= $time_sql;
		}

		if ( !empty( $_GET[ 'com_status' ] ) ) { 

			if ( $time_sql == '' ) { 
				$status_sql = " 
				WHERE status = '$com_status'
				"; 
			} else { 
				$status_sql = " 
				AND status = '$com_status'
				";
			}
			
			$sql .= $status_sql; 
		}


		if ( !empty( $_GET[ 'vendor_id' ] ) ) { 

			if ( $time_sql == '' || $status_sql == '' ) { 
				$vendor_sql = " 
				WHERE vendor_id = '$vendor_id'
				"; 
			} else { 
				$vendor_sql = " 
				AND vendor_id = '$vendor_id'
				";
			}
			
			$sql .= $vendor_sql; 
		}

		$max = $wpdb->get_var( $sql );

		$sql = "
			SELECT * FROM {$wpdb->prefix}pv_commission
		";

		if ( !empty( $_GET[ 'm' ] ) ) {
			$sql .= $time_sql;
		}

		if ( !empty( $_GET['com_status'] ) ) { 
			$sql .= $status_sql;
		}

		if ( !empty( $_GET['vendor_id'] ) ) { 
			$sql .= $vendor_sql;
		}

		$offset = ( $current_page - 1 ) * $per_page; 

		$sql .= "
			ORDER BY `{$orderby}` {$order}
			LIMIT {$offset}, {$per_page}
		";

		// $this->items = $wpdb->get_results( $wpdb->prepare( $sql, ( $current_page - 1 ) * $per_page, $per_page ) );
		$this->items = $wpdb->get_results( $sql );

		/**
		 * Pagination
		 */
		$this->set_pagination_args( array(
										 'total_items' => $max,
										 'per_page'    => $per_page,
										 'total_pages' => ceil( $max / $per_page )
									) );
	}


}
