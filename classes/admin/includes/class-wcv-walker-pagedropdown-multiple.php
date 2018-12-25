<?php
if ( ! class_exists( 'WCV_Walker_PageDropdown_Multiple' ) ) {

	/**
	 * Create HTML dropdown list of pages.
	 *
	 * @package WCVendors
	 * @since   2.0.8
	 * @uses    Walker_PageDropdown
	 * @source http://wordpress.kjetil-hartveit.com/2013/03/08/how-to-use-the-multiple-attribute-with-wp_dropdown_pages/
	 */
	class WCV_Walker_PageDropdown_Multiple extends Walker_PageDropdown {

		/**
		 * @see   Walker::start_el()
		 * @since 2.1.0
		 *
		 * @param string $output Passed by reference. Used to append additional content.
		 * @param object $page   Page data object.
		 * @param int    $depth  Depth of page in reference to parent pages. Used for padding.
		 * @param array  $args   Uses 'selected' argument for selected page to set selected HTML attribute for option element.
		 * @param int    $id
		 */
		public function start_el( &$output, $page, $depth = 0, $args = array(), $id = 0 ) {

			$pad = str_repeat( '&nbsp;', $depth * 3 );

			if ( ! isset( $args['value_field'] ) || ! isset( $page->{$args['value_field']} ) ) {
				$args['value_field'] = 'ID';
			}

			$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $page->{$args['value_field']} ) . '"';
			if ( in_array( $page->ID, (array) $args['selected'] ) ) {
				$output .= ' selected="selected"';
			}
			$output .= '>';

			$title = $page->post_title;
			if ( '' === $title ) {
				/* translators: %d: ID of a post */
				$title = sprintf( __( '#%d (no title)' ), $page->ID );
			}

			/**
			 * Filters the page title when creating an HTML drop-down list of pages.
			 *
			 * @since 3.1.0
			 *
			 * @param string $title Page title.
			 * @param object $page  Page data object.
			 */
			$title = apply_filters( 'list_pages', $title, $page );
			$title = apply_filters( 'pagedropdown_multiple_title', $title, $page, $args );

			$output .= $pad . esc_html( $title );
			$output .= "</option>\n";

		}

	}

}
