<?php
/**
 * Auto set pending payment to abandoned.
 *
 * @since 1.0
 *
 * @param int $payment_id
 */
function give_op_payment_service_set_donation_abandoned_callback( $payment_id ) {
	/**
	 * @var Give_Payment $payment Payment object.
	 */
	$payment = new Give_Payment( $payment_id );

	if ( 'pending' === $payment->status ) {
		$payment->update_status( 'abandoned' );
	}
}

add_action( 'give_op_payment_service_set_donation_abandoned', 'give_op_payment_service_set_donation_abandoned_callback' );


/**
 * Add phone field.
 *
 * @since 1.0
 *
 * @param $form_id
 *
 * @return bool
 */
function give_op_payment_service_add_phone_field( $form_id ) {
	// Bailout.
	if (
		'op_payment_service' !== give_get_chosen_gateway( $form_id )
		|| ! give_is_setting_enabled( give_get_option( 'op_payment_service_phone_field' ) )
	) {
		return false;
	}
	?>
	<p id="give-phone-wrap" class="form-row form-row-wide">
		<label class="give-label" for="give-phone">
			<?php esc_html_e( 'Phone', 'give-op_payment_service' ); ?>
			<span class="give-required-indicator">*</span>
			<span class="give-tooltip give-icon give-icon-question"
			      data-tooltip="<?php esc_attr_e( 'Enter only phone number.', 'give-op_payment_service' ); ?>"></span>

		</label>

		<input
			class="give-input required"
			type="tel"
			name="give_op_payment_service_phone"
			id="give-phone"
			value="<?php echo isset( $give_user_info['give_phone'] ) ? $give_user_info['give_phone'] : ''; ?>"
			required
			aria-required="true"
			maxlength="10"
			pattern="\d{10}"
		/>
	</p>
	<?php
}

add_action( 'give_donation_form_after_email', 'give_op_payment_service_add_phone_field' );

/**
 * Do not print cc field in donation form.
 *
 * Note: We do not need credit card field in donation form but we need billing detail fields.
 *
 * @since 1.0
 *
 * @param $form_id
 */
function give_op_payment_service_cc_form_callback( $form_id ) {
	//give_default_cc_address_fields( $form_id );
}

add_action( 'give_op_payment_service_cc_form', 'give_op_payment_service_cc_form_callback' );