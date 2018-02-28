<?php
/**
 * Admin View: Step One
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<h1><?php esc_html_e( "Your marketplace is ready!", 'wcvendors' ); ?></h1>

<div class="wcvendors-message wcvendors-newsletter">
	<p><?php esc_html_e( "Subscribe to our newsletter! Get product updates, marketplace tips, information and more.", 'wcvendors' ); ?></p>
	<form action="//wcvendors.us8.list-manage.com/subscribe/post?u=2c1434dc56f9506bf3c3ecd21&amp;id=13860df971" method="post" target="_blank" novalidate>
		<div class="newsletter-form-container">
			<input
				class="newsletter-form-email"
				type="email"
				value="<?php echo esc_attr( $user_email ); ?>"
				name="EMAIL"
				placeholder="<?php esc_attr_e( 'Email address', 'wcvendors' ); ?>"
				required
			>
			<p class="wcv-setup-actions step newsletter-form-button-container">
				<button
					type="submit"
					value="<?php esc_html_e( 'Subscribe me', 'wcvendors' ); ?>"
					name="subscribe"
					id="mc-embedded-subscribe"
					class="button-primary button newsletter-form-button"
				><?php esc_html_e( 'Subscribe me', 'wcvendors' ); ?></button>
			</p>
		</div>
	</form>
</div>

<ul class="wcv-wizard-next-steps">
	<li class="wcv-wizard-next-step-item">
		<div class="wcv-wizard-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'Next step', 'wcvendors' ); ?></p>
			<h3 class="next-step-description"><?php esc_html_e( 'Upgrade to Pro!', 'wcvendors' ); ?></h3>
			<p class="next-step-extra-info"><?php esc_html_e( "Upgrade today to extend the features of your marketplace.", 'wcvendors' ); ?></p>
			<p class="next-step-heading"><?php esc_html_e( 'Features', 'wcvendors' ); ?></p>
			<ul>
				<li><?php _e( 'Complete frontend dashboard for vendors', 'wcvendors' ); ?></li>
				<li><?php _e( 'Flat rate & table rate shipping module', 'wcvendors' ); ?></li>
				<li><?php _e( 'Coupons, ratings, reports, orders and more.', 'wcvendors' ); ?></li>
				<li><?php _e( 'Advanced commissions', 'wcvendors' ); ?></li>
				<li><?php _e( 'Premium support & updates', 'wcvendors' ); ?></li>
			</ul>
		</div>
		<div class="wcv-wizard-next-step-action">
			<p class="wcv-setup-actions step">
				<a class="button button-primary button-large" href="https://www.wcvendors.com/products/wc-vendors-pro/">
					<?php _e( 'Upgrade Now', 'wcvendors' ); ?>
				</a>
			</p>
		</div>
	</li>
	<li class="wcv-wizard-next-step-item">
		<div class="wcv-wizard-next-step-description">
			<p class="next-step-heading"><?php _e( 'Extend your marketplace', 'wcvendors' ); ?></p>
			<h3 class="next-step-description"><?php _e( 'Extensions', 'wcvendors' ); ?></h3>
			<p class="next-step-extra-info"><?php _e( 'Extend your marketplace today with a variety of extensions from us and 3rd party developers.', 'wcvendors' ); ?></p>
		</div>
		<div class="wcv-wizard-next-step-action">
			<p class="wcv-setup-actions step">
				<a class="button button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=wcv-addons' ) ); ?>">
					<?php _e( 'View Extensions', 'wcvendors' ); ?>
				</a>
			</p>
		</div>
	</li>
</ul>
<h4 class="help-title"><?php _e( 'Need Help?', 'wcvendors' ); ?></h4>
<p class="next-steps-help-text"><?php echo wp_kses_post( $help_text ); ?></p>