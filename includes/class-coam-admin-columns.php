<?php
/**
 * Admin columns.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin list table customizations.
 */
class COAM_Admin_Columns {
	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'manage_' . COAM_Helpers::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . COAM_Helpers::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . COAM_Helpers::POST_TYPE . '_sortable_columns', array( __CLASS__, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'sort_columns' ) );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'filters' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'apply_filters' ) );
	}

	/**
	 * Columns.
	 *
	 * @param array<string,string> $columns Columns.
	 * @return array<string,string>
	 */
	public static function columns( $columns ) {
		return array(
			'cb'          => $columns['cb'],
			'title'       => __( 'Title', 'coa-manager' ),
			'product'     => __( 'Product', 'coa-manager' ),
			'strength'    => __( 'Strength', 'coa-manager' ),
			'purity'      => __( 'Purity', 'coa-manager' ),
			'batch_lot'   => __( 'Batch / Lot', 'coa-manager' ),
			'laboratory'  => __( 'Laboratory', 'coa-manager' ),
			'tested_date' => __( 'Tested Date', 'coa-manager' ),
			'category'    => __( 'Category', 'coa-manager' ),
			'validity'    => __( 'Status', 'coa-manager' ),
			'date'        => __( 'Date', 'coa-manager' ),
		);
	}

	/**
	 * Content.
	 *
	 * @param string $column Column.
	 * @param int    $post_id Post ID.
	 */
	public static function column_content( $column, $post_id ) {
		if ( in_array( $column, array( 'product', 'strength', 'purity', 'batch_lot', 'laboratory', 'tested_date' ), true ) ) {
			$field = 'product' === $column ? 'product_name' : $column;
			$value = COAM_Helpers::get_meta( $post_id, $field );
			if ( 'purity' === $column && '' !== $value ) {
				$value .= '%';
			}
			if ( 'tested_date' === $column ) {
				$value = COAM_Helpers::format_date( $value );
			}
			echo esc_html( $value );
		} elseif ( 'category' === $column ) {
			echo wp_kses_post( get_the_term_list( $post_id, COAM_Helpers::TAXONOMY, '', ', ' ) );
		} elseif ( 'validity' === $column ) {
			echo COAM_Helpers::is_valid_record( $post_id ) ? '<span class="coam-status-ok">' . esc_html__( 'Verified', 'coa-manager' ) . '</span>' : '<span class="coam-status-bad">' . esc_html__( 'Incomplete', 'coa-manager' ) . '</span>';
		}
	}

	/**
	 * Sortable columns.
	 *
	 * @param array<string,string> $columns Columns.
	 * @return array<string,string>
	 */
	public static function sortable_columns( $columns ) {
		$columns['product']     = 'coam_product';
		$columns['purity']      = 'coam_purity';
		$columns['tested_date'] = 'coam_tested_date';
		return $columns;
	}

	/**
	 * Apply sort.
	 *
	 * @param WP_Query $query Query.
	 */
	public static function sort_columns( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || COAM_Helpers::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}
		$keys    = COAM_Helpers::meta_keys();
		$orderby = $query->get( 'orderby' );
		if ( 'coam_product' === $orderby ) {
			$query->set( 'meta_key', $keys['product_name'] );
			$query->set( 'orderby', 'meta_value' );
		} elseif ( 'coam_purity' === $orderby ) {
			$query->set( 'meta_key', $keys['purity'] );
			$query->set( 'orderby', 'meta_value_num' );
		} elseif ( 'coam_tested_date' === $orderby ) {
			$query->set( 'meta_key', $keys['tested_date'] );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Filters.
	 */
	public static function filters() {
		global $typenow;
		if ( COAM_Helpers::POST_TYPE !== $typenow ) {
			return;
		}

		wp_dropdown_categories(
			array(
				'show_option_all' => __( 'All Categories', 'coa-manager' ),
				'taxonomy'        => COAM_Helpers::TAXONOMY,
				'name'            => 'coam_category',
				'orderby'         => 'name',
				'selected'        => isset( $_GET['coam_category'] ) ? absint( $_GET['coam_category'] ) : 0,
				'hierarchical'    => true,
				'hide_empty'      => false,
			)
		);

		$laboratory = isset( $_GET['coam_laboratory'] ) ? sanitize_text_field( wp_unslash( $_GET['coam_laboratory'] ) ) : '';
		echo '<input type="search" name="coam_laboratory" value="' . esc_attr( $laboratory ) . '" placeholder="' . esc_attr__( 'Laboratory', 'coa-manager' ) . '" />';
	}

	/**
	 * Apply filters.
	 *
	 * @param WP_Query $query Query.
	 */
	public static function apply_filters( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() || COAM_Helpers::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( isset( $_GET['coam_category'] ) && absint( $_GET['coam_category'] ) ) {
			$query->set(
				'tax_query',
				array(
					array(
						'taxonomy' => COAM_Helpers::TAXONOMY,
						'field'    => 'term_id',
						'terms'    => absint( $_GET['coam_category'] ),
					),
				)
			);
		}

		if ( isset( $_GET['coam_laboratory'] ) && '' !== sanitize_text_field( wp_unslash( $_GET['coam_laboratory'] ) ) ) {
			$meta_query   = (array) $query->get( 'meta_query' );
			$meta_query[] = array(
				'key'     => COAM_Helpers::meta_keys()['laboratory'],
				'value'   => sanitize_text_field( wp_unslash( $_GET['coam_laboratory'] ) ),
				'compare' => 'LIKE',
			);
			$query->set( 'meta_query', $meta_query );
		}
	}
}
