<?php
/**
 * Shortcodes.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode registration.
 */
class COAM_Shortcodes {
	/**
	 * Hooks.
	 */
	public static function init() {
		add_shortcode( 'coa_manager', array( __CLASS__, 'manager' ) );
		add_shortcode( 'coa_product_latest', array( __CLASS__, 'product_latest' ) );
	}

	/**
	 * Manager shortcode.
	 *
	 * @param array<string,mixed> $atts Attributes.
	 * @return string
	 */
	public static function manager( $atts ) {
		$atts = shortcode_atts(
			array(
				'category'            => '',
				'laboratory'          => '',
				'limit'               => 24,
				'show_title'          => null,
				'show_description'    => null,
				'show_total'          => null,
				'show_search'         => null,
				'desktop_columns'     => null,
				'tablet_columns'      => null,
				'mobile_columns'      => null,
			),
			(array) $atts,
			'coa_manager'
		);

		return COAM_Renderer::render_manager( array_filter( $atts, static fn( $value ) => null !== $value ) );
	}

	/**
	 * Product latest shortcode.
	 *
	 * @param array<string,mixed> $atts Attributes.
	 * @return string
	 */
	public static function product_latest( $atts ) {
		$atts = shortcode_atts(
			array(
				'display'    => 'button',
				'product_id' => 0,
			),
			(array) $atts,
			'coa_product_latest'
		);

		return COAM_Renderer::render_latest_product_coa( absint( $atts['product_id'] ), array( 'display' => sanitize_key( $atts['display'] ) ) );
	}
}
