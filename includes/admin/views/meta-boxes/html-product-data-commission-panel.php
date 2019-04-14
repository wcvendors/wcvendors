<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="commission_product_data" class="panel woocommerce_options_panel hidden">
	<div class="options_group">
		<?php 
			woocommerce_wp_text_input( array(
					'id'          => '_wcv_commission_rate',
					'value'       => $product_object->get_meta( '_wcv_commission_rate' , true ),
					'label'       => __( 'Commission', 'wcvendors' ),
					'placeholder' => __( 'Percentage commission', 'wcvendors' ), 
					'desc_tip'    => true,
					'description' => __( 'Commission in percent %', 'wcvendors' ),
					'type'        => 'text',
					'data_type'   => 'text'
				) );
		?>
	</div>

	<?php do_action( 'wcvendors_product_options_commission' ); ?>
</div>