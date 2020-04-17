<?php
/**
 * Date picker Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/dashboard/date-picker.php
 *
 * @author        Jamie Madden, WC Vendors
 * @package       WCVendors/Templates/dashboard
 * @version       2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form method="post">
	<p>
		<label style="display:inline;" for="from"><?php _e( 'From:', 'wc-vendors' ); ?></label>
		<input class="date-pick" type="date" name="start_date" id="from"
		       value="<?php echo esc_attr( gmdate( 'Y-m-d', $start_date ) ); ?>"/>

		<label style="display:inline;" for="to"><?php _e( 'To:', 'wc-vendors' ); ?></label>
		<input type="date" class="date-pick" name="end_date" id="to"
		       value="<?php echo esc_attr( gmdate( 'Y-m-d', $end_date ) ); ?>"/>

		<input type="submit" class="btn btn-inverse btn-small" style="float:none;"
		       value="<?php _e( 'Show', 'wc-vendors' ); ?>"/>
	</p>
</form>
