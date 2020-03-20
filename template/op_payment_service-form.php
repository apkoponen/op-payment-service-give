<?php
/**
 * Provider form view
 */

// Ensure that the file is being run within the WordPress context.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Something went wrong loading the providers.
if ( ! empty( $data['error'] ) ) {
	printf(
		'<p>%s</p>',
		esc_html( $data['error'] )
	);

	return;
}

$group_titles = [
	'mobile'     => __( 'Mobile payment methods', 'give-op_payment_service' ),
	'bank'       => __( 'Bank payment methods', 'give-op_payment_service' ),
	'creditcard' => __( 'Card payment methods', 'give-op_payment_service' ),
	'credit'     => __( 'Invoice and instalment payment methods', 'give-op_payment_service' ),
];

$provider_groups = give_op_payment_service_get_providers();
?>
<!doctype html>
<html lang="en">
<head>
  <title><?php _e( 'Process Donations with the OP Payment Service', 'give-op_payment_service' ); ?></title>
	<?php wp_head(); ?>
  <style>
    .give-op_payment_service-payment-wrapper {
      padding: 30px;
      margin: 0 auto;
      max-width: 660px;
      background: #fff;
    }

    .give-op_payment_service-payment-divider {
      height: 30px;
    }

    .give-op_payment_service-payment-divider-sm {
      height: 15px;
    }

    .give-op_payment_service-payment-fields {
      margin: 0;
      padding: 0;
    }

    .give-op_payment_service-payment-fields--list-item {
      display: inline-block;
      position: relative;
      width: 25%;
      transition: box-shadow .3s ease-in-out;
      margin-bottom: 15px;
      padding: 5px;
    }

    .give-op_payment_service-payment-fields--list-item:hover {
      box-shadow: 0 0 .625rem 0 rgba(8, 19, 31, .12);
    }

    .give-op_payment_service-payment-fields--list-item--img {
      float: none !important;
      cursor: pointer;
      max-width: 100%;
      max-height: 100%;
    }</style>

</head>
<body>

<div class="give-op_payment_service-payment-wrapper">
  <h3><?= esc_html( __( 'Select payment method', 'give-op_payment_service' ) ); ?></h3>
  <div class="give-op_payment_service-payment-divider"></div>
	<?php
	array_walk( $provider_groups, function ( $provider_group, $title ) use ( $group_titles ) {
		?>
      <h4> <?= esc_html( $group_titles[ $title ] ?? $title ); ?></h4>
      <div class="give-op_payment_service-payment-fields">
		  <?php
		  array_walk( $provider_group, function ( $provider ) {
			  printf(
				  '<a href="/?process_op_payment_service_payment=redirect&provider_id=%s" class="give-op_payment_service-payment-fields--list-item">
                  <img class="give-op_payment_service-payment-fields--list-item--img" src="%s">
                </a>',
				  esc_html( $provider->getId() ),
				  esc_html( $provider->getSvg() )
			  );
		  } );
		  ?>
        <div class="give-op_payment_service-payment-divider-sm"></div>
      </div>
		<?php
	} );
	?>
</div>
</body>
</html>


