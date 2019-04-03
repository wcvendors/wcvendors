<h3><?php _e( 'WC Vendors', 'wc-vendors' ); ?></h3>

<table class="form-table">
	<tbody>

	<?php if ( apply_filters( 'wcvendors_admin_user_meta_shop_html_enable', true ) ) : ?>

		<?php do_action( 'wcvendors_admin_before_shop_html', $user ); ?>
		<tr>
			<th scope="row">Shop HTML</th>
			<td>
				<label for="pv_shop_html_enabled">
					<input name="pv_shop_html_enabled" type="checkbox"
					       id="pv_shop_html_enabled" <?php checked( true, get_user_meta( $user->ID, 'pv_shop_html_enabled', true ), $echo = true ); ?>/>
					<?php _e( 'Enable HTML for the shop description', 'wc-vendors' ); ?>
				</label>
			</td>
		</tr>
		<?php do_action( 'wcvendors_admin_after_shop_html', $user ); ?>

	<?php endif; ?>

	<?php if ( apply_filters( 'wcvendors_admin_user_meta_shop_name_enable', true ) ) : ?>

		<?php do_action( 'wcvendors_admin_before_shop_name', $user ); ?>
		<tr>
			<th><label for="pv_shop_name"><?php _e( 'Shop name', 'wc-vendors' ); ?></label></th>
			<td><input type="text" name="pv_shop_name" id="pv_shop_name"
			           value="<?php echo get_user_meta( $user->ID, 'pv_shop_name', true ); ?>" class="regular-text">
			</td>
		</tr>
		<?php do_action( 'wcvendors_admin_after_shop_name', $user ); ?>

	<?php endif; ?>

	<?php if ( apply_filters( 'wcvendors_admin_user_meta_paypal_enable', true ) ) : ?>

		<?php do_action( 'wcvendors_admin_before_paypal', $user ); ?>
		<tr>
			<th><label for="pv_paypal"><?php _e( 'PayPal E-mail', 'wc-vendors' ); ?> <span
							class="description"></span></label></th>
			<td><input type="email" name="pv_paypal" id="pv_paypal"
			           value="<?php echo get_user_meta( $user->ID, 'pv_paypal', true ); ?>" class="regular-text">
			</td>
		</tr>
		<?php do_action( 'wcvendors_admin_after_paypal', $user ); ?>

	<?php endif; ?>

	<?php if ( apply_filters( 'wcvendors_admin_user_meta_bank_details_enable', true ) ) : ?>

		<?php do_action( 'wcvendors_admin_before_bank_details', $user ); ?>
		<tr>
			<th><label for="wcv_bank_account_name"><?php _e( 'Bank Account Name', 'wc-vendors' ); ?> <span
							class="description"></span></label></th>
			<td><input type="text" name="wcv_bank_account_name" id="wcv_bank_account_name"
			           value="<?php echo get_user_meta( $user->ID, 'wcv_bank_account_name', true ); ?>"
			           class="regular-text">
			</td>
		</tr>
		<tr>
			<th><label for="wcv_bank_account_number"><?php _e( 'Bank Account Number', 'wc-vendors' ); ?> <span
							class="description"></span></label></th>
			<td><input type="text" name="wcv_bank_account_number" id="wcv_bank_account_number"
			           value="<?php echo get_user_meta( $user->ID, 'wcv_bank_account_number', true ); ?>"
			           class="regular-text">
			</td>
		</tr>
		<tr>
			<th><label for="wcv_bank_name"><?php _e( 'Bank Name', 'wc-vendors' ); ?> <span
							class="description"></span></label></th>
			<td><input type="text" name="wcv_bank_name" id="wcv_bank_name"
			           value="<?php echo get_user_meta( $user->ID, 'wcv_bank_name', true ); ?>" class="regular-text">
			</td>
		</tr>
		<tr>
			<th><label for="wcv_bank_routing_number"><?php _e( 'Routing Number', 'wc-vendors' ); ?> <span
							class="description"></span></label></th>
			<td><input type="text" name="wcv_bank_routing_number" id="wcv_bank_routing_number"
			           value="<?php echo get_user_meta( $user->ID, 'wcv_bank_routing_number', true ); ?>"
			           class="regular-text">
			</td>
		</tr>
		<tr>
			<th><label for="wcv_bank_iban"><?php _e( 'IBAN', 'wc-vendors' ); ?> <span
							class="description"></span></label></th>
			<td><input type="text" name="wcv_bank_iban" id="wcv_bank_iban"
			           value="<?php echo get_user_meta( $user->ID, 'wcv_bank_iban', true ); ?>" class="regular-text">
			</td>
		</tr>
		<tr>
			<th><label for="wcv_bank_bic_swift"><?php _e( 'BIC/SWIFT', 'wc-vendors' ); ?> <span
							class="description"></span></label></th>
			<td><input type="text" name="wcv_bank_bic_swift" id="wcv_bank_bic_swift"
			           value="<?php echo get_user_meta( $user->ID, 'wcv_bank_bic_swift', true ); ?>"
			           class="regular-text">
			</td>
		</tr>
		<?php do_action( 'wcvendors_admin_after_bank_details', $user ); ?>

	<?php endif; ?>


	<?php if ( apply_filters( 'wcvendors_admin_user_meta_commission_rate_enable', true ) ) : ?>

		<?php do_action( 'wcvendors_admin_before_commission_due', $user ); ?>
		<tr>
			<th><label for="pv_custom_commission_rate"><?php _e( 'Commission rate', 'wc-vendors' ); ?> (%)</label></th>
			<td><input type="number" step="0.01" max="100" min="0" name="pv_custom_commission_rate"
			           placeholder="<?php _e( 'Leave blank for default', 'wc-vendors' ); ?>"
			           id="pv_custom_commission_rate"
			           value="<?php echo get_user_meta( $user->ID, 'pv_custom_commission_rate', true ); ?>"
			           class="regular-text">
			</td>
		</tr>
		<?php do_action( 'wcvendors_admin_after_commission_due', $user ); ?>

	<?php endif; ?>

	<?php if ( apply_filters( 'wcvendors_admin_user_meta_give_tax_enable', true ) ) : ?>

		<?php do_action( 'wcvendors_admin_before_give_tax', $user ); ?>
		<tr>
			<th><label for="wcv_give_vendor_tax"><?php _e( 'Give Tax', 'wc-vendors' ); ?> (%)</label></th>
			<td>
				<label for="wcv_give_vendor_tax">
					<input name="wcv_give_vendor_tax" type="checkbox"
					       id="wcv_give_vendor_tax" <?php checked( true, get_user_meta( $user->ID, 'wcv_give_vendor_tax', true ), $echo = true ); ?>/>
					<?php printf( __( 'Tax override for %s', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ); ?>
				</label>
			</td>
		</tr>
		<?php do_action( 'wcvendors_admin_after_give_tax', $user ); ?>

	<?php endif; ?>

	<?php if ( apply_filters( 'wcvendors_admin_user_meta_give_shipping_enable', true ) ) : ?>

		<?php do_action( 'wcvendors_admin_before_give_shipping', $user ); ?>
		<tr>
			<th><label for="wcv_give_vendor_shipping"><?php _e( 'Give Shipping', 'wc-vendors' ); ?> (%)</label></th>
			<td>
				<label for="wcv_give_vendor_shipping">
					<input name="wcv_give_vendor_shipping" type="checkbox"
					       id="wcv_give_vendor_shipping" <?php checked( true, get_user_meta( $user->ID, 'wcv_give_vendor_shipping', true ), $echo = true ); ?>/>
					<?php printf( __( 'Shipping override for %s', 'wc-vendors' ), wcv_get_vendor_name( true, false ) ); ?>
				</label>
			</td>
		</tr>
		<?php do_action( 'wcvendors_admin_after_give_shipping', $user ); ?>

	<?php endif; ?>

	<?php if ( apply_filters( 'wcvendors_admin_user_meta_seller_info_enable', true ) ) : ?>

		<?php do_action( 'wcvendors_admin_before_seller_info', $user ); ?>
		<tr>
			<th><label for="pv_seller_info"><?php _e( 'Seller info', 'wc-vendors' ); ?></label></th>
			<td><?php wp_editor( get_user_meta( $user->ID, 'pv_seller_info', true ), 'pv_seller_info' ); ?></td>
		</tr>
		<?php do_action( 'wcvendors_admin_after_seller_info', $user ); ?>

	<?php endif; ?>

	<?php if ( apply_filters( 'wcvendors_admin_user_meta_shop_description_enable', true ) ) : ?>

		<?php do_action( 'wcvendors_admin_before_shop_description', $user ); ?>
		<tr>
			<th><label for="pv_shop_description"><?php _e( 'Shop description', 'wc-vendors' ); ?></label>
			</th>
			<td><?php wp_editor( get_user_meta( $user->ID, 'pv_shop_description', true ), 'pv_shop_description' ); ?></td>
		</tr>
		<?php do_action( 'wcvendors_admin_after_shop_description', $user ); ?>

	<?php endif; ?>

	</tbody>
</table>
