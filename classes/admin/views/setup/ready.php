<?php
/**
 * Admin View: Step One
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<h1><?php esc_html_e( 'Your marketplace is ready!', 'wc-vendors' ); ?></h1>

<div class="wcvendors-message wcvendors-newsletter">
<script type='text/javascript' src='//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js'></script><script type='text/javascript'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]='EMAIL';ftypes[0]='email';fnames[1]='FNAME';ftypes[1]='text';fnames[2]='LNAME';ftypes[2]='text';fnames[3]='ADDRESS';ftypes[3]='address';fnames[4]='PHONE';ftypes[4]='phone';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
<p><?php esc_html_e( 'Subscribe to our newsletter! Get product updates, marketplace tips, information and more.', 'wc-vendors' ); ?></p>
<form action="https://wcvendors.us20.list-manage.com/subscribe/post?u=c70c537d05355fa9ec97e8134&amp;id=86c131c9ef" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
	<div class="newsletter-form-container">
		<input
				class="newsletter-form-email"
				type="text"
				value="<?php echo esc_attr( $first_name ); ?>"
				name="FNAME"
				placeholder="<?php esc_attr_e( 'First name', 'wc-vendors' ); ?>"
				required
		>
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
		<div id="mce-responses" class="clear">
			<input type="checkbox"id="group_2" name="group[4145][2]" value="1" style="display:none" checked>
			<div class="response" id="mce-error-response" style="display:none"></div>
			<div class="response" id="mce-success-response" style="display:none"></div>
		</div>
		<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
    	<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_c70c537d05355fa9ec97e8134_462e6aa9c6" tabindex="-1" value=""></div>
	</form>
</div>

<ul class="wcv-wizard-next-steps">
	<li class="wcv-wizard-next-step-item">
		<div class="wcv-wizard-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'Make your life easy', 'wc-vendors' ); ?></p>
			<h2 class="next-step-description"><?php esc_html_e( 'Upgrade to Pro!', 'wc-vendors' ); ?></h2>
			<p class="next-step-extra-info"><?php esc_html_e( 'Upgrade today to enhance your marketplace for the best chance of success.', 'wc-vendors' ); ?></p>
			<p class="next-step-heading"><?php esc_html_e( 'Features', 'wc-vendors' ); ?></p>
			<ul>
				<li><?php _e( 'Remove the need for vendors to access the WordPress dmin with a complete frontend dashboard', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Flat rate & table rate shipping module', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Coupons, ratings, reports, orders and more.', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Advanced commission including fixed and tiered', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Premium support & updates', 'wc-vendors' ); ?></li>
				<li><?php _e( 'Starting from $199 annually', 'wc-vendors' ); ?></li>
			</ul>
		</div>
		<div class="wcv-wizard-next-step-action">
			<p class="wcv-setup-actions step">
				<a class="button button-primary button-large"
				   href="https://www.wcvendors.com/product/wc-vendors-pro/?utm_source=setup_wizard&utm_medium=plugin&utm_campaign=setup_complete"
				   target="_blank">
					<?php _e( 'Upgrade Today', 'wc-vendors' ); ?>
				</a>
			</p>
		</div>
	</li>
	<li class="wcv-wizard-next-step-item">
		<div class="wcv-wizard-next-step-description">
			<p class="next-step-heading"><?php _e( 'Extend your marketplace', 'wc-vendors' ); ?></p>
			<h2 class="next-step-description"><?php _e( 'Premium Extensions', 'wc-vendors' ); ?></h2>
			<p class="next-step-extra-info"><?php _e( 'Turn your marketplace into an auction or subscription site, charge your vendors a monthly membership and more with our awesome premium extensions.', 'wc-vendors' ); ?></p>
		</div>
		<div class="wcv-wizard-next-step-action">
			<p class="wcv-setup-actions step">
				<a class="button button-large"
				   href="https://www.wcvendors.com/plugins/?utm_source=setup_wizard&utm_medium=plugin&utm_campaign=setup_complete"
				   target="_blank">
					<?php _e( 'Buy Premium Extensions', 'wc-vendors' ); ?>
				</a>
			</p>
		</div>
	</li>
</ul>
<h4 class="help-title"><?php _e( 'Need Help?', 'wc-vendors' ); ?></h4>
<p class="next-steps-help-text"><?php echo wp_kses_post( $help_text ); ?></p>
