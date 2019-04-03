<?php
/**
 * Existing Comments Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/orders/comments/existing-comments.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/Emails/HTML
 * @version       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php

foreach ( $comments as $comment ) :
	$last_added = human_time_diff( strtotime( $comment->comment_date_gmt ), current_time( 'timestamp', 1 ) );

	?>

	<p>
		<?php printf( __( 'added %s ago', 'wc-vendors' ), $last_added ); ?>
		</br>
		<?php echo $comment->comment_content; ?>
	</p>

<?php endforeach; ?>
