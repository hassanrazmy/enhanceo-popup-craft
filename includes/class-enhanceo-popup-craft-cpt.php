<?php
/**
 * Register Custom Post Type.
 *
 * @package EnhanceoPopupCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Enhanceo_Popup_Craft_CPT
 */
class Enhanceo_Popup_Craft_CPT {

	/**
	 * The single instance of this class.
	 *
	 * @var Enhanceo_Popup_Craft_CPT|null
	 */
	private static ?Enhanceo_Popup_Craft_CPT $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return Enhanceo_Popup_Craft_CPT
	 */
	public static function instance(): Enhanceo_Popup_Craft_CPT {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_cpt' ) );
	}

	/**
	 * Register the custom post type.
	 */
	public function register_cpt(): void {
		$labels = array(
			'name'                  => _x( 'Popups', 'Post Type General Name', 'enhanceo-popup-craft' ),
			'singular_name'         => _x( 'Popup', 'Post Type Singular Name', 'enhanceo-popup-craft' ),
			'menu_name'             => __( 'Popup Craft', 'enhanceo-popup-craft' ),
			'name_admin_bar'        => __( 'Popup', 'enhanceo-popup-craft' ),
			'archives'              => __( 'Popup Archives', 'enhanceo-popup-craft' ),
			'attributes'            => __( 'Popup Attributes', 'enhanceo-popup-craft' ),
			'parent_item_colon'     => __( 'Parent Popup:', 'enhanceo-popup-craft' ),
			'all_items'             => __( 'All Popups', 'enhanceo-popup-craft' ),
			'add_new_item'          => __( 'Add New Popup', 'enhanceo-popup-craft' ),
			'add_new'               => __( 'Add New', 'enhanceo-popup-craft' ),
			'new_item'              => __( 'New Popup', 'enhanceo-popup-craft' ),
			'edit_item'             => __( 'Edit Popup', 'enhanceo-popup-craft' ),
			'update_item'           => __( 'Update Popup', 'enhanceo-popup-craft' ),
			'view_item'             => __( 'View Popup', 'enhanceo-popup-craft' ),
			'view_items'            => __( 'View Popups', 'enhanceo-popup-craft' ),
			'search_items'          => __( 'Search Popup', 'enhanceo-popup-craft' ),
			'not_found'             => __( 'Not found', 'enhanceo-popup-craft' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'enhanceo-popup-craft' ),
			'featured_image'        => __( 'Featured Image', 'enhanceo-popup-craft' ),
			'set_featured_image'    => __( 'Set featured image', 'enhanceo-popup-craft' ),
			'remove_featured_image' => __( 'Remove featured image', 'enhanceo-popup-craft' ),
			'use_featured_image'    => __( 'Use as featured image', 'enhanceo-popup-craft' ),
			'insert_into_item'      => __( 'Insert into popup', 'enhanceo-popup-craft' ),
			'uploaded_to_this_item' => __( 'Uploaded to this popup', 'enhanceo-popup-craft' ),
			'items_list'            => __( 'Popups list', 'enhanceo-popup-craft' ),
			'items_list_navigation' => __( 'Popups list navigation', 'enhanceo-popup-craft' ),
			'filter_items_list'     => __( 'Filter popups list', 'enhanceo-popup-craft' ),
		);
		$args = array(
			'label'                 => __( 'Popup', 'enhanceo-popup-craft' ),
			'description'           => __( 'Enhanceo Popup Craft Popups', 'enhanceo-popup-craft' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 20,
			'menu_icon'             => 'dashicons-format-chat',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'capability_type'       => 'post',
			'show_in_rest'          => true, // Enable Gutenberg editor
		);
		register_post_type( ENHANCEO_POPUP_CRAFT_CPT_SLUG, $args );
	}
}
