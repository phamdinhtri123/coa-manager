<?php
/**
 * Frontend renderer.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rendering API.
 */
class COAM_Renderer {
	/**
	 * Render manager.
	 *
	 * @param array<string,mixed> $args Args.
	 * @return string
	 */
	public static function render_manager( $args = array() ) {
		COAM_Assets::frontend_assets();

		$settings = COAM_Helpers::get_settings();
		$args     = self::normalize_args( $args, $settings );
		$query    = self::query_records( $args );
		$records  = array_filter( $query->posts, array( 'COAM_Helpers', 'is_valid_record' ) );
		$style    = self::style_attr( $args );

		ob_start();
		?>
		<div class="coam-manager" style="<?php echo esc_attr( $style ); ?>">
			<?php echo self::render_header( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="coam-grid" data-coam-grid>
				<?php foreach ( $records as $post_id ) : ?>
					<?php echo self::render_card( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endforeach; ?>
			</div>
			<?php echo self::render_pagination( $query, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="coam-empty" data-coam-empty hidden><?php esc_html_e( 'No COA reports match your search.', 'coa-manager' ); ?></div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Render latest product COA.
	 *
	 * @param int                 $product_id Product ID.
	 * @param array<string,mixed> $args Args.
	 * @return string
	 */
	public static function render_latest_product_coa( $product_id = 0, $args = array() ) {
		if ( ! $product_id && function_exists( 'is_product' ) && is_product() ) {
			$product_id = get_the_ID();
		}

		$post = COAM_Helpers::get_latest_coa_for_product( absint( $product_id ) );
		if ( ! $post ) {
			return '';
		}

		COAM_Assets::frontend_assets();
		$display = isset( $args['display'] ) && 'card' === $args['display'] ? 'card' : 'button';
		if ( 'card' === $display ) {
			return '<div class="coam-product-latest">' . self::render_card( $post->ID ) . '</div>';
		}

		$url = COAM_Helpers::get_coa_url( $post->ID );
		return '<p class="coam-product-latest"><a class="coam-button coam-button-primary" href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'View Latest COA', 'coa-manager' ) . '</a></p>';
	}

	/**
	 * Render card.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function render_card( $post_id ) {
		$url        = COAM_Helpers::get_coa_url( $post_id );
		$product    = COAM_Helpers::get_meta( $post_id, 'product_name' );
		$strength   = COAM_Helpers::get_meta( $post_id, 'strength' );
		$purity     = COAM_Helpers::get_meta( $post_id, 'purity' );
		$batch      = COAM_Helpers::get_meta( $post_id, 'batch_lot' );
		$laboratory = COAM_Helpers::get_meta( $post_id, 'laboratory' );
		$date       = COAM_Helpers::format_date( COAM_Helpers::get_meta( $post_id, 'tested_date' ) );
		$search     = strtolower( trim( $product . ' ' . $batch ) );

		ob_start();
		?>
		<article class="coam-card" data-coam-card data-coam-search="<?php echo esc_attr( $search ); ?>">
			<div class="coam-card-top">
				<span class="coam-verified"><span aria-hidden="true">&#10003;</span> <?php esc_html_e( 'VERIFIED COA', 'coa-manager' ); ?></span>
				<span class="coam-purity"><?php echo esc_html( $purity ); ?>% <?php esc_html_e( 'purity', 'coa-manager' ); ?></span>
			</div>
			<div class="coam-card-title">
				<h3><?php echo esc_html( $product ); ?></h3>
				<p><?php echo esc_html( $strength ); ?></p>
			</div>
			<div class="coam-meta">
				<div><span><?php esc_html_e( 'Batch / Lot', 'coa-manager' ); ?></span><strong><?php echo esc_html( $batch ); ?></strong></div>
				<div><span><?php esc_html_e( 'Tested', 'coa-manager' ); ?></span><strong><?php echo esc_html( $date ); ?></strong></div>
				<div><span><?php esc_html_e( 'Laboratory', 'coa-manager' ); ?></span><strong><?php echo esc_html( $laboratory ); ?></strong></div>
			</div>
			<div class="coam-actions">
				<a class="coam-button coam-button-primary" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View COA', 'coa-manager' ); ?></a>
				<a class="coam-button coam-button-secondary" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" <?php echo absint( COAM_Helpers::get_meta( $post_id, 'file_id' ) ) ? 'download' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php esc_html_e( 'Download PDF', 'coa-manager' ); ?></a>
			</div>
		</article>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Normalize args.
	 *
	 * @param array<string,mixed> $args Args.
	 * @param array<string,mixed> $settings Settings.
	 * @return array<string,mixed>
	 */
	private static function normalize_args( $args, $settings ) {
		$bools = array( 'show_title', 'show_description', 'show_total', 'show_search' );
		foreach ( $bools as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$args[ $key ] = self::to_bool( $args[ $key ] );
			} else {
				$args[ $key ] = (bool) $settings[ $key ];
			}
		}

		$args['page_title']          = sanitize_text_field( $args['page_title'] ?? $settings['page_title'] );
		$args['page_description']    = sanitize_textarea_field( $args['page_description'] ?? $settings['page_description'] );
		$args['total_reports_label'] = sanitize_text_field( $args['total_reports_label'] ?? $settings['total_reports_label'] );
		$args['desktop_columns']     = self::allowed_int( $args['desktop_columns'] ?? $settings['desktop_columns'], array( 2, 3, 4 ), 3 );
		$args['tablet_columns']      = self::allowed_int( $args['tablet_columns'] ?? $settings['tablet_columns'], array( 1, 2, 3 ), 2 );
		$args['mobile_columns']      = self::allowed_int( $args['mobile_columns'] ?? $settings['mobile_columns'], array( 1, 2 ), 1 );
		$args['primary_color']       = sanitize_hex_color( $settings['primary_color'] ) ?: '#123AA8';
		$args['primary_hover_color'] = sanitize_hex_color( $settings['primary_hover_color'] ) ?: '#0B2D86';
		$args['limit']               = isset( $args['limit'] ) ? max( 1, min( 100, absint( $args['limit'] ) ) ) : 24;
		$args['paged']               = isset( $_GET['coam_page'] ) ? max( 1, absint( $_GET['coam_page'] ) ) : 1;

		return $args;
	}

	/**
	 * Query records.
	 *
	 * @param array<string,mixed> $args Args.
	 * @return WP_Query
	 */
	private static function query_records( $args ) {
		$query_args = array(
			'post_type'              => COAM_Helpers::POST_TYPE,
			'post_status'            => 'publish',
			'posts_per_page'         => absint( $args['limit'] ),
			'paged'                  => absint( $args['paged'] ),
			'fields'                 => 'ids',
			'meta_key'               => COAM_Helpers::meta_keys()['tested_date'],
			'orderby'                => 'meta_value',
			'order'                  => 'DESC',
			'no_found_rows'          => false,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => true,
		);

		return new WP_Query( $query_args );
	}

	/**
	 * Pagination.
	 *
	 * @param WP_Query            $query Query.
	 * @param array<string,mixed> $args Args.
	 * @return string
	 */
	private static function render_pagination( $query, $args ) {
		if ( $query->max_num_pages < 2 ) {
			return '';
		}

		$links = paginate_links(
			array(
				'base'      => esc_url_raw( add_query_arg( 'coam_page', '%#%' ) ),
				'format'    => '',
				'current'   => absint( $args['paged'] ),
				'total'     => absint( $query->max_num_pages ),
				'type'      => 'array',
				'prev_text' => __( 'Previous', 'coa-manager' ),
				'next_text' => __( 'Next', 'coa-manager' ),
			)
		);

		if ( empty( $links ) || ! is_array( $links ) ) {
			return '';
		}

		return '<nav class="coam-pagination" aria-label="' . esc_attr__( 'COA pagination', 'coa-manager' ) . '">' . wp_kses_post( implode( '', $links ) ) . '</nav>';
	}

	/**
	 * Header.
	 *
	 * @param array<string,mixed> $args Args.
	 * @return string
	 */
	private static function render_header( $args ) {
		if ( ! $args['show_title'] && ! $args['show_description'] && ! $args['show_total'] && ! $args['show_search'] ) {
			return '';
		}

		$count = COAM_Helpers::count_valid_records();
		ob_start();
		?>
		<header class="coam-header">
			<div class="coam-heading">
				<?php if ( $args['show_title'] ) : ?>
					<h2><?php echo esc_html( $args['page_title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $args['show_description'] ) : ?>
					<p><?php echo esc_html( $args['page_description'] ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $args['show_total'] || $args['show_search'] ) : ?>
				<div class="coam-header-tools">
					<?php if ( $args['show_total'] ) : ?>
						<div class="coam-total">
							<span><?php echo esc_html( $args['total_reports_label'] ); ?></span>
							<strong><?php echo esc_html( $count ); ?> <?php echo esc_html( 1 === $count ? __( 'Report', 'coa-manager' ) : __( 'Reports', 'coa-manager' ) ); ?></strong>
						</div>
					<?php endif; ?>
					<?php if ( $args['show_search'] ) : ?>
						<div class="coam-search-wrap">
							<input type="search" class="coam-search" data-coam-search-input placeholder="<?php esc_attr_e( 'Search COA by product or batch/lot...', 'coa-manager' ); ?>" />
							<button type="button" class="coam-search-button" data-coam-search-button><?php esc_html_e( 'Search', 'coa-manager' ); ?></button>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</header>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * CSS variables.
	 *
	 * @param array<string,mixed> $args Args.
	 * @return string
	 */
	private static function style_attr( $args ) {
		return sprintf(
			'--coam-columns-desktop:%d;--coam-columns-tablet:%d;--coam-columns-mobile:%d;--coam-primary:%s;--coam-primary-hover:%s;',
			absint( $args['desktop_columns'] ),
			absint( $args['tablet_columns'] ),
			absint( $args['mobile_columns'] ),
			$args['primary_color'],
			$args['primary_hover_color']
		);
	}

	/**
	 * Boolean cast from shortcode/API input.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	private static function to_bool( $value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}
		return ! in_array( strtolower( (string) $value ), array( '0', 'false', 'no', 'off' ), true );
	}

	/**
	 * Allowed int.
	 *
	 * @param mixed      $value Value.
	 * @param array<int> $allowed Allowed.
	 * @param int        $default Default.
	 * @return int
	 */
	private static function allowed_int( $value, $allowed, $default ) {
		$value = absint( $value );
		return in_array( $value, $allowed, true ) ? $value : $default;
	}
}
