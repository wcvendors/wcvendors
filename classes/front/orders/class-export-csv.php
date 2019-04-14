<?php

class WCV_Export_CSV
{

	/**
	 * Sort the data for CSV output first
	 *
	 * @param int   $product_id
	 * @param array $headers
	 * @param array $body
	 * @param array $items
	 */


	public static function output_csv( $product_id, $headers, $body, $items )
	{

		$headers[ 'quantity' ] = __( 'Quantity', 'wcvendors' );
		$headers[ 'item_meta' ] = __( 'Item Meta', 'wcvendors' );

		$new_body = array(); 

		foreach ( $body as $i => $order ) {

			// Remove comments
			unset( $body[ $i ][ 'comments' ] );

			// Remove all numeric keys in each order (these are the meta values we are redoing into new lines)
			foreach ( $order as $key => $col ) {
	            if ( is_int( $key ) ) unset( $order[ $key ] );
	        }

	        // New order row 
	        $new_row = $body[ $i ]; 
	        // Remove order to redo
	        unset( $body[ $i ] );  

	        $order = new WC_Order( $i );

			foreach ( $items[ $i ][ 'items' ] as $item ) {

				$product_id = !empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id']; 

				$_product  = $order->get_product_from_item( $item );

				$new_row_with_meta = $new_row; 

				// Add the qty row 
				$new_row_with_meta[] = $item[ 'qty' ];
				// Add the new item meta row 
				
				$variation_detail = !empty( $item['variation_id'] ) ? WCV_Orders::get_variation_data( $item[ 'variation_id' ] ) : ''; 

				$new_row_with_meta[] = $variation_detail; 
				$new_row_with_meta['product'] =  $item[ 'name' ]; 
				$new_body[] = $new_row_with_meta; 
			}
		}		

		$headers = apply_filters( 'wcvendors_csv_headers', $headers, $product_id, $items );
		$body    = apply_filters( 'wcvendors_csv_body', $new_body, $product_id, $items );

		WCV_Export_CSV::download( $headers, $body, $product_id );
	}


	/**
	 * Send the CSV to the browser for download
	 *
	 * @param array  $headers
	 * @param array  $body
	 * @param string $filename
	 */
	public static function download( $headers, $body, $filename )
	{
		// Clear browser output before this point
		if (ob_get_contents()) ob_end_clean(); 

		// Output headers so that the file is downloaded rather than displayed
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=orders_for_' . $filename . '.csv' );

		// Create a file pointer connected to the output stream
		$output = fopen( 'php://output', 'w' );

		// Output the column headings
		fputcsv( $output, $headers );

		// Body
		foreach ( $body as $data )
			fputcsv( $output, $data );

		die();
	}


}
