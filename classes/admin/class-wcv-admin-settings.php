<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin settings class handles all settings in the admin area. This is a modified version of the WooCommerce Admin Settings class
 *
 * @author      WooCommerce, Jamie Madden, WC Vendors
 * @category    Admin
 * @package     WCVendors/Admin
 * @version     2.0.0
 */

class WCVendors_Admin_Settings extends WC_Admin_Settings {

	/**
	 * Setting pages.
	 *
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	private static $errors = array();

	/**
	 * Update messages.
	 *
	 * @var array
	 */
	private static $messages = array();

	/**
	 * Include the settings page classes.
	 */
	public static function get_settings_pages() {

		if ( empty( self::$settings ) ) {
			$settings = array();

			// Include the setings page.
			include_once WCV_ABSPATH_ADMIN . 'settings/class-wcv-settings-page.php';

			$settings[] = include WCV_ABSPATH_ADMIN . 'settings/class-wcv-settings-general.php';
			$settings[] = include WCV_ABSPATH_ADMIN . 'settings/class-wcv-settings-commission.php';
			$settings[] = include WCV_ABSPATH_ADMIN . 'settings/class-wcv-settings-capabilities.php';
			$settings[] = include WCV_ABSPATH_ADMIN . 'settings/class-wcv-settings-display.php';
			$settings[] = include WCV_ABSPATH_ADMIN . 'settings/class-wcv-settings-payments.php';
			$settings[] = include WCV_ABSPATH_ADMIN . 'settings/class-wcv-settings-advanced.php';

			self::$settings = apply_filters( 'wcvendors_get_settings_pages', $settings );
		}

		return self::$settings;
	}

	/**
	 * Save the settings.
	 */
	public static function save() {
		global $current_tab;

		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'wcvendors-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'wc-vendors' ) );
		}

		// Trigger actions
		do_action( 'wcvendors_settings_save_' . $current_tab );
		do_action( 'wcvendors_update_options_' . $current_tab );
		do_action( 'wcvendors_update_options' );

		self::add_message( __( 'Your settings have been saved.', 'wc-vendors' ) );

		update_option( 'wcvendors_queue_flush_rewrite_rules', 'yes' );
		do_action( 'wcvendors_settings_saved' );
	}

	/**
	 * Settings page.
	 *
	 * Handles the display of the main wcvendors settings page in admin.
	 */
	public static function output() {
		global $current_section, $current_tab;

		// $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$suffix = '';

		do_action( 'wcvendors_settings_start' );
		wp_enqueue_script( 'wcvendors_settings', wcv_assets_url . '/js/admin/settings' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-sortable', 'iris', 'selectWoo' ), WCV_VERSION, true );
		wp_localize_script(
			'wcvendors_settings', 'wcvendors_settings_params', array(
				'i18n_nav_warning' => __( 'The changes you made will be lost if you navigate away from this page.', 'wc-vendors' ),
			)
		);

		wp_enqueue_script( 'wcvendors-media', wcv_assets_url . 'js/admin/wcvendors-media.js', array( 'jquery' ), WCV_VERSION, true );

		// Get tabs for the settings page
		$tabs = apply_filters( 'wcvendors_settings_tabs_array', array() );

		include WCV_ABSPATH_ADMIN . 'views/html-admin-settings.php';
	}

	/**
	 * Output admin fields.
	 *
	 * Loops though the wcvendors options array and outputs each field.
	 *
	 * @param array[] $options Opens array to output
	 */
	public static function output_fields( $options ) {

		include WCV_ABSPATH_ADMIN . 'includes/class-wcv-walker-pagedropdown-multiple.php';

		foreach ( $options as $value ) {
			if ( ! isset( $value['type'] ) ) {
				continue;
			}
			if ( ! isset( $value['id'] ) ) {
				$value['id'] = '';
			}
			if ( ! isset( $value['title'] ) ) {
				$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
			}
			if ( ! isset( $value['class'] ) ) {
				$value['class'] = '';
			}
			if ( ! isset( $value['css'] ) ) {
				$value['css'] = '';
			}
			if ( ! isset( $value['default'] ) ) {
				$value['default'] = '';
			}
			if ( ! isset( $value['desc'] ) ) {
				$value['desc'] = '';
			}
			if ( ! isset( $value['desc_tip'] ) ) {
				$value['desc_tip'] = false;
			}
			if ( ! isset( $value['placeholder'] ) ) {
				$value['placeholder'] = '';
			}
			if ( ! isset( $value['suffix'] ) ) {
				$value['suffix'] = '';
			}

			// Custom attribute handling
			$custom_attributes = array();

			if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
				foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
					$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
				}
			}

			// Description handling
			$field_description = self::get_field_description( $value );
			extract( $field_description );

			// Switch based on type
			switch ( $value['type'] ) {

				// Section Titles
				case 'title':
					if ( ! empty( $value['title'] ) ) {
						echo '<h2>' . esc_html( $value['title'] ) . '</h2>';
					}
					if ( ! empty( $value['desc'] ) ) {
						echo wpautop( wptexturize( wp_kses_post( $value['desc'] ) ) );
					}
					echo '<table class="form-table">' . "\n\n";
					if ( ! empty( $value['id'] ) ) {
						do_action( 'wcvendors_settings_' . sanitize_title( $value['id'] ) );
					}
					break;

				// Section Ends
				case 'sectionend':
					if ( ! empty( $value['id'] ) ) {
						do_action( 'wcvendors_settings_' . sanitize_title( $value['id'] ) . '_end' );
					}
					echo '</table>';
					if ( ! empty( $value['id'] ) ) {
						do_action( 'wcvendors_settings_' . sanitize_title( $value['id'] ) . '_after' );
					}
					break;

				// Standard text inputs and subtypes like 'number'
				case 'text':
				case 'email':
				case 'number':
				case 'password':
					$option_value = self::get_option( $value['id'], $value['default'] );

					?><tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
							<?php do_action( 'wcvendors_after_standard_text_input_label', $value ); ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $value['type'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								/><?php echo esc_html( $value['suffix'] ); ?> <?php echo $description; ?>
							<?php do_action( 'wcvendors_after_standard_text_input_field', $value ); ?>
						</td>
					</tr>
					<?php
					break;

				// Color picker.
				case 'color':
					$option_value = self::get_option( $value['id'], $value['default'] );

					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php echo $tooltip_html; ?>
							<?php do_action( 'wcvendors_after_color_label', $value ); ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">&lrm;
							<span class="colorpickpreview" style="background: <?php echo esc_attr( $option_value ); ?>"></span>
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="text"
								dir="ltr"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								value="<?php echo esc_attr( $option_value ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?> colorpick"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								/>&lrm; <?php echo $description; ?>
								<?php do_action( 'wcvendors_aftor_color_picker', $value ); ?>
								<div id="colorPickerDiv_<?php echo esc_attr( $value['id'] ); ?>" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>
						</td>
					</tr>
					<?php
					break;

				// Textarea
				case 'textarea':
					$option_value = self::get_option( $value['id'], $value['default'] );

					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php do_action( 'wcvendors_after_text_area_label', $value ); ?>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
							<textarea
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								><?php echo esc_textarea( $option_value ); ?></textarea>
								<?php do_action( 'wcvendors_after_textarea', $value ); ?>
							<?php echo $description; ?>
						</td>
					</tr>
					<?php
					break;

				// Select boxes
				case 'select':
				case 'multiselect':
					$option_value = self::get_option( $value['id'], $value['default'] );

					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php do_action( 'wcvendors_after_select_label', $value ); ?>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
							<select
								name="<?php echo esc_attr( $value['id'] ); ?><?php echo ( 'multiselect' === $value['type'] ) ? '[]' : ''; ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								style="<?php echo esc_attr( $value['css'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo implode( ' ', $custom_attributes ); ?>
								<?php echo ( 'multiselect' == $value['type'] ) ? 'multiple="multiple"' : ''; ?>
								>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
										<option value="<?php echo esc_attr( $key ); ?>" 
																  <?php

																	if ( is_array( $option_value ) ) {
																		selected( in_array( $key, $option_value ), true );
																	} else {
																		selected( $option_value, $key );
																	}

																	?>
										><?php echo $val; ?></option>
										<?php
								}
								?>
							</select>
							<?php do_action( 'wcvendors_after_select', $value ); ?>
							<?php echo $description; ?>
						</td>
					</tr>
					<?php
					break;

				// Radio inputs
				case 'radio':
					$option_value = self::get_option( $value['id'], $value['default'] );

					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php do_action( 'wcvendors_after_radio_button_label', $value ); ?>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ); ?>">
							<fieldset>
								<?php echo $description; ?>
								<ul>
								<?php
								foreach ( $value['options'] as $key => $val ) {
									?>
										<li>
											<label><input
												name="<?php echo esc_attr( $value['id'] ); ?>"
												value="<?php echo $key; ?>"
												type="radio"
												style="<?php echo esc_attr( $value['css'] ); ?>"
												class="<?php echo esc_attr( $value['class'] ); ?>"
											<?php echo implode( ' ', $custom_attributes ); ?>
											<?php checked( $key, $option_value ); ?>
												/> <?php echo $val; ?></label>
												<?php do_action( 'wcvendors_after_radio_input', $value ); ?>
										</li>
										<?php
								}
								?>
								</ul>
							</fieldset>
							<?php do_action( 'wcvendors_after_radio_field_set', $value ); ?>
						</td>
					</tr>
					<?php
					break;

				// Checkbox input
				case 'checkbox':
					$option_value     = self::get_option( $value['id'], $value['default'] );
					$visibility_class = array();

					if ( ! isset( $value['hide_if_checked'] ) ) {
						$value['hide_if_checked'] = false;
					}
					if ( ! isset( $value['show_if_checked'] ) ) {
						$value['show_if_checked'] = false;
					}
					if ( 'yes' == $value['hide_if_checked'] || 'yes' == $value['show_if_checked'] ) {
						$visibility_class[] = 'hidden_option';
					}
					if ( 'option' == $value['hide_if_checked'] ) {
						$visibility_class[] = 'hide_options_if_checked';
					}
					if ( 'option' == $value['show_if_checked'] ) {
						$visibility_class[] = 'show_options_if_checked';
					}

					if ( ! isset( $value['checkboxgroup'] ) || 'start' == $value['checkboxgroup'] ) {
						?>
							<tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
								<th scope="row" class="titledesc">
									<?php echo esc_html( $value['title'] ); ?>
									<?php do_action( 'wcvendors_after_checkbox_label', $value ); ?>
								</th>
								<td class="forminp forminp-checkbox">
									<fieldset>
						<?php
					} else {
						?>
							<fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
						<?php
					}

					if ( ! empty( $value['title'] ) ) {
						?>
							<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>
						<?php
					}

					?>
						<label for="<?php echo $value['id']; ?>">
							<input
								name="<?php echo esc_attr( $value['id'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
								value="1"
								<?php checked( $option_value, 'yes' ); ?>
								<?php echo implode( ' ', $custom_attributes ); ?>
							/>
							<?php do_action( 'wcvendors_after_checkbox_input', $value ); ?>
							<?php echo $description; ?>
						</label> <?php echo $tooltip_html; ?>
					<?php

					if ( ! isset( $value['checkboxgroup'] ) || 'end' == $value['checkboxgroup'] ) {
						?>
									</fieldset>
								</td>
							</tr>
						<?php
					} else {
						?>
							</fieldset>
						<?php
					}
					do_action( 'wcvendors_after_checkbox_field_set', $value );
					break;

				// Single page selects
				case 'single_select_page':
					$args = array(
						'name'             => $value['id'],
						'id'               => $value['id'],
						'sort_column'      => 'menu_order',
						'sort_order'       => 'ASC',
						'show_option_none' => ' ',
						'class'            => $value['class'],
						'echo'             => false,
						'selected'         => absint( self::get_option( $value['id'] ) ),
					);

					if ( isset( $value['args'] ) ) {
						$args = wp_parse_args( $value['args'], $args );
					}

					?>
					<tr valign="top" class="single_select_page">
						<th scope="row" class="titledesc">
							<?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; ?>
							<?php do_action( 'wcvendors_after_single_select_page_label', $value ); ?>
						</th>
						<td class="forminp">
							<?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'wc-vendors' ) . "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) ); ?> <?php echo $description; ?>
							<?php do_action( 'wcvendors_after_single_select_page', $value ); ?>
						</td>
					</tr>
					<?php
					break;

				// Multi page selects
				case 'multi_select_page':
					$args = array(
						'name'             => $value['id'] . '[]',
						'walker'           => new WCV_Walker_PageDropdown_Multiple(),
						'id'               => $value['id'],
						'sort_column'      => 'menu_order',
						'sort_order'       => 'ASC',
						'show_option_none' => ' ',
						'class'            => $value['class'],
						'echo'             => false,
						'selected'         => self::get_option( $value['id'] ),
					);

					if ( isset( $value['args'] ) ) {
						$args = wp_parse_args( $value['args'], $args );
					}

					?>
					<tr valign="top" class="single_select_page">
						<th scope="row" class="titledesc">
							<?php echo esc_html( $value['title'] ); ?> <?php echo $tooltip_html; ?>
							<?php do_action( 'wcvendors_after_multi_select_page_label', $value ); ?>
						</th>
						<td class="forminp">
							<?php echo str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'wc-vendors' ) . "' style='" . $value['css'] . "' class='" . $value['class'] . "' multiple=\"multiple\" id=", wp_dropdown_pages( $args ) ); ?> <?php echo $description; ?>
							<?php do_action( 'wcvendors_after_multi_select_page', $value ); ?>
						</td>
					</tr>
					<?php
					break;

				// Single country selects
				case 'single_select_country':
					$country_setting = (string) self::get_option( $value['id'] );

					if ( strstr( $country_setting, ':' ) ) {
						$country_setting = explode( ':', $country_setting );
						$country         = current( $country_setting );
						$state           = end( $country_setting );
					} else {
						$country = $country_setting;
						$state   = '*';
					}
					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php do_action( 'wcvendors_after_single_country_select', $value ); ?>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp"><select name="<?php echo esc_attr( $value['id'] ); ?>" style="<?php echo esc_attr( $value['css'] ); ?>" data-placeholder="<?php esc_attr_e( 'Choose a country&hellip;', 'wc-vendors' ); ?>" aria-label="<?php esc_attr_e( 'Country', 'wc-vendors' ); ?>" class="wc-enhanced-select">
							<?php WC()->countries->country_dropdown_options( $country, $state ); ?>
						</select>
						<?php do_action( 'wcvendors_after_single_country_select', $value ); ?>
						<?php echo $description; ?>
						</td>
					</tr>
					<?php
					break;

				// Country multiselects
				case 'multi_select_countries':
					$selections = (array) self::get_option( $value['id'] );

					if ( ! empty( $value['options'] ) ) {
						$countries = $value['options'];
					} else {
						$countries = WC()->countries->countries;
					}

					asort( $countries );
					?>
					<tr valign="top">
						<th scope="row" class="titledesc">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
							<?php do_action( 'wcvendors_after_multi_country_select_label', $value ); ?>
							<?php echo $tooltip_html; ?>
						</th>
						<td class="forminp">
							<select multiple="multiple" name="<?php echo esc_attr( $value['id'] ); ?>[]" style="width:350px" data-placeholder="<?php esc_attr_e( 'Choose countries&hellip;', 'wc-vendors' ); ?>" aria-label="<?php esc_attr_e( 'Country', 'wc-vendors' ); ?>" class="wc-enhanced-select">
								<?php
								if ( ! empty( $countries ) ) {
									foreach ( $countries as $key => $val ) {
										echo '<option value="' . esc_attr( $key ) . '" ' . selected( in_array( $key, $selections ), true, false ) . '>' . $val . '</option>';
									}
								}
								?>
							</select>
							<?php echo ( $description ) ? $description : ''; ?> <br /><a class="select_all button" href="#"><?php _e( 'Select all', 'wc-vendors' ); ?></a> <a class="select_none button" href="#"><?php _e( 'Select none', 'wc-vendors' ); ?></a>
							<?php do_action( 'wcvendors_after_multi_country_select', $value ); ?>
						</td>
					</tr>
					<?php
					break;

				case 'image':
					$option_value = self::get_option( $value['id'], $value['default'] );

					?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
								<?php do_action( 'wcvendors_after_image_label', $value ); ?>
							<?php echo $tooltip_html; ?>
							</th>
							<td class="forminp">
								<img class="wcv-image-container-<?php echo $value['id']; ?>" src="<?php echo $option_value; ?>" alt="" style="max-width:100%;" />
								<br />
								<input id="wcv-add-<?php echo $value['id']; ?>" type="button" class="<?php echo $value['css']; ?>" value="<?php echo sprintf( __( 'Update %s', 'wc-vendors' ), strtolower( $value['title'] ) ); ?>" data-id="<?php echo $value['id']; ?>" data-save_button="<?php echo sprintf( __( 'Add %s', 'wc-vendors' ), $value['title'] ); ?>" data-window_title="<?php echo sprintf( __( 'Add %s', 'wc-vendors' ), strtolower( $value['title'] ) ); ?>" data-upload_notice="<?php echo sprintf( __( 'Upload an image for the %s', 'wc-vendors' ), strtolower( $value['title'] ) ); ?>" />
								<?php do_action( 'wcvendors_image_buttons', $value ); ?>
								<input type="hidden" name="<?php echo $value['id']; ?>" id="<?php echo $value['id']; ?>" value="<?php echo $option_value; ?>">
								<?php do_action( 'wcvendors_after_image_input', $value ); ?>
							</td>
						</tr>
						<?php

					break;

				case 'wysiwyg':
					$option_value = self::get_option( $value['id'], $value['default'] );

					?>
						<tr valign="top">
							<th scope="row" class="titledesc">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
								<?php do_action( 'wcvendors_after_wysiwyg_label', $value ); ?>
							<?php echo $tooltip_html; ?>
							</th>
							<td class="forminp">
							<?php wp_editor( $option_value, $value['id'], array( 'textarea_name' => $value['id'] ) ); ?>
							<?php do_action( 'wcvendors_after_wysiwyg', $value ); ?>
							<?php echo $description; ?>
							</td>
						</tr>
					<?php

					break;

				// Default: run an action
				default:
					do_action( 'wcvendors_admin_field_' . $value['type'], $value );
					break;
			}
		}
	}

	/**
	 * Save admin fields.
	 *
	 * Loops though the options array and saves each field.
	 *
	 * @param array $options Options array to output
	 * @param array $data Optional. Data to use for saving. Defaults to $_POST.
	 *
	 * @return bool
	 */
	public static function save_fields( $options, $data = null ) {
		if ( is_null( $data ) ) {
			$data = $_POST;
		}
		if ( empty( $data ) ) {
			return false;
		}

		// Options to update will be stored here and saved later.
		$update_options = array();

		// Loop options and get values to save.
		foreach ( $options as $option ) {
			if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) ) {
				continue;
			}

			// Get posted value.
			if ( strstr( $option['id'], '[' ) ) {
				parse_str( $option['id'], $option_name_array );
				$option_name  = current( array_keys( $option_name_array ) );
				$setting_name = key( $option_name_array[ $option_name ] );
				$raw_value    = isset( $data[ $option_name ][ $setting_name ] ) ? wp_unslash( $data[ $option_name ][ $setting_name ] ) : null;
			} else {
				$option_name  = $option['id'];
				$setting_name = '';
				$raw_value    = isset( $data[ $option['id'] ] ) ? wp_unslash( $data[ $option['id'] ] ) : null;
			}

			// Format the value based on option type.
			switch ( $option['type'] ) {
				case 'checkbox':
					$value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
					break;
				case 'textarea':
					$value = wp_kses_post( trim( $raw_value ) );
					break;
				case 'multiselect':
				case 'multi_select_countries':
					$value = array_filter( array_map( 'wc_clean', (array) $raw_value ) );
					break;
				case 'image_width':
					$value = array();
					if ( isset( $raw_value['width'] ) ) {
						$value['width']  = wc_clean( $raw_value['width'] );
						$value['height'] = wc_clean( $raw_value['height'] );
						$value['crop']   = isset( $raw_value['crop'] ) ? 1 : 0;
					} else {
						$value['width']  = $option['default']['width'];
						$value['height'] = $option['default']['height'];
						$value['crop']   = $option['default']['crop'];
					}
					break;
				case 'thumbnail_cropping':
					$value = wc_clean( $raw_value );

					if ( 'custom' === $value ) {
						$width_ratio  = wc_clean( wp_unslash( $_POST['thumbnail_cropping_aspect_ratio_width'] ) );
						$height_ratio = wc_clean( wp_unslash( $_POST['thumbnail_cropping_aspect_ratio_height'] ) );
						$value        = $width_ratio . ':' . $height_ratio;
					}
					break;
				case 'select':
					$allowed_values = empty( $option['options'] ) ? array() : array_keys( $option['options'] );
					if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
						$value = null;
						break;
					}
					$default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
					$value   = in_array( $raw_value, $allowed_values ) ? $raw_value : $default;
					break;
				case 'wysiwyg':
					$value = $raw_value;
					break;
				default:
					$value = wc_clean( $raw_value );
					break;
			}

			/**
			 * Sanitize the value of an option.
			 *
			 * @since 2.4.0
			 */
			$value = apply_filters( 'wcvendors_admin_settings_sanitize_option', $value, $option, $raw_value );

			/**
			 * Sanitize the value of an option by option name.
			 *
			 * @since 2.4.0
			 */
			$value = apply_filters( "wcvendors_admin_settings_sanitize_option_$option_name", $value, $option, $raw_value );

			if ( is_null( $value ) ) {
				continue;
			}

			// Check if option is an array and handle that differently to single values.
			if ( $option_name && $setting_name ) {
				if ( ! isset( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = get_option( $option_name, array() );
				}
				if ( ! is_array( $update_options[ $option_name ] ) ) {
					$update_options[ $option_name ] = array();
				}
				$update_options[ $option_name ][ $setting_name ] = $value;
			} else {
				$update_options[ $option_name ] = $value;
			}

			/**
			 * Fire an action before saved.
			 *
			 * @deprecated 2.4.0 - doesn't allow manipulation of values!
			 */
			do_action( 'wcvendors_update_option', $option );
		}

		// Save all options in our array.
		foreach ( $update_options as $name => $value ) {
			update_option( $name, $value );
		}

		return true;
	}

}
