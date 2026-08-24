<?php
/**
 * Post type and taxonomy.
 *
 * @package COAM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register COA post type.
 */
class COAM_Post_Type {
	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( __CLASS__, 'rename_submenu' ), 20 );
		add_action( 'admin_init', array( __CLASS__, 'remove_custom_fields' ) );
	}

	/**
	 * Register CPT and taxonomy.
	 */
	public static function register() {
		register_post_type(
			COAM_Helpers::POST_TYPE,
			array(
				'labels'       => array(
					'name'          => __( 'COA Manager', 'coa-manager' ),
					'singular_name' => __( 'COA Record', 'coa-manager' ),
					'menu_name'     => __( 'COA Manager', 'coa-manager' ),
					'all_items'     => __( 'All COAs', 'coa-manager' ),
					'add_new_item'  => __( 'Add New COA', 'coa-manager' ),
					'edit_item'     => __( 'Edit COA', 'coa-manager' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'menu_icon'    => 'dashicons-media-document',
				'supports'     => array( 'title' ),
				'capability_type' => 'post',
				'rewrite'      => false,
				'has_archive'  => false,
			)
		);

		register_taxonomy(
			COAM_Helpers::TAXONOMY,
			COAM_Helpers::POST_TYPE,
			array(
				'labels'            => array(
					'name'          => __( 'Categories', 'coa-manager' ),
					'singular_name' => __( 'Category', 'coa-manager' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => false,
				'hierarchical'      => true,
				'rewrite'           => false,
			)
		);
	}

	/**
	 * Rename first submenu.
	 */
	public static function rename_submenu() {
		global $submenu;
		if ( isset( $submenu[ 'edit.php?post_type=' . COAM_Helpers::POST_TYPE ][0][0] ) ) {
			$submenu[ 'edit.php?post_type=' . COAM_Helpers::POST_TYPE ][0][0] = __( 'All COAs', 'coa-manager' );
		}
	}

	/**
	 * Remove default custom fields support.
	 */
	public static function remove_custom_fields() {
		remove_post_type_support( COAM_Helpers::POST_TYPE, 'custom-fields' );
	}
}
