<?php
/**
 * Register Meta Boxes for settings.
 *
 * @package EnhanceoPopupCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Enhanceo_Popup_Craft_Meta_Box
 */
class Enhanceo_Popup_Craft_Meta_Box {

	/**
	 * The single instance of this class.
	 *
	 * @var Enhanceo_Popup_Craft_Meta_Box|null
	 */
	private static ?Enhanceo_Popup_Craft_Meta_Box $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return Enhanceo_Popup_Craft_Meta_Box
	 */
	public static function instance(): Enhanceo_Popup_Craft_Meta_Box {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . ENHANCEO_POPUP_CRAFT_CPT_SLUG, array( $this, 'save_meta_box_data' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue admin scripts for color picker.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_admin_scripts( $hook_suffix ): void {
		if ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			$screen = get_current_screen();
			if ( is_object( $screen ) && ENHANCEO_POPUP_CRAFT_CPT_SLUG === $screen->post_type ) {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script(
					'enhanceo-popup-craft-admin',
					ENHANCEO_POPUP_CRAFT_ASSETS_URL . 'admin.js',
					array( 'jquery', 'wp-color-picker' ),
					ENHANCEO_POPUP_CRAFT_VERSION,
					true
				);
			}
		}
	}

	/**
	 * Add meta boxes.
	 */
	public function add_meta_boxes(): void {
		add_meta_box(
			'popup_craft_settings',
			__( 'Popup Settings', 'enhanceo-popup-craft' ),
			array( $this, 'render_meta_box' ),
			ENHANCEO_POPUP_CRAFT_CPT_SLUG,
			'side',
			'high'
		);
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box( $post ): void {
		wp_nonce_field( 'popup_craft_save_data', 'popup_craft_meta_nonce' );

		$settings = get_post_meta( $post->ID, ENHANCEO_POPUP_CRAFT_META_KEY, true );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$defaults = array(
			'type'             => 'center_modal',
			'delay'            => 0,
			'targeting'        => 'all',
			'specific_ids'     => '',
			'cookie_expiry'    => 30,
			'bg_color'         => '#ffffff',
			'text_color'       => '#1e293b',
			'backdrop_blur'    => '1',
			'border_radius'    => 12,
			'close_on_overlay' => '1',
			'animation'        => 'zoom',
			'custom_css'       => '',
		);

		$settings = wp_parse_args( $settings, $defaults );

		// Type
		echo '<p><strong>' . esc_html__( 'Popup Type', 'enhanceo-popup-craft' ) . '</strong><br>';
		echo '<select name="popup_craft_settings[type]" style="width: 100%;">';
		$types = array(
			'center_modal'  => __( 'Center Modal', 'enhanceo-popup-craft' ),
			'top_banner'    => __( 'Top Banner', 'enhanceo-popup-craft' ),
			'bottom_banner' => __( 'Bottom Banner', 'enhanceo-popup-craft' ),
		);
		foreach ( $types as $val => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $val ),
				selected( $settings['type'], $val, false ),
				esc_html( $label )
			);
		}
		echo '</select></p>';

		// Entrance Animation
		echo '<p><strong>' . esc_html__( 'Entrance Animation', 'enhanceo-popup-craft' ) . '</strong><br>';
		echo '<select name="popup_craft_settings[animation]" style="width: 100%;">';
		$animations = array(
			'zoom'       => __( 'Zoom In', 'enhanceo-popup-craft' ),
			'fade'       => __( 'Fade In', 'enhanceo-popup-craft' ),
			'slide_up'   => __( 'Slide Up', 'enhanceo-popup-craft' ),
			'slide_down' => __( 'Slide Down', 'enhanceo-popup-craft' ),
		);
		foreach ( $animations as $val => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $val ),
				selected( $settings['animation'], $val, false ),
				esc_html( $label )
			);
		}
		echo '</select></p>';

		// Delay
		echo '<p><strong>' . esc_html__( 'Trigger Delay (seconds)', 'enhanceo-popup-craft' ) . '</strong><br>';
		printf(
			'<input type="number" name="popup_craft_settings[delay]" value="%d" min="0" step="1" style="width: 100%%;">',
			absint( $settings['delay'] )
		);
		echo '</p>';

		// Border Radius
		echo '<p><strong>' . esc_html__( 'Border Radius (px)', 'enhanceo-popup-craft' ) . '</strong><br>';
		printf(
			'<input type="number" name="popup_craft_settings[border_radius]" value="%d" min="0" step="1" style="width: 100%%;">',
			absint( $settings['border_radius'] )
		);
		echo '</p>';

		// Backdrop Blur (Glassmorphism)
		echo '<p><label>';
		printf(
			'<input type="checkbox" name="popup_craft_settings[backdrop_blur]" value="1" %s> ',
			checked( $settings['backdrop_blur'], '1', false )
		);
		echo '<strong>' . esc_html__( 'Enable Glassmorphic Backdrop Blur', 'enhanceo-popup-craft' ) . '</strong>';
		echo '</label></p>';

		// Close on Overlay Click
		echo '<p><label>';
		printf(
			'<input type="checkbox" name="popup_craft_settings[close_on_overlay]" value="1" %s> ',
			checked( $settings['close_on_overlay'], '1', false )
		);
		echo '<strong>' . esc_html__( 'Close when clicking overlay backdrop', 'enhanceo-popup-craft' ) . '</strong>';
		echo '</label></p>';

		// Targeting
		echo '<p><strong>' . esc_html__( 'Targeting', 'enhanceo-popup-craft' ) . '</strong><br>';
		echo '<select name="popup_craft_settings[targeting]" style="width: 100%;">';
		$targeting_opts = array(
			'all'      => __( 'All Pages', 'enhanceo-popup-craft' ),
			'specific' => __( 'Specific Post/Page IDs', 'enhanceo-popup-craft' ),
		);
		foreach ( $targeting_opts as $val => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $val ),
				selected( $settings['targeting'], $val, false ),
				esc_html( $label )
			);
		}
		echo '</select></p>';

		// Specific IDs
		echo '<p><strong>' . esc_html__( 'Specific IDs (comma-separated)', 'enhanceo-popup-craft' ) . '</strong><br>';
		printf(
			'<input type="text" name="popup_craft_settings[specific_ids]" value="%s" style="width: 100%%;">',
			esc_attr( $settings['specific_ids'] )
		);
		echo '</p>';

		// Cookie Expiry
		echo '<p><strong>' . esc_html__( 'Cookie Expiry (days)', 'enhanceo-popup-craft' ) . '</strong><br>';
		printf(
			'<input type="number" name="popup_craft_settings[cookie_expiry]" value="%d" min="0" step="1" style="width: 100%%;">',
			absint( $settings['cookie_expiry'] )
		);
		echo '<br><span class="description" style="font-size: 11px; color: #666;">' . esc_html__( 'Enter 0 to make the popup appear on every refresh.', 'enhanceo-popup-craft' ) . '</span></p>';

		// Background Color
		echo '<p><strong>' . esc_html__( 'Background Color', 'enhanceo-popup-craft' ) . '</strong><br>';
		printf(
			'<input type="text" name="popup_craft_settings[bg_color]" value="%s" class="popup-craft-color-picker">',
			esc_attr( $settings['bg_color'] )
		);
		echo '</p>';

		// Text Color
		echo '<p><strong>' . esc_html__( 'Text Color', 'enhanceo-popup-craft' ) . '</strong><br>';
		printf(
			'<input type="text" name="popup_craft_settings[text_color]" value="%s" class="popup-craft-color-picker">',
			esc_attr( $settings['text_color'] )
		);
		echo '</p>';

		// Custom CSS
		echo '<p><strong>' . esc_html__( 'Custom CSS', 'enhanceo-popup-craft' ) . '</strong><br>';
		printf(
			'<textarea name="popup_craft_settings[custom_css]" rows="5" style="width: 100%%; font-family: monospace; font-size: 12px;">%s</textarea>',
			esc_textarea( $settings['custom_css'] )
		);
		echo '</p>';
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_box_data( $post_id ): void {
		if ( ! isset( $_POST['popup_craft_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['popup_craft_meta_nonce'] ) ), 'popup_craft_save_data' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['popup_craft_settings'] ) && is_array( $_POST['popup_craft_settings'] ) ) {
			$raw_data = wp_unslash( $_POST['popup_craft_settings'] );
			
			$sanitized_data = array(
				'type'             => isset( $raw_data['type'] ) ? sanitize_key( $raw_data['type'] ) : 'center_modal',
				'delay'            => isset( $raw_data['delay'] ) ? absint( $raw_data['delay'] ) : 0,
				'targeting'        => isset( $raw_data['targeting'] ) ? sanitize_key( $raw_data['targeting'] ) : 'all',
				'specific_ids'     => isset( $raw_data['specific_ids'] ) ? sanitize_text_field( $raw_data['specific_ids'] ) : '',
				'cookie_expiry'    => isset( $raw_data['cookie_expiry'] ) ? absint( $raw_data['cookie_expiry'] ) : 30,
				'bg_color'         => isset( $raw_data['bg_color'] ) ? sanitize_hex_color( $raw_data['bg_color'] ) : '#ffffff',
				'text_color'       => isset( $raw_data['text_color'] ) ? sanitize_hex_color( $raw_data['text_color'] ) : '#1e293b',
				'backdrop_blur'    => isset( $raw_data['backdrop_blur'] ) ? '1' : '0',
				'border_radius'    => isset( $raw_data['border_radius'] ) ? absint( $raw_data['border_radius'] ) : 12,
				'close_on_overlay' => isset( $raw_data['close_on_overlay'] ) ? '1' : '0',
				'animation'        => isset( $raw_data['animation'] ) ? sanitize_key( $raw_data['animation'] ) : 'zoom',
				'custom_css'       => isset( $raw_data['custom_css'] ) ? wp_strip_all_tags( $raw_data['custom_css'] ) : '',
			);

			update_post_meta( $post_id, ENHANCEO_POPUP_CRAFT_META_KEY, $sanitized_data );
		}
	}
}
