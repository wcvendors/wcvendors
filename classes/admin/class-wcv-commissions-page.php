<?php
/**
 * WCVendors_Commissions_Page class.
 *
 * @category Admin
 * @package  WCVendors/Admin
 * @version  2.1.20
 * @since    2.0.0
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WCVendors_Commissions_Page class.
 *
 * @version     2.1.20
 * @since       2.0.0
 * @extends     WP_List_Table
 */
class WCVendors_Commissions_Page extends WP_List_Table {

	/**
	 * The current index.
	 *
	 * @var int
	 * @version
	 * @since
	 */
	public $index;

	/**
	 * __construct function.
	 *
	 * @access public
	 */
	public function __construct() {

		global $status, $page;

		$this->index = 0;

		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => 'commission',
				'plural'   => 'commissions',
				'ajax'     => false,
			)
		);
	}


	/**
	 * Column_default function.
	 *
	 * @access public
	 *
	 * @param unknown $item Commission item.
	 * @param mixed   $column_name The name of the column.
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
				if ( '' !== get_the_title( $product_id ) ) {
					$product_url = '<a href="' . admin_url( 'post.php?post=' . $product_id . '&action=edit' ) . '">' . get_the_title( $product_id ) . '</a> (<span title="' . get_the_title( $product_id ) . ' has sold ' . $wcv_total_sales . ' times total.">' . $wcv_total_sales . '</span>)';
				} else {
					$order = wc_get_order( $item->order_id );
					foreach ( $order->get_items() as $item_id => $items ) {
						if( $product_id == wc_get_order_item_meta( $item_id, '_product_id', true) ) {
							$product_url = $items->get_name();
						}
					}
				}
				return $product_url;
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

			default: 
				$value = ''; 
				return apply_filters( 'wcvendors_commissions_column_default_' . $column_name, $value, $item, $column_name );
		}
	}


	/**
	 * The column_cb function.
	 *
	 * @access public
	 *
	 * @param mixed $item The item.
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
	 * The get_columns function.
	 *
	 * @access public
	 * @return unknown
	 */
	public function get_columns() {

		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'order_id'       => __( 'Order ID', 'wc-vendors' ),
			// translators: %s - The name used to refer to a vendor.
			'vendor_id'      => sprintf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ), // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
			'product_id'     => __( 'Product', 'wc-vendors' ),
			'qty'            => __( 'Quantity', 'wc-vendors' ),
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

		return apply_filters( 'wcvendors_commissions_columns', $columns );
	}


	/**
	 * The get_sortable_columns function.
	 *
	 * @access public
	 * @return unknown
	 */
	public function get_sortable_columns() {

		$sortable_columns = array(
			'time'           => array( 'time', true ),
			'product_id'     => array( 'product_id', false ),
			'qty'            => array( 'qty', false ),
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

		return apply_filters( 'wcvendors_commissions_columns_sortable', $sortable_columns );
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

		return apply_filters( 'wcv_edit_bulk_actions', $actions, '2.2.2', 'wcvendors_edit_bulk_actions' );
	}


	/**
	 * Extra table navigation.
	 *
	 * @version 2.1.20
	 * @since   2.0.0
	 *
	 * @param string $which Which table nav to extend.
	 */
	public function extra_tablenav( $which ) {

		$from_date  = isset( $_GET['from_date'] ) ? sanitize_text_field( wp_unslash( $_GET['from_date'] ) ) : gmdate( 'Y-m-d', strtotime( '-2 months' ) );
		$to_date    = isset( $_GET['to_date'] ) ? sanitize_text_field( wp_unslash( $_GET['to_date'] ) ) : current_time( 'Y-m-d' );
		$com_status = isset( $_GET['com_status'] ) ? sanitize_text_field( wp_unslash( $_GET['com_status'] ) ) : '';
		$vendor_id  = isset( $_GET['vendor_id'] ) ? sanitize_text_field( wp_unslash( $_GET['vendor_id'] ) ) : '';
		$args_url   = '';

		$args_url .= '&from_date=' . $from_date . '&to_date=' . $to_date;

		if ( $com_status ) {
			$args_url .= '&com_status=' . $com_status;
		}
		if ( $vendor_id ) {
			$args_url .= '&vendor_id=' . $vendor_id;
		}

		if ( 'top' === $which ) {
			echo '<div class="alignleft actions" style="width: 70%;">';

			// Date range fields.
			$this->date_range_fields( 'commission' );

			// commission status drop down.
			$this->status_dropdown( 'commission' );

			// Vendor drop down.
			$this->vendor_dropdown( 'commission' );

			submit_button(
				__( 'Filter', 'wc-vendors' ),
				false,
				false,
				false,
				array(
					'id'   => 'post-query-submit',
					'name' => 'do-filter',
				)
			);
			submit_button( __( 'Clear', 'wc-vendors' ), 'secondary', 'reset', false, array( 'type' => 'reset' ) );

			echo '<a class="button export_commissions" style="width: 110px; float: left;" href="' . esc_url_raw( wp_nonce_url( admin_url( 'admin.php?page=wcv-commissions&action=export_commissions' . $args_url ), 'export_commissions', 'nonce' ) ) . '">' . esc_attr__( 'Export to CSV', 'wc-vendors' ) . '</a>';
			echo '<a class="button export_commission_totals" style="width: 150px; float: left;" href="' . esc_url_raw( wp_nonce_url( admin_url( 'admin.php?page=wcv-commissions&action=export_commission_totals' . $args_url ), 'export_commission_totals', 'nonce' ) ) . '">' . esc_attr__( 'Export Totals to CSV', 'wc-vendors' ) . '</a>';
			echo '<a class="button mark_all_commissions_paid" id="mark_all_paid" style="width: 100px; float: left;" href="' . esc_url_raw( wp_nonce_url( admin_url( 'admin.php?page=wcv-commissions&action=mark_all_paid' . $args_url ), 'mark_all_paid', 'nonce' ) ) . '">' . esc_attr__( 'Mark all paid', 'wc-vendors' ) . '</a>';
			echo '</div>';
		}
	}

	/**
	 * Display a monthly dropdown for filtering items
	 *
	 * @version 2.1.20
	 * @since   2.0.0
	 *
	 * @param unknown $post_type The post type.
	 */
	public function date_range_fields( $post_type ) {

		global $wpdb, $wp_locale;

		$table_name = $wpdb->prefix . 'pv_commission';

		$from_date = isset( $_GET['from_date'] ) ? sanitize_text_field( wp_unslash( $_GET['from_date'] ) ) : '';
		$to_date   = isset( $_GET['to_date'] ) ? sanitize_text_field( wp_unslash( $_GET['to_date'] ) ) : '';

		$from_date = '' !== $from_date ? gmdate( 'Y-m-d', strtotime( $from_date ) ) : '';
		$to_date   = '' !== $to_date ? gmdate( 'Y-m-d', strtotime( $to_date ) ) : '';
		?>

		<label for="from_date">
			<?php echo esc_attr_e( 'From:', 'wc-vendors' ); ?>
			<input
				type="text"
				size="9"
				min="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '-2 years' ) ) ); ?>"
				max="<?php echo esc_attr( gmdate( 'Y-m-d', time() ) ); ?>"
				placeholder="yyyy-mm-dd"
				value="<?php echo esc_attr( $from_date ); ?>"
				name="from_date"
				class="range_datepicker from"
				id="from_date"
			/>
		</label>

		<label for="from_date">
			<?php echo esc_attr_e( 'To:', 'wc-vendors' ); ?>
			<input
				type="text"
				size="9"
				min="<?php echo esc_attr( gmdate( 'Y-m-d', strtotime( '-2 years' ) ) ); ?>"
				max="<?php echo esc_attr( gmdate( 'Y-m-d', time() ) ); ?>"
				placeholder="yyyy-mm-dd"
				value="<?php echo esc_attr( $to_date ); ?>"
				name="to_date"
				class="range_datepicker to"
				id="to_date"
			/>
		</label>
		<?php
	}

	/**
	 * Display a status dropdown for filtering items
	 *
	 * @since  3.1.0
	 * @access protected
	 *
	 * @param unknown $post_type The post type.
	 */
	public function status_dropdown( $post_type ) {

		$com_status = isset( $_GET['com_status'] ) ? sanitize_text_field( wp_unslash( $_GET['com_status'] ) ) : '';
		?>
		<select id="com_status_dropdown" name="com_status" class="wc-enhanced-select">
			<option <?php selected( $com_status, '' ); ?> value=''><?php esc_attr_e( 'Show all Statuses', 'wc-vendors' ); ?></option>
			<option <?php selected( $com_status, 'due' ); ?> value="due"><?php esc_attr_e( 'Due', 'wc-vendors' ); ?></option>
			<option <?php selected( $com_status, 'paid' ); ?> value="paid"><?php esc_attr_e( 'Paid', 'wc-vendors' ); ?></option>
			<option <?php selected( $com_status, 'reversed' ); ?>
				value="reversed"><?php esc_attr_e( 'Reversed', 'wc-vendors' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Display a vendor dropdown for filtering commissions
	 *
	 * @since  1.9.2
	 * @access public
	 *
	 * @param unknown $post_type The post type.
	 */
	public function vendor_dropdown( $post_type ) {

		$selectbox_args = array(
			'id'          => 'vendor_id',
			'placeholder' => sprintf( __( 'Filer by %s', 'wc-vendors' ), wcv_get_vendor_name() ),
		);

		if ( isset( $_GET['vendor_id'] ) ) {
			$selectbox_args['selected'] = sanitize_text_field( wp_unslash( $_GET['vendor_id'] ) );
		}

		$output = WCV_Product_Meta::vendor_selectbox( $selectbox_args, false );

		echo $output; // phpcs:ignore

	} // vendor_dropdown


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
					echo '<div class="updated"><p>' . esc_attr__( 'Commission marked paid.', 'wc-vendors' ) . '</p></div>';
				}
				break;

			case 'mark_due':
				$result = $this->mark_due( $ids );

				if ( $result ) {
					echo '<div class="updated"><p>' . esc_attr__( 'Commission marked due.', 'wc-vendors' ) . '</p></div>';
				}
				break;

			case 'mark_reversed':
				$result = $this->mark_reversed( $ids );

				if ( $result ) {
					echo '<div class="updated"><p>' . esc_attr__( 'Commission marked reversed.', 'wc-vendors' ) . '</p></div>';
				}
				break;

			default:
				// code...
				do_action( 'wcv_edit_process_bulk_actions', $this->current_action(), $ids );
				break;
		}

	}


	/**
	 * Mark commission paid.
	 *
	 * @param unknown $ids (optional).
	 *
	 * @return unknown
	 */
	public function mark_paid( $ids = array() ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$query  = "UPDATE `{$table_name}` SET `status` = 'paid' WHERE id IN ($ids) AND `status` = 'due'";
		$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $result;
	}


	/**
	 * Mark commission as reversed
	 *
	 * @param array $ids (optional) Commission IDs.
	 *
	 * @return unknown
	 */
	public function mark_reversed( $ids = array() ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';
		$query      = "UPDATE `{$table_name}` SET `status` = 'reversed' WHERE id IN ($ids)";
		$result     = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $result;

	}


	/**
	 * Mark commission as due.
	 *
	 * @param array $ids (optional) Comission IDs.
	 *
	 * @return int|WP_Error
	 */
	public function mark_due( $ids = array() ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'pv_commission';

		$query  = "UPDATE `{$table_name}` SET `status` = 'due' WHERE id IN ($ids)";
		$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return $result;
	}


	/**
	 * The function to prepare_items.
	 *
	 * @version 2.1.20
	 * @since   2.0.0
	 */
	public function prepare_items() {

		global $wpdb;

		$http_referer           = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$_SERVER['REQUEST_URI'] = remove_query_arg( '_wp_http_referer', $http_referer );

		$per_page     = $this->get_items_per_page( 'wcvendor_commissions_perpage', 10 );
		$current_page = $this->get_pagenum();

		$orderby    = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'time';
		$order      = ( ! empty( $_REQUEST['order'] ) && 'asc' === $_REQUEST['order'] ) ? 'ASC' : 'DESC';
		$com_status = ! empty( $_REQUEST['com_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['com_status'] ) ) : '';
		$vendor_id  = ! empty( $_REQUEST['vendor_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['vendor_id'] ) ) : '';
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

		if ( ! empty( $_REQUEST['from_date'] ) && ! empty( $_REQUEST['to_date'] ) ) {
			$from_date = sanitize_text_field( wp_unslash( $_REQUEST['from_date'] ) );
			$to_date   = sanitize_text_field( wp_unslash( $_REQUEST['to_date'] ) );
			$time_sql  = " WHERE time BETWEEN '$from_date' AND '$to_date'";

			$sql .= $time_sql;
		}

		if ( ! empty( $_GET['com_status'] ) ) {

			if ( '' === $time_sql ) {
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

			if ( '' === $time_sql && '' === $status_sql ) {
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

		$max = $wpdb->get_var( $sql ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared 

		$sql
			= "
			SELECT * FROM {$wpdb->prefix}pv_commission
		";

		if ( ! empty( $_GET['from_date'] ) ) {
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

		$sql_args = array(
			'orderby'      => $orderby,
			'order'        => $order,
			'offset'       => $offset,
			'per_page'     => $per_page,
			'current_page' => $current_page,
			'comm_status'  => $com_status,
			'vendor_id'    => $vendor_id,
		);
		$sql = apply_filters_deprecated( 'wcv_get_commissions_sql', array( $sql, $sql_args ), '2.2.2', 'wcvendors_get_commissions_sql' );

		$this->items = $wpdb->get_results( $sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

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
