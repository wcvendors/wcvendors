<?php
/**
 * Admin View: Page - Addons
 *
 * @var string $view
 * @var object $addons
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap woocommerce wcv_addons_wrap">

	<h1><?php _e( 'Upgrade your marketplace today!', 'wc-vendors' ); ?></h1>
	<br/>

	<div class="addons-wcs-banner-block">
		<div class="addons-wcs-banner-block-image">
			<img class="addons-img" src="<?php echo wcv_assets_url; ?>images/extensions/screenshot-1.png"
			     alt="WC Vendors Pro">
			<img class="addons-img" src="<?php echo wcv_assets_url; ?>images/extensions/screenshot-2.png"
			     alt="WC Vendors Pro">
			<img class="addons-img" src="<?php echo wcv_assets_url; ?>images/extensions/screenshot-3.png"
			     alt="WC Vendors Pro">
		</div>
		<div class="addons-wcs-banner-block-content">
			<h1><?php _e( 'WC Vendors Pro', 'wc-vendors' ); ?></h1>

			<p><?php _e( 'Enhanced your marketplace with pro features and capabilities. Move all your vendors tasks to the frontend. They no longer need or see the WordPress admin. Shipping system included.', 'wc-vendors' ); ?></p>

			<h4>Features</h4>

			<ul class="feature-list">
				<li><?php _e( 'Vendors have a main dashboard showing sales reports and recent orders and products.', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendors have complete front end product management', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendors can add their own coupons that only apply to their products', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendors have advanced shipment tracking including shipping providers', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendors have the ability to print a shipping label with a picking list', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendors can add all their own social media profile links', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendors can add SEO to their store and products', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendors can add their own banner and store icon', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendors have a comprehensive shipping system available. Either flat rate or table rate based.', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendor stores templates are more advanced', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendors have more control over their order notes', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Vendors vacation module is included as part of pro not an extra addon', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Admins have multiple commission rate options including percentage, percentage + fee, fixed, fixed + fee.', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Admins can set global shipping rates as well as allow different shipping modules per vendor. Setting one as flat rate, while another can be table rate', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Ebay style feedback allows customers to rate the products from the vendors', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Premium ticket based support for all pro users. Annual and lifetime support licenses available.', 'wc-vendors' ); ?></li>
			</ul>


			<a class="product-addons-button product-addons-button-solid"
			   href="https://www.wcvendors.com/product/wc-vendors-pro/">From $199</a>
		</div>
	</div>

	<ul class="products">
		<li class="product">
			<a href="https://www.wcvendors.com/product/stripe-commissions-gateway/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">
				<h2><?php _e( 'WC Vendors Stripe Commissions &amp; Gateway', 'wc-vendors' ); ?></h2>
				<p><?php _e( 'Pay your vendors their commissions instantly when the customer purchases. No need to manually pay your vendors.', 'wc-vendors' ); ?></p>
				<span class="product-addons-button product-addons-button-solid"><?php _e( 'From $69', 'wc-vendors' ); ?></span>
			</a>
		</li>
		<li class="product">
			<a href="https://www.wcvendors.com/product/woocommerce-bookings-integration/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">
				<h2><?php _e( 'WooCommerce Bookings Integration<', 'wc-vendors' ); ?>/h2>
					<span class="price"><?php _e( 'From $69.00', 'wc-vendors' ); ?></span>
					<p><?php _e( 'Allow vendors to create bookings. Integrate WooCommerce bookings into the WC Vendors Pro Dashboard.', 'wc-vendors' ); ?></p>
					<span class="product-addons-button product-addons-button-solid"><?php _e( 'From $69', 'wc-vendors' ); ?></span>
			</a>
		</li>
		<li class="product">
			<a href="https://www.wcvendors.com/product/woocommerce-simple-auctions-integration/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">
				<h2><?php _e( 'WooCommerce Simple Auctions', 'wc-vendors' ); ?></h2>
				<p><?php _e( 'Integreate WooCommerce Simple Auctions into the WC Vendors Pro dashboard.', 'wc-vendors' ); ?> </p>
				<span class="product-addons-button product-addons-button-solid"><?php _e( 'From $69', 'wc-vendors' ); ?></span>
			</a>
		</li>
		<li class="product">
			<a href="https://www.wcvendors.com/home/3rd-party-extensions/y/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">
				<h2><?php _e( '3rd Party Extensions', 'wc-vendors' ); ?></h2>
				<p><?php _e( 'We have a list of 3rd party developer extensions that inetegrate with WC Vendors and WC Vendors Pro on our website.', 'wc-vendors' ); ?></p>
				<span class="product-addons-button product-addons-button-solid"><?php _e( 'View extensions', 'wc-vendors' ); ?></span>
			</a>
		</li>

	</ul>


	<p><?php printf( __( 'Our list of extensions for WC Vendors can be found on our website. <a href="%s">Click here to view our extensions list.</a>', 'wc-vendors' ), 'https://www.wcvendors.com/extensions/' ); ?></p>


</div>
