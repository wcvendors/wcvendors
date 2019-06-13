<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WCVendors_Commissions_Page class.
 *
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 * @extends     WP_List_Table
 */
class WCVendors_Commissions_Page extends WP_List_Table {

	public $index;

	/**
	 * __construct function.
	 *
	 * @access public
	 */
	public function __construct() {

		global $status, $page;

		$this->index = 0;

		// Set parent defaults
		parent::__construct(
			array(
				'singular' => 'commission',
				'plural'   => 'commissions',
				'ajax'     => false,
			)
		);
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
	public function column_default( $item, $column_name ) {

		global $wpdb;

		switch ( $column_name ) {
			case 'id':
				return $item->id;
			case 'vendor_id':
				$user = get_userdata( $item->vendor_id );

				return '<a href="' . admin_url( 'user-edit.php?user_id=' . $item->vendor_id ) . '">' . WCV_Vendors::get_vendor_shop_name( $item->vendor_id ) . '</a>';
			case 'total_due':
				return wc_price( $item->total_due );
			case 'total_shipping':
				return wc_price( $item->total_shipping );
			case 'tax':
				return wc_price( $item->tax );
			case 'qty':
				return $item->qty;
			case 'totals':
				$totals = ( wc_tax_enabled() ) ? $item->total_due + $item->total_shipping + $item->tax : $item->total_due + $item->total_shipping;

				return wc_price( $totals );
			case 'product_id':
				$parent          = get_post_ancestors( $item->product_id );
				$product_id      = $parent ? $parent[0] : $item->product_id;
				$wcv_total_sales = get_post_meta( $product_id, 'total_sales', true );
				if ( ! get_post_status( $product_id ) ) {
					$product_id = WCV_Vendors::find_parent_id_from_order( $item->order_id, $product_id );
				}

				return '<a href="' . admin_url( 'post.php?post=' . $product_id . '&action=edit' ) . '">' . get_the_title( $product_id ) . '</a> (<span title="' . get_the_title( $product_id ) . ' has sold ' . $wcv_total_sales . ' times total.">' . $wcv_total_sales . '</span>)';
			case 'order_id':
				$order = wc_get_order( $item->order_id );
				if ( $order ) {
					return '<a href="' . admin_url( 'post.php?post=' . $item->order_id . '&action=edit' ) . '">' . $order->get_order_number() . '</a>';
				} else {
					return $item->order_id;
				}
			case 'status':
				return $item->status;
			case 'time':
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
	public function column_cb( $item ) {

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
	public function get_columns() {

		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'order_id'       => __( 'Order ID', 'wc-vendors' ),
			'vendor_id'      => sprintf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ),
			'product_id'     => __( 'Product', 'wc-vendors' ),
			'qty'     	     => __( 'Quantity', 'wc-vendors' ),
			'total_due'      => __( 'Commission', 'wc-vendors' ),
			'total_shipping' => __( 'Shipping', 'wc-vendors' ),
			'tax'            => __( 'Tax', 'wc-vendors' ),
			'totals'         => __( 'Total', 'wc-vendors' ),
			'status'         => __( 'Status', 'wc-vendors' ),
			'time'           => __( 'Date', 'wc-vendors' ),
		);

		if ( ! wc_tax_enabled() ) {
			unset( $columns['tax'] );
		}

		return $columns;
	}


	/**
	 * get_sortable_columns function.
	 *
	 * @access public
	 * @return unknown
	 */
	public function get_sortable_columns() {

		$sortable_columns = array(
			'time'           => array( 'time', true ),
			'product_id'     => array( 'product_id', false ),
			'qty'     		 => array( 'qty', false ),
			'order_id'       => array( 'order_id', false ),
			'total_due'      => array( 'total_due', false ),
			'total_shipping' => array( 'total_shipping', false ),
			'tax'            => array( 'tax', false ),
			'totals'         => array( 'totals', false ),
			'status'         => array( 'status', false ),
			'vendor_id'      => array( 'vendor_id', false ),
			'status'         => array( 'status', false ),
		);

		if ( ! wc_tax_enabled() ) {
			unset( $sortable_columns['tax'] );
		}

		return $sortable_columns;
	}


	/**
	 * Get bulk actions
	 *
	 * @return unknown
	 */
	public function get_bulk_actions() {

		$actions = array(
			'mark_paid'     => __( 'Mark paid', 'wc-vendors' ),
			'mark_due'      => __( 'Mark due', 'wc-vendors' ),
			'mark_reversed' => __( 'Mark reversed', 'wc-vendors' ),
			// 'delete' => __('Delete', 'wc-vendors'),
		);

		$actions = apply_filters( 'wcv_edit_bulk_actions', $actions );

		return $actions;
	}


	/**
	 *
	 */
	public function extra_tablenav( $which ) {

		$m          = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
		$com_status = isset( $_GET['com_status'] ) ? $_GET['com_status'] : '';
		$vendor_id  = isset( $_GET['vendor_id'] ) ? $_GET['vendor_id'] : '';
		$args_url   = '';

		if ( $m ) {
			$args_url .= '&m=' . $m;
		}
		if ( $com_status ) {
			$args_url .= '&com_status=' . $com_status;
		}
		if ( $vendor_id ) {
			$args_url .= '&vendor_id=' . $vendor_id;
		}

		if ( $which == 'top' ) {
			echo '<div class="alignleft actions" style="width: 70%;">';

			// Months drop down
			$this->months_dropdown( 'commission' );

			// commission status drop down
			$this->status_dropdown( 'commission' );

			// Vendor drop down
			$this->vendor_dropdown( 'commission' );

			submit_button(
				__( 'Filter', 'wc-vendors' ), false, false, false, array(
					'id'   => 'post-query-submit',
					'name' => 'do-filter',
				)
			);
			submit_button( __( 'Clear', 'wc-vendors' ), 'secondary', 'reset', false, array( 'type' => 'reset' ) );

			echo '<a class="button export_commissions" style="width: 110px; float: left;" href="' . wp_nonce_url( admin_url( 'admin.php?page=wcv-commissions&action=export_commissions' . $args_url ), 'export_commissions', 'nonce' ) . '">' . __( 'Export to CSV', 'wc-vendors' ) . '</a>';
			echo '<a class="button export_commission_totals" style="width: 150px; float: left;" href="' . wp_nonce_url( admin_url( 'admin.php?page=wcv-commissions&action=export_commission_totals' . $args_url ), 'export_commission_totals', 'nonce' ) . '">' . __( 'Export Totals to CSV', 'wc-vendors' ) . '</a>';
			echo '<a class="button mark_all_commissions_paid" id="mark_all_paid" style="width: 100px; float: left;" href="' . wp_nonce_url( admin_url( 'admin.php?page=wcv-commissions&action=mark_all_paid' . $args_url ), 'mark_all_paid', 'nonce' ) . '">' . __( 'Mark all paid', 'wc-vendors' ) . '</a>';
			echo '</div>';

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
	public function months_dropdown( $post_type ) {

		global $wpdb, $wp_locale;

		$table_name = $wpdb->prefix . 'pv_commission';

		$months = $wpdb->get_results(
			"
			SELECT DISTINCT YEAR( time ) AS year, MONTH( time ) AS month
			FROM $table_name
			ORDER BY time DESC
		"
		);

		$month_count = count( $months );

		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
			return;
		}

		$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
		?>
		<select name="m" id="filter-by-date" class="wc-enhanced-select-nostd" style="min-width:150px;">
			<option<?php selected( $m, 0 ); ?> value='0'><?php _e( 'Show all dates', 'wc-vendors' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year ) {
					continue;
				}

				$month = zeroise( $arc_row->month, 2 );
				$year  = $arc_row->year;

				printf(
					"<option %s value='%s'>%s</option>\n",
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
	public function status_dropdown( $post_type ) {

		$com_status = isset( $_GET['com_status'] ) ? $_GET['com_status'] : '';
		?>
		<select name="com_status" class="wc-enhanced-select">
			<option<?php selected( $com_status, '' ); ?>
					value=''><?php _e( 'Show all Statuses', 'wc-vendors' ); ?></option>
			<option<?php selected( $com_status, 'due' ); ?> value="due"><?php _e( 'Due', 'wc-vendors' ); ?></option>
			<option<?php selected( $com_status, 'paid' ); ?> value="paid"><?php _e( 'Paid', 'wc-vendors' ); ?></option>
			<option<?php selected( $com_status, 'reversed' ); ?>
					value="reversed"><?php _e( 'Reversed', 'wc-vendors' ); ?></option>
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
	public function vendor_dropdown( $post_type ) {

		$user_args        = array( 'fields' => array( 'ID', 'display_name' ) );
		$vendor_id        = isset( $_GET['vendor_id'] ) ? $_GET['vendor_id'] : '';
		$new_args         = $user_args;
		$new_args['role'] = 'vendor';
		$users            = get_users( $new_args );

		// Generate the drop down
		$output = '<select style="width:250px;" name="vendor_id" id="vendor_id" class="wc-enhanced-select">';
		$output .= '<option></option>';
		$output .= wcv_vendor_drop_down_options( $users, $vendor_id );
		$output .= '</select>';
		$output .= '<script type="text/javascript">jQuery(function() { jQuery("#vendor_id").select2(); } );</script>';

		echo $output;

	} // vendor_dropdown()


	/**
	 * Process bulk actions
	 *
	 * @return unknown
	 */
	public function process_bulk_action() {

		if ( ! isset( $_GET['id'] ) ) {
			return;
		}

		$items = array_map( 'intval', $_GET['id'] );
		$ids   = implode( ',', $items );

		switch ( $this->current_action() ) {
			case 'mark_paid':
				$result = $this->mark_paid( $ids );

				if ( $result ) {
					echo '<div class="updated"><p>' . __( 'Commission marked paid.', 'wc-vendors' ) . '</p></div>';
				}
				break;

			case 'mark_due':
				$result = $this->mark_due( $ids );

				if ( $result ) {
					echo '<div class="updated"><p>' . __( 'Commission marked due.', 'wc-vendors' ) . '</p></div>';
				}
				break;

			case 'mark_reversed':
				$result = $this->mark_reversed( $ids );

				if ( $result ) {
					echo '<div class="updated"><p>' . __( 'Commission marked reversed.', 'wc-vendors' ) . '</p></div>';
				}
				break;

			default:
				// code...
				do_action( 'wcv_edit_process_bulk_actions', $this->current_action(), $ids );
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
	public function mark_paid( $ids = array() ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

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
	public function mark_reversed( $ids = array() ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';
		$query      = "UPDATE `{$table_name}` SET `status` = 'reversed' WHERE id IN ($ids)";
		$result     = $wpdb->query( $query );

		return $result;

	}


	/**
	 *
	 *
	 * @param unknown $ids (optional)
	 *
	 * @return unknown
	 */
	public function mark_due( $ids = array() ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$query  = "UPDATE `{$table_name}` SET `status` = 'due' WHERE id IN ($ids)";
		$result = $wpdb->query( $query );

		return $result;
	}


	/**
	 * cubrid_prepare(conn_identifier, prepare_stmt)_items function.
	 *
	 * @access public
	 */
	public function prepare_items() {

		global $wpdb;

		$_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $_SERVER['REQUEST_URI'] );

		$per_page     = $this->get_items_per_page( 'commissions_per_page', 10 );
		$current_page = $this->get_pagenum();

		$orderby    = ! empty( $_REQUEST['orderby'] ) ? esc_attr( $_REQUEST['orderby'] ) : 'time';
		$order      = ( ! empty( $_REQUEST['order'] ) && $_REQUEST['order'] == 'asc' ) ? 'ASC' : 'DESC';
		$com_status = ! empty( $_REQUEST['com_status'] ) ? esc_attr( $_REQUEST['com_status'] ) : '';
		$vendor_id  = ! empty( $_REQUEST['vendor_id'] ) ? esc_attr( $_REQUEST['vendor_id'] ) : '';
		$status_sql = '';
		$time_sql   = '';

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

		if ( ! empty( $_GET['m'] ) ) {

			$year  = substr( $_GET['m'], 0, 4 );
			$month = substr( $_GET['m'], 4, 2 );

			$time_sql
				= "
				WHERE MONTH(`time`) = '$month'
				AND YEAR(`time`) = '$year'
			";

			$sql .= $time_sql;
		}

		if ( ! empty( $_GET['com_status'] ) ) {

			if ( $time_sql == '' ) {
				$status_sql
					= "
				WHERE status = '$com_status'
				";
			} else {
				$status_sql
					= "
				AND status = '$com_status'
				";
			}

			$sql .= $status_sql;
		}

		if ( ! empty( $_GET['vendor_id'] ) ) {

			if ( $time_sql == '' && $status_sql == '' ) {
				$vendor_sql
					= "
				WHERE vendor_id = '$vendor_id'
				";
			} else {
				$vendor_sql
					= "
				AND vendor_id = '$vendor_id'
				";
			}

			$sql .= $vendor_sql;
		}

		$max = $wpdb->get_var( $sql );

		$sql
			= "
			SELECT * FROM {$wpdb->prefix}pv_commission
		";

		if ( ! empty( $_GET['m'] ) ) {
			$sql .= $time_sql;
		}

		if ( ! empty( $_GET['com_status'] ) ) {
			$sql .= $status_sql;
		}

		if ( ! empty( $_GET['vendor_id'] ) ) {
			$sql .= $vendor_sql;
		}

		$offset = ( $current_page - 1 ) * $per_page;

		$sql
			.= "
			ORDER BY `{$orderby}` {$order}
			LIMIT {$offset}, {$per_page}
		";

		// $this->items = $wpdb->get_results( $wpdb->prepare( $sql, ( $current_page - 1 ) * $per_page, $per_page ) );
		$this->items = $wpdb->get_results( $sql );

		/**
		 * Pagination
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $max,
				'per_page'    => $per_page,
				'total_pages' => ceil( $max / $per_page ),
			)
		);
	}

	/**
	 * Get Views for commissions page
	 */
	public function get_views() {

		$views = array(
			'all'  => '<li class="all"><a href="' . admin_url( 'admin.php?page=wcv-commissions' ) . '">' . __( 'All', 'wc-vendors' ) . '</a></li>',
			'due'  => '<li class="all"><a href="' . admin_url( 'admin.php?page=wcv-commissions&com_status=due' ) . '">' . __( 'Due', 'wc-vendors' ) . '</a></li>',
			'paid' => '<li class="all"><a href="' . admin_url( 'admin.php?page=wcv-commissions&com_status=paid' ) . '">' . __( 'Paid', 'wc-vendors' ) . '</a></li>',
			'void' => '<li class="all"><a href="' . admin_url( 'admin.php?page=wcv-commissions&com_status=reversed' ) . '">' . __( 'Reversed', 'wc-vendors' ) . '</a></li>',
		);

		return $views;
	}


}
