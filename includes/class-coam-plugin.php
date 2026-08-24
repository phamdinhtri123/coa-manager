<?php
/**
 * Plugin bootstrap.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin coordinator.
 */
class COAM_Plugin {
	/**
	 * Instance.
	 *
	 * @var COAM_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return COAM_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register hooks.
	 */
	public function init() {
		load_plugin_textdomain( 'coa-manager', false, dirname( plugin_basename( COAM_FILE ) ) . '/languages' );

		COAM_Assets::init();
		COAM_Post_Type::init();
		COAM_Meta_Boxes::init();
		COAM_Admin_Columns::init();
		COAM_Settings::init();
		COAM_Shortcodes::init();
		COAM_WooCommerce::init();
		COAM_Updater::init();
	}

	/**
	 * Activation callback.
	 */
	public static function activate() {
		COAM_Post_Type::register();
		COAM_Settings::add_defaults();
		flush_rewrite_rules();
	}

	/**
	 * Deactivation callback.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
