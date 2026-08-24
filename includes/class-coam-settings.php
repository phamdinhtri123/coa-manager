<?php
/**
 * Settings page.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin settings.
 */
class COAM_Settings {
	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Add defaults once.
	 */
	public static function add_defaults() {
		if ( false === get_option( 'coam_settings', false ) ) {
			add_option( 'coam_settings', COAM_Helpers::default_settings() );
		}
	}

	/**
	 * Menu.
	 */
	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=' . COAM_Helpers::POST_TYPE,
			__( 'Settings', 'coa-manager' ),
			__( 'Settings', 'coa-manager' ),
			'manage_options',
			'coam-settings',
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Register setting.
	 */
	public static function register() {
		register_setting( 'coam_settings', 'coam_settings', array( __CLASS__, 'sanitize' ) );
	}

	/**
	 * Sanitize.
	 *
	 * @param array<string,mixed> $input Input.
	 * @return array<string,mixed>
	 */
	public static function sanitize( $input ) {
		$defaults = COAM_Helpers::default_settings();
		$input    = is_array( $input ) ? $input : array();
		$output   = $defaults;

		$output['page_title']             = sanitize_text_field( $input['page_title'] ?? $defaults['page_title'] );
		$output['page_description']       = sanitize_textarea_field( $input['page_description'] ?? $defaults['page_description'] );
		$output['total_reports_label']    = sanitize_text_field( $input['total_reports_label'] ?? $defaults['total_reports_label'] );
		$output['show_title']             = empty( $input['show_title'] ) ? 0 : 1;
		$output['show_description']       = empty( $input['show_description'] ) ? 0 : 1;
		$output['show_total']             = empty( $input['show_total'] ) ? 0 : 1;
		$output['show_search']            = empty( $input['show_search'] ) ? 0 : 1;
		$output['auto_show_product_coa']  = empty( $input['auto_show_product_coa'] ) ? 0 : 1;
		$output['delete_on_uninstall']    = empty( $input['delete_on_uninstall'] ) ? 0 : 1;
		$output['desktop_columns']        = in_array( absint( $input['desktop_columns'] ?? 3 ), array( 2, 3, 4 ), true ) ? absint( $input['desktop_columns'] ) : 3;
		$output['tablet_columns']         = in_array( absint( $input['tablet_columns'] ?? 2 ), array( 1, 2, 3 ), true ) ? absint( $input['tablet_columns'] ) : 2;
		$output['mobile_columns']         = in_array( absint( $input['mobile_columns'] ?? 1 ), array( 1, 2 ), true ) ? absint( $input['mobile_columns'] ) : 1;
		$output['primary_color']          = sanitize_hex_color( $input['primary_color'] ?? $defaults['primary_color'] ) ?: $defaults['primary_color'];
		$output['primary_hover_color']    = sanitize_hex_color( $input['primary_hover_color'] ?? $defaults['primary_hover_color'] ) ?: $defaults['primary_hover_color'];
		$output['product_display_type']   = in_array( $input['product_display_type'] ?? 'button', array( 'button', 'card' ), true ) ? sanitize_key( $input['product_display_type'] ) : 'button';
		$output['product_position']       = in_array( $input['product_position'] ?? 'after_summary', array( 'after_summary', 'after_tabs', 'before_related' ), true ) ? sanitize_key( $input['product_position'] ) : 'after_summary';

		return $output;
	}

	/**
	 * Render page.
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = COAM_Helpers::get_settings();
		?>
		<div class="wrap coam-settings">
			<h1><?php esc_html_e( 'COA Manager Settings', 'coa-manager' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'coam_settings' ); ?>
				<h2><?php esc_html_e( 'Page Header', 'coa-manager' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php self::text_row( 'page_title', __( 'Page Title', 'coa-manager' ), $settings['page_title'] ); ?>
					<?php self::textarea_row( 'page_description', __( 'Description', 'coa-manager' ), $settings['page_description'] ); ?>
					<?php self::text_row( 'total_reports_label', __( 'Total Reports Label', 'coa-manager' ), $settings['total_reports_label'] ); ?>
					<?php self::checkbox_row( 'show_title', __( 'Show Page Title', 'coa-manager' ), $settings['show_title'] ); ?>
					<?php self::checkbox_row( 'show_description', __( 'Show Description', 'coa-manager' ), $settings['show_description'] ); ?>
					<?php self::checkbox_row( 'show_total', __( 'Show Total Reports', 'coa-manager' ), $settings['show_total'] ); ?>
					<?php self::checkbox_row( 'show_search', __( 'Show Search', 'coa-manager' ), $settings['show_search'] ); ?>
				</table>

				<h2><?php esc_html_e( 'Display', 'coa-manager' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php self::select_row( 'desktop_columns', __( 'Desktop Columns', 'coa-manager' ), $settings['desktop_columns'], array( 2, 3, 4 ) ); ?>
					<?php self::select_row( 'tablet_columns', __( 'Tablet Columns', 'coa-manager' ), $settings['tablet_columns'], array( 1, 2, 3 ) ); ?>
					<?php self::select_row( 'mobile_columns', __( 'Mobile Columns', 'coa-manager' ), $settings['mobile_columns'], array( 1, 2 ) ); ?>
				</table>

				<h2><?php esc_html_e( 'Appearance', 'coa-manager' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php self::text_row( 'primary_color', __( 'Primary Button Color', 'coa-manager' ), $settings['primary_color'], 'coam-color-field' ); ?>
					<?php self::text_row( 'primary_hover_color', __( 'Primary Button Hover Color', 'coa-manager' ), $settings['primary_hover_color'], 'coam-color-field' ); ?>
				</table>

				<h2><?php esc_html_e( 'WooCommerce Integration', 'coa-manager' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php self::checkbox_row( 'auto_show_product_coa', __( 'Automatically show latest COA', 'coa-manager' ), $settings['auto_show_product_coa'] ); ?>
					<?php self::choice_row( 'product_display_type', __( 'Display Type', 'coa-manager' ), $settings['product_display_type'], array( 'button' => __( 'View Latest COA button', 'coa-manager' ), 'card' => __( 'Full COA card', 'coa-manager' ) ) ); ?>
					<?php self::choice_row( 'product_position', __( 'Position', 'coa-manager' ), $settings['product_position'], array( 'after_summary' => __( 'After product summary', 'coa-manager' ), 'after_tabs' => __( 'After product tabs', 'coa-manager' ), 'before_related' => __( 'Before related products', 'coa-manager' ) ) ); ?>
				</table>

				<h2><?php esc_html_e( 'Uninstall', 'coa-manager' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php self::checkbox_row( 'delete_on_uninstall', __( 'Delete plugin data when uninstalling', 'coa-manager' ), $settings['delete_on_uninstall'] ); ?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Field helpers.
	 */
	private static function name( $key ) {
		return 'coam_settings[' . esc_attr( $key ) . ']';
	}

	private static function text_row( $key, $label, $value, $class = '' ) {
		echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><input class="regular-text ' . esc_attr( $class ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( self::name( $key ) ) . '" value="' . esc_attr( $value ) . '" /></td></tr>';
	}

	private static function textarea_row( $key, $label, $value ) {
		echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><textarea class="large-text" rows="4" id="' . esc_attr( $key ) . '" name="' . esc_attr( self::name( $key ) ) . '">' . esc_textarea( $value ) . '</textarea></td></tr>';
	}

	private static function checkbox_row( $key, $label, $value ) {
		echo '<tr><th>' . esc_html( $label ) . '</th><td><label><input type="checkbox" name="' . esc_attr( self::name( $key ) ) . '" value="1" ' . checked( 1, (int) $value, false ) . ' /> ' . esc_html__( 'On', 'coa-manager' ) . '</label></td></tr>';
	}

	private static function select_row( $key, $label, $value, $options ) {
		echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><select id="' . esc_attr( $key ) . '" name="' . esc_attr( self::name( $key ) ) . '">';
		foreach ( $options as $option ) {
			echo '<option value="' . esc_attr( $option ) . '" ' . selected( (int) $value, (int) $option, false ) . '>' . esc_html( $option ) . '</option>';
		}
		echo '</select></td></tr>';
	}

	private static function choice_row( $key, $label, $value, $options ) {
		echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td><select id="' . esc_attr( $key ) . '" name="' . esc_attr( self::name( $key ) ) . '">';
		foreach ( $options as $option => $text ) {
			echo '<option value="' . esc_attr( $option ) . '" ' . selected( $value, $option, false ) . '>' . esc_html( $text ) . '</option>';
		}
		echo '</select></td></tr>';
	}
}
