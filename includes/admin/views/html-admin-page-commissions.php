<?php 
/**
 * Output the commisions table on the commissions page 
 * 
 * @since 2.0.0
 * @version 2.0.0 
 */
?>

<div class="wrap">

	<h2><?php esc_html_e( 'Vendor Commissions', 'wcvendors' ); ?>
		<?php 
			if ( ! empty( $_REQUEST[ 's' ] ) ) {
				echo '<span class="subtitle">' . esc_html__( 'Search results for', 'wcvendors' ) . ' "' . sanitize_text_field( $_REQUEST['s'] ) . '"</span>';
			} 
		?>
	</h2>

	<ul class="subsubsub"><?php $commissions_table->views(); ?></ul>

	<form id="wcv-commission-table" action="" method="get">
		<input type="hidden" name="page" value="wcv-commissions" />
		<?php $commissions_table->search_box( esc_html__( 'Search Order #', 'wcvendors' ), 'search_id' ); ?>
		<?php $commissions_table->display(); ?>
	</form>

</div>