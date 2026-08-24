<?php
/**
 * Shared helpers.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper methods.
 */
class COAM_Helpers {
	const POST_TYPE = 'coa_record';
	const TAXONOMY  = 'coa_category';

	/**
	 * Meta key map.
	 *
	 * @return array<string,string>
	 */
	public static function meta_keys() {
		return array(
			'product_name'   => '_coam_product_name',
			'strength'       => '_coam_strength',
			'purity'         => '_coam_purity',
			'batch_lot'      => '_coam_batch_lot',
			'laboratory'     => '_coam_laboratory',
			'tested_date'    => '_coam_tested_date',
			'file_id'        => '_coam_file_id',
			'external_url'   => '_coam_external_url',
			'file_source'    => '_coam_file_source',
			'product_id'     => '_coam_product_id',
			'internal_notes' => '_coam_internal_notes',
		);
	}

	/**
	 * Get a meta value.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $field Field name.
	 * @return string
	 */
	public static function get_meta( $post_id, $field ) {
		$keys = self::meta_keys();
		if ( ! isset( $keys[ $field ] ) ) {
			return '';
		}

		return (string) get_post_meta( $post_id, $keys[ $field ], true );
	}

	/**
	 * Sanitize date in Y-m-d.
	 *
	 * @param string $date Date value.
	 * @return string
	 */
	public static function sanitize_date( $date ) {
		$date = sanitize_text_field( $date );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return '';
		}

		$parts = array_map( 'absint', explode( '-', $date ) );
		return checkdate( $parts[1], $parts[2], $parts[0] ) ? $date : '';
	}

	/**
	 * Format tested date.
	 *
	 * @param string $date Date value.
	 * @return string
	 */
	public static function format_date( $date ) {
		$date = self::sanitize_date( $date );
		if ( '' === $date ) {
			return '';
		}

		return date_i18n( 'M j, Y', strtotime( $date ) );
	}

	/**
	 * Determine PDF/report URL.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function get_coa_url( $post_id ) {
		$file_id = absint( self::get_meta( $post_id, 'file_id' ) );
		$url     = esc_url_raw( self::get_meta( $post_id, 'external_url' ) );
		$source  = self::get_meta( $post_id, 'file_source' );

		if ( 'external' === $source && $url ) {
			return $url;
		}

		if ( $file_id ) {
			$media_url = wp_get_attachment_url( $file_id );
			if ( $media_url ) {
				return esc_url_raw( $media_url );
			}
		}

		return $url;
	}

	/**
	 * Validate required COA fields.
	 *
	 * @param int $post_id Post ID.
	 * @return array<int,string>
	 */
	public static function validate_record( $post_id ) {
		$errors = array();

		$required = array(
			'product_name' => __( 'Product Name is required.', 'coa-manager' ),
			'strength'     => __( 'Strength / Size is required.', 'coa-manager' ),
			'batch_lot'    => __( 'Batch / Lot Number is required.', 'coa-manager' ),
			'laboratory'   => __( 'Laboratory is required.', 'coa-manager' ),
		);

		foreach ( $required as $field => $message ) {
			if ( '' === trim( self::get_meta( $post_id, $field ) ) ) {
				$errors[] = $message;
			}
		}

		$purity = self::get_meta( $post_id, 'purity' );
		if ( '' === $purity || ! is_numeric( $purity ) ) {
			$errors[] = __( 'Purity must be numeric.', 'coa-manager' );
		}

		if ( '' === self::sanitize_date( self::get_meta( $post_id, 'tested_date' ) ) ) {
			$errors[] = __( 'Tested Date must be a valid date.', 'coa-manager' );
		}

		if ( '' === self::get_coa_url( $post_id ) ) {
			$errors[] = __( 'A COA file or external URL is required.', 'coa-manager' );
		}

		return $errors;
	}

	/**
	 * Is a record valid and published.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_valid_record( $post_id ) {
		return 'publish' === get_post_status( $post_id ) && empty( self::validate_record( $post_id ) );
	}

	/**
	 * Get default settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function default_settings() {
		return array(
			'page_title'             => __( 'Certificate of Analysis (COA)', 'coa-manager' ),
			'page_description'       => __( 'All reports are third-party tested for purity and quality.', 'coa-manager' ),
			'show_title'             => 1,
			'show_description'       => 1,
			'show_total'             => 1,
			'show_search'            => 1,
			'show_category_filter'   => 1,
			'show_laboratory_filter' => 1,
			'total_reports_label'    => __( 'Total COA Reports', 'coa-manager' ),
			'desktop_columns'        => 3,
			'tablet_columns'         => 2,
			'mobile_columns'         => 1,
			'primary_color'          => '#123AA8',
			'primary_hover_color'    => '#0B2D86',
			'auto_show_product_coa'  => 0,
			'product_display_type'   => 'button',
			'product_position'       => 'after_summary',
			'delete_on_uninstall'    => 0,
		);
	}

	/**
	 * Get plugin settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_settings() {
		return wp_parse_args( (array) get_option( 'coam_settings', array() ), self::default_settings() );
	}

	/**
	 * Count valid published records.
	 *
	 * @return int
	 */
	public static function count_valid_records() {
		$query = new WP_Query(
			array(
				'post_type'              => self::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'fields'                 => 'ids',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			)
		);

		$count = 0;
		foreach ( $query->posts as $post_id ) {
			if ( self::is_valid_record( $post_id ) ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Latest COA for product by tested date.
	 *
	 * @param int $product_id Product ID.
	 * @return WP_Post|null
	 */
	public static function get_latest_coa_for_product( $product_id ) {
		if ( ! $product_id ) {
			return null;
		}

		$query = new WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'meta_query'     => array(
					array(
						'key'   => self::meta_keys()['product_id'],
						'value' => $product_id,
					),
				),
				'meta_key'       => self::meta_keys()['tested_date'],
				'orderby'        => 'meta_value',
				'order'          => 'DESC',
			)
		);

		foreach ( $query->posts as $post ) {
			if ( self::is_valid_record( $post->ID ) ) {
				return $post;
			}
		}

		return null;
	}
}
