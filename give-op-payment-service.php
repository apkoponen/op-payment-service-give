<?php
/**
 * Plugin Name: OP Payment Service for Give
 * Plugin URI: https://a-p.fi
 * Description: This OP Payment Service (formerly OP Payment Service) for WooCommerce
 * Author: Ari-Pekka Koponen
 * Author URI: https://www.apkoponen.com
 * Version: 0.0.1
 * Text Domain: give-checkout-fi
 * Domain Path: /languages
 *
 * This is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this software. If not, see <https://www.gnu.org/licenses/>.
 *
 */
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class Give_CheckoutFi_Payment
 *
 * @since 1.0
 */
class Give_CheckoutFi_Payment {

	/**
	 * @since  1.0
	 * @access static
	 * @var Give_CheckoutFi_Payment $instance
	 */
	static private $instance;

	/**
	 * Give_CheckoutFi_Payment constructor.
	 */
	protected function __construct() {
	}


	/**
	 * Singleton pattern.
	 *
	 * @since  1.0
	 * @access static
	 * @return Give_CheckoutFi_Payment
	 */
	static function get_instance() {
		if ( null === self::$instance ) {
			static::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Load libraries.
	 *
	 * @since  1.0
	 * @access public
	 * @return void
	 */
	public function setup() {
		// Check if Composer has been initialized in this directory.
		// Otherwise we just use global composer autoloading.
		if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
			require_once __DIR__ . '/vendor/autoload.php';
		}

		// Load general functions.
		require_once 'includes/functions.php';

		// Load admin settings and register payment gateway.
		require_once 'includes/admin/class-admin-settings.php';

		if ( is_admin() ) {
			// Load admin actions
			require_once 'includes/admin/actions.php';
		}

		// Load filters.
		require_once 'includes/filters.php';

		// Load frontend actions.
		require_once 'includes/actions.php';

		// Process donation payment.
		require_once 'includes/donation-processing.php';
	}

	/**
	 * Setup constants.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_CheckoutFi_Payment
	 */
	public function set_constants() {
		// Global Params.
		define( 'GIVE_OP_PAYMENT_SERVICE_VERSION', '1.0' );
		define( 'GIVE_OP_PAYMENT_SERVICE_MIN_GIVE_VER', '1.8.4' );
		define( 'GIVE_OP_PAYMENT_SERVICE_BASENAME', plugin_basename( __FILE__ ) );
		define( 'GIVE_OP_PAYMENT_SERVICE_URL', plugins_url( '/', __FILE__ ) );
		define( 'GIVE_OP_PAYMENT_SERVICE_DIR', plugin_dir_path( __FILE__ ) );

		add_action( 'init', array( $this, 'load_textdomain' ) );

		return self::$instance;
	}

	/**
	 * Load the text domain.
	 *
	 * @access private
	 * @since  1.0
	 *
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$give_op_payment_service_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$give_op_payment_service_lang_dir = apply_filters( 'give_op_payment_service_languages_directory', $give_op_payment_service_lang_dir );

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'give-op_payment_service' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'give-op_payment_service', $locale );

		// Setup paths to current locale file
		$mofile_local  = $give_op_payment_service_lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/give-op_payment_service/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/give-op_payment_service folder
			load_textdomain( 'give-op_payment_service', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/give-op_payment_service/languages/ folder
			load_textdomain( 'give-op_payment_service', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'give-op_payment_service', false, $give_op_payment_service_lang_dir );
		}

	}


	/**
	 * Check if plugin dependencies satisfied or not
	 *
	 * @since 1.0
	 * @access public
	 * @return bool
	 */
	public function is_plugin_dependency_satisfied() {
		return ( - 1 !== version_compare( GIVE_VERSION, GIVE_OP_PAYMENT_SERVICE_MIN_GIVE_VER ) );
	}
}

/**
 * Initialize plugin
 */
function give_op_payment_service_init() {
	// Get instance.
	$give_cca = Give_CheckoutFi_Payment::get_instance();

	// Setup constants.
	$give_cca->set_constants();

	if ( is_admin() ) {
		// Process plugin activation.
		require_once 'includes/admin/plugin-activation.php';
	}

	if ( class_exists( 'Give' ) && $give_cca->is_plugin_dependency_satisfied() ) {
		$give_cca->setup();
	}
}

add_action( 'plugins_loaded', 'give_op_payment_service_init' );
