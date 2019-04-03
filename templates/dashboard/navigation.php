<?php
/**
 * Navigation Template
 *
 * This template can be overridden by copying it to yourtheme/wc-vendors/dashboard/navigation.php
 *
 * @author        WC Vendors
 * @package       WCVendors/Templates/dashboard
 * @version       2.1.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<nav class="wcv-dashboard-navigation">
	<ul>
		<?php foreach ( $items as $item_id => $item ) : ?>
			<?php if ( ! isset( $item['url'] ) || ! isset( $item['label'] ) ) {
				continue;
			} ?>
			<li>
				<a href="<?php echo esc_url( $item['url'] ); ?>"
				   target="<?php echo isset( $item['target'] ) ? esc_attr( $item['target'] ) : '_self'; ?>"
				   class="<?php echo esc_attr( wcv_get_dashboard_nav_item_classes( $item_id ) ); ?>"
				>
					<?php echo esc_html( $item['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<hr/>
