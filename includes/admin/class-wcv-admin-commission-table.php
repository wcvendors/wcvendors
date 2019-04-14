<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Commission list Class.
 *
 * A class that generates the commission list.
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */
class WCVendors_Admin_Commission_Table extends WP_List_Table {
	
	protected $commission;
	public $log;

	/**
	 * Constructor
	 *
	 * @access public
	 * @since 2.0.0
	 * @version 2.0.0
	 */
	public function __construct(  ) {

		parent::__construct( array(
			'singular'  => 'commission',
			'plural'    => 'commissions',
			'ajax'      => false,
		) );

	}

	/**
	 * Prepares the items for display
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function prepare_items() {
		global $wpdb;

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->process_bulk_action();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$orderby = ! empty( $_REQUEST[ 'orderby' ] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'order_id';
		$order   = ( ! empty( $_REQUEST[ 'order' ] ) && 'asc' === $_REQUEST['order'] ) ? 'ASC' : 'DESC';

		$items_per_page = $this->get_items_per_page( 'commissions_per_page', apply_filters( 'wcvendors_commission_list_default_item_per_page', 20 ) );

		$current_page = $this->get_pagenum();

		// Replace with data_store query 

		// $sql = 'SELECT COUNT( commission.id ) FROM ' .  . ' AS commission';

		// $sql .= ' WHERE 1=1';

		// // check if it is a search
		// if ( ! empty( $_REQUEST['s'] ) ) {
		// 	$order_id = absint( $_REQUEST['s'] );

		// 	$sql .= " AND `order_id` = {$order_id}";

		// } else {

		// 	if ( ! empty( $_REQUEST['m'] ) ) {

		// 		$year  = absint( substr( $_REQUEST['m'], 0, 4 ) );
		// 		$month = absint( substr( $_REQUEST['m'], 4, 2 ) );

		// 		$time_filter = " AND MONTH( commission.order_date ) = {$month} AND YEAR( commission.order_date ) = {$year}";

		// 		$sql .= $time_filter;
		// 	}

		// 	if ( ! empty( $_REQUEST['commission_status'] ) ) {
		// 		$commission_status = esc_sql( $_REQUEST['commission_status'] );

		// 		$status_filter = " AND commission.commission_status = '{$commission_status}'";

		// 		$sql .= $status_filter;
		// 	}

		// 	if ( ! empty( $_REQUEST['vendor'] ) ) {
		// 		$vendor = absint( $_REQUEST['vendor'] );

		// 		$vendor_filter = " AND commission.vendor_id = '{$vendor}'";

		// 		$sql .= $vendor_filter;
		// 	}
		// }

		$total_items = 0;

		$this->set_pagination_args( array(
			'total_items' => (double) $total_items,
			'per_page'    => $items_per_page,
		) );

		$offset = ( $current_page - 1 ) * $items_per_page;

		// Replace with data_store query 

		// $sql = 'SELECT * FROM ' . wcv_COMMISSION_TABLE . ' AS commission';

		// $sql .= ' WHERE 1=1';

		// // check if it is a search
		// if ( ! empty( $_REQUEST['s'] ) ) {
		// 	$order_id = absint( $_REQUEST['s'] );

		// 	$sql .= " AND commission.order_id = {$order_id}";

		// } else {

		// 	if ( ! empty( $_REQUEST['m'] ) ) {
		// 		$sql .= $time_filter;
		// 	}

		// 	if ( ! empty( $_REQUEST['commission_status'] ) ) {
		// 		$sql .= $status_filter;
		// 	}

		// 	if ( ! empty( $_REQUEST['vendor'] ) ) {
		// 		$sql .= $vendor_filter;
		// 	}
		// }

		// $sql .= " ORDER BY `{$orderby}` {$order}";

		// $sql .= " LIMIT {$items_per_page}";

		// $sql .= " OFFSET {$offset}";

		// $data = $wpdb->get_results( $sql );

		$this->items = array(); 

		return true;
	}

	/**
	 * Adds additional views
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param mixed $views
	 * @return bool
	 */
	public function get_views() {
		$views = array(
			'all' => '<li class="all"><a href="' . admin_url( 'admin.php?page=wcv-commissions' ) . '">' . __( 'All', 'wcvendors' ) . '</a></li>',
			'due' => '<li class="all"><a href="' . admin_url( 'admin.php?page=wcv-commissions?=commission_status=due' ) . '">' . __( 'Due', 'wcvendors' ) . '</a></li>',
			'paid' => '<li class="all"><a href="' . admin_url( 'admin.php?page=wcv-commissions?=commission_status=paid' ) . '">' . __( 'Paid', 'wcvendors' ) . '</a></li>',
			'void' => '<li class="all"><a href="' . admin_url( 'admin.php?page=wcv-commissions?=commission_status=void' ) . '">' . __( 'Void', 'wcvendors' ) . '</a></li>',
		);

		return $views;
	}

	/**
	 * Adds filters to the table
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param string $position whether top/bottom
	 * @return bool
	 */
	public function extra_tablenav( $position ) {

		if ( 'top' === $position ) {

			$order_id          = '';
			$year              = '';
			$month             = '';
			$commission_status = '';
			$vendor            = '';

			if ( ! empty( $_REQUEST['s'] ) ) {
				$order_id = $_REQUEST['s'];

			} else {
				if ( ! empty( $_REQUEST['m'] ) ) {

					$year  = substr( $_REQUEST['m'], 0, 4 );
					$month = substr( $_REQUEST['m'], 4, 2 );
				}

				if ( ! empty( $_REQUEST['commission_status'] ) ) {
					$commission_status = $_REQUEST['commission_status'];
				}

				if ( ! empty( $_REQUEST['vendor'] ) ) {
					$vendor = $_REQUEST['vendor'];
				}
			}

			$other_attributes = array( 
				'data-nonce' 				=> esc_attr( wp_create_nonce( '_wcv_export_commissions_nonce' ) ),
				'data-order_id' 			=> esc_attr( $order_id ),
				'data-year' 				=> esc_attr( $year ),
				'data-month' 				=> esc_attr( $month ),
				'data-commission_status' 	=> esc_attr( $commission_status ),
				'data-vendor' 				=> esc_attr( $vendor ),
				'download=' 				=> esc_attr( sprintf( __( 'commissions-%s.csv', 'wcvendors' ), date( 'm-d-Y' ) ) ),
			); 	

			echo '<div class="alignleft actions">';
			$this->months_dropdown( 'commission' );
			$this->status_dropdown( 'commission' );
			$this->vendors_dropdown( 'commission' );
			submit_button( __( 'Filter', 'wcvendors' ), false, false, false );
			submit_button( __( 'Export Commissions', 'wcvendors' ), false, false, false, $other_attributes );
			echo '</div>';

		}
	}

	/**
	 * Displays the months filter
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function months_dropdown( $post_type ) {
		global $wpdb, $wp_locale;


		// Replace with data-store query 

		// $months = $wpdb->get_results( '
		// 	SELECT DISTINCT YEAR( commission.order_date ) AS year, MONTH( commission.order_date ) AS month
		// 	FROM ' . wcv_COMMISSION_TABLE . ' AS commission
		// 	ORDER BY commission.order_date DESC
		// ' );

		$month_count = 0; 

		$months = array();

		if ( ! $month_count || ( 1 === $month_count && 0 === $months[0]->month ) ) {
			return;
		}

		$m = isset( $_REQUEST[ 'm' ] ) ? (int) $_REQUEST[ 'm' ] : 0;
		?>

		<select name="m" id="filter-by-date">
			<option<?php selected( $m, 0 ); ?> value='0'><?php esc_html_e( 'Show all dates', 'wcvendors' ); ?></option>
			<?php
			foreach ( $months as $month ) {
				if ( 0 === $month->year ) {
					continue;
				}

				$month = zeroise( $month->month, 2 );
				$year  = $month->year;

				if ( '00' === $month || '0' === $year ) {
					continue;
				}

				printf( "<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $month->year . $month ),
					/* translators: 1: month name, 2: 4-digit year */
					sprintf( __( '%1$s %2$d', 'wcvendors' ), $wp_locale->get_month( $month ), $year )
				);
			}
			?>
		</select>
		
	<?php
	}

	/**
	 * Displays the commission status dropdown filter
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function status_dropdown( $post_type ) {
		$commission_status = isset( $_REQUEST['commission_status'] ) ? sanitize_text_field( $_REQUEST['commission_status'] ) : '';
	?>
		<select name="commission_status">
			<option <?php selected( $commission_status, '' ); ?> value=''><?php esc_html_e( 'Show all Statuses', 'wcvendors' ); ?></option>
			<option <?php selected( $commission_status, 'due' ); ?> value="due"><?php esc_html_e( 'Due', 'wcvendors' ); ?></option>
			<option <?php selected( $commission_status, 'paid' ); ?> value="paid"><?php esc_html_e( 'Paid', 'wcvendors' ); ?></option>
			<option <?php selected( $commission_status, 'void' ); ?> value="void"><?php esc_html_e( 'Void', 'wcvendors' ); ?></option>
		</select>
	<?php
		return true;
	}

	/**
	 * Displays the vendors dropdown filter
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function vendors_dropdown( $post_type ) {
		global $wpdb;

		$vendor = isset( $_REQUEST['vendor'] ) ? sanitize_text_field( $_REQUEST['vendor'] ) : '';

		// $sql = 'SELECT DISTINCT vendor_name, vendor_id FROM ' . wcv_COMMISSION_TABLE;

		// $vendor_lists = $wpdb->get_results( $sql );

		$vendor_lists = array(); 
	?>
		<select name="vendor">
			<option <?php selected( $vendor, '' ); ?> value=""><?php esc_html_e( 'Show all Vendors', 'wcvendors' ); ?></option>

			<?php
			if ( ! empty( $vendor_lists ) && is_array( $vendor_lists ) ) {
				foreach ( $vendor_lists as $vendor_list ) {
					if ( empty( $vendor_list->vendor_name ) || empty( $vendor_list->vendor_id ) ) {
						continue;
					}
					?>
					<option <?php selected( $vendor, $vendor_list->vendor_id ); ?> value="<?php echo esc_attr( $vendor_list->vendor_id ); ?>"><?php echo esc_html( $vendor_list->vendor_name ); ?></option>
					<?php
				}
			}
			?>
		</select>
	<?php
		return true;
	}

	/**
	 * Defines the columns to show
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return array $columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'                      	=> '<input type="checkbox" />',
			'order_id'                	=> __( 'Order', 			'wcvendors' ),
			'product_name'				=> __( 'Product Name', 		'wcvendors' ), 
			'vendor_name'           	=> __( 'Vendor', 			'wcvendors' ),
			'commission'		 		=> __( 'Commission', 		'wcvendors' ),
			'commission_status'       	=> __( 'Commission Status', 'wcvendors' ),
			'order_date'              	=> __( 'Order Date', 		'wcvendors' ),
			'paid_date'               	=> __( 'Paid Date', 		'wcvendors' ),
		);

		return $columns;
	}

	/**
	 * Adds checkbox to each row
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param object $item
	 * @return mixed
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="ids[%d]" value="%d" />', $item->id, $item->order_item_id );
	}

	/**
	 * Defines what data to show on each column
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param array $item
	 * @param string $column_name
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'order_id' :
				$order = wc_get_order( absint( $item->order_id ) );

				if ( is_object( $order ) ) {
					return edit_post_link( $order->get_order_number(), '', '', absint( $item->order_id ) );
				} else {
					return sprintf( '%s ' . __( 'Order Not Found', 'wcvendors' ), '#' . $order->get_order_number() );
				}

			case 'order_status' :
				$order = wc_get_order( $item->order_id );

				if ( is_object( $order ) ) {
					$formated_order_status = wcv_format_order_status( $order->get_status() );

					return sprintf( '<span class="wcv-order-status-%s">%s</span>', esc_attr( $order->get_status() ), $formated_order_status );
				} else {
					return __( 'N/A', 'wcvendors' );
				}

			case 'order_date' :

				if ( $item->order_date ) {
					return wcv_format_date( sanitize_text_field( $item->order_date ) );
				}

				return __( 'N/A', 'wcvendors' );

			case 'vendor_name' :
				$vendor = get_userdata( $item->vendor_id );

				if ( is_object( $vendor ) ){ 
					return '<a href="' . admin_url( 'user-edit.php?user_id=' . $item->vendor_id ) . '">' . $vendor->display_name . '</a>';
				} else {
					return sprintf( '%s ' . __( 'Vendor Not Found', 'wcvendors' ), '#' . absint( $item->vendor_id ) );
				}

			case 'product_name' :
				$quantity = absint( $item->product_quantity );

				$var_attributes = '';
				$sku = '';

				// check if product is a variable product
				if ( ! empty( $item->variation_id ) ) {
					$order   = wc_get_order( $item->order_id );
					$product = wc_get_product( absint( $item->variation_id ) );

					$order_item = WC_Order_Factory::get_order_item( $item->order_item_id );
					
					if ( $metadata = $order_item->get_formatted_meta_data() ) {
						foreach ( $metadata as $meta_id => $meta ) {
							// Skip hidden core fields
							if ( in_array( $meta->key, apply_filters( 'wcv_hidden_order_itemmeta', array(
								'_qty',
								'_tax_class',
								'_product_id',
								'_variation_id',
								'_line_subtotal',
								'_line_subtotal_tax',
								'_line_total',
								'_line_tax',
								'_fulfillment_status',
								'_commission_status',
								'method_id',
								'cost',
							) ) ) ) {
								continue;
							}

							$var_attributes .= sprintf( __( '<br /><small>( %1$s: %2$s )</small>', 'wcvendors' ), wp_kses_post( rawurldecode( $meta->display_key ) ), wp_kses_post( $meta->value ) );
						}
					}
				
				} else {
					$product = wc_get_product( absint( $item->product_id ) );
				}

				if ( is_object( $product ) && $product->get_sku() ) {
					$sku = sprintf( __( '%1$s %2$s: %s', 'wcvendors' ), '<br />', 'SKU', $product->get_sku() );
				}

				if ( is_object( $product ) ) {
					return edit_post_link( $quantity . 'x ' . sanitize_text_field( $item->product_name ), '', '', absint( $item->product_id ) ) . $var_attributes . $sku;

				} elseif ( ! empty( $item->product_name ) ) {
					return $quantity . 'x ' . sanitize_text_field( $item->product_name );

				} else {
					return sprintf( '%s ' . __( 'Product Not Found', 'wcvendors' ), '#' . absint( $item->product_id ) );
				}

			case 'commission' :
				// Show commission, shipping, tax and total 
				return wc_price( sanitize_text_field( $item->total_commission_amount ) );

			case 'commission_status' :
				$status = __( 'N/A', 'wcvendors' );

				if ( 'due' === $item->commission_status ) {
					$status = '<span class="wcv-due-status">' . esc_html__( 'DUE', 'wcvendors' ) . '</span>';
				}

				if ( 'paid' === $item->commission_status ) {
					$status = '<span class="wcv-paid-status">' . esc_html__( 'PAID', 'wcvendors' ) . '</span>';
				}

				if ( 'void' === $item->commission_status ) {
					$status = '<span class="wcv-void-status">' . esc_html__( 'VOID', 'wcvendors' ) . '</span>';
				}

			case 'paid_date' :
				// add how the commission was paid 
				return wcv_format_date( sanitize_text_field( $item->paid_date ) );

			default :
				return print_r( $item, true );
		}
	}

	/**
	 * Defines the hidden columns
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return array $columns
	 */
	public function get_hidden_columns() {
		// get user hidden columns
		$hidden = get_hidden_columns( $this->screen );

		$new_hidden = array();

		foreach ( $hidden as $k => $v ) {
			if ( ! empty( $v ) ) {
				$new_hidden[] = $v;
			}
		}

		return array_merge( array(), $new_hidden );
	}

	/**
	 * Returns the columns that need sorting
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return array $sort
	 */
	public function get_sortable_columns() {
		$sort = array(
			'order_id'          => array( 'order_id', false ),
			'vendor_name'       => array( 'vendor_name', false ),
			'commission_status' => array( 'commission_status', false ),
		);

		return $sort;
	}

	/**
	 * Display custom no items found text
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function no_items() {
		_e( 'No commissions found.', 'wcvendors' );

		return true;
	}

	/**
	 * Add bulk actions
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function get_bulk_actions() {

		$actions = array(
			'pay'         => __( 'Pay Commission', 'wcvendors' ),
			'unpaid'      => __( 'Mark Due', 'wcvendors' ),
			'paid'        => __( 'Mark Paid', 'wcvendors' ),
			'void'        => __( 'Mark Void', 'wcvendors' ),
			'delete'      => __( 'Delete Commission', 'wcvendors' ),
		);

		$actions = apply_filters( 'wcvendors_edit_bulk_actions', $actions );

		return $actions;
	}

	/**
	 * Processes bulk actions
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @return bool
	 */
	public function process_bulk_action() {
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-commissions' ) ) {
			return;
		}

		if ( empty( $_REQUEST['ids'] ) ) {
			return;
		}

		if ( false === $this->current_action() ) {
			return;
		}

		$status = sanitize_text_field( $this->current_action() );

		$ids = array_map( 'absint', $_REQUEST['ids'] );
		$update_status = false;

		// handle pay bulk action
		// if ( 'pay' === $this->current_action() ) {
		// 	try {
		// 		$results = $this->commission->pay( $ids );
		// 		$update_status = true;

		// 	} catch ( Exception $e ) {
		// 		$this->log->add( 'wcv-masspay', $e->getMessage() );
		// 	}
		// }

		$processed = 0;

		// foreach ( $ids as $id => $order_item_id ) {
		// 	switch ( $this->current_action() ) {
		// 		case 'pay' :
		// 			if ( $update_status ) {
		// 				$this->commission->update_status( $id, absint( $order_item_id ), 'paid' );
		// 			}
		// 			break;

		// 		case 'delete' :
		// 			$this->commission->delete( $id );
		// 			break;

		// 		case 'unpaid' :
		// 			$this->commission->update_status( $id, absint( $order_item_id ), 'unpaid' );
		// 			break;

		// 		case 'paid' :
		// 			$this->commission->update_status( $id, absint( $order_item_id ), 'paid' );
		// 			break;

		// 		case 'fulfilled' :
		// 			$this->set_fulfill_status( absint( $order_item_id ), 'fulfilled' );
		// 			break;

		// 		case 'unfulfilled' :
		// 			$this->set_fulfill_status( absint( $order_item_id ), 'unfulfilled' );
		// 			break;

		// 		case 'void' :
		// 			$this->commission->update_status( $id, absint( $order_item_id ), 'void' );
		// 			break;
		// 	}

		// 	$processed++;
		// }

		echo '<div class="notice-success notice"><p>' . sprintf( _n( '%d item processed.', '%d items processed', $processed, 'wcvendors' ), $processed ) . '</p></div>';

		// WC_Product_Vendors_Utils::clear_reports_transients();

		do_action( 'wcv_commission_list_bulk_action' );

		return true;
	}

	// /**
	//  * Set shipping status of an order item
	//  *
	//  * @access public
	//  * @since 1.0.0
	//  * @version 1.0.0
	//  * @param int $order_item_id
	//  * @param string $status
	//  * @return bool
	//  */
	// public function set_fulfill_status( $order_item_id, $status = 'unfulfilled' ) {
	// 	global $wpdb;

	// 	$sql = "UPDATE {$wpdb->prefix}woocommerce_order_itemmeta";
	// 	$sql .= ' SET `meta_value` = %s';
	// 	$sql .= ' WHERE `order_item_id` = %d AND `meta_key` = %s';

	// 	$status = $wpdb->get_var( $wpdb->prepare( $sql, $status, $order_item_id, '_fulfillment_status' ) );

	// 	return true;
	// }

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 * this overrides WP core simply to make column headers use REQUEST instead of GET
	 *
	 * @access public
	 * @since 1.0.0
	 * @version 1.0.0
	 * @param bool $with_id Whether to set the id attribute or not
	 * @return bool
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_REQUEST['orderby'] ) ) {
			$current_orderby = $_REQUEST['orderby'];
		} else {
			$current_orderby = '';
		}

		if ( isset( $_REQUEST['order'] ) && 'desc' == $_REQUEST['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;

			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . esc_html__( 'Select All', 'wcvendors' ) . '</label>'
				. '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';

			$cb_counter++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			$style = '';

			if ( in_array( $column_key, $hidden ) ) {
				$style = 'display:none;';
			}

			$style = ' style="' . $style . '"';

			if ( 'cb' == $column_key ) {
				$class[] = 'check-column';
			} elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) ) {
				$class[] = 'num';
			}

			if ( isset( $sortable[ $column_key ] ) ) {
				list( $orderby, $desc_first ) = $sortable[ $column_key ];

				if ( $current_orderby == $orderby ) {
					$order = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
			}

			$id = $with_id ? "id='$column_key'" : '';

			if ( ! empty( $class ) ) {
				$class = "class='" . join( ' ', $class ) . "'";
			}

			echo "<th scope='col' $id $class $style>$column_display_name</th>";
		}

		return true;
	}
}