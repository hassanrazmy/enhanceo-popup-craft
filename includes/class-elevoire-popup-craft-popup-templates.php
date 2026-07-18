<?php
/**
 * Starter Templates Library for Elevoire Popup Craft.
 *
 * @package ElevoirePopupCraft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Elevoire_Popup_Templates
 */
class Elevoire_Popup_Templates {

	/**
	 * The single instance of this class.
	 *
	 * @var Elevoire_Popup_Templates|null
	 */
	private static ?Elevoire_Popup_Templates $instance = null;

	/**
	 * Returns the singleton instance.
	 *
	 * @return Elevoire_Popup_Templates
	 */
	public static function instance(): Elevoire_Popup_Templates {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_templates_page' ) );
		add_action( 'admin_init', array( $this, 'handle_import_action' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue admin stylesheet for Template Library.
	 *
	 * @param string $hook_suffix The current page hook.
	 */
	public function enqueue_admin_assets( $hook_suffix ): void {
		if ( 'elevoire_popup_craft_page_elevoire-popup-craft-popup-templates' === $hook_suffix ) {
			wp_enqueue_style(
				'elevoire-popup-craft-popup-templates-admin',
				ELEVOIRE_POPUP_CRAFT_ASSETS_URL . 'admin.css',
				array(),
				ELEVOIRE_POPUP_CRAFT_VERSION
			);
			wp_enqueue_script(
				'elevoire-popup-craft-admin',
				ELEVOIRE_POPUP_CRAFT_ASSETS_URL . 'admin.js',
				array( 'jquery', 'wp-color-picker' ),
				ELEVOIRE_POPUP_CRAFT_VERSION,
				true
			);
			wp_localize_script(
				'elevoire-popup-craft-admin',
				'elevoirePopupCraftTemplatesData',
				self::get_templates()
			);
		}
	}

	/**
	 * Register submenu page under CPT menu.
	 */
	public function register_templates_page(): void {
		add_submenu_page(
			'edit.php?post_type=' . ELEVOIRE_POPUP_CRAFT_CPT_SLUG,
			__( 'Starter Templates', 'elevoire-popup-craft' ),
			__( 'Templates Library', 'elevoire-popup-craft' ),
			'edit_posts',
			'elevoire-popup-craft-popup-templates',
			array( $this, 'render_templates_page' )
		);
	}

	/**
	 * Listen and process template import action.
	 */
	public function handle_import_action(): void {
		if ( ! isset( $_GET['action'] ) || 'elevoire_popup_craft_import_template' !== sanitize_key( wp_unslash( $_GET['action'] ) ) ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'elevoire_popup_craft_import_template_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'elevoire-popup-craft' ) );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to create popups.', 'elevoire-popup-craft' ) );
		}

		$template_id = isset( $_GET['template_id'] ) ? sanitize_key( $_GET['template_id'] ) : '';
		$templates   = self::get_templates();

		if ( ! isset( $templates[ $template_id ] ) ) {
			wp_die( esc_html__( 'Invalid template selected.', 'elevoire-popup-craft' ) );
		}

		$template = $templates[ $template_id ];

		// Create draft post
		$post_id = wp_insert_post( array(
			'post_title'   => sprintf( '%s (Starter)', $template['name'] ),
			'post_content' => $template['content'],
			'post_status'  => 'draft',
			'post_type'    => ELEVOIRE_POPUP_CRAFT_CPT_SLUG,
		) );

		if ( is_wp_error( $post_id ) ) {
			wp_die( esc_html__( 'Failed to import template.', 'elevoire-popup-craft' ) );
		}

		// Save preset metadata settings
		update_post_meta( $post_id, ELEVOIRE_POPUP_CRAFT_META_KEY, $template['settings'] );

		// Redirect to post editor
		wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
		exit;
	}

	/**
	 * Render Templates Library layout inside admin dashboard.
	 */
	public function render_templates_page(): void {
		$templates = self::get_templates();
		$categories = array(
			'all'      => __( 'All Templates', 'elevoire-popup-craft' ),
			'lead'     => __( 'Lead Gen', 'elevoire-popup-craft' ),
			'discount' => __( 'Discounts', 'elevoire-popup-craft' ),
			'promo'    => __( 'Promos', 'elevoire-popup-craft' ),
			'notice'   => __( 'Notices', 'elevoire-popup-craft' ),
			'legal'    => __( 'Legal & GDPR', 'elevoire-popup-craft' ),
			'feedback' => __( 'Feedback', 'elevoire-popup-craft' ),
			'social'   => __( 'Social Links', 'elevoire-popup-craft' ),
		);
		?>
		<div class="wrap elevoire-popup-craft-templates-wrap">
			<div class="elevoire-popup-craft-templates-header">
				<h1><?php esc_html_e( 'Popup Craft Starter Library', 'elevoire-popup-craft' ); ?></h1>
				<p class="description"><?php esc_html_e( 'Choose from our premium, high-converting templates to kickstart your popups instantly.', 'elevoire-popup-craft' ); ?></p>
			</div>

			<!-- Category filters — client-side only, zero page reload -->
			<div class="elevoire-popup-craft-templates-filter-bar">
				<?php foreach ( $categories as $slug => $label ) : ?>
					<button type="button"
						class="button elevoire-popup-craft-filter-btn<?php echo 'all' === $slug ? ' active' : ''; ?>"
						data-filter="<?php echo esc_attr( $slug ); ?>"
					><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
			</div>

			<!-- Templates Grid — all 20 cards rendered; JS toggles visibility -->
			<div class="elevoire-popup-craft-templates-grid" id="elevoire-popup-craft-templates-grid">
				<?php foreach ( $templates as $id => $template ) :
					$import_url = wp_nonce_url(
						add_query_arg(
							array(
								'action'      => 'elevoire_popup_craft_import_template',
								'template_id' => $id,
							),
							admin_url( 'edit.php?post_type=' . ELEVOIRE_POPUP_CRAFT_CPT_SLUG . '&page=elevoire-popup-craft-popup-templates' )
						),
						'elevoire_popup_craft_import_template_nonce'
					);
				?>
					<div class="elevoire-popup-craft-template-card" data-category="<?php echo esc_attr( $template['category_slug'] ); ?>">
						<div class="elevoire-popup-craft-template-preview-meta">
							<span class="elevoire-popup-craft-template-badge"><?php echo esc_html( $template['category_name'] ); ?></span>
							<span class="elevoire-popup-craft-template-badge type-badge"><?php echo esc_html( str_replace( '_', ' ', $template['settings']['type'] ) ); ?></span>
						</div>
						<div class="elevoire-popup-craft-template-card-body">
							<h3 class="elevoire-popup-craft-template-title"><?php echo esc_html( $template['name'] ); ?></h3>
							<p class="elevoire-popup-craft-template-desc"><?php echo esc_html( $template['description'] ); ?></p>
						</div>
						<div class="elevoire-popup-craft-template-card-footer">
							<a href="<?php echo esc_url( $import_url ); ?>" class="button button-primary elevoire-popup-craft-import-btn"><?php esc_html_e( 'Use Template', 'elevoire-popup-craft' ); ?></a>
							<button type="button" class="button button-secondary elevoire-popup-craft-preview-btn" data-template-id="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Preview', 'elevoire-popup-craft' ); ?></button>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Lightbox Container for Live Preview -->
			<div id="elevoire-popup-craft-preview-lightbox" class="elevoire-popup-craft-lightbox" style="display: none;">
				<div class="elevoire-popup-craft-lightbox-overlay" id="elevoire-popup-craft-lightbox-close-overlay"></div>
				<div class="elevoire-popup-craft-lightbox-container" id="elevoire-popup-craft-lightbox-container-box">
					<button type="button" class="elevoire-popup-craft-lightbox-close" id="elevoire-popup-craft-lightbox-close-btn">&times;</button>
					<div class="elevoire-popup-craft-lightbox-content" id="elevoire-popup-craft-lightbox-inner-content"></div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get the array definitions for all 20 premium starter templates.
	 *
	 * @return array
	 */
	public static function get_templates(): array {
		return array(
			'minimal_newsletter' => array(
				'name'          => __( 'Minimalist Newsletter Subscription', 'elevoire-popup-craft' ),
				'category_name' => __( 'Lead Gen', 'elevoire-popup-craft' ),
				'category_slug' => 'lead',
				'description'   => __( 'A clean subscription layout with system fonts and an sleek newsletter form.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#ffffff',
					'text_color'       => '#1e293b',
					'border_radius'    => 16,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'zoom',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center;">' . "\n" .
				'    <div style="font-size: 24px; font-weight: 700; margin-bottom: 8px; color: #1e293b;">Join Our Newsletter</div>' . "\n" .
				'    <p style="color: #64748b; font-size: 14px; margin-bottom: 24px;">Get weekly insights, design templates, and exclusive updates delivered straight to your inbox.</p>' . "\n" .
				'    <form style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: center;" onsubmit="event.preventDefault(); alert(\'Subscribed!\');">' . "\n" .
				'        <input type="email" placeholder="Your email address" required style="flex: 1; min-width: 200px; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none;" />' . "\n" .
				'        <button type="submit" style="padding: 12px 24px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer;">Subscribe</button>' . "\n" .
				'    </form>' . "\n" .
				'</div>',
			),
			'ebook_download' => array(
				'name'          => __( 'Ebook Download Lead Magnet', 'elevoire-popup-craft' ),
				'category_name' => __( 'Lead Gen', 'elevoire-popup-craft' ),
				'category_slug' => 'lead',
				'description'   => __( 'Split-column design featuring a purple-blue gradient cover placeholder for offering books/guides.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#0f172a',
					'text_color'       => '#f8fafc',
					'border_radius'    => 20,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'slide_up',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap;">' . "\n" .
				'    <div style="flex: 1; min-width: 200px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); padding: 48px 24px; border-radius: 12px; text-align: center; color: #fff; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3);">' . "\n" .
				'        <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.8; margin-bottom: 8px;">Free Ebook</div>' . "\n" .
				'        <div style="font-size: 28px; font-weight: 800; line-height: 1.2;">Mastering<br>Web Design</div>' . "\n" .
				'        <div style="font-size: 10px; margin-top: 16px; opacity: 0.7;">2026 EDITION</div>' . "\n" .
				'    </div>' . "\n" .
				'    <div style="flex: 1.5; min-width: 240px;">' . "\n" .
				'        <div style="font-size: 22px; font-weight: 700; margin-bottom: 8px; color: #f8fafc;">Grow Your Design Skills</div>' . "\n" .
				'        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 20px;">Download our comprehensive 120-page guide covering color theory, grid systems, and layout principles.</p>' . "\n" .
				'        <form style="display: flex; flex-direction: column; gap: 10px;" onsubmit="event.preventDefault(); alert(\'Download started!\');">' . "\n" .
				'            <input type="email" placeholder="Enter your email" required style="padding: 10px 14px; border: 1px solid #334155; background: #1e293b; color: #fff; border-radius: 6px; font-size: 14px; outline: none;" />' . "\n" .
				'            <button type="submit" style="padding: 12px; background: #3b82f6; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Download Free PDF</button>' . "\n" .
				'        </form>' . "\n" .
				'    </div>' . "\n" .
				'</div>',
			),
			'vip_club' => array(
				'name'          => __( 'VIP Club Invitation', 'elevoire-popup-craft' ),
				'category_name' => __( 'Lead Gen', 'elevoire-popup-craft' ),
				'category_slug' => 'lead',
				'description'   => __( 'A luxury dark card with gold details suitable for exclusive membership invitations.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#18181b',
					'text_color'       => '#fafafa',
					'border_radius'    => 24,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '0',
					'animation'        => 'zoom',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center; border: 1px solid #d4af37; padding: 16px; border-radius: 12px;">' . "\n" .
				'    <div style="font-family: Georgia, serif; font-size: 28px; font-weight: 300; letter-spacing: 0.05em; color: #d4af37; margin-bottom: 12px;">The VIP Lounge</div>' . "\n" .
				'    <div style="text-transform: uppercase; font-size: 11px; letter-spacing: 0.2em; color: #a1a1aa; margin-bottom: 24px;">By Invitation Only</div>' . "\n" .
				'    <p style="color: #d4d4d8; font-size: 14px; line-height: 1.7; margin-bottom: 28px; font-family: Georgia, serif; font-style: italic;">Unlock access to ultra-exclusive premium templates, private developer webinars, and early access codes.</p>' . "\n" .
				'    <form style="display: flex; flex-direction: column; gap: 12px; max-width: 320px; margin: 0 auto;" onsubmit="event.preventDefault(); alert(\'VIP Invitation Requested!\');">' . "\n" .
				'        <input type="email" placeholder="Your premium email address" required style="padding: 12px; border: 1px solid #3f3f46; background: #27272a; color: #fff; text-align: center; border-radius: 8px; font-size: 14px; outline: none;" />' . "\n" .
				'        <button type="submit" style="padding: 12px; background: #d4af37; color: #18181b; border: none; border-radius: 8px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer;">Request Invite</button>' . "\n" .
				'    </form>' . "\n" .
				'</div>',
			),
			'exit_intent' => array(
				'name'          => __( 'Exit Intent Cart Saver', 'elevoire-popup-craft' ),
				'category_name' => __( 'Discounts', 'elevoire-popup-craft' ),
				'category_slug' => 'discount',
				'description'   => __( 'Urgent notice with a large dashed coupon holder in rose colors to recover abandoning buyers.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#fff1f2',
					'text_color'       => '#9f1239',
					'border_radius'    => 16,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'slide_down',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center;">' . "\n" .
				'    <div style="font-size: 32px; font-weight: 800; color: #e11d48; margin-bottom: 8px;">Wait, Don\'t Go!</div>' . "\n" .
				'    <div style="font-size: 16px; font-weight: 600; color: #4c0519; margin-bottom: 16px;">We noticed you leaving something behind.</div>' . "\n" .
				'    <p style="color: #be123c; font-size: 14px; margin-bottom: 24px; line-height: 1.5;">Take <strong>15% OFF</strong> your entire cart right now. Use this code at checkout to claim your savings:</p>' . "\n" .
				'    <div style="display: inline-block; background: #ffe4e6; border: 2px dashed #f43f5e; padding: 12px 28px; border-radius: 8px; font-size: 22px; font-weight: 800; letter-spacing: 0.05em; color: #e11d48; margin-bottom: 24px; cursor: pointer;" onclick="navigator.clipboard.writeText(\'COMEBACK15\'); alert(\'Code copied: COMEBACK15\');">' . "\n" .
				'        COMEBACK15' . "\n" .
				'    </div>' . "\n" .
				'    <div>' . "\n" .
				'        <a href="#" style="font-size: 14px; color: #9f1239; font-weight: 600; text-decoration: underline;" onclick="event.preventDefault(); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();">No thanks, I want to pay full price</a>' . "\n" .
				'    </div>' . "\n" .
				'</div>',
			),
			'flash_sale' => array(
				'name'          => __( 'Flash Sale Promotional Banner', 'elevoire-popup-craft' ),
				'category_name' => __( 'Promos', 'elevoire-popup-craft' ),
				'category_slug' => 'promo',
				'description'   => __( 'High-contrast red banner targeting top placement with call to action shop button.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'top_banner',
					'bg_color'         => '#dc2626',
					'text_color'       => '#ffffff',
					'border_radius'    => 0,
					'backdrop_blur'    => '0',
					'close_on_overlay' => '0',
					'animation'        => 'slide_down',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="display: flex; justify-content: center; align-items: center; gap: 16px; flex-wrap: wrap; font-size: 15px; font-weight: 600; padding: 4px 0;">' . "\n" .
				'    <span style="background: rgba(0,0,0,0.3); padding: 4px 10px; border-radius: 4px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">URGENT</span>' . "\n" .
				'    <span>⚡ FLASH SALE: Get 30% OFF everything with code <strong>FLASH30</strong>. Only active for the next 2 hours!</span>' . "\n" .
				'    <a href="#" style="background: #ffffff; color: #dc2626; padding: 6px 16px; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: 700;">Shop Now</a>' . "\n" .
				'</div>',
			),
			'coupon_code' => array(
				'name'          => __( 'Standard Coupon Code Box', 'elevoire-popup-craft' ),
				'category_name' => __( 'Discounts', 'elevoire-popup-craft' ),
				'category_slug' => 'discount',
				'description'   => __( 'E-commerce focused centered box showing copyable coupon values.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#ffffff',
					'text_color'       => '#1e293b',
					'border_radius'    => 14,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'zoom',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center;">' . "\n" .
				'    <div style="font-size: 40px; margin-bottom: 8px;">🎉</div>' . "\n" .
				'    <div style="font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">You Unlocked a Coupon!</div>' . "\n" .
				'    <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">Use the code below at checkout to enjoy a 20% discount on your first subscription.</p>' . "\n" .
				'    <div style="display: flex; gap: 8px; justify-content: center; align-items: center; max-width: 320px; margin: 0 auto 16px;">' . "\n" .
				'        <input type="text" value="WELCOME20" readonly id="coupon-field" style="flex: 1; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; text-align: center; font-size: 16px; font-weight: 700; color: #0f172a; background: #f8fafc;" />' . "\n" .
				'        <button onclick="var c = document.getElementById(\'coupon-field\'); c.select(); document.execCommand(\'copy\'); alert(\'Copied WELCOME20!\');" style="padding: 12px 20px; background: #10b981; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Copy</button>' . "\n" .
				'    </div>' . "\n" .
				'</div>',
			),
			'free_shipping' => array(
				'name'          => __( 'Free Shipping Indicator', 'elevoire-popup-craft' ),
				'category_name' => __( 'Promos', 'elevoire-popup-craft' ),
				'category_slug' => 'promo',
				'description'   => __( 'Bottom bar design suggesting threshold metrics for obtaining shipping bonuses.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'bottom_banner',
					'bg_color'         => '#0f172a',
					'text_color'       => '#f8fafc',
					'border_radius'    => 0,
					'backdrop_blur'    => '0',
					'close_on_overlay' => '0',
					'animation'        => 'slide_up',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="display: flex; justify-content: center; align-items: center; gap: 12px; font-size: 14px; padding: 4px 0;">' . "\n" .
				'    <span>🚚 You are only <strong>$20 away</strong> from <strong>FREE SHIPPING</strong>! Add items to your cart.</span>' . "\n" .
				'    <a href="#" style="color: #3b82f6; text-decoration: underline; font-weight: 600;">Continue Shopping</a>' . "\n" .
				'</div>',
			),
			'cart_abandonment' => array(
				'name'          => __( 'Cart Abandonment Reminder', 'elevoire-popup-craft' ),
				'category_name' => __( 'Promos', 'elevoire-popup-craft' ),
				'category_slug' => 'promo',
				'description'   => __( 'Friendly cart recovery popup offering free shipping values.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#ffffff',
					'text_color'       => '#334155',
					'border_radius'    => 16,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'zoom',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center;">' . "\n" .
				'    <div style="font-size: 36px; margin-bottom: 8px;">🛒</div>' . "\n" .
				'    <div style="font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">Did you forget something?</div>' . "\n" .
				'    <p style="color: #64748b; font-size: 14px; margin-bottom: 24px; line-height: 1.5;">The items in your cart are highly popular and may sell out soon. Complete your checkout now and get <strong>Free Express Shipping</strong>.</p>' . "\n" .
				'    <div style="display: flex; gap: 12px; justify-content: center;">' . "\n" .
				'        <a href="#" style="padding: 12px 24px; background: #4f46e5; color: #fff; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 14px;">Return to Cart</a>' . "\n" .
				'        <button onclick="this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="padding: 12px 24px; background: #f1f5f9; color: #475569; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;">Dismiss</button>' . "\n" .
				'    </div>' . "\n" .
				'</div>',
			),
			'maintenance' => array(
				'name'          => __( 'Website Maintenance Alert', 'elevoire-popup-craft' ),
				'category_name' => __( 'Notices', 'elevoire-popup-craft' ),
				'category_slug' => 'notice',
				'description'   => __( 'Temporary notification box with amber warning icons and schedule guidelines.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#fafafa',
					'text_color'       => '#27272a',
					'border_radius'    => 12,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '0',
					'animation'        => 'fade',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center; padding: 8px;">' . "\n" .
				'    <div style="font-size: 40px; margin-bottom: 12px; color: #f59e0b;">⚠️</div>' . "\n" .
				'    <div style="font-size: 24px; font-weight: 800; color: #18181b; margin-bottom: 10px;">Scheduled Maintenance</div>' . "\n" .
				'    <p style="color: #71717a; font-size: 14px; line-height: 1.6; margin-bottom: 24px;">We are currently conducting essential database upgrades. The service will resume shortly. We apologize for any inconvenience caused.</p>' . "\n" .
				'    <div style="font-size: 12px; color: #a1a1aa; border-top: 1px solid #e4e4e7; padding-top: 16px;">Estimated Completion: 06:00 AM UTC</div>' . "\n" .
				'</div>',
			),
			'product_launch' => array(
				'name'          => __( 'New Feature Showcase', 'elevoire-popup-craft' ),
				'category_name' => __( 'Notices', 'elevoire-popup-craft' ),
				'category_slug' => 'notice',
				'description'   => __( 'Showcase panel with bullet lists to outline releases and software versions.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#ffffff',
					'text_color'       => '#0f172a',
					'border_radius'    => 20,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'zoom',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div>' . "\n" .
				'    <span style="display: inline-block; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 99px; font-size: 12px; font-weight: 700; margin-bottom: 12px;">NEW RELEASE</span>' . "\n" .
				'    <div style="font-size: 24px; font-weight: 800; line-height: 1.2; margin-bottom: 8px;">Say Hello to Dark Mode!</div>' . "\n" .
				'    <p style="color: #475569; font-size: 14px; line-height: 1.6; margin-bottom: 20px;">Toggle between light and dark aesthetics seamlessly. Protect your eyes and enhance your coding experience.</p>' . "\n" .
				'    <div style="background: #f1f5f9; padding: 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #475569;">' . "\n" .
				'        <strong>Included updates:</strong>' . "\n" .
				'        <ul style="margin: 8px 0 0 16px; padding: 0;">' . "\n" .
				'            <li>Dynamic theme switcher</li>' . "\n" .
				'            <li>Automated sunset detection</li>' . "\n" .
				'            <li>20+ fully tailored editor colors</li>' . "\n" .
				'        </ul>' . "\n" .
				'    </div>' . "\n" .
				'    <button onclick="this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 100%; padding: 12px; background: #0284c7; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Enable in Settings</button>' . "\n" .
				'</div>',
			),
			'webinar_event' => array(
				'name'          => __( 'Webinar Reservation Form', 'elevoire-popup-craft' ),
				'category_name' => __( 'Notices', 'elevoire-popup-craft' ),
				'category_slug' => 'notice',
				'description'   => __( 'Split info/form layout optimized for event sign-ups and reservations.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#0f172a',
					'text_color'       => '#f8fafc',
					'border_radius'    => 16,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'slide_up',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="display: flex; gap: 20px; flex-wrap: wrap;">' . "\n" .
				'    <div style="flex: 1; min-width: 220px;">' . "\n" .
				'        <div style="color: #60a5fa; font-weight: 700; font-size: 12px; text-transform: uppercase; margin-bottom: 6px;">Live Webinar</div>' . "\n" .
				'        <div style="font-size: 20px; font-weight: 800; margin-bottom: 12px;">Building Advanced WordPress Plugins</div>' . "\n" .
				'        <div style="font-size: 13px; color: #94a3b8; margin-bottom: 16px;">' . "\n" .
				'            🗓️ Thursday, July 23<br>' . "\n" .
				'            🕒 4:00 PM EST / 9:00 PM GMT' . "\n" .
				'        </div>' . "\n" .
				'        <p style="font-size: 13px; color: #94a3b8; line-height: 1.5;">Learn how to construct custom post types, sanitization frameworks, and high-performance JS modules.</p>' . "\n" .
				'    </div>' . "\n" .
				'    <div style="flex: 1; min-width: 220px; background: #1e293b; padding: 20px; border-radius: 8px; display: flex; flex-direction: column; justify-content: center;">' . "\n" .
				'        <div style="font-size: 14px; font-weight: 700; margin-bottom: 12px; text-align: center;">Reserve Your Free Seat</div>' . "\n" .
				'        <form style="display: flex; flex-direction: column; gap: 8px;" onsubmit="event.preventDefault(); alert(\'Seat reserved!\');">' . "\n" .
				'            <input type="text" placeholder="Name" required style="padding: 10px; border: 1px solid #334155; background: #0f172a; color: #fff; border-radius: 6px; font-size: 13px;" />' . "\n" .
				'            <input type="email" placeholder="Email" required style="padding: 10px; border: 1px solid #334155; background: #0f172a; color: #fff; border-radius: 6px; font-size: 13px;" />' . "\n" .
				'            <button type="submit" style="padding: 10px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Register Now</button>' . "\n" .
				'        </form>' . "\n" .
				'    </div>' . "\n" .
				'</div>',
			),
			'holiday_hours' => array(
				'name'          => __( 'Holiday & Scheduled Notices', 'elevoire-popup-craft' ),
				'category_name' => __( 'Notices', 'elevoire-popup-craft' ),
				'category_slug' => 'notice',
				'description'   => __( 'Pink seasonal announcement panel with clean notification text.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#fdf2f8',
					'text_color'       => '#831843',
					'border_radius'    => 12,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'zoom',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center;">' . "\n" .
				'    <div style="font-size: 32px; margin-bottom: 8px;">🎄</div>' . "\n" .
				'    <div style="font-size: 22px; font-weight: 700; color: #9d174d; margin-bottom: 10px;">Holiday Schedule Updates</div>' . "\n" .
				'    <p style="color: #be185d; font-size: 14px; line-height: 1.6; margin-bottom: 20px;">Please note that our shipping center will be closed from December 24 to December 27. Delivery requests received during this period will be processed on December 28.</p>' . "\n" .
				'    <button onclick="this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="padding: 10px 24px; background: #db2777; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">I Understand</button>' . "\n" .
				'</div>',
			),
			'cookie_consent' => array(
				'name'          => __( 'GDPR Cookie Consent Banner', 'elevoire-popup-craft' ),
				'category_name' => __( 'Legal & GDPR', 'elevoire-popup-craft' ),
				'category_slug' => 'legal',
				'description'   => __( 'A clean bottom cookie compliance prompt block featuring Accept and Decline buttons.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'bottom_banner',
					'bg_color'         => '#1e293b',
					'text_color'       => '#f8fafc',
					'border_radius'    => 0,
					'backdrop_blur'    => '0',
					'close_on_overlay' => '0',
					'animation'        => 'slide_up',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; font-size: 13px; max-width: 1200px; margin: 0 auto; padding: 4px 0;">' . "\n" .
				'    <div style="flex: 1; min-width: 250px; text-align: left; line-height: 1.5;">' . "\n" .
				'        🍪 We use cookies to enhance your browsing experience, serve personalized ads, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.' . "\n" .
				'    </div>' . "\n" .
				'    <div style="display: flex; gap: 8px; justify-content: flex-end;">' . "\n" .
				'        <button onclick="this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="padding: 6px 14px; background: #10b981; color: #fff; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">Accept All</button>' . "\n" .
				'        <button onclick="this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="padding: 6px 14px; background: #475569; color: #fff; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">Decline</button>' . "\n" .
				'    </div>' . "\n" .
				'</div>',
			),
			'age_gate' => array(
				'name'          => __( 'Age Verification Guard', 'elevoire-popup-craft' ),
				'category_name' => __( 'Legal & GDPR', 'elevoire-popup-craft' ),
				'category_slug' => 'legal',
				'description'   => __( 'High-impact red verification gate requiring affirmative user action to proceed.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#000000',
					'text_color'       => '#ffffff',
					'border_radius'    => 16,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '0',
					'animation'        => 'zoom',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center; padding: 12px;">' . "\n" .
				'    <div style="font-size: 28px; font-weight: 900; letter-spacing: 0.05em; margin-bottom: 12px; color: #ef4444;">AGE VERIFICATION</div>' . "\n" .
				'    <div style="font-size: 15px; margin-bottom: 24px; color: #d1d5db;">You must be <strong>18 years of age or older</strong> to enter this website. Please confirm your age to continue.</div>' . "\n" .
				'    <div style="display: flex; gap: 12px; justify-content: center; max-width: 280px; margin: 0 auto;">' . "\n" .
				'        <button onclick="this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="flex: 1; padding: 12px; background: #ef4444; color: #fff; border: none; border-radius: 6px; font-weight: 700; cursor: pointer;">I am 18+</button>' . "\n" .
				'        <button onclick="window.location.href=\'https://google.com\';" style="flex: 1; padding: 12px; background: #27272a; color: #a1a1aa; border: none; border-radius: 6px; font-weight: 700; cursor: pointer;">Leave</button>' . "\n" .
				'    </div>' . "\n" .
				'</div>',
			),
			'privacy_update' => array(
				'name'          => __( 'Terms of Service Update Banner', 'elevoire-popup-craft' ),
				'category_name' => __( 'Legal & GDPR', 'elevoire-popup-craft' ),
				'category_slug' => 'legal',
				'description'   => __( 'Top warning banner for legal adjustments and terms agreement notices.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'top_banner',
					'bg_color'         => '#172554',
					'text_color'       => '#eff6ff',
					'border_radius'    => 0,
					'backdrop_blur'    => '0',
					'close_on_overlay' => '0',
					'animation'        => 'slide_down',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="display: flex; justify-content: center; align-items: center; gap: 12px; font-size: 13px; padding: 2px 0;">' . "\n" .
				'    <span>⚖️ We have updated our Terms of Service and Privacy Policy, effective July 2026.</span>' . "\n" .
				'    <a href="#" style="color: #93c5fd; text-decoration: underline; font-weight: 600;">Learn More</a>' . "\n" .
				'    <button onclick="this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="background: rgba(255,255,255,0.15); border: none; color: #fff; padding: 3px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">Dismiss</button>' . "\n" .
				'</div>',
			),
			'nps_survey' => array(
				'name'          => __( 'Interactive Net Promoter Score Card', 'elevoire-popup-craft' ),
				'category_name' => __( 'Feedback', 'elevoire-popup-craft' ),
				'category_slug' => 'feedback',
				'description'   => __( 'Rating list modal designed to fetch scoring metrics from clients.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#ffffff',
					'text_color'       => '#1e293b',
					'border_radius'    => 16,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'zoom',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center;">' . "\n" .
				'    <div style="font-size: 20px; font-weight: 700; margin-bottom: 8px;">How likely are you to recommend us?</div>' . "\n" .
				'    <p style="color: #64748b; font-size: 13px; margin-bottom: 20px;">On a scale from 1 (not likely) to 10 (extremely likely).</p>' . "\n" .
				'    <div style="display: flex; justify-content: center; gap: 4px; flex-wrap: wrap; margin-bottom: 20px;">' . "\n" .
				'        <button onclick="alert(\'Thank you!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px; font-weight: 600; cursor: pointer;">1</button>' . "\n" .
				'        <button onclick="alert(\'Thank you!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px; font-weight: 600; cursor: pointer;">2</button>' . "\n" .
				'        <button onclick="alert(\'Thank you!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px; font-weight: 600; cursor: pointer;">3</button>' . "\n" .
				'        <button onclick="alert(\'Thank you!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px; font-weight: 600; cursor: pointer;">4</button>' . "\n" .
				'        <button onclick="alert(\'Thank you!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px; font-weight: 600; cursor: pointer;">5</button>' . "\n" .
				'        <button onclick="alert(\'Thank you!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px; font-weight: 600; cursor: pointer;">6</button>' . "\n" .
				'        <button onclick="alert(\'Thank you!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px; font-weight: 600; cursor: pointer;">7</button>' . "\n" .
				'        <button onclick="alert(\'Thank you!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px; font-weight: 600; cursor: pointer;">8</button>' . "\n" .
				'        <button onclick="alert(\'Thank you!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 32px; height: 32px; border: 1px solid #cbd5e1; background: #fff; border-radius: 4px; font-weight: 600; cursor: pointer;">9</button>' . "\n" .
				'        <button onclick="alert(\'Thank you!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();" style="width: 38px; height: 32px; border: 1px solid #cbd5e1; background: #4f46e5; color: #fff; border-radius: 4px; font-weight: 600; cursor: pointer;">10</button>' . "\n" .
				'    </div>' . "\n" .
				'    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #94a3b8; max-width: 360px; margin: 0 auto;">' . "\n" .
				'        <span>Not Likely</span>' . "\n" .
				'        <span>Extremely Likely</span>' . "\n" .
				'    </div>' . "\n" .
				'</div>',
			),
			'feedback_form' => array(
				'name'          => __( 'Simple Feedback Collector', 'elevoire-popup-craft' ),
				'category_name' => __( 'Feedback', 'elevoire-popup-craft' ),
				'category_slug' => 'feedback',
				'description'   => __( 'Textarea form optimized to gather comments and user ideas.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#ffffff',
					'text_color'       => '#0f172a',
					'border_radius'    => 16,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'slide_up',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div>' . "\n" .
				'    <div style="font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">We Value Your Feedback!</div>' . "\n" .
				'    <p style="color: #64748b; font-size: 13px; margin-bottom: 16px;">Help us improve our tool. Tell us what you like, or what we can do better.</p>' . "\n" .
				'    <form style="display: flex; flex-direction: column; gap: 10px;" onsubmit="event.preventDefault(); alert(\'Feedback submitted!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();">' . "\n" .
				'        <textarea placeholder="Write your comments here..." required rows="4" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; font-family: inherit; resize: none;"></textarea>' . "\n" .
				'        <button type="submit" style="padding: 10px; background: #0f172a; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px;">Submit Feedback</button>' . "\n" .
				'    </form>' . "\n" .
				'</div>',
			),
			'app_download' => array(
				'name'          => __( 'App Store Badges Grid', 'elevoire-popup-craft' ),
				'category_name' => __( 'Social Links', 'elevoire-popup-craft' ),
				'category_slug' => 'social',
				'description'   => __( 'Showcases links to Android and iOS mobile marketplaces.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#f8fafc',
					'text_color'       => '#0f172a',
					'border_radius'    => 16,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'zoom',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center;">' . "\n" .
				'    <div style="font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Get Our Mobile App</div>' . "\n" .
				'    <p style="color: #64748b; font-size: 13px; margin-bottom: 20px;">Manage your templates, schedules, and notifications directly from your smartphone.</p>' . "\n" .
				'    <div style="display: flex; justify-content: center; gap: 12px; align-items: center; flex-wrap: wrap;">' . "\n" .
				'        <a href="#" style="background: #000; color: #fff; padding: 8px 16px; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-family: sans-serif; text-align: left;">' . "\n" .
				'            <span style="font-size: 20px; line-height: 1;"></span>' . "\n" .
				'            <span><span style="font-size: 8px; display: block; opacity: 0.7;">Download on the</span><strong style="font-size: 12px;">App Store</strong></span>' . "\n" .
				'        </a>' . "\n" .
				'        <a href="#" style="background: #000; color: #fff; padding: 8px 16px; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-family: sans-serif; text-align: left;">' . "\n" .
				'            <span style="font-size: 18px; line-height: 1;">🤖</span>' . "\n" .
				'            <span><span style="font-size: 8px; display: block; opacity: 0.7;">GET IT ON</span><strong style="font-size: 12px;">Google Play</strong></span>' . "\n" .
				'        </a>' . "\n" .
				'    </div>' . "\n" .
				'</div>',
			),
			'social_follow' => array(
				'name'          => __( 'Social Channels Grid', 'elevoire-popup-craft' ),
				'category_name' => __( 'Social Links', 'elevoire-popup-craft' ),
				'category_slug' => 'social',
				'description'   => __( 'Showcase buttons linking to LinkedIn, Twitter, and developer portals.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#ffffff',
					'text_color'       => '#1e293b',
					'border_radius'    => 16,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '1',
					'animation'        => 'zoom',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center;">' . "\n" .
				'    <div style="font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Let\'s Connect!</div>' . "\n" .
				'    <p style="color: #64748b; font-size: 13px; margin-bottom: 20px;">Follow our development journey and get instant release updates on social channels.</p>' . "\n" .
				'    <div style="display: flex; justify-content: center; gap: 12px; font-size: 14px; font-weight: 600; flex-wrap: wrap;">' . "\n" .
				'        <a href="#" style="padding: 8px 16px; background: #1da1f2; color: #fff; border-radius: 6px; text-decoration: none;">Twitter</a>' . "\n" .
				'        <a href="#" style="padding: 8px 16px; background: #0077b5; color: #fff; border-radius: 6px; text-decoration: none;">LinkedIn</a>' . "\n" .
				'        <a href="#" style="padding: 8px 16px; background: #24292e; color: #fff; border-radius: 6px; text-decoration: none;">GitHub</a>' . "\n" .
				'    </div>' . "\n" .
				'</div>',
			),
			'content_gate' => array(
				'name'          => __( 'Content Gate lock modal', 'elevoire-popup-craft' ),
				'category_name' => __( 'Lead Gen', 'elevoire-popup-craft' ),
				'category_slug' => 'lead',
				'description'   => __( 'Full lock overlay preventing navigation until form submission completes.', 'elevoire-popup-craft' ),
				'settings'      => array(
					'type'             => 'center_modal',
					'bg_color'         => '#0f172a',
					'text_color'       => '#f8fafc',
					'border_radius'    => 16,
					'backdrop_blur'    => '1',
					'close_on_overlay' => '0',
					'animation'        => 'fade',
					'delay'            => 0,
					'targeting'        => 'all',
					'specific_ids'     => '',
					'cookie_expiry'    => 30,
	
				),
				'content'       => '<div style="text-align: center;">' . "\n" .
				'    <div style="font-size: 36px; margin-bottom: 12px;">🔒</div>' . "\n" .
				'    <div style="font-size: 22px; font-weight: 800; color: #f8fafc; margin-bottom: 8px;">Subscribe to Unlock Reading</div>' . "\n" .
				'    <p style="color: #94a3b8; font-size: 14px; margin-bottom: 24px; line-height: 1.5;">This premium resource is reserved for members. Provide your email to instantly unlock and read this guide.</p>' . "\n" .
				'    <form style="display: flex; flex-direction: column; gap: 10px; max-width: 320px; margin: 0 auto;" onsubmit="event.preventDefault(); alert(\'Unlocked!\'); this.closest(\'.elevoire-popup-craft-wrapper\').querySelector(\'.elevoire-popup-craft-close\').click();">' . "\n" .
				'        <input type="email" placeholder="Your best email address" required style="padding: 12px; border: 1px solid #334155; background: #1e293b; color: #fff; text-align: center; border-radius: 8px; font-size: 14px; outline: none;" />' . "\n" .
				'        <button type="submit" style="padding: 12px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Unlock Content</button>' . "\n" .
				'    </form>' . "\n" .
				'</div>',
			),
		);
	}
}
