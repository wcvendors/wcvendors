<div class="pv_paypal_container">
	<p><b><?php _e( 'PayPal Address', 'wc-vendors' ); ?></b><br/>
		<?php _e( 'Your PayPal address can be used to manually send you your commission.', 'wc-vendors' ); ?><br/>

		<input type="email" name="pv_paypal" id="pv_paypal" placeholder="some@email.com"
			   value="<?php echo get_user_meta( $user_id, 'pv_paypal', true ); ?>"/>
	</p>
</div>
