<?php
/**
 * Show transaction ID under donation meta.
 *
 * @since 1.0
 *
 * @param $transaction_id
 */
function give_op_payment_service_link_transaction_id( $transaction_id ) {

	$payment = new Give_Payment( $transaction_id );

	$op_payment_service_trans_url = '#';

	if ( 'test' === $payment->mode ) {
		$op_payment_service_trans_url = '#';
	}

	$op_payment_service_response = get_post_meta( absint( $_GET['id'] ), 'op_payment_service_donation_response', true );
	$op_payment_service_trans_url .= $op_payment_service_response['tracking_id'];

	echo sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $op_payment_service_trans_url, $op_payment_service_response['tracking_id'] );
}

// add_filter( 'give_payment_details_transaction_id-op_payment_service', 'give_op_payment_service_link_transaction_id', 10, 2 );


/**
 * Add op_payment_service donor detail to "Donor Detail" metabox
 *
 * @since 1.0
 *
 * @param $payment_id
 *
 * @return bool
 */
function give_op_payment_service_view_details( $payment_id ) {
	// Bailout.
	if ( 'op_payment_service' !== give_get_payment_gateway( $payment_id ) ) {
		return false;
	}

	$op_payment_service_response = get_post_meta( absint( $_GET['id'] ), 'op_payment_service_donation_response', true );

	// Check if phone exit in op_payment_service response.
	if ( empty( $op_payment_service_response['billing_tel'] ) ) {
		return false;
	}
	?>
	<div class="column">
		<p>
			<strong><?php _e( 'Phone:', 'give-op_payment_service' ); ?></strong><br>
			<?php echo $op_payment_service_response['billing_tel']; ?>
		</p>
	</div>
	<?php
}

add_action( 'give_payment_view_details', 'give_op_payment_service_view_details' );
