<?php
/**
 * Register Custom Post Type.
 *
 * @package ElevoirePopupCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Elevoire_Popup_Craft_CPT
 */
class Elevoire_Popup_Craft_CPT {

	/**
	 * The single instance of this class.
	 *
	 * @var Elevoire_Popup_Craft_CPT|null
	 */
	private static ?Elevoire_Popup_Craft_CPT $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return Elevoire_Popup_Craft_CPT
	 */
	public static function instance(): Elevoire_Popup_Craft_CPT {
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
			'name'                  => _x( 'Popups', 'Post Type General Name', 'elevoire-popup-craft' ),
			'singular_name'         => _x( 'Popup', 'Post Type Singular Name', 'elevoire-popup-craft' ),
			'menu_name'             => __( 'Popup Craft', 'elevoire-popup-craft' ),
			'name_admin_bar'        => __( 'Popup', 'elevoire-popup-craft' ),
			'archives'              => __( 'Popup Archives', 'elevoire-popup-craft' ),
			'attributes'            => __( 'Popup Attributes', 'elevoire-popup-craft' ),
			'parent_item_colon'     => __( 'Parent Popup:', 'elevoire-popup-craft' ),
			'all_items'             => __( 'All Popups', 'elevoire-popup-craft' ),
			'add_new_item'          => __( 'Add New Popup', 'elevoire-popup-craft' ),
			'add_new'               => __( 'Add New', 'elevoire-popup-craft' ),
			'new_item'              => __( 'New Popup', 'elevoire-popup-craft' ),
			'edit_item'             => __( 'Edit Popup', 'elevoire-popup-craft' ),
			'update_item'           => __( 'Update Popup', 'elevoire-popup-craft' ),
			'view_item'             => __( 'View Popup', 'elevoire-popup-craft' ),
			'view_items'            => __( 'View Popups', 'elevoire-popup-craft' ),
			'search_items'          => __( 'Search Popup', 'elevoire-popup-craft' ),
			'not_found'             => __( 'Not found', 'elevoire-popup-craft' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'elevoire-popup-craft' ),
			'featured_image'        => __( 'Featured Image', 'elevoire-popup-craft' ),
			'set_featured_image'    => __( 'Set featured image', 'elevoire-popup-craft' ),
			'remove_featured_image' => __( 'Remove featured image', 'elevoire-popup-craft' ),
			'use_featured_image'    => __( 'Use as featured image', 'elevoire-popup-craft' ),
			'insert_into_item'      => __( 'Insert into popup', 'elevoire-popup-craft' ),
			'uploaded_to_this_item' => __( 'Uploaded to this popup', 'elevoire-popup-craft' ),
			'items_list'            => __( 'Popups list', 'elevoire-popup-craft' ),
			'items_list_navigation' => __( 'Popups list navigation', 'elevoire-popup-craft' ),
			'filter_items_list'     => __( 'Filter popups list', 'elevoire-popup-craft' ),
		);
		$args = array(
			'label'                 => __( 'Popup', 'elevoire-popup-craft' ),
			'description'           => __( 'Elevoire Popup Craft Popups', 'elevoire-popup-craft' ),
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
		register_post_type( ELEVOIRE_POPUP_CRAFT_CPT_SLUG, $args );
	}
}
