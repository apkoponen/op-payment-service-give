<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function give_process_op_payment_service_payment( $donation_data ) {
	if ( ! wp_verify_nonce( $donation_data['gateway_nonce'], 'give-gateway' ) ) {
		wp_die( esc_html__( 'Nonce verification has failed.', 'give-op_payment_service' ), esc_html__( 'Error', 'give-op_payment_service' ), array(
			'response' => 403,
		) );
	}

	$form_id  = intval( $donation_data['post_data']['give-form-id'] );
	$price_id = isset( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : '';

	// Collect payment data.
	$donation_payment_data = array(
		'price'           => $donation_data['price'],
		'give_form_title' => $donation_data['post_data']['give-form-title'],
		'give_form_id'    => $form_id,
		'give_price_id'   => $price_id,
		'date'            => $donation_data['date'],
		'user_email'      => $donation_data['user_email'],
		'purchase_key'    => $donation_data['purchase_key'],
		'currency'        => give_get_currency(),
		'user_info'       => $donation_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'op_payment_service',
	);

	// Record the pending payment.
	$payment = give_insert_payment( $donation_payment_data );

	// Verify donation payment.
	if ( ! $payment ) {
		// Record the error.
		give_record_gateway_error(
			esc_html__( 'Payment Error', 'give-op_payment_service' ),
			/* translators: %s: payment data */
			sprintf(
				esc_html__( 'Payment creation failed before process OP Payment Service gateway. Payment data: %s', 'give-op_payment_service' ),
				json_encode( $donation_payment_data )
			),
			$payment
		);

		// Problems? Send back.
		give_send_back_to_checkout( '?payment-mode=' . $donation_data['post_data']['give-gateway'] );
	}

	// Auto set payment to abandoned in one hour if donor is not able to donate in that time.
	wp_schedule_single_event( current_time( 'timestamp', 1 ) + HOUR_IN_SECONDS, "give_op_payment_service_set_donation_abandoned", array( $payment ) );

	// Redirect to op_payment_service checkout page.
	wp_redirect( home_url( "/?process_op_payment_service_payment=processing&donation={$payment}&form-id={$form_id}" ) );
}

add_action( 'give_gateway_op_payment_service', 'give_process_op_payment_service_payment' );
