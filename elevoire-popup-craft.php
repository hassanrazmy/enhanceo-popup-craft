<?php
/**
 * Plugin Name:       Elevoire Popup Craft
 * Plugin URI:        https://wordpress.org/plugins/elevoire-popup-craft/
 * Description:       A lightweight, performant, and secure popup builder. Build beautiful, accessible popups with zero jQuery dependencies.
 * Version:           1.0.0
 * Author:            Elevoire
 * Author URI:        https://elevoire.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       elevoire-popup-craft
 * Requires at least: 6.0
 * Tested up to:      7.0
 * Requires PHP:      7.4
 *
 * @package ElevoirePopupCraft
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define plugin constants.
 */
define( 'ELEVOIRE_POPUP_CRAFT_VERSION',     '1.0.0' );
define( 'ELEVOIRE_POPUP_CRAFT_FILE',        __FILE__ );
define( 'ELEVOIRE_POPUP_CRAFT_PATH',        plugin_dir_path( __FILE__ ) );
define( 'ELEVOIRE_POPUP_CRAFT_URL',         plugin_dir_url( __FILE__ ) );
define( 'ELEVOIRE_POPUP_CRAFT_ASSETS_URL',  ELEVOIRE_POPUP_CRAFT_URL  . 'assets/' );
define( 'ELEVOIRE_POPUP_CRAFT_ASSETS_PATH', ELEVOIRE_POPUP_CRAFT_PATH . 'assets/' );
define( 'ELEVOIRE_POPUP_CRAFT_CPT_SLUG',    'elevoire_popup_craft' );
define( 'ELEVOIRE_POPUP_CRAFT_META_KEY',    '_elevoire_elevoire_popup_craft_settings' );

/**
 * Class Elevoire_Popup_Craft
 *
 * Central bootstrap class. Responsible for loading all sub-modules
 * and hooking them into WordPress at the correct priority.
 *
 * @since 1.0.0
 */
final class Elevoire_Popup_Craft {

	/**
	 * The single instance of this class.
	 *
	 * @var Elevoire_Popup_Craft|null
	 */
	private static ?Elevoire_Popup_Craft $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return Elevoire_Popup_Craft
	 */
	public static function instance(): Elevoire_Popup_Craft {
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
		require_once ELEVOIRE_POPUP_CRAFT_PATH . 'includes/class-elevoire-popup-craft-cpt.php';
		require_once ELEVOIRE_POPUP_CRAFT_PATH . 'includes/class-elevoire-popup-craft-meta-box.php';
		require_once ELEVOIRE_POPUP_CRAFT_PATH . 'includes/class-elevoire-popup-craft-public.php';
		require_once ELEVOIRE_POPUP_CRAFT_PATH . 'includes/class-elevoire-popup-craft-popup-templates.php';
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
		Elevoire_Popup_Craft_CPT::instance();
		Elevoire_Popup_Craft_Meta_Box::instance();
		Elevoire_Popup_Craft_Public::instance();
		Elevoire_Popup_Templates::instance();
	}
}

/**
 * Returns the main instance — global access point.
 *
 * @return Elevoire_Popup_Craft
 */
function elevoire_popup_craft(): Elevoire_Popup_Craft {
	return Elevoire_Popup_Craft::instance();
}

// Boot it up.
elevoire_popup_craft();
