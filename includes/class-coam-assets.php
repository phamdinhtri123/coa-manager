<?php
/**
 * Assets.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Asset loader.
 */
class COAM_Assets {
	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
	}

	/**
	 * Admin assets.
	 *
	 * @param string $hook Hook suffix.
	 */
	public static function admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || ( COAM_Helpers::POST_TYPE !== $screen->post_type && 'coa_record_page_coam-settings' !== $screen->id ) ) {
			return;
		}

		wp_enqueue_style( 'coam-admin', COAM_URL . 'assets/css/admin.css', array(), COAM_VERSION );
		wp_enqueue_script( 'coam-admin', COAM_URL . 'assets/js/admin.js', array( 'jquery' ), COAM_VERSION, true );

		if ( COAM_Helpers::POST_TYPE === $screen->post_type ) {
			wp_enqueue_media();
		}

		if ( 'coa_record_page_coam-settings' === $screen->id ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}
	}

	/**
	 * Frontend assets.
	 */
	public static function frontend_assets() {
		if ( wp_style_is( 'coam-frontend', 'enqueued' ) ) {
			return;
		}

		wp_enqueue_style( 'coam-frontend', COAM_URL . 'assets/css/frontend.css', array(), COAM_VERSION );
		wp_enqueue_script( 'coam-frontend', COAM_URL . 'assets/js/frontend.js', array(), COAM_VERSION, true );
	}
}
