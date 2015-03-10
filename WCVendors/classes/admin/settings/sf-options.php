<?php
$options = array();

$options[ ] = array( 'name' => __( 'General', 'wcvendors' ), 'type' => 'heading' );
$options[ ] = array( 'name' => __( 'General options', 'wcvendors' ), 'type' => 'title', 'desc' => __( ' ', 'wcvendors' ) );

$options[ ] = array(
	'name'     => __( 'Default commission (%)', 'wcvendors' ),
	'desc'     => __( 'The default rate you pay each vendor for a product sale. If a product has a commission rate already set, this value will be ignored for that product.', 'wcvendors' ),
	'id'       => 'default_commission',
	'css'      => 'width:70px;',
	'type'     => 'number',
	'restrict' => array(
		'min' => 0,
		'max' => 100
	)
);

$options[ ] = array(
	'name' => __( 'Registration', 'wcvendors' ),
	'desc' => __( 'Allow users or guests to apply to become a vendor', 'wcvendors' ),
	'tip'  => __( 'This will show a checkbox on the My Account page\'s registration form asking if the user would like to apply to be a vendor. Also, on the Vendor Dashboard, users can still apply to become a vendor even if this is disabled.', 'wcvendors' ),
	'id'   => 'show_vendor_registration',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'Approve vendor applications manually', 'wcvendors' ),
	'tip'  => __( 'With this unchecked, all vendor applications are automatically accepted. Otherwise, you must approve each manually.', 'wcvendors' ),
	'id'   => 'manual_vendor_registration',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'name' => __( 'Taxes', 'wcvendors' ),
	'desc' => __( 'Give vendors any tax collected per-product', 'wcvendors' ),
	'tip'  => __( 'The tax collected on a vendor\'s product will be given in its entirety', 'wcvendors' ),
	'id'   => 'give_tax',
	'type' => 'checkbox',
	'std'  => false,
);

$options[ ] = array(
	'name' => __( 'Shipping', 'wcvendors' ),
	'desc' => __( 'Give vendors any shipping collected per-product', 'wcvendors' ),
	'tip'  => __( 'The shipping collected on a vendor\'s product will be given in its entirety', 'wcvendors' ),
	'id'   => 'give_shipping',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array( 'name' => __( 'Shop options', 'wcvendors' ), 'type' => 'title', 'desc' => __( ' ', 'wcvendors' ) );

$options[ ] = array(
	'name' => __( 'Shop HTML', 'wcvendors' ),
	'desc' => __( 'Enable HTML for a vendor\'s shop description by default.  You can enable or disable this per vendor by editing the vendors username.', 'wcvendors' ),
	'id'   => 'shop_html_enabled',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'name' => __( 'Vendor shop page', 'wcvendors' ),
	'desc' => __( 'Eg: <code>yoursite.com/[your_setting_here]/[vendor_name_here]</code>', 'wcvendors' ),
	'id'   => 'vendor_shop_permalink',
	'type' => 'text',
	'std'  => 'vendors/',
);

$options[ ] = array(
	'name' => __( 'Shop Headers', 'wcvendors' ),
	'desc' => __( 'Enable vendor shop headers', 'wcvendors' ),
	'tip'  => __( 'This will override the HTML Shop description output on product-archive pages.  In order to customize the shop headers visit wcvendors.com and read the article in the Knowledgebase titled Changing the Vendor Templates.', 'wcvendors' ),
	'id'   => 'shop_headers_enabled',
	'type' => 'checkbox',
	'std'  => false,
);

$options[ ] = array( 'name' => __( 'Products', 'wcvendors' ), 'type' => 'heading' );
$options[ ] = array( 'name' => __( 'Product Add Page', 'wcvendors' ), 'type' => 'title', 'desc' => __( 'Configure what to hide from all vendors when adding a product', 'wcvendors' ) );

$options[ ] = array(
	'name'     => __( 'Left side panel', 'wcvendors' ),
	'desc'     => __( 'CHECKING these boxes will HIDE these areas of the add product page for vendors', 'wcvendors' ),
	'id'       => 'hide_product_panel',
	'options'  => array(
		'inventory'      => 'Inventory',
		'shipping'       => 'Shipping',
		'linked_product' => 'Linked Products',
		'attribute'     => 'Attributes',
		'advanced'       => 'Advanced',
	),
	'type'     => 'checkbox',
	'multiple' => true,
);

$options[ ] = array(
	'name'     => __( 'Types', 'wcvendors' ),
	'desc'     => __( 'CHECKING these boxes will HIDE these product types from the vendor', 'wcvendors' ),
	'id'       => 'hide_product_types',
	'options'  => array(
		'simple'   => 'Simple',
		'variable' => 'Variable',
		'grouped'  => 'Grouped',
		'external' => 'External / affiliate',
	),
	'type'     => 'checkbox',
	'multiple' => true,
);

$options[ ] = array(
	'name'     => __( 'Type options', 'wcvendors' ),
	'desc'     => __( 'CHECKING these boxes will HIDE these product options from the vendor', 'wcvendors' ),
	'id'       => 'hide_product_type_options',
	'options'  => array(
		'virtual'      => 'Virtual',
		'downloadable' => 'Downloadable',
	),
	'type'     => 'checkbox',
	'multiple' => true,
);

$options[ ] = array(
	'name'     => __( 'Miscellaneous', 'wcvendors' ),
	'id'       => 'hide_product_misc',
	'options'  => array(
		'taxes' 		=> 'Taxes',
		'sku'   		=> 'SKU',
		'featured'		=> 'Featured',
	),
	'type'     => 'checkbox',
	'multiple' => true,
);

$options[ ] = array(
	'name' => __( 'Stylesheet', 'wcvendors' ),
	'desc' => __( 'You can add CSS in this textarea, which will be loaded on the product add/edit page for vendors.', 'wcvendors' ),
	'id'   => 'product_page_css',
	'type' => 'textarea',
);


$options[ ] = array( 'name' => __( 'Capabilities', 'wcvendors' ), 'type' => 'heading', 'id' => 'capabilities' );
$options[ ] = array( 'name' => __( 'Permissions', 'wcvendors' ), 'id' => 'permissions', 'type' => 'title', 'desc' => __( 'General permissions used around the shop', 'wcvendors' ) );

$options[ ] = array(
	'name' => __( 'Orders', 'wcvendors' ),
	'desc' => __( 'View orders', 'wcvendors' ),
	'tip'  => __( 'Show customer details such as email, address, name, etc, for each order', 'wcvendors' ),
	'id'   => 'can_show_orders',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'View comments', 'wcvendors' ),
	'tip'  => __( 'View all vendor comments for an order on the frontend', 'wcvendors' ),
	'id'   => 'can_view_order_comments',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'Submit comments', 'wcvendors' ),
	'tip'  => __( 'Submit comments for an order on the frontend. Eg, tracking ID for a product', 'wcvendors' ),
	'id'   => 'can_submit_order_comments',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'View email addresses', 'wcvendors' ),
	'tip'  => __( 'While viewing order details on the frontend, you can disable or enable email addresses', 'wcvendors' ),
	'id'   => 'can_view_order_emails',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'Export a CSV file of orders for a product', 'wcvendors' ),
	'tip'  => __( 'Vendors could export orders for a product on the frontend', 'wcvendors' ),
	'id'   => 'can_export_csv',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'name' => __( 'Reports', 'wcvendors' ),
	'desc' => __( 'View backend sales reports', 'wcvendors' ),
	'tip'  => __( 'Graphs and tables via the Reports page in backend. The reports will only display sales data that pertain to their products', 'wcvendors' ),
	'id'   => 'can_view_backend_reports',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'View Frontend sales reports', 'wcvendors' ),
	'tip'  => __( 'Sales table on the frontend on the My Account page. The table will only display sales data that pertain to their products', 'wcvendors' ),
	'id'   => 'can_view_frontend_reports',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'name' => __( 'Products', 'wcvendors' ),
	'desc' => __( 'Submit products', 'wcvendors' ),
	'tip'  => __( 'Vendors could submit a product through the backend, and an admin would approve or deny it', 'wcvendors' ),
	'id'   => 'can_submit_products',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'Edit live products', 'wcvendors' ),
	'tip'  => __( 'Vendors could edit an approved product after it has already gone live. There is no approval or review after editing a live product. This could be dangerous with malicious vendors, so take caution.', 'wcvendors' ),
	'id'   => 'can_edit_published_products',
	'type' => 'checkbox',
	'std'  => false,
);

$options[ ] = array(
	'desc' => __( 'Submit products live without requiring approval', 'wcvendors' ),
	'tip'  => __( 'Vendors can submit products without review or approval from a shop admin. This could be dangerous with malicious vendors, so take caution.', 'wcvendors' ),
	'id'   => 'can_submit_live_products',
	'type' => 'checkbox',
	'std'  => false,
);

$options[ ] = array( 'name' => __( 'Pages', 'wcvendors' ), 'type' => 'heading' );
$options[ ] = array( 'name' => __( 'Page configuration', 'wcvendors' ), 'type' => 'title', 'desc' => __( ' ', 'wcvendors' ) );

$options[ ] = array(
	'name'    => __( 'Vendor dashboard', 'wcvendors' ),
	'desc'    => __( 'Choose the page that has the shortcode <code>[wcv_vendor_dashboard]</code><br/>By default, My Account > Vendor Dashboard should have the shortcode.', 'wcvendors' ),
	'id'      => 'vendor_dashboard_page',
	'type'    => 'single_select_page',
	'select2' => true,
);

$options[ ] = array(
	'name'    => __( 'Shop settings', 'wcvendors' ),
	'desc'    => __( 'Choose the page that has the shortcode <code>[wcv_shop_settings]</code><br/>These are the shop settings a vendor can configure.', 'wcvendors' ),
	'id'      => 'shop_settings_page',
	'type'    => 'single_select_page',
	'select2' => true,
);

$options[ ] = array(
	'name'    => __( 'Orders page', 'wcvendors' ),
	'desc'    => __( 'Choose the page that has the shortcode <code>[wcv_orders]</code><br/>By default, My Account > Orders should have the shortcode.', 'wcvendors' ),
	'id'      => 'orders_page',
	'type'    => 'single_select_page',
	'select2' => true,
);

$options[ ] = array(
	'name'    => __( 'Vendor terms', 'wcvendors' ),
	'desc'    => __( 'These terms are shown to a user when submitting an application to become a vendor.<br/>If left blank, no terms will be shown to the applicant.', 'wcvendors' ),
	'id'      => 'terms_to_apply_page',
	'type'    => 'single_select_page',
	'select2' => true,
);

$total_due = 0;
if ( !empty( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == 'payments' ) {
	global $wpdb;

	$table_name = $wpdb->prefix . "pv_commission";
	$query      = "SELECT sum(total_due + total_shipping + tax) as total
				FROM `{$table_name}`
				WHERE status = %s";
	$results    = $wpdb->get_results( $wpdb->prepare( $query, 'due' ) );

	$total_due = array_shift( $results )->total;
}
$options[ ] = array( 'name' => __( 'Payments', 'wcvendors' ), 'type' => 'heading' );
$options[ ] = array(
	'name' => __( 'User payments', 'wcvendors' ), 'type' => 'title', 'desc' =>
		sprintf( __( 'Total commission currently due: %s. <a href="%s">View details</a>.', 'wcvendors' ), !function_exists( 'woocommerce_price' ) ? $total_due : woocommerce_price( $total_due ), '?page=pv_admin_commissions' ) .
		'<br/><br/>' . sprintf( __( 'Make sure you update your PayPal Adaptive Payments settings <a href="%s">here</a>.', 'wcvendors' ), 'admin.php?page=wc-settings&tab=checkout&section=wc_paypalap' )
);

$options[ ] = array(
	'name' => __( 'Instant pay', 'wcvendors' ),
	'desc' => __( 'Instantly pay vendors their commission when an order is made', 'wcvendors' ),
	'tip'  => __( 'For this to work, customers must checkout with the PayPal Adaptive Payments gateway. Using any other gateways will not pay vendors instantly', 'wcvendors' ),
	'id'   => 'instapay',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'name'    => __( 'Payment schedule', 'wcvendors' ),
	'desc'    => __( 'Note: Schedule will only work if instant pay is unchecked', 'wcvendors' ),
	'id'      => 'schedule',
	'type'    => 'radio',
	'std'     => 'manual',
	'options' => array(
		'weekly'   => __( 'Weekly', 'wcvendors' ),
		'biweekly' => __( 'Biweekly', 'wcvendors' ),
		'monthly'  => __( 'Monthly', 'wcvendors' ),
		'manual'   => __( 'Manual', 'wcvendors' ),
		'now'      => '<span style="color:green;"><strong>' . __( 'Now', 'wcvendors' ) . '</strong></span>',
	)
);

$options[ ] = array(
	'name' => __( 'Email notification', 'wcvendors' ),
	'desc' => __( 'Send the WooCommerce admin an email each time a payment has been made via the payment schedule options above', 'wcvendors' ),
	'id'   => 'mail_mass_pay_results',
	'type' => 'checkbox',
	'std'  => true,
);
