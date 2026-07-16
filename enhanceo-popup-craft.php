<?php
/**
 * Plugin Name:       Enhanceo Popup Craft
 * Plugin URI:        https://wordpress.org/plugins/enhanceo-popup-craft/
 * Description:       A lightweight, performant, and secure popup builder. Build beautiful, accessible popups with zero jQuery dependencies.
 * Version:           1.0.0
 * Author:            Enhanceo
 * Author URI:        https://profiles.wordpress.org/enhanceo/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       enhanceo-popup-craft
 * Requires at least: 6.0
 * Requires PHP:      7.4
 *
 * @package EnhanceoPopupCraft
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define plugin constants.
 */
define( 'ENHANCEO_POPUP_CRAFT_VERSION',     '1.0.0' );
define( 'ENHANCEO_POPUP_CRAFT_FILE',        __FILE__ );
define( 'ENHANCEO_POPUP_CRAFT_PATH',        plugin_dir_path( __FILE__ ) );
define( 'ENHANCEO_POPUP_CRAFT_URL',         plugin_dir_url( __FILE__ ) );
define( 'ENHANCEO_POPUP_CRAFT_ASSETS_URL',  ENHANCEO_POPUP_CRAFT_URL  . 'assets/' );
define( 'ENHANCEO_POPUP_CRAFT_ASSETS_PATH', ENHANCEO_POPUP_CRAFT_PATH . 'assets/' );
define( 'ENHANCEO_POPUP_CRAFT_CPT_SLUG',    'enhanceo_popup' );
define( 'ENHANCEO_POPUP_CRAFT_META_KEY',    '_enhanceo_popup_settings' );

/**
 * Class Enhanceo_Popup_Craft
 *
 * Central bootstrap class. Responsible for loading all sub-modules
 * and hooking them into WordPress at the correct priority.
 *
 * @since 1.0.0
 */
final class Enhanceo_Popup_Craft {

	/**
	 * The single instance of this class.
	 *
	 * @var Enhanceo_Popup_Craft|null
	 */
	private static ?Enhanceo_Popup_Craft $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return Enhanceo_Popup_Craft
	 */
	public static function instance(): Enhanceo_Popup_Craft {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor — use ::instance().
	 */
	private function __construct() {
		$this->load_includes();
		$this->init_hooks();
	}

	/**
	 * Load all required PHP classes/files.
	 */
	private function load_includes(): void {
		require_once ENHANCEO_POPUP_CRAFT_PATH . 'includes/class-enhanceo-popup-craft-cpt.php';
		require_once ENHANCEO_POPUP_CRAFT_PATH . 'includes/class-enhanceo-popup-craft-meta-box.php';
		require_once ENHANCEO_POPUP_CRAFT_PATH . 'includes/class-enhanceo-popup-craft-public.php';
		require_once ENHANCEO_POPUP_CRAFT_PATH . 'includes/class-enhanceo-popup-templates.php';
	}

	/**
	 * Register top-level WordPress hooks.
	 * Note: load_plugin_textdomain() is not needed since WP 4.6; WordPress
	 * loads translations automatically for plugins hosted on WordPress.org.
	 */
	private function init_hooks(): void {
		$this->boot_modules();
	}

	/**
	 * Instantiate all feature modules.
	 */
	public function boot_modules(): void {
		Enhanceo_Popup_Craft_CPT::instance();
		Enhanceo_Popup_Craft_Meta_Box::instance();
		Enhanceo_Popup_Craft_Public::instance();
		Enhanceo_Popup_Templates::instance();
	}
}

/**
 * Returns the main instance — global access point.
 *
 * @return Enhanceo_Popup_Craft
 */
function enhanceo_popup_craft(): Enhanceo_Popup_Craft {
	return Enhanceo_Popup_Craft::instance();
}

// Boot it up.
enhanceo_popup_craft();
