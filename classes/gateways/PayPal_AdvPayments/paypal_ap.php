<?php

if ( !class_exists( 'WC_Payment_Gateway' ) ) return false;

/**
 * Add the gateway to WooCommerce
 *
 * @access public
 *
 * @param array $methods
 *
 * @return array
 */


function add_paypal_ap_gateway( $methods ){

	$settings = get_option( 'woocommerce_paypalap_settings', false );
	if ( $settings && array_key_exists('username_live', $settings ) && $settings[ 'username_live' ] !== '' ) {
		$methods[ ] = 'WC_PaypalAP';
	}

	return $methods;
}



add_filter( 'woocommerce_payment_gateways', 'add_paypal_ap_gateway' );

class WC_PaypalAP extends WC_Payment_Gateway
{

	public static $pluginDir;

	/**
	 *
	 */
	public function __construct()
	{
		global $woocommerce;

		self::$pluginDir = trailingslashit( dirname( __FILE__ ) ) . 'PayPal_AP/';

		/* Standard WooCommerce Configuration */
		$this->id           = 'paypalap';
		$this->icon         = plugin_dir_url( __FILE__ ) . 'PayPal_AP/assets/icons/paypalap.png';
		$this->method_title = __( 'PayPal Adaptive Payments - deprecated do not use', 'wc-vendors' );
		$this->has_fields   = false;

		// Load the settings
		$this->init_form_fields();
		$this->init_settings();

		$this->enabled     = $this->settings[ 'enabled' ];
		$this->title       = $this->settings[ 'title' ];
		$this->description = $this->settings[ 'description' ];

		$this->currency = get_option( 'woocommerce_currency' );

		/* PayPal Adaptive Payments Configuration. */
		$this->sandbox     = $this->settings[ 'sandbox_enabled' ];
		$this->main_paypal = $this->sandbox == 'yes' ? $this->settings[ 'main_paypal' ] : $this->settings[ 'main_paypal_live' ];
		$this->instapay    = wc_string_to_bool( get_option( 'wcvendors_payments_paypal_instantpay_enable', 'no' ) );
		$this->give_tax    = wc_string_to_bool( get_option( 'wcvendors_vendor_give_taxes', 'no' ) );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Payment listener/API hook
		add_action( 'woocommerce_api_wc_paypalap', array( $this, 'paypal_ap_ipn' ) );

		$this->debug_me = false;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function is_available()
	{
		return ( $this->is_valid_currency() && $this->enabled == 'yes' );
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function is_valid_currency()
	{
		return in_array( get_woocommerce_currency(), array( 'AUD', 'BRL', 'CAD', 'MXN', 'NZD', 'HKD', 'SGD', 'USD', 'EUR', 'JPY', 'TRY', 'NOK', 'CZK', 'DKK', 'HUF', 'ILS', 'MYR', 'PHP', 'PLN', 'SEK', 'CHF', 'TWD', 'THB', 'GBP', 'RMB' ) );
	}


	/**
	 *
	 */
	public function include_paypal_sdk()
	{
		$path = self::$pluginDir . 'classes/adaptivepayments-sdk/lib';
		set_include_path( get_include_path() . PATH_SEPARATOR . $path );
		require_once 'services/AdaptivePayments/AdaptivePaymentsService.php';
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function paypal_ap_ipn()
	{
		if ( empty( $_GET[ 'paypal_chain_ipn' ] ) || empty( $_GET[ 'order_id' ] ) ) return false;

		$order_id = (int) $_GET[ 'order_id' ];

		$order = wc_get_order( $order_id );
		if ( !$order ) return false;

		if ( ! in_array( $_POST['status'], array( 'COMPLETED', 'Completed' ), true ) ) {
			$order->update_status( 'failed', sprintf( __( 'Something went wrong. Response from PayPal invalidated this order. Status: %s.', 'wc-vendors' ), $_POST[ 'status' ] ) );
			exit;
		}

		$order->payment_complete();
		$order->add_order_note( __( 'IPN payment completed', 'wc-vendors' ) );

		if ( $this->instapay ) {
			WCV_Commission::set_order_commission_paid( $order_id );
		}
		exit;
	}


	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	function init_form_fields()
	{

		$this->form_fields = array();

		// List each option in order of appearance with details
		$this->form_fields[ 'enabled' ] = array(
			'title'   => __( 'Enable PayPal Adaptive Payments', 'wc-vendors' ),
			'default' => 'no',
			'type'    => 'checkbox',
		);

		$this->form_fields[ 'title' ] = array(
			'title'       => __( 'Method Title', 'wc-vendors' ),
			'description' => __( 'This controls the title which the user sees during checkout.', 'wc-vendors' ),
			'default'     => __( 'PayPal', 'wc-vendors' ),
			'type'        => 'text'
		);

		$this->form_fields[ 'description' ] = array(
			'title'       => __( 'Description', 'wc-vendors' ),
			'description' => __( 'This controls the description which the user sees during checkout.', 'wc-vendors' ),
			'default'     => __( "Pay via PayPal!", 'wc-vendors' ),
			'type'        => 'textarea'
		);

		// ==================================================================
		//
		// Credentials
		//
		// ------------------------------------------------------------------

		$this->form_fields[ ] = array(
			'title'       => __( 'Live Credentials', 'wc-vendors' ),
			'type'        => 'title',
			'description' => sprintf( __( 'You must have an <a href="%s">Application ID</a> to process live transactions. You do not need one for testing in Sandbox mode.', 'wc-vendors' )
				, 'https://developer.paypal.com/webapps/developer/docs/classic/lifecycle/goingLive/' )
		);

		$this->form_fields[ 'main_paypal_live' ] = array(
			'title'       => __( 'PayPal Email', 'wc-vendors' ),
			'description' => __( 'The email address main payments should go to.', 'wc-vendors' ),
			'type'        => 'text'
		);

		$this->form_fields[ 'username_live' ] = array(
			'title' => __( 'API Username', 'wc-vendors' ),
			'type'  => 'text'
		);

		$this->form_fields[ 'password_live' ] = array(
			'title' => __( 'API Password', 'wc-vendors' ),
			'type'  => 'text'
		);

		$this->form_fields[ 'signature_live' ] = array(
			'title' => __( 'API Signature', 'wc-vendors' ),
			'type'  => 'text'
		);

		$this->form_fields[ 'app_id' ] = array(
			'title'       => __( 'Application ID', 'wc-vendors' ),
			'type'        => 'text',
			'description' => __( 'Only required when doing live transactions.', 'wc-vendors' ),
		);

		$this->form_fields[ ] = array(
			'title'       => __( 'Sandbox Credentials', 'wc-vendors' ),
			'type'        => 'title',
			'description' => sprintf( __( 'You can signup for a sandbox developer account <a href="%s">here</a>. You need a developer account if you want to enable Sandbox mode for testing.', 'wc-vendors' )
				, 'https://developer.paypal.com/' )
		);

		$this->form_fields[ 'main_paypal' ] = array(
			'title'       => __( 'PayPal Email', 'wc-vendors' ),
			'description' => __( 'The email address main payments should go to.', 'wc-vendors' ),
			'type'        => 'text'
		);

		$this->form_fields[ 'username' ] = array(
			'title' => __( 'API Username', 'wc-vendors' ),
			'type'  => 'text'
		);

		$this->form_fields[ 'password' ] = array(
			'title' => __( 'API Password', 'wc-vendors' ),
			'type'  => 'text'
		);

		$this->form_fields[ 'signature' ] = array(
			'title' => __( 'API Signature', 'wc-vendors' ),
			'type'  => 'text'
		);

		// ==================================================================
		//
		// Misc. Settings
		//
		// ------------------------------------------------------------------

		$this->form_fields[ ] = array(
			'title' => __( 'Misc. Settings', 'wc-vendors' ),
			'type'  => 'title',
		);

		$this->form_fields[ 'sandbox_enabled' ] = array(
			'title'   => __( 'Enable PayPal Sandbox mode', 'wc-vendors' ),
			'default' => 'yes',
			'type'    => 'checkbox',
		);

		$this->form_fields[ 'logging_enabled' ] = array(
			'title'   => __( 'Enable logging', 'wc-vendors' ),
			'default' => 'no',
			'type'    => 'checkbox',
		);

	}


	/**
	 * Admin Panel Options
	 *
	 * @access public
	 * @return void
	 */
	function admin_options()
	{
		?>
		<h3><?php echo $this->method_title; ?></h3>
		<p><?php _e( 'The PayPal Adaptive Payments gateway can instantly pay your vendors their due commission (if enabled). Also used to mass pay vendors on a schedule / manual method (if enabled).', 'wc-vendors' ); ?></p>
		<p><strong><?php _e( 'Please Note: PayPal has deprecated Adaptive Payments. This will soon cease to function.', 'wc-vendors' ); ?></strong></p>
		<table class="form-table">

			<?php if ( $this->is_valid_currency() ) :

				// Generate the HTML For the settings form.
				$this->generate_settings_html();

			else : ?>

				<div class="inline error"><p>
						<strong><?php _e( 'Gateway Disabled', 'wc-vendors' ); ?></strong>: <?php printf( __( '%s does not support your store currency.', 'wc-vendors' ), $this->method_title ); ?>
					</p></div>

			<?php endif; ?>

		</table>
	<?php
	}


	/**
	 *
	 *
	 * @param unknown $order
	 *
	 * @return unknown
	 */
	private function get_receivers( $order )
	{
		$response = array();

		// Process the payment and split as required
		if ( $this->instapay ) {

			$receivers = WCV_Vendors::get_vendor_dues_from_order( $order );
			$i        = 0;

			$order_id 					= $order->get_id();

			foreach ( $receivers as $author => $values ) {
				if ( empty( $values[ 'total' ] ) ) continue;

				$response[ $i ]            = new Receiver();
				$response[ $i ]->email     = $values[ 'vendor_id' ] == 1 ? $this->main_paypal : WCV_Vendors::get_vendor_paypal( $values[ 'vendor_id' ] );
				$response[ $i ]->amount    = round( $values[ 'total' ], 2);
				$response[ $i ]->primary   = false;
				$response[ $i ]->invoiceId = $order_id;
				$i++;
			}

		} else {
			// Send all monies to the site admin
			$single_receiver            = new Receiver();
			$single_receiver->email     = $this->main_paypal;
			$single_receiver->amount    = $order->get_total();
			$single_receiver->primary   = false;
			$single_receiver->invoiceId = $order_id;
			// Set a single reciever for the transaction
			$response[] = $single_receiver;
		}

		if ( $this->debug_me ) {
			var_dump( $response );
		}

		return $response;
	}


	/**
	 *
	 *
	 * @param unknown $order_id
	 *
	 * @return unknown
	 */
	public function paypalap_check_form( $order_id )
	{
		global $woocommerce;

		$this->include_paypal_sdk();
		$this->logger 	= new PPLoggingManager( 'Pay' );
		$order        	= wc_get_order( $order_id );
		$order_id 	  	= $order->get_id();
		$order_key 	  	= $order->get_order_key();
		$customer_note 	= $order->get_customer_note();

		$receivers    = $this->get_receivers( $order );
		$receiverList = new ReceiverList( $receivers );

		$actionType   = 'CREATE';
		$cancelUrl    = $order->get_cancel_order_url_raw();
		$currencyCode = get_woocommerce_currency();
		$returnUrl    = esc_url_raw( add_query_arg( 'key', $order_key, add_query_arg( 'order-received', $order_id, $order->get_checkout_order_received_url() ) ) );

		$payRequest = new PayRequest( new RequestEnvelope( "en_US" ), $actionType, $cancelUrl, $currencyCode, $receiverList, $returnUrl );

		// ==================================================================
		//
		// Optional params
		//
		// ------------------------------------------------------------------

		$payRequest->feesPayer = 'EACHRECEIVER';

		$args = array(
			'wc-api'           	=> 'WC_PayPalAP',
			'paypal_chain_ipn'	=> '1',
			'order_id'         	=> $order_id,
		);

		$payRequest->ipnNotificationUrl                = add_query_arg( $args, home_url( '/' ) );
		$payRequest->memo                              = !empty( $customer_note ) ? $customer_note : '';
		$payRequest->reverseAllParallelPaymentsOnError = true;

		$service = new AdaptivePaymentsService();
		try {
			$response = $service->Pay( $payRequest );
		} catch ( Exception $ex ) {
			wc_add_notice( sprintf( __( 'Error: %s', 'wc-vendors' ), $ex->getMessage() ), 'error' );

			return false;
		}

		$this->logger->log( "Received payResponse:" );
		$ack = strtoupper( $response->responseEnvelope->ack );

		if ( $ack != 'SUCCESS' ) {
			$order->update_status( 'cancelled', sprintf( __( 'Error ID: %s. %s', 'wc-vendors' ), $response->error[ 0 ]->errorId, $response->error[ 0 ]->message ) );
			wc_add_notice( sprintf( __( 'Error ID: %s. %s', 'wc-vendors' ), $response->error[ 0 ]->errorId, $response->error[ 0 ]->message ), 'error' );

			return false;
		}

		return $response->payKey;
	}


	/**
	 *
	 *
	 * @param unknown $order
	 * @param unknown $author_email
	 * @param unknown $setPaymentOptionsRequest
	 * @param unknown $is_admin (optional)
	 *
	 * @return unknown
	 */
	public function set_vendor_items( $order, $setPaymentOptionsRequest )
	{
		$receivers = WCV_Vendors::get_vendor_dues_from_order( $order, false );
		$receivers_two = WCV_Vendors::get_vendor_dues_from_order( $order );

		foreach ( $receivers as $products ) {
			$invoice_items  = array();
			$shipping_given = $tax_given = 0;

			foreach ( $products as $key => $product ) {

				$product_id = $product[ 'product_id' ];
				$shipping_given += $product[ 'shipping' ];
				$tax_given += $product[ 'tax' ];

				$product[ 'commission' ] = round( $product[ 'commission' ] , 2);

				if ( !empty( $product[ 'commission' ] ) ) {
					$item             = new InvoiceItem();
					$item->name       = get_the_title( $product_id );
					$item->identifier = $product_id;
					$item->price      = $product[ 'commission' ];
					$item->itemPrice  = round( $product[ 'commission' ] / $product[ 'qty' ], 2 );
					$item->itemCount  = $product[ 'qty' ];
					$invoice_items[ ] = $item;
				}
			}

			if ( empty( $invoice_items ) ) {
				continue;
			}

			$receiverOptions                              = new ReceiverOptions();
			$setPaymentOptionsRequest->receiverOptions[ ] = $receiverOptions;

			// Set the current vendor
			$receiverId = new ReceiverIdentifier();
			$receiverId->email = $product[ 'vendor_id' ] == 1 ? $this->main_paypal : WCV_Vendors::get_vendor_paypal( $product[ 'vendor_id' ] );

			$receiverOptions->receiver                   = $receiverId;
			$receiverOptions->invoiceData                = new InvoiceData();
			$receiverOptions->invoiceData->item          = $invoice_items;
			$receiverOptions->invoiceData->totalTax      = number_format( $receivers_two[$product['vendor_id']]['tax'], 2 );
			$receiverOptions->invoiceData->totalShipping = number_format( $receivers_two[$product['vendor_id']]['shipping'], 2 );
		}

		return $setPaymentOptionsRequest;
	}


	/**
	 *
	 *
	 * @param unknown $pay_key
	 * @param unknown $order_id
	 *
	 * @return unknown
	 */
	public function set_paypal_author_specifics( $pay_key, $order_id )
	{
		global $woocommerce;

		$this->include_paypal_sdk();
		$order = wc_get_order( $order_id );

		// Create request
		$setPaymentOptionsRequest         = new SetPaymentOptionsRequest( new RequestEnvelope( "en_US" ) );
		$setPaymentOptionsRequest->payKey = $pay_key;

		if ( $this->instapay ) {
			$setPaymentOptionsRequest = $this->set_vendor_items( $order, $setPaymentOptionsRequest );
		}

		if ( $this->debug_me ) {
			foreach ( $setPaymentOptionsRequest->receiverOptions as $k ) {
				var_dump( $k->invoiceData );
			}
			exit;
		}

		$setPaymentOptionsRequest->senderOptions                                  = new SenderOptions();
		$setPaymentOptionsRequest->senderOptions->requireShippingAddressSelection = true;

		$service = new AdaptivePaymentsService();
		try {
			$response = $service->SetPaymentOptions( $setPaymentOptionsRequest );
		} catch ( Exception $ex ) {
			wc_add_notice( sprintf( __( 'Error: %s', 'wc-vendors' ), $ex->getMessage() ), 'error' );

			return false;
		}

		$this->logger->error( "Received SetPaymentOptionsResponse:" );
		$ack = strtoupper( $response->responseEnvelope->ack );
		if ( $ack != "SUCCESS" ) {
			$order->update_status( 'cancelled', sprintf( __( 'Error ID: %d. %s', 'wc-vendors'), $response->error[ 0 ]->errorId, $response->error[ 0 ]->message ) );
			wc_add_notice( sprintf( __( 'Error ID: %d. %s', 'wc-vendors' ), $response->error[ 0 ]->errorId, $response->error[ 0 ]->message ), 'error' );

			return false;
		}

		return true;
	}


	/**
	 * Payment fields
	 */
	function payment_fields()
	{
		if ( $description = $this->settings[ 'description' ] ) echo wpautop( wptexturize( $this->description ) );
	}


	/**
	 * Process the payment and return the result
	 *
	 * @param unknown $order_id
	 *
	 * @return unknown
	 */
	function process_payment( $order_id )
	{
		global $woocommerce;

		$order   = wc_get_order( $order_id );
		$pay_key = $this->paypalap_check_form( $order_id );

		if ( !empty( $woocommerce->errors ) ) return false;

		if ( $this->instapay ) {
			$this->set_paypal_author_specifics( $pay_key, $order_id );
			if ( !empty( $woocommerce->errors ) ) return false;
		}

		$paypal_redirect_url = $this->sandbox == 'yes' ? 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=' : 'https://www.paypal.com/cgi-bin/webscr?cmd=';
		$pay_url             = $paypal_redirect_url . '_ap-payment&paykey=' . $pay_key;

		return array(
			'result'   => 'success',
			'redirect' => $pay_url,
		);

	}
}
