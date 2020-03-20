<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get Give core settigns.
$give_settings = give_get_settings();

// List of plugin settings.
$plugin_settings = array(
	'op_payment_service_payment_method_label',
	'op_payment_service_live_merchant_id',
	'op_payment_service_live_merchant_secret',
	'op_payment_service_phone_field',
);

// Unset all plugin settings.
foreach ( $plugin_settings as $setting ) {
	if ( isset( $give_settings[ $setting ] ) ) {
		unset( $give_settings[ $setting ] );
	}
}

// Remove from active gateways list.
if ( isset( $give_settings['gateways']['op_payment_service'] ) ) {
	unset( $give_settings['gateways']['op_payment_service'] );
}


// Update settings.
update_option( 'give_settings', $give_settings );
