<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use OpMerchantServices\SDK\Exception\ValidationException;
use OpMerchantServices\SDK\Request\PaymentRequest;
use OpMerchantServices\SDK\Model\Customer;
use OpMerchantServices\SDK\Model\Item;
use OpMerchantServices\SDK\Model\CallbackUrl;
use OpMerchantServices\SDK\Exception\HmacException;
use OpMerchantServices\SDK\Client;
use OpMerchantServices\SDK\Model\Provider;
use GuzzleHttp\Exception\RequestException;

/**
 * Get payment gateway label.
 *
 * @since 1.0
 * @return string
 */
function give_op_payment_service_get_payment_method_label() {
	return give_get_option( 'op_payment_service_payment_method_label', __( 'OP Payment Service', 'give-op_payment_service' ) );
}

/**
 * Check if sandbox mode is enabled or disabled.
 *
 * @since 1.0
 * @return bool
 */
function give_op_payment_service_is_sandbox_mode_enabled() {
	return give_is_test_mode();
}


/**
 * Get op_payment_service agent credentials.
 *
 * @since 1.0
 * @return array
 */
function give_op_payment_service_get_merchant_credentials() {
	$give_settings = give_get_settings();

	$merchant_id     = intval( $give_settings['op_payment_service_live_merchant_id'] ?? 0 );
	$merchant_secret = $give_settings['op_payment_service_live_merchant_secret'] ?? '';

	if ( give_op_payment_service_is_sandbox_mode_enabled() ) {
		$merchant_id     = 375917;
		$merchant_secret = 'SAIPPUAKAUPPIAS';
	}

	$credentials = array(
		'merchant_id'     => $merchant_id,
		'merchant_secret' => $merchant_secret,
	);

	return apply_filters( 'give_op_payment_service_get_merchant_credentials', $credentials );
}

function give_op_payment_service_get_client() {
	$credentials = give_op_payment_service_get_merchant_credentials();

	// Create SDK client instance
	return new Client(
		$credentials['merchant_id'],
		$credentials['merchant_secret'],
		'op-payment-service-for-give-0.0.1'
	);
}

function give_op_payment_service_get_providers() {
	$donation_data  = Give()->session->get( 'give_purchase' );
	$payment_amount = ( give_op_payment_service_format_currency( $donation_data['price'] ) );
	try {
		$providers = give_op_payment_service_get_client()->getPaymentProviders( $payment_amount );
	} catch ( HmacException | RequestException $exception ) {
		// If there was an error getting the payment providers, show it
		echo '<p>' . esc_html( __(
				'An error occurred loading the payment providers.',
				'give-op_payment_service'
			) ) . '</p>';

		return;
	}

	// Group the providers by type
	$providers_by_group = array_reduce( $providers, function ( ?array $carry, Provider $item ): array {
		if ( ! is_array( $carry[ $item->getGroup() ] ?? false ) ) {
			$carry[ $item->getGroup() ] = [];
		}

		$carry[ $item->getGroup() ][] = $item;

		return $carry;
	} );

	return $providers_by_group;
}

function give_op_payment_service_format_currency( $amount_with_decimals ) {
	return $amount_with_decimals * 100;
}

function give_op_payment_service_get_payment_provider_for_redirect( $payment_provider_id ) {
	// Check that we have payment provider
	if ( ! $payment_provider_id ) {
		throw new \Exception( __(
			'The payment provider was not chosen.',
			'give-op_payment_service'
		) );
	}

	$donation_data = Give()->session->get( 'give_purchase' );
	$donation_id   = $donation_data['donation_id'];

	$payment = new PaymentRequest();

	// Set the order ID as the stamp to the payment request
	$payment->setStamp( get_current_blog_id() . '-' . $donation_id . '-' . time() );

	// Use the donation id as a reference
	$payment->setReference( 'donation_' . $donation_id );

	// Fetch current currency and the cart total
	$currency       = give_get_currency( $donation_id );
	$donation_total = give_op_payment_service_format_currency( $donation_data['price'] );

	// Set the aforementioned values to the payment request
	$payment->setCurrency( $currency )
	        ->setAmount( $donation_total );

	$customer = new Customer();

	$user_info = $donation_data['user_info'];
	$customer->setEmail( $user_info['email'] ?? null )
	         ->setFirstName( $user_info['first_name'] ?? null )
	         ->setLastName( $user_info['last_name'] ?? null );


	// Set the customer object to the payment request
	$payment->setCustomer( $customer );

	$full_locale = get_locale();

	$short_locale = substr( $full_locale, 0, 2 );

	// Get and assign the WordPress locale
	switch ( $short_locale ) {
		case 'sv':
			$locale = 'SV';
			break;
		case 'fi':
			$locale = 'FI';
			break;
		default:
			$locale = 'EN';
			break;
	}

	$payment->setLanguage( $locale );


	$donation_item = new Item();
	$donation_item->setDescription( sprintf( __( 'This is a donation payment for %s', 'give-op_payment_service' ), $donation_id ) );
	$donation_item->setDeliveryDate( date( 'Y-m-d' ) );
	$donation_item->setVatPercentage( 0 );
	$donation_item->setUnits( 1 );
	$donation_item->setUnitPrice( $donation_total );
	$donation_item->setProductCode( 'donation' );

	$items = [ $donation_item ];

	// Assign the items to the payment request.
	$payment->setItems( $items );

	// Create and assign the return urls
	$redirect_url = $callback = new CallbackUrl();


	$site_url    = home_url( '/' );
	$process_url = add_query_arg( [ 'process_op_payment_service_payment_process' => 1 ], $site_url );
	$redirect_url->setSuccess( $process_url );
	$redirect_url->setCancel( $process_url );

	$payment->setRedirectUrls( $redirect_url );

	// Create a payment via Checkout SDK
	$error_message = '';
	try {
		$response = give_op_payment_service_get_client()->createPayment( $payment );
	} catch ( ValidationException $exception ) {
		error_log( $exception );
		$error_message = __(
			'An error occurred validating the payment.',
			'give-op_payment_service'
		);
	} catch ( HmacException $exception ) {
		error_log( $exception );
		$error_message = __(
			'An error occurred validating the payment request.',
			'give-op_payment_service'
		);
	} catch ( RequestException $exception ) {
		error_log( $exception );
		$error_message = __(
			'An error occurred performing the payment request.',
			'give-op_payment_service'
		);
	}
	if ( ! empty( $error_message ) ) {
		throw new \Exception( $error_message );
	}

	$providers = $response->getProviders();

	// Get only the wanted payment provider object
	$wanted_provider = array_reduce(
		$providers, function ( $carry, $item = null ) use ( $payment_provider_id ) : ?Provider {
		if ( $item && $item->getId() === $payment_provider_id ) {
			return $item;
		}

		return $carry;
	} );

	return $wanted_provider;
}

/**
 * Process op_payment_service success payment.
 *
 * @since  1.0
 *
 * @access public
 *
 * @param int $donation_id
 * @param array $response
 */
function give_op_payment_service_process_success_payment( $donation_id, $response ) {
	$donation = new Give_Payment( $donation_id );
	$donation->update_status( 'completed' );
	$donation->add_note( sprintf( __( 'OP Payment Service payment completed (Payment id: %s)', 'give-op_payment_service' ), $response['payment_id'] ) );

	wp_clear_scheduled_hook( 'give_op_payment_service_set_donation_abandoned', array( absint( $donation_id ) ) );

	give_set_payment_transaction_id( $donation_id, $response['payment_id'] );
	update_post_meta( $donation_id, 'op_payment_service_donation_response', $response );

	give_send_to_success_page();
}

/**
 * Process op_payment_service cancelled payment.
 *
 * @since  1.0
 *
 * @access public
 *
 * @param int $donation_id
 * @param array $response
 */
function give_op_payment_service_process_cancelled_payment( $donation_id, $response ) {
	$donation = new Give_Payment( $donation_id );
	$donation->update_status( 'cancelled' );
	$donation->add_note( sprintf( __( 'OP Payment Service payment cancelled (Payment id: %s)', 'give-op_payment_service' ), $response['payment_id'] ) );

	wp_clear_scheduled_hook( 'give_op_payment_service_set_donation_abandoned', array( absint( $donation_id ) ) );

	give_set_payment_transaction_id( $donation_id, $response['payment_id'] );
	update_post_meta( $donation_id, 'op_payment_service_donation_response', $response );


	wp_redirect( home_url() );
	exit();
}

/**
 * Process op_payment_service failure payment.
 *
 * @since  1.0
 *
 * @access public
 *
 * @param int $donation_id
 * @param array $response
 */
function give_op_payment_service_process_failed_payment( $donation_id, $response ) {
	$donation = new Give_Payment( $donation_id );
	$donation->update_status( 'failed' );
	$donation->add_note( sprintf( __( 'OP Payment Service payment failed (Payment id: %s)', 'give-op_payment_service' ), $response['payment_id'] ) );

	wp_clear_scheduled_hook( 'give_op_payment_service_set_donation_abandoned', array( absint( $donation_id ) ) );

	give_set_payment_transaction_id( $donation_id, $response['payment_id'] );
	update_post_meta( $donation_id, 'op_payment_service_donation_response', $response );

	give_record_gateway_error(
		esc_html__( 'OP Payment Service Error', 'give-op_payment_service' ),
		esc_html__( 'The OP Payment Service Gateway returned an error while charging a donation.', 'give-op_payment_service' ) . '<br><br>' . sprintf( esc_attr__( 'Details: %s', 'give-op_payment_service' ), '<br>' . print_r( $response, true ) ),
		$donation_id
	);

	wp_redirect( give_get_failed_transaction_uri() );
	exit();
}

