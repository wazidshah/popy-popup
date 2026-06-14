<?php
/**
 * Popy Settings – Admin page.
 *
 * @package Popy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popy_Settings {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu',            array( $this, 'register_menu' ) );
		add_action( 'admin_init',            array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/* ── Menu ───────────────────────────────────────────────────── */

	public function register_menu() {
		add_menu_page(
			__( 'Popy Popup', 'popy' ),
			__( 'Popy', 'popy' ),
			'manage_options',
			'popy-settings',
			array( $this, 'render_page' ),
			'dashicons-megaphone',
			82
		);
	}

	/* ── Settings API ───────────────────────────────────────────── */

	public function register_settings() {
		register_setting(
			'popy_group',
			POPY_OPTION_KEY,
			array(
				'sanitize_callback' => array( $this, 'sanitize' ),
			)
		);
	}

	/**
	 * Sanitize all settings before saving.
	 *
	 * @param  array $in  Raw POST values.
	 * @return array      Sanitized values.
	 */
	public function sanitize( $in ) {
		$out = array();

		// Plain-text fields.
		$plain = array(
			'eyebrow', 'title', 'footnote',
			'primary_btn_text', 'secondary_btn_text',
			'dismiss_text', 'icon',
		);
		foreach ( $plain as $key ) {
			$out[ $key ] = sanitize_text_field( $in[ $key ] ?? '' );
		}

		// Fields that allow limited HTML.
		$allowed_html = array(
			'strong' => array(),
			'em'     => array(),
			'br'     => array(),
		);
		$out['subtitle'] = wp_kses( $in['subtitle'] ?? '', $allowed_html );
		$out['body']     = wp_kses( $in['body']     ?? '', $allowed_html );

		// URLs — allow mailto: and tel: schemes.
		$out['primary_btn_url']   = sanitize_url( $in['primary_btn_url']   ?? '' );
		$out['secondary_btn_url'] = sanitize_url( $in['secondary_btn_url'] ?? '' );

		// Colour.
		$out['accent_color'] = sanitize_hex_color( $in['accent_color'] ?? '#1e4d3b' );

		// Integers.
		$out['delay']       = absint( $in['delay']       ?? 10 );
		$out['cookie_days'] = absint( $in['cookie_days'] ?? 7 );

		// Booleans (checkboxes).
		$out['enabled']       = ! empty( $in['enabled'] )       ? 1 : 0;
		$out['show_dismiss']  = ! empty( $in['show_dismiss'] )  ? 1 : 0;
		$out['overlay_close'] = ! empty( $in['overlay_close'] ) ? 1 : 0;

		return $out;
	}

	/* ── Assets ─────────────────────────────────────────────────── */

	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_popy-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_script(
			'popy-admin',
			POPY_URL . 'admin/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			POPY_VERSION,
			true
		);

		wp_enqueue_style(
			'popy-admin',
			POPY_URL . 'admin/admin.css',
			array(),
			POPY_VERSION
		);
	}

	/* ── Admin page HTML ────────────────────────────────────────── */

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$o = wp_parse_args( get_option( POPY_OPTION_KEY, array() ), popy_defaults() );
		?>
		<div class="wrap popy-wrap">

			<div class="popy-header">
				<div class="popy-header-inner">
					<span class="popy-logo" aria-hidden="true">🎯</span>
					<div>
						<h1><?php esc_html_e( 'Popy', 'popy' ); ?></h1>
						<p><?php esc_html_e( 'Timed Popups That Respect Your Visitors', 'popy' ); ?></p>
					</div>
					<div class="popy-header-version">
						<?php /* translators: %s: plugin version number */ ?>
						<span><?php printf( esc_html__( 'v%s', 'popy' ), esc_html( POPY_VERSION ) ); ?></span>
					</div>
				</div>
			</div>

			<div class="popy-layout">

				<!-- ── Settings form ── -->
				<div class="popy-form-col">
					<form method="post" action="options.php" id="popyForm">
						<?php settings_fields( 'popy_group' ); ?>

						<!-- BEHAVIOUR -->
						<div class="popy-card">
							<h2><?php esc_html_e( '⚙️ Behaviour', 'popy' ); ?></h2>

							<div class="popy-row popy-toggle-row">
								<label for="popy_enabled"><?php esc_html_e( 'Enable Popup', 'popy' ); ?></label>
								<label class="popy-switch">
									<input type="checkbox" id="popy_enabled" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[enabled]" value="1" <?php checked( 1, $o['enabled'] ); ?>>
									<span></span>
								</label>
							</div>

							<div class="popy-row">
								<label for="popy_delay"><?php esc_html_e( 'Delay before showing', 'popy' ); ?> <small><?php esc_html_e( '(seconds)', 'popy' ); ?></small></label>
								<input type="number" id="popy_delay" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[delay]" value="<?php echo esc_attr( $o['delay'] ); ?>" min="0" max="3600" class="popy-small-input">
							</div>

							<div class="popy-row">
								<label for="popy_cookie_days"><?php esc_html_e( 'Cookie lifetime', 'popy' ); ?> <small><?php esc_html_e( '(days — 0 = show every visit)', 'popy' ); ?></small></label>
								<input type="number" id="popy_cookie_days" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[cookie_days]" value="<?php echo esc_attr( $o['cookie_days'] ); ?>" min="0" max="365" class="popy-small-input">
							</div>

							<div class="popy-row popy-toggle-row">
								<label for="popy_overlay_close"><?php esc_html_e( 'Close when clicking overlay', 'popy' ); ?></label>
								<label class="popy-switch">
									<input type="checkbox" id="popy_overlay_close" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[overlay_close]" value="1" <?php checked( 1, $o['overlay_close'] ); ?>>
									<span></span>
								</label>
							</div>
						</div>

						<!-- CONTENT -->
						<div class="popy-card">
							<h2><?php esc_html_e( '✏️ Content', 'popy' ); ?></h2>

							<div class="popy-row">
								<label for="popyIcon"><?php esc_html_e( 'Icon / Emoji', 'popy' ); ?></label>
								<input type="text" id="popyIcon" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[icon]" value="<?php echo esc_attr( $o['icon'] ); ?>" class="popy-small-input" maxlength="8">
							</div>

							<div class="popy-row">
								<label for="popyEyebrow"><?php esc_html_e( 'Eyebrow', 'popy' ); ?> <small><?php esc_html_e( '(small text above title)', 'popy' ); ?></small></label>
								<input type="text" id="popyEyebrow" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[eyebrow]" value="<?php echo esc_attr( $o['eyebrow'] ); ?>" class="popy-full-input">
							</div>

							<div class="popy-row">
								<label for="popyTitle"><?php esc_html_e( 'Title', 'popy' ); ?></label>
								<input type="text" id="popyTitle" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[title]" value="<?php echo esc_attr( $o['title'] ); ?>" class="popy-full-input">
							</div>

							<div class="popy-row">
								<label for="popySubtitle"><?php esc_html_e( 'Subtitle', 'popy' ); ?> <small><?php esc_html_e( '(HTML: <strong>, <em> allowed)', 'popy' ); ?></small></label>
								<input type="text" id="popySubtitle" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[subtitle]" value="<?php echo esc_attr( $o['subtitle'] ); ?>" class="popy-full-input">
							</div>

							<div class="popy-row">
								<label for="popyBody"><?php esc_html_e( 'Body line', 'popy' ); ?> <small><?php esc_html_e( '(HTML: <strong>, <em> allowed)', 'popy' ); ?></small></label>
								<input type="text" id="popyBody" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[body]" value="<?php echo esc_attr( $o['body'] ); ?>" class="popy-full-input">
							</div>

							<div class="popy-row">
								<label for="popyFootnote"><?php esc_html_e( 'Footnote', 'popy' ); ?> <small><?php esc_html_e( '(bottom small text)', 'popy' ); ?></small></label>
								<input type="text" id="popyFootnote" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[footnote]" value="<?php echo esc_attr( $o['footnote'] ); ?>" class="popy-full-input">
							</div>
						</div>

						<!-- BUTTONS -->
						<div class="popy-card">
							<h2><?php esc_html_e( '🔘 Buttons', 'popy' ); ?></h2>

							<div class="popy-row">
								<label for="popyPrimaryText"><?php esc_html_e( 'Primary button text', 'popy' ); ?></label>
								<input type="text" id="popyPrimaryText" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[primary_btn_text]" value="<?php echo esc_attr( $o['primary_btn_text'] ); ?>" class="popy-full-input">
							</div>
							<div class="popy-row">
								<label for="popyPrimaryUrl"><?php esc_html_e( 'Primary button URL', 'popy' ); ?> <small><?php esc_html_e( '(mailto: and tel: supported)', 'popy' ); ?></small></label>
								<input type="text" id="popyPrimaryUrl" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[primary_btn_url]" value="<?php echo esc_attr( $o['primary_btn_url'] ); ?>" class="popy-full-input">
							</div>

							<hr class="popy-divider">

							<div class="popy-row">
								<label for="popySecondaryText"><?php esc_html_e( 'Secondary button text', 'popy' ); ?></label>
								<input type="text" id="popySecondaryText" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[secondary_btn_text]" value="<?php echo esc_attr( $o['secondary_btn_text'] ); ?>" class="popy-full-input">
							</div>
							<div class="popy-row">
								<label for="popySecondaryUrl"><?php esc_html_e( 'Secondary button URL', 'popy' ); ?></label>
								<input type="text" id="popySecondaryUrl" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[secondary_btn_url]" value="<?php echo esc_attr( $o['secondary_btn_url'] ); ?>" class="popy-full-input">
							</div>

							<hr class="popy-divider">

							<div class="popy-row popy-toggle-row">
								<label for="popy_show_dismiss"><?php esc_html_e( 'Show dismiss link', 'popy' ); ?></label>
								<label class="popy-switch">
									<input type="checkbox" id="popy_show_dismiss" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[show_dismiss]" value="1" <?php checked( 1, $o['show_dismiss'] ); ?>>
									<span></span>
								</label>
							</div>
							<div class="popy-row">
								<label for="popyDismissText"><?php esc_html_e( 'Dismiss link text', 'popy' ); ?></label>
								<input type="text" id="popyDismissText" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[dismiss_text]" value="<?php echo esc_attr( $o['dismiss_text'] ); ?>" class="popy-small-input">
							</div>
						</div>

						<!-- APPEARANCE -->
						<div class="popy-card">
							<h2><?php esc_html_e( '🎨 Appearance', 'popy' ); ?></h2>
							<div class="popy-row">
								<label for="popyAccent"><?php esc_html_e( 'Accent colour', 'popy' ); ?> <small><?php esc_html_e( '(primary button + eyebrow)', 'popy' ); ?></small></label>
								<input type="text" id="popyAccent" name="<?php echo esc_attr( POPY_OPTION_KEY ); ?>[accent_color]" value="<?php echo esc_attr( $o['accent_color'] ); ?>" class="popy-color" data-default-color="<?php echo esc_attr( $o['accent_color'] ); ?>">
							</div>
						</div>

						<?php submit_button( __( 'Save Changes', 'popy' ), 'primary popy-save' ); ?>
					</form>
				</div>

				<!-- ── Live Preview ── -->
				<div class="popy-preview-col">
					<div class="popy-card popy-preview-card">
						<h2><?php esc_html_e( '👁 Live Preview', 'popy' ); ?></h2>

						<div class="popy-preview-stage" aria-hidden="true">
							<div class="popy-preview-overlay">
								<div class="popy-preview-popup" id="popyPreviewBox">

									<button class="popy-preview-x" tabindex="-1">&#x2715;</button>

									<div class="popy-preview-icon" id="popyPrevIcon"><?php echo esc_html( $o['icon'] ); ?></div>
									<div class="popy-preview-eyebrow" id="popyPrevEyebrow" style="color:<?php echo esc_attr( $o['accent_color'] ); ?>"><?php echo esc_html( $o['eyebrow'] ); ?></div>
									<h3 class="popy-preview-title" id="popyPrevTitle"><?php echo esc_html( $o['title'] ); ?></h3>
									<p class="popy-preview-subtitle" id="popyPrevSubtitle"><?php echo wp_kses_post( $o['subtitle'] ); ?></p>
									<p class="popy-preview-body" id="popyPrevBody"><?php echo wp_kses_post( $o['body'] ); ?></p>

									<hr class="popy-preview-rule">

									<a href="#" class="popy-preview-primary" id="popyPrevPrimary" style="background:<?php echo esc_attr( $o['accent_color'] ); ?>" tabindex="-1"><?php echo esc_html( $o['primary_btn_text'] ); ?></a>
									<a href="#" class="popy-preview-secondary" id="popyPrevSecondary" tabindex="-1"><?php echo esc_html( $o['secondary_btn_text'] ); ?></a>

									<p class="popy-preview-footnote">
										<span id="popyPrevFootnote"><?php echo esc_html( $o['footnote'] ); ?></span>
										<?php if ( $o['show_dismiss'] ) : ?>
										&nbsp;&middot;&nbsp;<a href="#" class="popy-preview-dismiss" id="popyPrevDismiss" tabindex="-1"><?php echo esc_html( $o['dismiss_text'] ); ?></a>
										<?php endif; ?>
									</p>

								</div>
							</div>
						</div>

						<div class="popy-reset-wrap">
							<button type="button" id="popyResetCookie" class="button">
								<?php esc_html_e( '🔄 Reset cookie (re-test popup)', 'popy' ); ?>
							</button>
							<p><?php esc_html_e( 'Clears the dismissed cookie on this browser so the popup appears again on your next visit.', 'popy' ); ?></p>
						</div>
					</div>
				</div>

			</div><!-- .popy-layout -->
		</div><!-- .wrap -->
		<?php
	}
}
