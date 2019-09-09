<?php

/**
 * Product meta configurations
 *
 * @package WCVendors
 */


class WCV_Product_Meta {


	/**
	 * Constructor
	 */
	function __construct() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Allow products to have authors
		add_post_type_support( 'product', 'author' );

		add_action( 'add_meta_boxes'   , array( $this, 'change_author_meta_box_title' ) );
		add_action( 'wp_dropdown_users', array( $this, 'author_vendor_roles' ), 0, 1 );

		if ( apply_filters( 'wcv_product_commission_tab', true ) ) {
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_tab' ) );
			add_action( 'woocommerce_product_data_panels'     , array( $this, 'add_panel' ) );
			add_action( 'woocommerce_process_product_meta'    , array( $this, 'save_panel' ) );
		}

		add_action( 'woocommerce_product_quick_edit_end' , array( $this, 'display_vendor_dropdown_quick_edit' ) );
		add_action( 'woocommerce_product_bulk_edit_start', array( $this, 'display_vendor_dropdown_bulk_edit' ) );


		add_action( 'woocommerce_product_quick_edit_save', array( $this, 'save_vendor_quick_edit' ), 2, 99 );
		add_action( 'woocommerce_product_bulk_edit_save',  array( $this, 'save_vendor_bulk_edit' ), 1, 99 );
		add_action( 'manage_product_posts_custom_column' , array( $this, 'display_vendor_column' ), 2, 99 );
		add_filter( 'manage_product_posts_columns'       , array( $this, 'vendor_column_quickedit' ) );

		add_action( 'woocommerce_process_product_meta', array( $this, 'update_post_media_author' ) );

	}


	/**
	 * Change the "Author" metabox to "Vendor"
	 */
	public function change_author_meta_box_title() {

		global $wp_meta_boxes;
		$wp_meta_boxes['product']['normal']['core']['authordiv']['title'] = wcv_get_vendor_name();
	}


	/**
	 * Override the authors selectbox with +vendor roles
	 *
	 * @param html $output
	 *
	 * @return html
	 */
	public function author_vendor_roles( $output ) {

		global $post;

		if ( empty( $post ) ) {
			return $output;
		}

		// Return if this isn't a WooCommerce product post type
		if ( $post->post_type != 'product' ) {
			return $output;
		}

		// Return if this isn't the vendor author override dropdown
		if ( ! strpos( $output, 'post_author_override' ) ) {
			return $output;
		}

		$args = array(
			'selected' => $post->post_author,
			'id'       => 'post_author_override',
		);

		$output = $this->vendor_selectbox( $args );

		return $output;
	}


	/**
	 * Create a selectbox to display vendor & administrator roles
	 *
	 * @param array $args
	 *
	 * @return html
	 */
	public function vendor_selectbox( $args ) {

		$default_args = array(
			'placeholder',
			'id',
			'class',
		);

		foreach ( $default_args as $key ) {
			if ( ! is_array( $key ) && empty( $args[ $key ] ) ) {
				$args[ $key ] = '';
			} elseif ( is_array( $key ) ) {
				foreach ( $key as $val ) {
					$args[ $key ][ $val ] = esc_attr( $args[ $key ][ $val ] );
				}
			}
		}
		extract( $args );

		$roles     = array( 'vendor', 'administrator' );
		$user_args = array( 'fields' => array( 'ID', 'display_name' ) );

		$output = "<select style='width:200px;' name='$id' id='$id' class='$class' data-placeholder='$placeholder'>\n";
		$output .= "\t<option value=''></option>\n";

		foreach ( $roles as $role ) {

			$new_args         = $user_args;
			$new_args['role'] = $role;
			$users            = get_users( $new_args );

			if ( empty( $users ) ) {
				continue;
			}
			$output .= wcv_vendor_drop_down_options( $users, $selected );
		}
		$output .= '</select>';

		$output .= '<br class="clear" />';
		$output .= '<p><label class="product_media_author_override">';
		$output .= '<input name="product_media_author_override" type="checkbox" /> ';
		$output .= sprintf( __( 'Assign media to %s', 'wc-vendors' ), wcv_get_vendor_name() );
		$output .= '</label></p>';

		// Convert this selectbox with select2
		$output
			.= '
		<script type="text/javascript">jQuery(function() { jQuery("#' . $id . '").select2(); } );</script>';

		return $output;
	}


	/**
	 * Save commission rate of a product
	 *
	 * @param int $post_id
	 */
	public function save_panel( $post_id ) {

		if ( isset( $_POST['pv_commission_rate'] ) ) {
			update_post_meta( $post_id, 'pv_commission_rate', is_numeric( $_POST['pv_commission_rate'] ) ? (float) $_POST['pv_commission_rate'] : false );
		}

	}

	/**
	 * Update the author of the media attached to this product
	 *
	 * @param int $post_id the ID of the product to be updated
	 *
	 * @return void
	 * @since 2.0.8
	 */
	public function update_post_media_author( $post_id ) {

		$product = wc_get_product( $post_id );
		if ( isset( $_POST['product_media_author_override'] ) ) {
			$this->save_product_media( $product );
		}
	}


	/**
	 * Add the Commission tab to a product
	 */
	public function add_tab() {

		?>
		<li class="commission_tab">
			<a href="#commission"><span><?php _e( 'Commission', 'wc-vendors' ); ?></span></a>
		</li>
		<?php
	}


	/**
	 * Add the Commission panel to a product
	 */
	public function add_panel() {

		global $post;
		?>

		<div id="commission" class="panel woocommerce_options_panel">
			<fieldset>

				<p class='form-field commission_rate_field'>
					<label for='pv_commission_rate'><?php _e( 'Commission', 'wc-vendors' ); ?> (%)</label>
					<input
							type='number'
							id='pv_commission_rate'
							name='pv_commission_rate'
							class='short'
							max="100"
							min="0"
							step='any'
							placeholder='<?php _e( 'Leave blank for default', 'wc-vendors' ); ?>'
							value="<?php echo get_post_meta( $post->ID, 'pv_commission_rate', true ); ?>"
					/>
				</p>

			</fieldset>
		</div>
		<?php

	}

	/**
	 * Remove the author column and replace it with a vendor column on the products page
	 *
	 * @version 2.1.0
	 */
	public function vendor_column_quickedit( $columns ) {

		unset( $columns[ 'author'] );
		$columns['vendor'] = sprintf( __( '%s Store ', 'wc-vendors' ), wcv_get_vendor_name() );
		return $columns;
	}

	/*
	*	Display the vendor drop down on the quick edit screen
	*/
	public function display_vendor_dropdown_quick_edit() {

		global $post;
		$selected = $post->post_author;

		$roles     = array( 'vendor', 'administrator' );
		$user_args = array( 'fields' => array( 'ID', 'display_name' ) );

		$output = "<select style='width:200px;' name='post_author-new' class='select'>\n";

		foreach ( $roles as $role ) {

			$new_args         = $user_args;
			$new_args['role'] = $role;
			$users            = get_users( $new_args );

			if ( empty( $users ) ) {
				continue;
			}
			$output .= wcv_vendor_drop_down_options( $users, $selected );
		}
		$output .= '</select>';


		?>
		<br class="clear"/>
		<label class="inline-edit-author-new">
			<span class="title"><?php printf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ); ?></span>
			<?php echo $output; ?>
		</label>
		<br class="clear"/>
		<label class="inline-edit-author-new">
			<input name="product_media_author_override" type="checkbox"/>
			<span class="title">Media</span>
			<?php printf( __( 'Assign media to %s', 'wc-vendors' ), wcv_get_vendor_name() ); ?>
		</label>
		<?php
	}


	/**
	* Save the vendor on the quick edit screen
	*
	* @param WC_Product $product
	*/
	public function save_vendor_quick_edit( $product ) {

		if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) ) {

			if ( isset( $_REQUEST['_vendor'] ) ) {
				$vendor            = wc_clean( $_REQUEST['_vendor'] );
				$post              = get_post( $product->get_id() );
				$post->post_author = $vendor;
			}

			if ( isset( $_REQUEST['product_media_author_override'] ) ) {
				$this->save_product_media( $product );
			}
		}

		return $product;
	}

	/**
	* Display the vendor drop down on the bulk edit screen
	*
	* @since 2.1.14
	* @version 2.1.14
	*/
	public function display_vendor_dropdown_bulk_edit() {

		global $post;
		$selected = '';

		$roles     = array( 'vendor', 'administrator' );
		$user_args = array( 'fields' => array( 'ID', 'display_name' ) );

		$output = "<select style='width:200px;' name='vendor' class='select'>\n";
		$output .= '<option value=""> '. __('— No change —', 'wc-vendors') . '</option>';

		foreach ( $roles as $role ) {

			$new_args         = $user_args;
			$new_args['role'] = $role;
			$users            = get_users( $new_args );

			if ( empty( $users ) ) {
				continue;
			}
			$output .= wcv_vendor_drop_down_options( $users, '' );
		}
		$output .= '</select>';


		?>
		<br class="clear"/>
		<label class="inline-edit-author-new">
			<span class="title"><?php printf( __( '%s', 'wc-vendors' ), wcv_get_vendor_name() ); ?></span>
			<?php echo $output; ?>
		</label>
		<?php
	}

	/**
	* Save the vendor from the bulk edit action
	*
	* @since 2.1.14
	* @version 2.1.14
	* @param WC_Product $product
	*/
	public function save_vendor_bulk_edit( $product ) {

		if ( isset( $_REQUEST['vendor'] ) && $_REQUEST['vendor'] != ''  ) {
			$vendor            = wc_clean( $_REQUEST['vendor'] );
			$update_vendor = array(
				'ID'          => $product->get_id(),
				'post_author' => $vendor,
	   		);
	   		wp_update_post( $update_vendor );
		}
	}

	/**
	 * Override the product media author
	 *
	 * @param object $product
	 * @param int    $vendor
	 *
	 * @return void
	 * @since 2.0.8
	 */
	public function save_product_media( $product ) {

		global $post;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}
		$vendor = $post->post_author;

		$attachment_ids   = $product->get_gallery_image_ids( 'edit' );
		$attachment_ids[] = $product->get_image_id( 'edit' );

		foreach ( $attachment_ids as $id ) {
			$edit_attachment = array(
				'ID'          => $id,
				'post_author' => $vendor,
			);

			wp_update_post( $edit_attachment );
		}
	}

	/**
	 * Display the vendor column and the hidden vendor column
	 *
	 * @since 1.0.1
	 * @version 2.1.10
	 */
	public function display_vendor_column( $column, $post_id ) {

		$vendor = get_post_field( 'post_author', $post_id );

		switch ( $column ) {
			case 'name':
				?>
				<div class="hidden vendor" id="vendor_<?php echo $post_id; ?>">
					<div id="post_author"><?php echo $vendor; ?></div>
				</div>
				<?php
				break;
			case 'vendor':
				$post = get_post( $post_id );
				$args = array(
					'post_type' => $post->post_type,
					'author'    => get_the_author_meta( 'ID' ),
				);
				$shop_name = WCV_Vendors::get_vendor_sold_by( $vendor  );
				$display_name = empty( $shop_name ) ? get_the_author() : $shop_name;
				echo $this->get_edit_link( $args, $display_name );
			break;

			default:
				break;
		}
	}

	/**
	 * Helper to create links to edit.php with params.
	 *
	 * @since 2.1.10
	 *
	 * @param string[] $args  Associative array of URL parameters for the link.
	 * @param string   $label Link text.
	 * @param string   $class Optional. Class attribute. Default empty string.
	 * @return string The formatted link string.
	 */
	protected function get_edit_link( $args, $label, $class = '' ) {
		$url = add_query_arg( $args, 'edit.php' );

		$class_html = $aria_current = '';
		if ( ! empty( $class ) ) {
			$class_html = sprintf(
				' class="%s"',
				esc_attr( $class )
			);

			if ( 'current' === $class ) {
				$aria_current = ' aria-current="page"';
			}
		}

		return sprintf(
			'<a href="%s"%s%s>%s</a>',
			esc_url( $url ),
			$class_html,
			$aria_current,
			$label
		);
	}

}
