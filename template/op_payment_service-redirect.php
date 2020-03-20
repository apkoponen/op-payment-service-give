<?php
/**
 * Checkout redirect
 */

// Ensure that the file is being run within the WordPress context.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$provider = give_op_payment_service_get_payment_provider_for_redirect($_GET['provider_id']);
?>

<!doctype html>
<html lang="en">
	<head>
		<title><?php _e( 'Redirecting you to the payment provider', 'give-op_payment_service' ); ?></title>
	</head>
	<body>
    <form action="<?php echo esc_html( $provider->getUrl() ); ?>" method="POST" id="checkout-redirect-form">
		<?php
		$parameters = $provider->getParameters();

		array_walk( $parameters, function( $parameter ) {
			printf(
				'<input type="hidden" name="%s" value="%s" />',
				esc_html( $parameter->name ),
				esc_html( $parameter->value )
			);
		});

		esc_html_e( 'Redirecting... If nothing happens in a few seconds, click the button below.', 'op-payment-service-woocommerce' );
		?>
      <p><input type="submit" value="<?php esc_html_e( 'Submit', 'op-payment-service-woocommerce' ); ?>" /></p>
    </form>

    <script>
      document.getElementById( 'checkout-redirect-form' ).submit();
    </script>	</body>
</html>
