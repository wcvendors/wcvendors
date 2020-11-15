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

		if ( current_user_can( 'vendor' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Allow products to have authors
		add_post_type_support( 'product', 'author' );

		add_action( 'add_meta_boxes'   , array( $this, 'change_author_meta_box_title' ) );
		add_action( 'wp_dropdown_users', array( $this, 'author_vendor_roles' ), 0, 1 );
		add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ), 12 );

		if ( apply_filters( 'wcv_product_commission_tab', true ) ) {
			add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_tab' ) );
			add_action( 'woocommerce_product_data_panels'     , array( $this, 'add_panel' ) );
			add_action( 'woocommerce_process_product_meta'    , array( $this, 'save_panel' ) );
		}

		add_action( 'woocommerce_product_quick_edit_end' , array( $this, 'display_vendor_dropdown_quick_edit' ) );
		add_action( 'woocommerce_product_bulk_edit_start', array( $this, 'display_vendor_dropdown_bulk_edit' ) );


		add_action( 'woocommerce_product_quick_edit_save', array( $this, 'save_vendor_quick_edit' ), 99, 1 );
		add_action( 'woocommerce_product_bulk_edit_save',  array( $this, 'save_vendor_bulk_edit' ), 99, 1 );
		add_action( 'manage_product_posts_custom_column' , array( $this, 'display_vendor_column' ), 99, 2 );
		add_filter( 'manage_product_posts_columns'       , array( $this, 'vendor_column_quickedit' ) );

		add_action( 'woocommerce_process_product_meta', array( $this, 'update_post_media_author' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );

		add_action( 'wp_ajax_wcv_search_vendors', array( $this, 'search_vendors' ) );

		add_filter( 'posts_clauses', array( $this, 'filter_by_vendor' ) );
	}

	public function enqueue_script() {
		wp_enqueue_script('wcv-vendor-select', wcv_assets_url . 'js/admin/wcv-vendor-select.js', array( 'select2' ), WCV_VERSION, true );
		wp_localize_script(
			'wcv-vendor-select',
			'wcv_vendor_select',
			array(
				'minimum_input_length' => apply_filters( 'wcvndors_vendor_select_minimum_input_length', 4 ),
			)
		);
	}

	/**
	 * Print inline script for product add or edit page
	 *
	 * @return void
	 * @version 2.2.2
	 * @since   2.2.2
	 */
	public function enqueue_styles( $hook_suffix ) {
		$screen = get_current_screen();

		if ( 'product' !== $screen->post_type ) {
			return;
		}

		wp_register_style( 'wcv-inline', false ); // phpcs:ignore
		wp_enqueue_style( 'wcv-inline' );

		$styles = $this->get_inline_style();
		wp_add_inline_style( 'wcv-inline', $styles );
	}

	/**
	 * Get the inline styles
	 *
	 * @return string
	 * @version 2.2.2
	 * @since   2.2.2
	 */
	public function get_inline_style() {
		$product_misc = self::get_product_capabilities();
		// Add any custom css
		$css = get_option( 'wcvendors_display_advanced_stylesheet' );
		// Filter taxes
		if ( ! empty( $product_misc['taxes'] ) ) {
			$css .= '.form-field._tax_status_field, .form-field._tax_class_field{display:none !important;}';
		}
		// Filter the rest of the fields
		foreach ( $product_misc as $key => $value ) {
			if ( $value ) {
				$css .= sprintf( '._%s_field{display:none !important;}', $key );
			}
		}

		return apply_filters( 'wcvendors_display_advanced_styles', $css );
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
	 * Output a vendor drop down to restrict the product type by
	 *
	 * @version 2.1.21
	 * @since   1.3.0
	 */
	public function restrict_manage_posts() {

		global $typenow, $wp_query;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( 'product' === $typenow ) {
			$selectbox_args = array(
				'id' => 'vendor',
				'fields' => array(
					'ID',
					'user_login',
				),
				'placeholder' => sprintf( __( 'Search  %s', 'wc-vendors' ), wcv_get_vendor_name() ),
			);

			if ( isset( $_GET['vendor'] ) ) {
				$selectbox_args['selected'] = sanitize_text_field( wp_unslash( $_GET['vendor'] ) );
			}

			$output = $this->vendor_selectbox( $selectbox_args, false );
			echo $output; // phpcs:ignore
		}

	}

	/**
	 * Create a selectbox to display vendor & administrator roles
	 *
	 * @version 2.1.18
	 * @since   2.
	 * @param array $args  Arguments used to render user dopdown box.
	 * @param bool  $media Whether to display assign media checkbox.
	 *
	 * @return string
	 */
	public static function vendor_selectbox( $args, $media = true ) {
		$args = wp_parse_args( $args, array(
			'class'       => '',
			'id'          => '',
			'placeholder' => '',
			'selected'    => '',
		) );

		/**
		 * Filter the arguments used to render the selectbox.
		 *
		 * @param array $args The arguments to be filtered.
		 */
		$args = apply_filters( 'wcv_vendor_selectbox_args', $args );

		extract( $args );

		$user_args = array(
			'fields'   => array( 'ID', 'display_name' ),
			'role__in' => array( 'vendor', 'administrator' ),
			'number'   => 100,
		);

		if ( $selected ) {
			$user_args['include'] = array( $selected );
		}

		/**
		 * Filter the arguments used to search for vendors.
		 *
		 * @param array $user_args The arguments to be filtered.
		 */
		$user_args = apply_filters( 'wcv_vendor_selectbox_user_args',  $user_args );
		$users = get_users( $user_args );

		$output = "<select style='width:200px;' name='$id' id='$id' class='wcv-vendor-select $class'>\n";
		$output .= "\t<option value=''>$placeholder</option>\n";
		foreach ( (array) $users as $user ) {
			$select = selected( $user->ID, $selected, false );
			$output .= "<option value='$user->ID' $select>$user->display_name</option>";
		}
		$output .= '</select>';

		if ( $media ) {
		    $output .= '<p><label class="product_media_author_override">';
			$output .= '<input name="product_media_author_override" type="checkbox" /> ';
			$output .= sprintf( __( 'Assign media to %s', 'wc-vendors' ), wcv_get_vendor_name() );
			$output .= '</label></p>';
        }

		return apply_filters( 'wcv_vendor_selectbox', $output, $user_args, $media );
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

		$selectbox_args = array(
			'id' => 'post_author-new',
            'class' => 'select',
            'selected' => $post->post_author,
		);
		$output = $this->vendor_selectbox( $selectbox_args, false);
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

			if ( isset( $_REQUEST['_vendor'] ) && '' !== $_REQUEST['vendor'] ) {
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
		$selectbox_args = array(
			'id' => 'vendor',
            'placeholder' => __('— No change —', 'wc-vendors'),
		);
		$output = $this->vendor_selectbox( $selectbox_args, false);
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

		if( ! isset( $_REQUEST['vendor'] ) || isset( $_REQUEST['vendor'] ) && '' !== $_REQUEST['vendor'] ) {
			return;
		}

		if ( isset( $_REQUEST['vendor'] ) && '' !== $_REQUEST['vendor'] ) {
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

	/**
	 * Search for vendor using a single SQL query.
	 *
	 * @return false|string|void
	 */
	public function search_vendors() {
		global $wpdb;

		$search_string = esc_attr( $_POST['term'] );

		if( strlen( $search_string ) <= 3 ) {
			return;
		}

		$search_string = '%' . $search_string . '%';
		$search_string = $wpdb->prepare("%s", $search_string);

		$sql = "
	  SELECT DISTINCT ID as `id`, display_name as `text`
	  FROM  $wpdb->users
		INNER JOIN $wpdb->usermeta as mt1 ON $wpdb->users.ID = mt1.user_id
		INNER JOIN $wpdb->usermeta as mt2 ON $wpdb->users.ID = mt2.user_id
	  WHERE ( mt1.meta_key = '$wpdb->prefix" . "capabilities' AND ( mt1.meta_value LIKE '%vendor%' OR mt1.meta_value LIKE '%administrator%' ) )
	  AND (
		user_login LIKE $search_string
		OR user_nicename LIKE $search_string
		OR display_name LIKE $search_string
		OR user_email LIKE $search_string
		OR user_url LIKE $search_string
		OR ( mt2.meta_key = 'first_name' AND mt2.meta_value LIKE $search_string )
		OR ( mt2.meta_key = 'last_name' AND mt2.meta_value LIKE $search_string )
		OR ( mt2.meta_key = 'pv_shop_name' AND mt2.meta_value LIKE $search_string )
		OR ( mt2.meta_key = 'pv_shop_slug' AND mt2.meta_value LIKE $search_string )
		OR ( mt2.meta_key = 'pv_seller_info' AND mt2.meta_value LIKE $search_string )
		OR ( mt2.meta_key = 'pv_shop_description' AND mt2.meta_value LIKE $search_string )
	  )
	  ORDER BY display_name
	";

		$response = new stdClass();
		$response->results = $wpdb->get_results( $sql );

		wp_send_json($response);
	}

	/**
	 * Add posts clauses to filter products by vendor ID
	 *
	 * @param array $args The current posts search args.
	 * @return array
	 * @version 2.1.21
	 * @since   2.1.21
	 */
	public function filter_by_vendor( $args ) {
		global $wpdb;

		if ( ! isset( $_GET['vendor'] ) ) {
			return $args;
		}

		$vendor_id = sanitize_text_field( wp_unslash( $_GET['vendor'] ) );
		$post_type = '';
		if ( isset( $_GET['post_type'] ) ) {
			$post_type = sanitize_text_field( wp_unslash( $_GET['post_type'] ) );
		}

		if ( $vendor_id && 'product' === $post_type ) {
			$args['where'] .= $wpdb->prepare( " AND {$wpdb->posts}.post_author=%d", $vendor_id );
		}

		return $args;
	}

	/**
	 * Get product capabilities.
	 *
	 * @return void
	 * @version 2.2.2
	 * @since   2.2.2
	 */
	public static function get_product_capabilities() {
		return apply_filters(
			'wcvendors_product_capabilities',
			array(
				'taxes'     => wc_string_to_bool( get_option( 'wcvendors_capability_product_taxes', 'no' ) ),
				'sku'       => wc_string_to_bool( get_option( 'wcvendors_capability_product_sku', 'no' ) ),
				'duplicate' => wc_string_to_bool( get_option( 'wcvendors_capability_product_duplicate', 'no' ) ),
				'delete'    => wc_string_to_bool( get_option( 'wcvendors_capability_product_delete', 'no' ) ),
				'featured'  => wc_string_to_bool( get_option( 'wcvendors_capability_product_featured', 'no' ) ),
			)
		);
	}
}
