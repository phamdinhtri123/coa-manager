<?php
/**
 * Plugin Name: COA Manager
 * Plugin URI: https://github.com/your-org/coa-manager
 * Description: Manage and display Certificate of Analysis records with WooCommerce integration.
 * Version: 1.0.5
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: COA Manager
 * Text Domain: coa-manager
 * Domain Path: /languages
 * WC requires at least: 8.0
 * WC tested up to: 10.0
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COAM_VERSION', '1.0.5' );
define( 'COAM_FILE', __FILE__ );
define( 'COAM_PATH', plugin_dir_path( __FILE__ ) );
define( 'COAM_URL', plugin_dir_url( __FILE__ ) );

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', COAM_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', COAM_FILE, true );
		}
	}
);

require_once COAM_PATH . 'includes/class-coam-helpers.php';
require_once COAM_PATH . 'includes/class-coam-assets.php';
require_once COAM_PATH . 'includes/class-coam-post-type.php';
require_once COAM_PATH . 'includes/class-coam-meta-boxes.php';
require_once COAM_PATH . 'includes/class-coam-admin-columns.php';
require_once COAM_PATH . 'includes/class-coam-settings.php';
require_once COAM_PATH . 'includes/class-coam-renderer.php';
require_once COAM_PATH . 'includes/class-coam-shortcodes.php';
require_once COAM_PATH . 'includes/class-coam-woocommerce.php';
require_once COAM_PATH . 'includes/class-coam-updater.php';
require_once COAM_PATH . 'includes/class-coam-plugin.php';

register_activation_hook( __FILE__, array( 'COAM_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'COAM_Plugin', 'deactivate' ) );

COAM_Plugin::instance()->init();

if ( ! function_exists( 'coam_render_manager' ) ) {
	/**
	 * Render the public COA manager.
	 *
	 * @param array<string,mixed> $args Render arguments.
	 */
	function coam_render_manager( $args = array() ) {
		echo COAM_Renderer::render_manager( (array) $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

if ( ! function_exists( 'coam_get_latest_coa_for_product' ) ) {
	/**
	 * Get latest valid COA for a WooCommerce product by tested date.
	 *
	 * @param int $product_id Product ID.
	 * @return WP_Post|null
	 */
	function coam_get_latest_coa_for_product( $product_id ) {
		return COAM_Helpers::get_latest_coa_for_product( absint( $product_id ) );
	}
}

if ( ! function_exists( 'coam_render_latest_product_coa' ) ) {
	/**
	 * Render latest product COA.
	 *
	 * @param int                 $product_id Product ID.
	 * @param array<string,mixed> $args Render arguments.
	 */
	function coam_render_latest_product_coa( $product_id = 0, $args = array() ) {
		echo COAM_Renderer::render_latest_product_coa( absint( $product_id ), (array) $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
