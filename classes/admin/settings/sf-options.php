<?php
$options = array();

$options[ ] = array( 'name' => __( 'General', 'wc-vendors' ), 'type' => 'heading' );
$options[ ] = array( 'name' => __( 'General options', 'wc-vendors' ), 'type' => 'title', 'desc' => '' );

$options[ ] = array(
	'name'     => __( 'Default commission (%)', 'wc-vendors' ),
	'desc'     => __( 'The default rate you pay each vendor for a product sale. <br>You can also give vendors their own individual commission rates by editing the vendors user account.<br>Also, you can edit an individual products commission to override both of these settings on a per product basis.', 'wc-vendors' ),
	'id'       => 'default_commission',
	'css'      => 'width:70px;',
	'type'     => 'number',
	'restrict' => array(
		'min' => 0,
		'max' => 100
	)
);

/* Customize registration message depending on if they have registration enabled on the my account page */
$registration_message = __( 'Allow users or guests to apply to become a vendor', 'wc-vendors' );
if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'no' ) {
        $registration_message = __( 'Allow users or guests to apply to become a vendor.  <br><br><strong>WARNING:</strong>  You MUST "<strong>Enable registration on the "My Account" page</strong>" in your <strong>WooCommerce > Settings > Accounts</strong> page for this option to work.  Currently, you have registration disabled.', 'wc-vendors' );
}

$options[ ] = array(
	'name' => __( 'Registration', 'wc-vendors' ),
	'desc' => __( 'Allow users or guests to apply to become a vendor', 'wc-vendors' ),
	'tip'  => __( 'This will show a checkbox on the My Account page\'s registration form asking if the user would like to apply to be a vendor. Also, on the Vendor Dashboard, users can still apply to become a vendor even if this is disabled.', 'wc-vendors' ),
	'id'   => 'show_vendor_registration',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'Approve vendor applications manually', 'wc-vendors' ),
	'tip'  => __( 'With this unchecked, all vendor applications are automatically accepted. Otherwise, you must approve each manually.', 'wc-vendors' ),
	'id'   => 'manual_vendor_registration',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'name' => __( 'Taxes', 'wc-vendors' ),
	'desc' => __( 'Give vendors any tax collected per-product', 'wc-vendors' ),
	'tip'  => __( 'The tax collected on a vendor\'s product will be given in its entirety', 'wc-vendors' ),
	'id'   => 'give_tax',
	'type' => 'checkbox',
	'std'  => false,
);

$options[ ] = array(
	'name' => __( 'Shipping', 'wc-vendors' ),
	'desc' => __( 'Give vendors any shipping collected per-product', 'wc-vendors' ),
	'tip'  => __( 'WC Vendors Free - Give vendors shipping if using Per Product Shipping gateway.  WC Vendors Pro - Give vendors shipping when using Vendor Shipping.  No other shipping module is compatible with this option.', 'wc-vendors' ),
	'id'   => 'give_shipping',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array( 'name' => __( 'Shop options', 'wc-vendors' ), 'type' => 'title', 'desc' => '' );

$options[ ] = array(
	'name' => __( 'Shop HTML', 'wc-vendors' ),
	'desc' => __( 'Enable HTML for a vendor\'s shop description by default.  You can enable or disable this per vendor by editing the vendors user account.', 'wc-vendors' ),
	'id'   => 'shop_html_enabled',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'name' => __( 'Vendor Shop Page', 'wc-vendors' ),
	'desc' => __( 'Enter one word for the URI.  If you enter "<strong>vendors</strong>" your vendors store will be <code>yourdomain.com/vendors/store-name/</code>', 'wc-vendors' ),
	'id'   => 'vendor_shop_permalink',
	'type' => 'text',
	'std'  => 'vendors/',
);

$options[ ] = array(
	'name' => __( 'Shop Headers', 'wc-vendors' ),
	'desc' => __( 'Enable vendor shop headers', 'wc-vendors' ),
	'tip'  => __( 'This will override the HTML Shop description output on product-archive pages.  In order to customize the shop headers visit wcvendors.com and read the article in the Knowledgebase titled Changing the Vendor Templates.', 'wc-vendors' ),
	'id'   => 'shop_headers_enabled',
	'type' => 'checkbox',
	'std'  => false,
);

$options[ ] = array(
	'name'    => __( 'Vendor Display Name', 'wc-vendors' ),
	'desc'    => __( 'Select what will be displayed for the sold by text throughout the store.', 'wc-vendors' ),
	'id'      => 'vendor_display_name',
	'type'    => 'select',
	'options' => array(
		'display_name' 	=> __( 'Display Name', 'wc-vendors'),
		'shop_name'		=> __( 'Shop Name', 'wc-vendors'),
		'user_login' 	=> __( 'User Login', 'wc-vendors'),
		'user_email' 	=> __( 'User Email', 'wc-vendors'),
	),
	'std'	=> 'shop_name'

);

$options[ ] = array(
	'name' => __( 'Sold By', 'wc-vendors' ),
	'desc' => __( 'Enable sold by labels', 'wc-vendors' ),
	'tip'  => __( 'This will enable or disable the sold by labels.', 'wc-vendors' ),
	'id'   => 'sold_by',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'name' => __( 'Sold By Label', 'wc-vendors' ),
	'desc' => __( 'The sold by label used on the site and emails.', 'wc-vendors' ),
	'id'   => 'sold_by_label',
	'type' => 'text',
	'std'  => __( 'Sold By', 'wc-vendors' ),
);

$options[ ] = array(
	'name' => __( 'Seller Info Label', 'wc-vendors' ),
	'desc' => __( 'The seller info tab title on the single product page.', 'wc-vendors' ),
	'id'   => 'seller_info_label',
	'type' => 'text',
	'std'  => __( 'Seller Info', 'wc-vendors' ),
);

$options[ ] = array( 'name' => __( 'Products', 'wc-vendors' ), 'type' => 'heading' );
$options[ ] = array( 'name' => __( 'Product Add Page', 'wc-vendors' ), 'type' => 'title', 'desc' => __( 'Configure what to hide from all vendors when adding a product', 'wc-vendors' ) );

$options[ ] = array(
	'name'     => __( 'Left side panel', 'wc-vendors' ),
	'desc'     => __( 'CHECKING these boxes will **HIDE** these areas of the add product page for vendors', 'wc-vendors' ),
	'id'       => 'hide_product_panel',
	'options'  => array(
		'inventory'      => __( 'Inventory', 'wc-vendors' ),
		'shipping'       => __( 'Shipping', 'wc-vendors' ),
		'linked_product' => __( 'Linked Products', 'wc-vendors' ),
		'attribute'      => __( 'Attributes', 'wc-vendors' ),
		'advanced'       => __( 'Advanced', 'wc-vendors' ),
	),
	'type'     => 'checkbox',
	'multiple' => true,
);

$options[ ] = array(
	'name'     => __( 'Types', 'wc-vendors' ),
	'desc'     => __( 'CHECKING these boxes will HIDE these product types from the vendor', 'wc-vendors' ),
	'id'       => 'hide_product_types',
	'options'  => array(
		'simple'   => __( 'Simple', 'wc-vendors' ),
		'variable' => __( 'Variable', 'wc-vendors' ),
		'grouped'  => __( 'Grouped', 'wc-vendors' ),
		'external' => __( 'External / affiliate', 'wc-vendors' ),
	),
	'type'     => 'checkbox',
	'multiple' => true,
);

$options[ ] = array(
	'name'     => __( 'Type options', 'wc-vendors' ),
	'desc'     => __( 'CHECKING these boxes will **HIDE** these product options from the vendor', 'wc-vendors' ),
	'id'       => 'hide_product_type_options',
	'options'  => array(
		'virtual'      => __( 'Virtual', 'wc-vendors' ),
		'downloadable' => __( 'Downloadable', 'wc-vendors' ),
	),
	'type'     => 'checkbox',
	'multiple' => true,
);

$options[ ] = array(
	'name'     => __( 'Miscellaneous', 'wc-vendors' ),
	'id'       => 'hide_product_misc',
	'options'  => array(
		'taxes' 		=> __( 'Taxes', 'wc-vendors' ),
		'sku'   		=> __( 'SKU', 'wc-vendors' ),
		'featured'		=> __( 'Featured', 'wc-vendors' ),
		'duplicate'		=> __( 'Duplicate Product', 'wc-vendors' ),
	),
	'type'     => 'checkbox',
	'multiple' => true,
);

$options[ ] = array(
	'name' => __( 'Stylesheet', 'wc-vendors' ),
	'desc' => __( 'You can add CSS in this textarea, which will be loaded on the product add/edit page for vendors.', 'wc-vendors' ),
	'id'   => 'product_page_css',
	'type' => 'textarea',
);


$options[ ] = array( 'name' => __( 'Capabilities', 'wc-vendors' ), 'type' => 'heading', 'id' => 'capabilities' );
$options[ ] = array( 'name' => __( 'Permissions', 'wc-vendors' ), 'id' => 'permissions', 'type' => 'title', 'desc' => __( 'General permissions used around the shop', 'wc-vendors' ) );

$options[ ] = array(
	'name' => __( 'Orders', 'wc-vendors' ),
	'desc' => __( 'View orders', 'wc-vendors' ),
	'tip'  => __( 'Show customer details such as email, address, name, etc, for each order', 'wc-vendors' ),
	'id'   => 'can_show_orders',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'View comments', 'wc-vendors' ),
	'tip'  => __( 'View all vendor comments for an order on the frontend', 'wc-vendors' ),
	'id'   => 'can_view_order_comments',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'Submit comments', 'wc-vendors' ),
	'tip'  => __( 'Submit comments for an order on the frontend. Eg, tracking ID for a product', 'wc-vendors' ),
	'id'   => 'can_submit_order_comments',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'View email addresses', 'wc-vendors' ),
	'tip'  => __( 'While viewing order details on the frontend, you can disable or enable email addresses', 'wc-vendors' ),
	'id'   => 'can_view_order_emails',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'Export a CSV file of orders for a product', 'wc-vendors' ),
	'tip'  => __( 'Vendors could export orders for a product on the frontend', 'wc-vendors' ),
	'id'   => 'can_export_csv',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'View Frontend sales reports', 'wc-vendors' ),
	'tip'  => __( 'Sales table on the frontend on the Vendor Dashboard page. The table will only display sales data that pertain to their products, and only for orders that are processing or completed.', 'wc-vendors' ),
	'id'   => 'can_view_frontend_reports',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'name' => __( 'Products', 'wc-vendors' ),
	'desc' => __( 'Submit products', 'wc-vendors' ),
	'tip'  => __( 'Check to allow vendors to list new products.  Admin must approve new products by editing the product, and clicking Publish.', 'wc-vendors' ),
	'id'   => 'can_submit_products',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'desc' => __( 'Edit live products', 'wc-vendors' ),
	'tip'  => __( 'Vendors could edit an approved product after it has already gone live. There is no approval or review after editing a live product. This could be dangerous with malicious vendors, so take caution.', 'wc-vendors' ),
	'id'   => 'can_edit_published_products',
	'type' => 'checkbox',
	'std'  => false,
);

$options[ ] = array(
	'desc' => __( 'Submit products live without requiring approval', 'wc-vendors' ),
	'tip'  => __( 'Vendors can submit products without review or approval from a shop admin. This could be dangerous with malicious vendors, so take caution.', 'wc-vendors' ),
	'id'   => 'can_submit_live_products',
	'type' => 'checkbox',
	'std'  => false,
);

$options[ ] = array( 'name' => __( 'Pages', 'wc-vendors' ), 'type' => 'heading' );
$options[ ] = array( 'name' => __( 'Page configuration', 'wc-vendors' ), 'type' => 'title', 'desc' => '' );

$options[ ] = array(
	'name'    => __( 'Vendor dashboard', 'wc-vendors' ),
	'desc'    => __( 'Choose the page that has the shortcode <code>[wcv_vendor_dashboard]</code><br/>.  If this page is not set, you will break your site.  If you upgrade to Pro, keep this page unchanged as both Pro Dashboard and this Dashboard page must be set.', 'wc-vendors' ),
	'id'      => 'vendor_dashboard_page',
	'type'    => 'single_select_page',
	'select2' => true,
);

$options[ ] = array(
	'name'    => __( 'Shop settings', 'wc-vendors' ),
	'desc'    => __( 'Choose the page that has the shortcode <code>[wcv_shop_settings]</code><br/>These are the shop settings a vendor can configure.  By default, Vendor Dashboard > Shop Settings should have this shortcode.', 'wc-vendors' ),
	'id'      => 'shop_settings_page',
	'type'    => 'single_select_page',
	'select2' => true,
);

$options[ ] = array(
	'name'    => __( 'Orders page', 'wc-vendors' ),
	'desc'    => __( 'Choose the page that has the shortcode <code>[wcv_orders]</code><br/>By default, Vendor Dashboard > Orders should have the shortcode.', 'wc-vendors' ),
	'id'      => 'product_orders_page',
	'type'    => 'single_select_page',
	'select2' => true,
);

$options[ ] = array(
	'name'    => __( 'Vendor terms', 'wc-vendors' ),
	'desc'    => __( 'These terms are shown to a user when submitting an application to become a vendor.<br/>If left blank, no terms will be shown to the applicant.  Vendor must accept terms in order to register, if set.', 'wc-vendors' ),
	'id'      => 'terms_to_apply_page',
	'type'    => 'single_select_page',
	'select2' => true,
);

$total_due = 0;
if ( !empty( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] == __( 'payments', 'wc-vendors' ) ) {
	global $wpdb;

	$table_name = $wpdb->prefix . "pv_commission";
	$query      = "SELECT sum(total_due + total_shipping + tax) as total
				FROM `{$table_name}`
				WHERE status = %s";
	$results    = $wpdb->get_results( $wpdb->prepare( $query, 'due' ) );

	$total_due = array_shift( $results )->total;
}
$options[ ] = array( 'name' => __( 'Payments', 'wc-vendors' ), 'type' => 'heading' );
$options[ ] = array(
	'name' => __( 'PayPal Adaptive Payments Scheduling - PayPal MassPay has been depreciated by PayPal as of September 2017', 'wc-vendors' ), 'type' => 'title', 'desc' =>
		sprintf( __( 'Total commission currently due: %s. <a href="%s">View details</a>.', 'wc-vendors' ), !function_exists( 'wc_price' ) ? $total_due : wc_price( $total_due ), '?page=pv_admin_commissions' ) .
		'<br/><br/>' . sprintf( __( 'Make sure you update your PayPal Adaptive Payments settings <a href="%s">here</a>.  <br><br>To instantly pay with Adaptive Payments you must activate the PayPal AP gateway in your Checkout settings. <br><a href="https://www.wcvendors.com/kb/configuring-paypal-adaptive-payments/" target="top">PayPal AP Application Help</a>.  <br><br>Another gateway that offers instant payments to vendors that also accepts credit cards directly on your checkout page is Stripe.   <br><a href="https://www.wcvendors.com/product/stripe-commissions-gateway/" target="top">Stripe Commissions & Gateway plugin</a> is specifically coded for WC Vendors and <a href="https://www.wcvendors.com/product/wc-vendors-pro/" target="top">WC Vendors Pro</a>.', 'wc-vendors' ), 'admin.php?page=wc-settings&tab=checkout&section=wc_paypalap' )
);

$options[ ] = array(
	'name' => __( 'Instant pay', 'wc-vendors' ),
	'desc' => __( 'Instantly pay vendors their commission when an order is made, and if a vendor has a valid PayPal email added on their Shop Settings page.', 'wc-vendors' ),
	'tip'  => __( 'For this to work, customers must checkout with the PayPal Adaptive Payments gateway. Using any other gateways will not pay vendors instantly', 'wc-vendors' ),
	'id'   => 'instapay',
	'type' => 'checkbox',
	'std'  => true,
);

$options[ ] = array(
	'name'    => __( 'Payment schedule', 'wc-vendors' ),
	'desc'    => __( 'Note: Schedule will only work if instant pay is unchecked', 'wc-vendors' ),
	'id'      => 'schedule',
	'type'    => 'radio',
	'std'     => 'manual',
	'options' => array(
		'daily'    => __( 'Daily', 'wc-vendors' ),
		'weekly'   => __( 'Weekly', 'wc-vendors' ),
		'biweekly' => __( 'Biweekly', 'wc-vendors' ),
		'monthly'  => __( 'Monthly', 'wc-vendors' ),
		'manual'   => __( 'Manual', 'wc-vendors' ),
		'now'      => '<span style="color:green;"><strong>' . __( 'Now', 'wc-vendors' ) . '</strong></span>',
	)
);

$options[ ] = array(
	'name' => __( 'Email notification', 'wc-vendors' ),
	'desc' => __( 'Send the WooCommerce admin an email each time a payment has been made via the payment schedule options above', 'wc-vendors' ),
	'id'   => 'mail_mass_pay_results',
	'type' => 'checkbox',
	'std'  => true,
);
