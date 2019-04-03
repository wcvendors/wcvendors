<!-- DEPRECAITED -  -->

<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( 'Hi there. This is a notification about a new product on %s.', 'wc-vendors' ), get_option( 'blogname' ) ); ?></p>

<p>
	<?php printf( __( 'Product title: %s', 'wc-vendors' ), $product_name ); ?><br/>
	<?php printf( __( 'Submitted by: %s', 'wc-vendors' ), $vendor_name ); ?><br/>
	<?php printf( __( 'Edit product: %s', 'wc-vendors' ), admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ); ?>
	<br/>
</p>

<?php do_action( 'woocommerce_email_footer' ); ?>
