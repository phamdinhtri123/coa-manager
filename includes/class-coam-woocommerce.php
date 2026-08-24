<?php
/**
 * WooCommerce integration.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce hooks.
 */
class COAM_WooCommerce {
	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp', array( __CLASS__, 'register_product_hook' ) );
	}

	/**
	 * Register selected product page hook.
	 */
	public static function register_product_hook() {
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		$settings = COAM_Helpers::get_settings();
		if ( empty( $settings['auto_show_product_coa'] ) ) {
			return;
		}

		$position = $settings['product_position'];
		if ( 'after_tabs' === $position ) {
			add_action( 'woocommerce_after_single_product_summary', array( __CLASS__, 'render_auto' ), 25 );
		} elseif ( 'before_related' === $position ) {
			add_action( 'woocommerce_after_single_product_summary', array( __CLASS__, 'render_auto' ), 15 );
		} else {
			add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_auto' ), 35 );
		}
	}

	/**
	 * Render automatic product COA.
	 */
	public static function render_auto() {
		$settings = COAM_Helpers::get_settings();
		echo COAM_Renderer::render_latest_product_coa( get_the_ID(), array( 'display' => $settings['product_display_type'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
