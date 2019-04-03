<!-- DEPRECAITED -->

<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( 'Hi there. This is a notification about a %1$s application on %2$s.', 'wc-vendors' ), wcv_get_vendor_name( true, false ), get_option( 'blogname' ) ); ?></p>

<p>
	<?php printf( __( 'Application status: %s', 'wc-vendors' ), $status ); ?><br/>
	<?php printf( __( 'Applicant username: %s', 'wc-vendors' ), $user->user_login ); ?>
</p>

<?php do_action( 'woocommerce_email_footer' ); ?>
