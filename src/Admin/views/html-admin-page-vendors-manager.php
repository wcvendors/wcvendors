
<?php
/**
 * Output the vendor table on the vendor management page
 *
 * @since 2.0.0
 * @version 2.0.0
 */
?>
<div class="wrap">

	<h2><?php esc_html_e( 'Vendor Management', 'wc-vendors' ); ?>
		<?php
		if ( ! empty( $_REQUEST['s'] ) ) {
			echo '<span class="subtitle">' . esc_html__( 'Search results for', 'wc-vendors' ) . ' "' . sanitize_text_field( $_REQUEST['s'] ) . '"</span>';
		}
		?>
	</h2>

	<ul class="subsubsub"><?php $vendors_mamagement_table->views(); ?></ul>

	<form id="wcv-vendors-table" action="" method="get">

		<?php $vendors_mamagement_table->search_box( esc_html__( 'Search Vendor', 'wc-vendors' ), 'search_id' ); ?>
		<?php $vendors_mamagement_table->display(); ?>
		<input type="hidden" name="page" value="wcv-vendors"/>
		
	</form>

</div>