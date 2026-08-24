<?php
/**
 * GitHub release updater.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Update Checker integration.
 */
class COAM_Updater {
	/**
	 * Initialize updater.
	 */
	public static function init() {
		$repository_url = apply_filters( 'coam_github_repository_url', defined( 'COAM_GITHUB_REPOSITORY_URL' ) ? COAM_GITHUB_REPOSITORY_URL : '' );
		if ( '' === $repository_url ) {
			return;
		}

		$loader = COAM_PATH . 'vendor/plugin-update-checker/plugin-update-checker.php';
		if ( file_exists( $loader ) ) {
			require_once $loader;
		}

		if ( class_exists( '\YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
			\YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
				esc_url_raw( $repository_url ),
				COAM_FILE,
				'coa-manager'
			);
		}
	}
}
