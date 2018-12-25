<?php
/**
 * Admin View: Step One
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<h1><?php esc_html_e( 'Your marketplace is ready!', 'wc-vendors' ); ?></h1>

<!-- <div class="wcvendors-message wcvendors-newsletter">
	<p><?php esc_html_e( 'Subscribe to our newsletter! Get product updates, marketplace tips, information and more.', 'wc-vendors' ); ?></p>
	<form action="//wcvendors.us8.list-manage.com/subscribe/post?u=2c1434dc56f9506bf3c3ecd21&amp;id=13860df971" method="post" target="_blank" novalidate>
		<div class="newsletter-form-container">
			<input
				class="newsletter-form-email"
				type="email"
				value="<?php echo esc_attr( $user_email ); ?>"
				name="EMAIL"
				placeholder="<?php esc_attr_e( 'Email address', 'wc-vendors' ); ?>"
				required
			>
			<p class="wcv-setup-actions step newsletter-form-button-container">
				<button
					type="submit"
					value="<?php esc_html_e( 'Subscribe me', 'wc-vendors' ); ?>"
					name="subscribe"
					id="mc-embedded-subscribe"
					class="button-primary button newsletter-form-button"
				><?php esc_html_e( 'Subscribe me', 'wc-vendors' ); ?></button>
			</p>
		</div>
	</form>
</div>
 -->
<ul class="wcv-wizard-next-steps">
	<li class="wcv-wizard-next-step-item">
		<div class="wcv-wizard-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'Next step', 'wc-vendors' ); ?></p>
			<h3 class="next-step-description"><?php esc_html_e( 'Upgrade to Pro!', 'wc-vendors' ); ?></h3>
			<p class="next-step-extra-info"><?php esc_html_e( 'Upgrade today to extend the features of your marketplace.', 'wc-vendors' ); ?></p>
			<p class="next-step-heading"><?php esc_html_e( 'Features', 'wc-vendors' ); ?></p>
			<ul>
				<li><?php _e( 'Complete frontend dashboard for vendors', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Flat rate & table rate shipping module', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Coupons, ratings, reports, orders and more.', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Advanced commissions', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Premium support & updates', 'wc-vendors' ); ?></li>
			</ul>
		</div>
		<div class="wcv-wizard-next-step-action">
			<p class="wcv-setup-actions step">
				<a class="button button-primary button-large"
				   href="https://www.wcvendors.com/product/wc-vendors-pro/?utm_source=setup_wizard&utm_medium=plugin&utm_campaign=setup_complete"
				   target="_blank">
					<?php _e( 'Upgrade Now', 'wc-vendors' ); ?>
				</a>
			</p>
		</div>
	</li>
	<li class="wcv-wizard-next-step-item">
		<div class="wcv-wizard-next-step-description">
			<p class="next-step-heading"><?php _e( 'Extend your marketplace', 'wc-vendors' ); ?></p>
			<h3 class="next-step-description"><?php _e( 'Extensions', 'wc-vendors' ); ?></h3>
			<p class="next-step-extra-info"><?php _e( 'Extend your marketplace today with a variety of extensions from us and 3rd party developers.', 'wc-vendors' ); ?></p>
		</div>
		<div class="wcv-wizard-next-step-action">
			<p class="wcv-setup-actions step">
				<a class="button button-large"
				   href="https://www.wcvendors.com/extensions/?utm_source=setup_wizard&utm_medium=plugin&utm_campaign=setup_complete"
				   target="_blank">
					<?php _e( 'View Extensions', 'wc-vendors' ); ?>
				</a>
			</p>
		</div>
	</li>
</ul>
<h4 class="help-title"><?php _e( 'Need Help?', 'wc-vendors' ); ?></h4>
<p class="next-steps-help-text"><?php echo wp_kses_post( $help_text ); ?></p>
