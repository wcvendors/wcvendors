<?php
/**
 * Admin View: Settings
 *
 * @author      Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );
$paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );


?>
<div class="wrap">

	<div id="icon-woocommerce" class="icon32 icon32-woocommerce-reports"><br/></div>
	<h2><?php _e( 'Commission', 'wc-vendors' ); ?></h2>
	<form id="posts-filter" method="get">

		<?php printf( '<input type="hidden" name="page" value="%s" />', $page ); ?>
		<?php printf( '<input type="hidden" name="paged" value="%d" />', $paged ); ?>

		<input type="hidden" name="page" value="wcv-commissions"/>

		<?php $this->commissions_table->prepare_items(); ?>
		<?php $this->commissions_table->views(); ?>
		<?php $this->commissions_table->display(); ?>

	</form>
	<div id="ajax-response"></div>

	<br class="clear"/>
</div>
