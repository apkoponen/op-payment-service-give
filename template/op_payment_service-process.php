<?php
/**
 * Process OP Payment Service response.
 *
 * @since 1.0
 */

use OpMerchantServices\SDK\Exception\HmacException;

// Ensure that the file is being run within the WordPress context.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Check the HMAC
try {
	give_op_payment_service_get_client()->validateHmac( filter_input_array( INPUT_GET ), '', filter_input( INPUT_GET, 'signature' ) );
} catch ( HmacException $exception ) {
	echo '<p>' . __(
			'An error occurred validating the payment request.',
			'give-op_payment_service'
		) . '</p>';
	die();
}

$reference_parts = explode( '_', filter_input( INPUT_GET, 'checkout-reference' ) );
$donation_id     = intval( $reference_parts[1] ?? 0 );
$status          = filter_input( INPUT_GET, 'checkout-status' );

if ( ! empty( $donation_id ) && ! empty( $status ) ) {
	$response_array = array(
		'payment_id' => filter_input( INPUT_GET, 'checkout-transaction-id' ),
		'stamp'      => filter_input( INPUT_GET, 'checkout-stamp' )
	);
	switch ( $status ) {
		// Success
		case 'ok':
			give_op_payment_service_process_success_payment( $donation_id, $response_array );
			break;
		// Cancel.
		case 'fail':
			give_op_payment_service_process_cancelled_payment( $donation_id, $response_array );
			break;
		// Error.
		default:
			give_op_payment_service_process_failed_payment( $donation_id, $response_array );
	}
} else {
	wp_redirect( home_url() );
	exit();
}

