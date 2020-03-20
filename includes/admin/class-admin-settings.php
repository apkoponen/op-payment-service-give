<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_CheckoutFi_Gateway_Settings
 *
 * @since 1.0
 */
class Give_CheckoutFi_Gateway_Settings {
	/**
	 * @since  1.0
	 * @access static
	 * @var Give_CheckoutFi_Gateway_Settings $instance
	 */
	static private $instance;

	/**
	 * @since  1.0
	 * @access private
	 * @var string $section_id
	 */
	private $section_id;

	/**
	 * @since  1.0
	 * @access private
	 * @var string $section_label
	 */
	private $section_label;

	/**
	 * Give_CheckoutFi_Gateway_Settings constructor.
	 */
	private function __construct() {
	}

	/**
	 * get class object.
	 *
	 * @since  1.0
	 * @access static
	 * @return Give_CheckoutFi_Gateway_Settings
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Setup hooks.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function setup_hooks() {
		$this->section_id    = 'op_payment_service';
		$this->section_label = __( 'OP Payment Service', 'give-op_payment_service' );

		// Add payment gateway to payment gateways list.
		add_filter( 'give_payment_gateways', array( $this, 'add_gateways' ) );

		if ( is_admin() ) {

			// Add section to payment gateways tab.
			add_filter( 'give_get_sections_gateways', array( $this, 'add_section' ) );

			// Add section settings.
			add_filter( 'give_get_settings_gateways', array( $this, 'add_settings' ) );
		}
	}

	/**
	 * Add payment gateways to gateways list.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $gateways array of payment gateways.
	 *
	 * @return array
	 */
	public function add_gateways( $gateways ) {
		$gateways[ $this->section_id ] = array(
			'admin_label'    => $this->section_label,
			'checkout_label' => give_op_payment_service_get_payment_method_label(),
		);

		return $gateways;
	}

	/**
	 * Add setting section.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $sections Array of section.
	 *
	 * @return array
	 */
	public function add_section( $sections ) {
		$sections[ $this->section_id ] = $this->section_label;

		return $sections;
	}

	/**
	 * Add plugin settings.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $settings Array of setting fields.
	 *
	 * @return array
	 */
	public function add_settings( $settings ) {
		$current_section = give_get_current_setting_section();

		if ( $this->section_id == $current_section ) {
			$settings = array(
				array(
					'id'   => 'give_op_payment_service_payments_setting',
					'type' => 'title',
				),
				array(
					'title'   => __( 'Payment Method Label', 'give-op_payment_service' ),
					'id'      => 'op_payment_service_payment_method_label',
					'type'    => 'text',
					'default' => give_op_payment_service_get_payment_method_label(),
					'desc'    => __( 'Payment method label will be appear on frontend.', 'give-op_payment_service' ),
				),
				array(
					'title' => __( 'Merchant ID', 'give-op_payment_service' ),
					'id'    => 'op_payment_service_live_merchant_id',
					'type'  => 'text',
					'desc'  => __( 'The Merchant ID provided by OP Payment Service.', 'give-op_payment_service' ),
				),
				array(
					'title' => __( 'Merchant Secret', 'give-op_payment_service' ),
					'id'    => 'op_payment_service_live_merchant_secret',
					'type'  => 'api_key',
					'desc'  => __( 'The Merchant Secret provided by OP Payment Service.', 'give-op_payment_service' ),
				),
				array(
					'title'   => __( 'Show Phone Field', 'give-op_payment_service' ),
					'id'      => 'op_payment_service_phone_field',
					'type'    => 'radio_inline',
					'desc'    => __( 'Enable this setting if you want to show phone field on donation form.', 'give-op_payment_service' ),
					'default' => 'enabled',
					'options' => array(
						'enabled'  => __( 'Enabled', 'give-op_payment_service' ),
						'disabled' => __( 'Disabled', 'give-op_payment_service' ),
					),
				),
				array(
					'id'   => 'give_op_payment_service_payments_setting',
					'type' => 'sectionend',
				),
			);
		}// End if().

		return $settings;
	}
}

Give_CheckoutFi_Gateway_Settings::get_instance()->setup_hooks();
