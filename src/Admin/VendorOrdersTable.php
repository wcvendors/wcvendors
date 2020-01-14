<?php
/**
 * Manage the Vendor Orders table.
 *
 * @package WCVendors/Admin
 */

namespace WCVendors\Admin;

use WCVendors\VendorOrder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manage the Vendor Orders table.
 */
class VendorOrdersTable {
	/**
	 * Vendor order object.
	 *
	 * @var Vendor $object Vendor order object;
	 */
	private $object;

	/**
	 * Initialize WP hooks.
	 */
	public function init_hooks() {
		add_action( 'wp_admin_enqueue_scripts', array( $this, 'enqueue' ) );

		add_filter( 'request', array( $this, 'request' ) );

		// List table.
		add_filter( 'manage_edit-shop_order_vendor_columns', array( $this, 'columns' ) );
		add_action( 'manage_shop_order_vendor_posts_custom_column', array( $this, 'render_columns' ), 10, 2 );
	}

	/**
	 * Enqueue scripts and style.
	 */
	public function enqueue() {
		wp_enqueue_script( 'wc-orders' );
	}

	/**
	 * Filter request when query vendor order from admin.
	 *
	 * @param array $query_args Query arguments.
	 */
	public function request( $query_args ) {
		if (
			'shop_order_vendor' === $query_args['post_type'] 
			&& isset( $query_args['post_status'] )
			&& empty( $query_args['post_status'] ) 
		) {
			$query_args['post_status'] = array_keys( wc_get_order_statuses() );
		}
		return $query_args;
	}

	/**
	 * Define custom columns for vendor order
	 *
	 * Column names that have a corresponding `WC_Order` column use the `order_` prefix here
	 * to take advantage of core WooCommerce assets, like JS/CSS.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function columns( $columns ) {
		return array(
			'cb'               => $columns['cb'],
			'order_number'     => __( 'Order', 'wc-vendors' ),
			'order_parent'     => __( 'Parent order', 'wc-vendors' ),
			'order_date'       => __( 'Date', 'wc-vendors' ),
			'order_status'     => __( 'Status', 'wc-vendors' ),
			'billing_address'  => __( 'Billing', 'wc-vendors' ),
			'shipping_address' => __( 'Shipping', 'wc-vendors' ),
			'order_total'      => __( 'Total', 'wc-vendors' ),
		);
	}

	/**
	 * Render individual columns.
	 *
	 * @param string $column Column ID to render.
	 * @param int    $post_id Post ID being shown.
	 */
	public function render_columns( $column, $post_id ) {
		$this->object = wc_get_order( $post_id );

		if ( ! $this->object ) {
			return;
		}

		if ( is_callable( array( $this, 'render_' . $column . '_column' ) ) ) {
			$this->{"render_{$column}_column"}();
		}
	}

	/**
	 * Render columm: order_number.
	 */
	protected function render_order_number_column() {
		$buyer = '';

		if ( $this->object->get_billing_first_name() || $this->object->get_billing_last_name() ) {
			/* translators: 1: first name 2: last name */
			$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'wc-vendors' ), $this->object->get_billing_first_name(), $this->object->get_billing_last_name() ) );
		} elseif ( $this->object->get_billing_company() ) {
			$buyer = trim( $this->object->get_billing_company() );
		} elseif ( $this->object->get_customer_id() ) {
			$user  = get_user_by( 'id', $this->object->get_customer_id() );
			$buyer = ucwords( $user->display_name );
		}

		/**
		 * Filter buyer name in list table orders.
		 *
		 * @since 3.7.0
		 * @param string   $buyer Buyer name.
		 * @param WC_Order $order Order data.
		 */
		$buyer = apply_filters( 'woocommerce_admin_order_buyer_name', $buyer, $this->object );

		if ( $this->object->get_status() === 'trash' ) {
			echo '<strong>#' . esc_attr( $this->object->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong>';
		} else {
			echo '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $this->object->get_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $this->object->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong></a>';
		}
	}

	/**
	 * Render columm: order_parent.
	 */
	protected function render_order_parent_column() {
		if ( $this->object->get_status() === 'trash' ) {
			echo '<strong>#' . esc_attr( $this->object->get_order_number() ) . '</strong>';
		} else {
			echo '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $this->object->get_parent_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $this->object->get_parent_id() ) . '</strong></a>';
		}
	}

	/**
	 * Render columm: order_status.
	 */
	protected function render_order_status_column() {
		$tooltip                 = '';
		$comment_count           = get_comment_count( $this->object->get_id() );
		$approved_comments_count = absint( $comment_count['approved'] );

		if ( $approved_comments_count ) {
			$latest_notes = wc_get_order_notes(
				array(
					'order_id' => $this->object->get_id(),
					'limit'    => 1,
					'orderby'  => 'date_created_gmt',
				)
			);

			$latest_note = current( $latest_notes );

			if ( isset( $latest_note->content ) && 1 === $approved_comments_count ) {
				$tooltip = wc_sanitize_tooltip( $latest_note->content );
			} elseif ( isset( $latest_note->content ) ) {
				/* translators: %d: notes count */
				$tooltip = wc_sanitize_tooltip( $latest_note->content . '<br/><small style="display:block">' . sprintf( _n( 'Plus %d other note', 'Plus %d other notes', ( $approved_comments_count - 1 ), 'wc-vendors' ), $approved_comments_count - 1 ) . '</small>' );
			} else {
				/* translators: %d: notes count */
				$tooltip = wc_sanitize_tooltip( sprintf( _n( '%d note', '%d notes', $approved_comments_count, 'wc-vendors' ), $approved_comments_count ) );
			}
		}

		if ( $tooltip ) {
			printf( '<mark class="order-status %s tips" data-tip="%s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $this->object->get_status() ) ), wp_kses_post( $tooltip ), esc_html( wc_get_order_status_name( $this->object->get_status() ) ) );
		} else {
			printf( '<mark class="order-status %s"><span>%s</span></mark>', esc_attr( sanitize_html_class( 'status-' . $this->object->get_status() ) ), esc_html( wc_get_order_status_name( $this->object->get_status() ) ) );
		}
	}

	/**
	 * Render columm: order_date.
	 */
	protected function render_order_date_column() {
		$order_timestamp = $this->object->get_date_created() ? $this->object->get_date_created()->getTimestamp() : '';

		if ( ! $order_timestamp ) {
			echo '&ndash;';
			return;
		}

		// Check if the order was created within the last 24 hours, and not in the future.
		if ( $order_timestamp > strtotime( '-1 day', time() ) && $order_timestamp <= time() ) {
			$show_date = sprintf(
				/* translators: %s: human-readable time difference */
				_x( '%s ago', '%s = human-readable time difference', 'wc-vendors' ),
				human_time_diff( $this->object->get_date_created()->getTimestamp(), time() )
			);
		} else {
			$show_date = $this->object->get_date_created()->date_i18n( apply_filters( 'woocommerce_admin_order_date_format', __( 'M j, Y', 'wc-vendors' ) ) );
		}
		printf(
			'<time datetime="%1$s" title="%2$s">%3$s</time>',
			esc_attr( $this->object->get_date_created()->date( 'c' ) ),
			esc_html( $this->object->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
			esc_html( $show_date )
		);
	}

	/**
	 * Render columm: order_total.
	 */
	protected function render_order_total_column() {
		if ( $this->object->get_payment_method_title() ) {
			/* translators: %s: method */
			echo '<span class="tips" data-tip="' . esc_attr( sprintf( __( 'via %s', 'wc-vendors' ), $this->object->get_payment_method_title() ) ) . '">' . wp_kses_post( $this->object->get_formatted_order_total() ) . '</span>';
		} else {
			echo wp_kses_post( $this->object->get_formatted_order_total() );
		}
	}
	/**
	 * Render columm: billing_address.
	 */
	protected function render_billing_address_column() {
		$address = $this->object->get_formatted_billing_address();

		if ( $address ) {
			echo esc_html( preg_replace( '#<br\s*/?>#i', ', ', $address ) );

			if ( $this->object->get_payment_method() ) {
				/* translators: %s: payment method */
				echo '<span class="description">' . sprintf( __( 'via %s', 'wc-vendors' ), esc_html( $this->object->get_payment_method_title() ) ) . '</span>'; // phpcs:ignore
			}
		} else {
			echo '&ndash;';
		}
	}

	/**
	 * Render columm: shipping_address.
	 */
	protected function render_shipping_address_column() {
		$address = $this->object->get_formatted_shipping_address();

		if ( $address ) {
			echo '<a target="_blank" href="' . esc_url( $this->object->get_shipping_address_map_url() ) . '">' . esc_html( preg_replace( '#<br\s*/?>#i', ', ', $address ) ) . '</a>';
			if ( $this->object->get_shipping_method() ) {
				/* translators: %s: shipping method */
				echo '<span class="description">' . sprintf( __( 'via %s', 'wc-vendors' ), esc_html( $this->object->get_shipping_method() ) ) . '</span>'; // phpcs:ignore
			}
		} else {
			echo '&ndash;';
		}
	}
}
