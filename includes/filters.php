<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template include callback.
 *
 * @param $template
 *
 * @return string
 */
function give_op_payment_service_template_include_callback( $template ) {
	if ( isset( $_GET['process_op_payment_service_payment'] ) && 'processing' === esc_attr( $_GET['process_op_payment_service_payment'] ) ) {
		return dirname( plugin_dir_path( __FILE__ ) ) . '/template/op_payment_service-form.php';
	} elseif (  isset( $_GET['process_op_payment_service_payment'] ) && 'redirect' === esc_attr( $_GET['process_op_payment_service_payment'] ) ) {
		return dirname( plugin_dir_path( __FILE__ ) ) . '/template/op_payment_service-redirect.php';
	} elseif ( isset( $_GET['process_op_payment_service_payment_process'] ) ) {
		return dirname( plugin_dir_path( __FILE__ ) ) . '/template/op_payment_service-process.php';
	}

	return $template;
}

add_filter( 'template_include', 'give_op_payment_service_template_include_callback' );