<div class="wrap">
	<h2>Shop Settings</h2>
	<form method="post">

		<table class="form-table">
			<?php do_action( 'wcvendors_settings_before_paypal' ); ?>

			<?php if ( $paypal_address !== 'false' ) : ?>

				<tr>
					<th>
						<label for="pv_paypal">
							<?php _e( 'PayPal Address', 'wc-vendors' ); ?>
						</label>
					</th>
					<td>
						<input type="email" name="pv_paypal" class="regular-text" id="pv_paypal"
					           placeholder="some@email.com"
					           value="<?php echo get_user_meta( $user_id, 'pv_paypal', true ); ?>">
						<p class="description">
							<?php _e( 'Your PayPal address can be used to send you your commission.', 'wc-vendors' ); ?>
							<br>
						</p>
					</td>
				</tr>

			<?php endif; ?>

			<?php do_action( 'wcvendors_settings_after_paypal' ); ?>

			<?php if ( apply_filters( 'wcvendors_admin_user_meta_bank_details_enable', true ) ) : ?>

				<?php do_action( 'wcvendors_settings_before_bank_details', $user_id ); ?>
				<tr>
					<th>
						<label for="wcv_bank_account_name">
							<?php _e( 'Bank Account Name', 'wc-vendors' ); ?>
						</label>
					</th>
					<td>
						<input type="text" name="wcv_bank_account_name" id="wcv_bank_account_name"
					           value="<?php echo get_user_meta( $user_id, 'wcv_bank_account_name', true ); ?>"
					           class="regular-text">
					</td>
				</tr>
				<tr>
					<th>
						<label for="wcv_bank_account_number">
							<?php _e( 'Bank Account Number', 'wc-vendors' ); ?>
						</label>
					</th>
					<td>
						<input type="text" name="wcv_bank_account_number" id="wcv_bank_account_number"
					           value="<?php echo get_user_meta( $user_id, 'wcv_bank_account_number', true ); ?>"
					           class="regular-text">
					</td>
				</tr>
				<tr>
					<th>
						<label for="wcv_bank_name">
							<?php _e( 'Bank Name', 'wc-vendors' ); ?>
						</label>
					</th>
					<td>
						<input type="text" name="wcv_bank_name" id="wcv_bank_name"
                               value="<?php echo get_user_meta( $user_id, 'wcv_bank_name', true ); ?>"
                               class="regular-text">
					</td>
				</tr>
				<tr>
					<th>
						<label for="wcv_bank_routing_number">
							<?php _e( 'Routing Number', 'wc-vendors' ); ?>
						</label>
					</th>
					<td>
						<input type="text" name="wcv_bank_routing_number" id="wcv_bank_routing_number"
					           value="<?php echo get_user_meta( $user_id, 'wcv_bank_routing_number', true ); ?>"
					           class="regular-text">
					</td>
				</tr>
				<tr>
					<th>
						<label for="wcv_bank_iban">
							<?php _e( 'IBAN', 'wc-vendors' ); ?>
						</label>
					</th>
					<td>
						<input type="text" name="wcv_bank_iban" id="wcv_bank_iban"
					           value="<?php echo get_user_meta( $user_id, 'wcv_bank_iban', true ); ?>"
					           class="regular-text">
					</td>
				</tr>
				<tr>
					<th>
						<label for="wcv_bank_bic_swift">
							<?php _e( 'BIC/SWIFT', 'wc-vendors' ); ?>
						</label>
					</th>
					<td>
						<input type="text" name="wcv_bank_bic_swift" id="wcv_bank_bic_swift"
					           value="<?php echo get_user_meta( $user_id, 'wcv_bank_bic_swift', true ); ?>"
					           class="regular-text">
					</td>
				</tr>
				<?php do_action( 'wcvendors_settings_after_bank_details', $user_id ); ?>

			<?php endif; ?>

			<tr>
				<th>
					<label for="pv_shop_name">
						<?php _e( 'Shop Name', 'wc-vendors' ); ?>
					</label>
				</th>
				<td>
					<input type="text" name="pv_shop_name" id="pv_shop_name" placeholder="Your shop name"
				           value="<?php echo get_user_meta( $user_id, 'pv_shop_name', true ); ?>">
                    <p class="description">
						<?php _e( 'Your shop name is public and must be unique.', 'wc-vendors' ); ?>
					</p>
				</td>
			</tr>

			<?php do_action( 'wcvendors_settings_after_shop_name' ); ?>

			<tr>
				<th>
					<label for="pv_seller_info_unhtml">
						<?php echo apply_filters( 'wcvendors_seller_info_label', __( 'Seller info', 'wc-vendors' ) ); ?>
					</label>
				</th>
				<td>
					<?php

					if ( $global_html || $has_html ) {
						$old_post        = $GLOBALS['post'];
						$GLOBALS['post'] = 0;
						wp_editor( $seller_info, 'pv_seller_info' );
						$GLOBALS['post'] = $old_post;
					} else {
						?>
						<textarea class="large-text" rows="10" id="pv_seller_info_unhtml" style="width:95%"
						          name="pv_seller_info"><?php echo $seller_info; ?></textarea>
						<?php
					}
					?>
					<p class="description">
						<?php _e( 'This is displayed on each of your products.', 'wc-vendors' ); ?>
					</p>
				</td>
			</tr>

			<?php do_action( 'wcvendors_settings_after_seller_info' ); ?>

			<?php if ( $shop_description !== 'false' ) : ?>

				<tr>
					<th>
						<label for="pv_shop_description_unhtml">
							<?php _e( 'Shop Description', 'wc-vendors' ); ?>
						</label>
					</th>
					<td>
						<?php

						if ( $global_html || $has_html ) {
							$old_post        = $GLOBALS['post'];
							$GLOBALS['post'] = 0;
							wp_editor( $description, 'pv_shop_description' );
							$GLOBALS['post'] = $old_post;
						} else {
							?>
							<textarea class="large-text" rows="10" id="pv_shop_description_unhtml" style="width:95%"
							          name="pv_shop_description"><?php echo $description; ?></textarea>
							<?php
						}
						?>
						<p class="description">
							<?php printf( __( 'This is displayed on your <a href="%s">shop page</a>.', 'wc-vendors' ), $shop_page ); ?>
						</p>
					</td>
				</tr>

				<?php do_action( 'wcvendors_settings_after_shop_description' ); ?>

			<?php endif; ?>

			<tr>
				<td colspan="2">
					<?php wp_nonce_field( 'save-shop-settings-admin', 'wc-vendors-nonce' ); ?>
					<input type="submit" class="button button-primary" name="vendor_application_submit"
					       value="<?php _e( 'Save Shop Settings', 'wc-vendors' ); ?>">
				</td>
			</tr>
		</table>
	</form>
</div>
