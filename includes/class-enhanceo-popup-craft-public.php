<?php
/**
 * Frontend logic for Enhanceo Popup Craft.
 *
 * @package EnhanceoPopupCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Enhanceo_Popup_Craft_Public
 */
class Enhanceo_Popup_Craft_Public {

	/**
	 * The single instance of this class.
	 *
	 * @var Enhanceo_Popup_Craft_Public|null
	 */
	private static ?Enhanceo_Popup_Craft_Public $instance = null;

	/**
	 * The active popup post to display, if any.
	 *
	 * @var WP_Post|null
	 */
	private ?WP_Post $active_popup = null;

	/**
	 * Settings for the active popup.
	 *
	 * @var array
	 */
	private array $active_settings = array();

	/**
	 * Returns the singleton instance.
	 *
	 * @return Enhanceo_Popup_Craft_Public
	 */
	public static function instance(): Enhanceo_Popup_Craft_Public {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		// Hook late to wp to determine if we need to show a popup.
		add_action( 'wp', array( $this, 'determine_active_popup' ) );
		
		// Hook to enqueue scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Hook to output the HTML.
		add_action( 'wp_footer', array( $this, 'render_popup' ) );
	}

	/**
	 * Determine which popup (if any) should be displayed on the current request.
	 */
	public function determine_active_popup(): void {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}

		$args = array(
			'post_type'      => ENHANCEO_POPUP_CRAFT_CPT_SLUG,
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$popups = get_posts( $args );

		foreach ( $popups as $popup ) {
			$settings = get_post_meta( $popup->ID, ENHANCEO_POPUP_CRAFT_META_KEY, true );
			
			if ( ! is_array( $settings ) ) {
				continue;
			}

			$targeting = $settings['targeting'] ?? 'all';
			
			if ( 'all' === $targeting ) {
				$this->active_popup    = $popup;
				$this->active_settings = $settings;
				break;
			}

			if ( 'specific' === $targeting && ( is_single() || is_page() ) ) {
				$specific_ids = $settings['specific_ids'] ?? '';
				$ids_array    = array_map( 'intval', array_map( 'trim', explode( ',', $specific_ids ) ) );
				
				if ( in_array( get_queried_object_id(), $ids_array, true ) ) {
					$this->active_popup    = $popup;
					$this->active_settings = $settings;
					break;
				}
			}
		}
	}

	/**
	 * Enqueue frontend scripts and styles if a popup is active.
	 */
	public function enqueue_scripts(): void {
		if ( ! $this->active_popup ) {
			return;
		}

		// Main CSS
		wp_enqueue_style(
			'enhanceo-popup-craft',
			ENHANCEO_POPUP_CRAFT_ASSETS_URL . 'public.css',
			array(),
			ENHANCEO_POPUP_CRAFT_VERSION
		);

		// Vanilla JS
		wp_enqueue_script(
			'enhanceo-popup-craft',
			ENHANCEO_POPUP_CRAFT_ASSETS_URL . 'public.js',
			array(),
			ENHANCEO_POPUP_CRAFT_VERSION,
			true // In footer
		);

		// Pass data to JS.
		wp_localize_script(
			'enhanceo-popup-craft',
			'enhanceoPopupCraftData',
			array(
				'id'               => $this->active_popup->ID,
				'delay'            => absint( $this->active_settings['delay'] ?? 0 ) * 1000,
				'cookie_expiry'    => absint( $this->active_settings['cookie_expiry'] ?? 30 ),
				'close_on_overlay' => (bool) ( $this->active_settings['close_on_overlay'] ?? '1' ),
			)
		);
	}

	/**
	 * Render the popup HTML in the footer.
	 */
	public function render_popup(): void {
		if ( ! $this->active_popup ) {
			echo '<!-- Enhanceo Popup Craft: No active popup found for this page. -->';
			return;
		}

		$type             = esc_attr( $this->active_settings['type'] ?? 'center_modal' );
		$bg_color         = esc_attr( $this->active_settings['bg_color'] ?? '#ffffff' );
		$text_color       = esc_attr( $this->active_settings['text_color'] ?? '#1e293b' );
		$border_radius    = absint( $this->active_settings['border_radius'] ?? 12 );
		$backdrop_blur    = (bool) ( $this->active_settings['backdrop_blur'] ?? '1' );
		$close_on_overlay = (bool) ( $this->active_settings['close_on_overlay'] ?? '1' );
		$animation        = esc_attr( $this->active_settings['animation'] ?? 'zoom' );
		$custom_css       = $this->active_settings['custom_css'] ?? '';

		$content    = do_blocks( $this->active_popup->post_content );
		$content    = do_shortcode( $content );
		$popup_id   = (int) $this->active_popup->ID;

		// We use inline CSS variables for customization so the main stylesheet stays static.
		$style = sprintf(
			'--popup-bg: %s; --popup-text: %s; --popup-border-radius: %dpx;',
			$bg_color,
			$text_color,
			$border_radius
		);

		$classes = array(
			'popup-craft-wrapper',
			'popup-craft-type-' . $type,
			'popup-craft-anim-' . $animation,
		);

		if ( $backdrop_blur ) {
			$classes[] = 'popup-craft-has-blur';
		}

		if ( $close_on_overlay ) {
			$classes[] = 'popup-craft-close-on-overlay';
		}

		?>
		<div id="popup-craft-wrapper-<?php echo esc_attr( $popup_id ); ?>" 
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" 
			style="<?php echo esc_attr( $style ); ?>"
			data-popup-id="<?php echo esc_attr( $popup_id ); ?>"
			aria-hidden="true"
			role="dialog"
			aria-modal="true">
			
			<div class="popup-craft-overlay" <?php echo $close_on_overlay ? 'data-popup-close' : ''; ?>></div>
			
			<div class="popup-craft-content-box">
				<button type="button" class="popup-craft-close" data-popup-close aria-label="<?php esc_attr_e( 'Close popup', 'enhanceo-popup-craft' ); ?>">
					<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
						<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
					</svg>
				</button>
				<div class="popup-craft-inner">
					<?php echo wp_kses_post( $content ); ?>
				</div>
			</div>

		</div>
		<?php
		// Custom CSS Output
		if ( ! empty( $custom_css ) ) {
			printf(
				'<style type="text/css" id="popup-craft-custom-css-%d">%s</style>',
				absint( $popup_id ),
				esc_html( $custom_css )
			);
		}
	}
}
