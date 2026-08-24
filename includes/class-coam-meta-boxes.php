<?php
/**
 * Admin meta boxes.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * COA meta boxes.
 */
class COAM_Meta_Boxes {
	/**
	 * Avoid recursive status update.
	 *
	 * @var bool
	 */
	private static $updating_status = false;

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . COAM_Helpers::POST_TYPE, array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
	}

	/**
	 * Add boxes.
	 */
	public static function add_meta_boxes() {
		add_meta_box( 'coam_details', __( 'COA Details', 'coa-manager' ), array( __CLASS__, 'render_details' ), COAM_Helpers::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'coam_product', __( 'WooCommerce Product', 'coa-manager' ), array( __CLASS__, 'render_product' ), COAM_Helpers::POST_TYPE, 'side', 'default' );
		add_meta_box( 'coam_notes', __( 'Internal Notes', 'coa-manager' ), array( __CLASS__, 'render_notes' ), COAM_Helpers::POST_TYPE, 'normal', 'default' );
	}

	/**
	 * Render details.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_details( $post ) {
		wp_nonce_field( 'coam_save_meta', 'coam_nonce' );
		$keys   = COAM_Helpers::meta_keys();
		$source = COAM_Helpers::get_meta( $post->ID, 'file_source' );
		$source = in_array( $source, array( 'media', 'external' ), true ) ? $source : 'media';
		$fields = array(
			'product_name' => __( 'Product Name', 'coa-manager' ),
			'strength'     => __( 'Strength / Size', 'coa-manager' ),
			'purity'       => __( 'Purity', 'coa-manager' ),
			'batch_lot'    => __( 'Batch / Lot Number', 'coa-manager' ),
			'laboratory'   => __( 'Laboratory', 'coa-manager' ),
			'tested_date'  => __( 'Tested Date', 'coa-manager' ),
		);
		?>
		<div class="coam-admin-grid">
			<?php foreach ( $fields as $field => $label ) : ?>
				<p class="coam-field">
					<label for="<?php echo esc_attr( $keys[ $field ] ); ?>"><?php echo esc_html( $label ); ?> <span class="required">*</span></label>
					<input
						type="<?php echo 'purity' === $field ? 'number' : ( 'tested_date' === $field ? 'date' : 'text' ); ?>"
						<?php echo 'purity' === $field ? 'step="0.01" min="0" max="100"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						id="<?php echo esc_attr( $keys[ $field ] ); ?>"
						name="<?php echo esc_attr( $keys[ $field ] ); ?>"
						value="<?php echo esc_attr( COAM_Helpers::get_meta( $post->ID, $field ) ); ?>"
						class="widefat"
						required
					/>
				</p>
			<?php endforeach; ?>
		</div>
		<div class="coam-file-source">
			<strong><?php esc_html_e( 'COA File / URL', 'coa-manager' ); ?> <span class="required">*</span></strong>
			<p>
				<label><input type="radio" name="<?php echo esc_attr( $keys['file_source'] ); ?>" value="media" <?php checked( $source, 'media' ); ?> /> <?php esc_html_e( 'WordPress Media Library PDF', 'coa-manager' ); ?></label>
				<label><input type="radio" name="<?php echo esc_attr( $keys['file_source'] ); ?>" value="external" <?php checked( $source, 'external' ); ?> /> <?php esc_html_e( 'External URL', 'coa-manager' ); ?></label>
			</p>
			<div class="coam-media-row">
				<input type="hidden" id="coam-file-id" name="<?php echo esc_attr( $keys['file_id'] ); ?>" value="<?php echo esc_attr( absint( COAM_Helpers::get_meta( $post->ID, 'file_id' ) ) ); ?>" />
				<input type="text" id="coam-file-url" class="widefat" readonly value="<?php echo esc_url( wp_get_attachment_url( absint( COAM_Helpers::get_meta( $post->ID, 'file_id' ) ) ) ); ?>" />
				<button type="button" class="button coam-select-file"><?php esc_html_e( 'Select PDF', 'coa-manager' ); ?></button>
				<button type="button" class="button coam-clear-file"><?php esc_html_e( 'Clear', 'coa-manager' ); ?></button>
			</div>
			<p>
				<input type="url" class="widefat" name="<?php echo esc_attr( $keys['external_url'] ); ?>" value="<?php echo esc_url( COAM_Helpers::get_meta( $post->ID, 'external_url' ) ); ?>" placeholder="https://example.com/report.pdf" />
			</p>
		</div>
		<?php
	}

	/**
	 * Render product selector.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_product( $post ) {
		$keys       = COAM_Helpers::meta_keys();
		$product_id = absint( COAM_Helpers::get_meta( $post->ID, 'product_id' ) );
		?>
		<p>
			<label for="<?php echo esc_attr( $keys['product_id'] ); ?>"><?php esc_html_e( 'Product ID', 'coa-manager' ); ?></label>
			<input list="coam-products" type="number" min="0" class="widefat" id="<?php echo esc_attr( $keys['product_id'] ); ?>" name="<?php echo esc_attr( $keys['product_id'] ); ?>" value="<?php echo esc_attr( $product_id ); ?>" />
			<datalist id="coam-products">
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<?php
					$products = wc_get_products( array( 'limit' => 50, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) );
					foreach ( $products as $product ) :
						?>
						<option value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( $product->get_name() ); ?></option>
					<?php endforeach; ?>
				<?php endif; ?>
			</datalist>
		</p>
		<?php if ( ! class_exists( 'WooCommerce' ) ) : ?>
			<p class="description"><?php esc_html_e( 'WooCommerce is inactive. The COA record remains usable.', 'coa-manager' ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render notes.
	 *
	 * @param WP_Post $post Post.
	 */
	public static function render_notes( $post ) {
		$keys = COAM_Helpers::meta_keys();
		?>
		<textarea class="widefat" rows="5" name="<?php echo esc_attr( $keys['internal_notes'] ); ?>"><?php echo esc_textarea( COAM_Helpers::get_meta( $post->ID, 'internal_notes' ) ); ?></textarea>
		<?php
	}

	/**
	 * Save meta.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post.
	 */
	public static function save( $post_id, $post ) {
		if ( self::$updating_status || wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['coam_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['coam_nonce'] ) ), 'coam_save_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$keys = COAM_Helpers::meta_keys();
		foreach ( array( 'product_name', 'strength', 'batch_lot', 'laboratory' ) as $field ) {
			$value = isset( $_POST[ $keys[ $field ] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $keys[ $field ] ] ) ) : '';
			update_post_meta( $post_id, $keys[ $field ], $value );
		}

		$purity = isset( $_POST[ $keys['purity'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $keys['purity'] ] ) ) : '';
		update_post_meta( $post_id, $keys['purity'], is_numeric( $purity ) ? (string) (float) $purity : '' );
		update_post_meta( $post_id, $keys['tested_date'], isset( $_POST[ $keys['tested_date'] ] ) ? COAM_Helpers::sanitize_date( wp_unslash( $_POST[ $keys['tested_date'] ] ) ) : '' );
		update_post_meta( $post_id, $keys['file_id'], isset( $_POST[ $keys['file_id'] ] ) ? absint( $_POST[ $keys['file_id'] ] ) : 0 );
		update_post_meta( $post_id, $keys['external_url'], isset( $_POST[ $keys['external_url'] ] ) ? esc_url_raw( wp_unslash( $_POST[ $keys['external_url'] ] ) ) : '' );
		update_post_meta( $post_id, $keys['file_source'], isset( $_POST[ $keys['file_source'] ] ) && 'external' === $_POST[ $keys['file_source'] ] ? 'external' : 'media' );
		update_post_meta( $post_id, $keys['product_id'], isset( $_POST[ $keys['product_id'] ] ) ? absint( $_POST[ $keys['product_id'] ] ) : 0 );
		update_post_meta( $post_id, $keys['internal_notes'], isset( $_POST[ $keys['internal_notes'] ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $keys['internal_notes'] ] ) ) : '' );

		if ( 'publish' === $post->post_status ) {
			$errors = COAM_Helpers::validate_record( $post_id );
			if ( ! empty( $errors ) ) {
				self::$updating_status = true;
				wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
				self::$updating_status = false;
				set_transient( 'coam_validation_' . get_current_user_id(), $errors, 60 );
			}
		}
	}

	/**
	 * Admin notices.
	 */
	public static function admin_notices() {
		$errors = get_transient( 'coam_validation_' . get_current_user_id() );
		if ( empty( $errors ) || ! is_array( $errors ) ) {
			return;
		}

		delete_transient( 'coam_validation_' . get_current_user_id() );
		echo '<div class="notice notice-error"><p><strong>' . esc_html__( 'COA was saved as draft. Please fix:', 'coa-manager' ) . '</strong></p><ul>';
		foreach ( $errors as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';
		}
		echo '</ul></div>';
	}
}
