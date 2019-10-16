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

	<?php if ( ! class_exists( 'WCVendors_Pro' ) ) : ?>

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

			<h3>Features</h3>

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
			   href="https://www.wcvendors.com/product/wc-vendors-pro/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">From $199</a>
		</div>
	</div>

	<?php endif; ?>

	<ul class="products">
		<li class="product">
			<a href="https://www.wcvendors.com/product/wc-vendors-stripe-connect/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">
				<h2><?php _e( 'WC Vendors Stripe Connect', 'wc-vendors' ); ?></h2>
				<p><?php _e( 'Take credit card payments and pay your vendors their commissions instantly when the customer purchases. No need to manually pay your vendors.', 'wc-vendors' ); ?></p>
				<span class="product-addons-button product-addons-button-solid"><?php _e( 'From $89', 'wc-vendors' ); ?></span>
			</a>
		</li>
		<li class="product">
			<a href="https://www.wcvendors.com/product/wc-vendors-membership/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">
				<h2><?php _e( 'WC Vendors Membership', 'wc-vendors' ); ?></h2>
				<p><?php _e( 'Earn guaranteed income from your vendors with this easy to use extension. Want to charge your vendors to list products on your marketplace? ', 'wc-vendors' ); ?></p>
				<span class="product-addons-button product-addons-button-solid"><?php _e( 'From $89', 'wc-vendors' ); ?></span>
			</a>
		</li>
	</ul>
	<ul class="products">
		<li class="product">
			<a href="https://www.wcvendors.com/product/wc-vendors-woocommerce-bookings/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">
				<h2><?php _e( 'WC Vendors WooCommerce Bookings<', 'wc-vendors' ); ?>/h2>
					<span class="price"><?php _e( 'From $89.00', 'wc-vendors' ); ?></span>
					<p><?php _e( 'Allow vendors to create bookings. Integrate WooCommerce bookings into the WC Vendors Pro Dashboard.', 'wc-vendors' ); ?></p>
					<span class="product-addons-button product-addons-button-solid"><?php _e( 'From $89', 'wc-vendors' ); ?></span>
			</a>
		</li>
		<li class="product">
			<a href="https://www.wcvendors.com/product/wc-vendors-woocommerce-subscriptions/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">
				<h2><?php _e( 'WC Vendors WooCommerce Subscriptions<', 'wc-vendors' ); ?>/h2>
					<span class="price"><?php _e( 'From $89.00', 'wc-vendors' ); ?></span>
					<p><?php _e( 'Allow vendors to create subscriptions. Integrates WooCommerce subscriptions into the WC Vendors Pro Dashboard.', 'wc-vendors' ); ?></p>
					<span class="product-addons-button product-addons-button-solid"><?php _e( 'From $89', 'wc-vendors' ); ?></span>
			</a>
		</li>
	</ul>
	<ul class="products">
		<li class="product">
			<a href="https://www.wcvendors.com/product/woocommerce-simple-auctions-integration/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">
				<h2><?php _e( 'WooCommerce Simple Auctions', 'wc-vendors' ); ?></h2>
				<p><?php _e( 'Allow vendors to create auctions. Integreate WooCommerce Simple Auctions into the WC Vendors Pro dashboard.', 'wc-vendors' ); ?> </p>
				<span class="product-addons-button product-addons-button-solid"><?php _e( 'From $49', 'wc-vendors' ); ?></span>
			</a>
		</li>
		<li class="product">
			<a href="https://www.wcvendors.com/home/compatible-plugins/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions">
				<h2><?php _e( 'Compatible 3rd party plugins', 'wc-vendors' ); ?></h2>
				<p><?php _e( 'You can find a selection of compatible plugins with WC Vendors Marketplace and or WC Vendors Pro. <br /><br />They cover a range of areas including vendor payment gateways, shipping, service, support and chat, social media and currency/credit systems.', 'wc-vendors' ); ?></p>
				<span class="product-addons-button product-addons-button-solid"><?php _e( 'View compatible plugins', 'wc-vendors' ); ?></span><br />
			</a>
		</li>

	</ul>


	<p><?php printf( __( 'Our list of premium extesnions for WC Vendors can be found on our website. <a href="%s">Click here to view our extensions list.</a>', 'wc-vendors' ), 'https://www.wcvendors.com/plugins/?utm_source=plugin&utm_medium=addons&utm_campaign=extensions' ); ?></p>


</div>
