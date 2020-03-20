<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give Display Donors Activation Banner
 *
 * Includes and initializes Give activation banner class.
 *
 * @since 1.0
 */

function give_op_payment_service_activation_banner() {

	// Check for if give plugin activate or not.
	$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

	//Check to see if Give is activated, if it isn't deactivate and show a banner
	if ( current_user_can( 'activate_plugins' ) && ! $is_give_active ) {

		add_action( 'admin_notices', 'give_op_payment_service_inactive_notice' );

		//Don't let this plugin activate
		deactivate_plugins( GIVE_OP_PAYMENT_SERVICE_BASENAME );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		return false;

	}

	// Minimum Give version required for this plugin to work.
	if ( version_compare( GIVE_VERSION, GIVE_OP_PAYMENT_SERVICE_MIN_GIVE_VER, '<' ) ) {

		add_action( 'admin_notices', 'give_op_payment_service_version_notice' );

		//Don't let this plugin activate.
		deactivate_plugins( GIVE_OP_PAYMENT_SERVICE_BASENAME );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		return false;

	}

	// Check for activation banner inclusion.
	if (
		! class_exists( 'Give_Addon_Activation_Banner' )
		&& file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' )
	) {
		include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
	}

	// Initialize activation welcome banner.
	if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

		// Only runs on admin.
		$args = array(
			'file'              => __FILE__,
			'name'              => esc_html__( 'OP Payment Service Gateway', 'give-op_payment_service' ),
			'version'           => GIVE_OP_PAYMENT_SERVICE_VERSION,
			'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=op_payment_service' ),
			'documentation_url' => 'http://docs.givewp.com/addon-op_payment_service',
			'support_url'       => 'https://givewp.com/support/',
			'testing'           => false, //Never leave true.
		);

		new Give_Addon_Activation_Banner( $args );

	}


	return false;

}

add_action( 'admin_init', 'give_op_payment_service_activation_banner' );

/**
 * Notice for No Core Activation
 *
 * @since 1.0
 */
function give_op_payment_service_inactive_notice() {
	echo '<div class="error"><p>' . __( '<strong>Activation Error:</strong> You must have the <a href="https://givewp.com/" target="_blank">Give</a> plugin installed and activated for the OP Payment Service add-on to activate.', 'give-op_payment_service' ) . '</p></div>';
}

/**
 * Notice for min. version violation.
 *
 * @since 1.0
 */
function give_op_payment_service_version_notice() {
	echo '<div class="error"><p>' . sprintf( __( '<strong>Activation Error:</strong> You must have <a href="%1$s" target="_blank">Give</a> version %2$s+ for the OP Payment Service add-on to activate.', 'give-op_payment_service' ), 'https://givewp.com', GIVE_OP_PAYMENT_SERVICE_MIN_GIVE_VER ) . '</p></div>';
}


/**
 * Plugins row action links
 *
 * @since 1.0
 *
 * @param array $actions An array of plugin action links.
 *
 * @return array An array of updated action links.
 */
function give_op_payment_service_plugin_action_links( $actions ) {
	$new_actions = array(
		'settings' => sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=op_payment_service' ),
			esc_html__( 'Settings', 'give-op_payment_service' )
		),
	);

	return array_merge( $new_actions, $actions );
}

add_filter( 'plugin_action_links_' . GIVE_OP_PAYMENT_SERVICE_BASENAME, 'give_op_payment_service_plugin_action_links' );