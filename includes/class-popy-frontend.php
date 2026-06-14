<?php
/**
 * Popy Frontend – enqueues assets and renders the popup HTML.
 *
 * @package Popy
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Popy_Frontend {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer',          array( $this, 'render' ) );
	}

	/**
	 * Merged settings with defaults.
	 *
	 * @return array
	 */
	private function opts() {
		return wp_parse_args( get_option( POPY_OPTION_KEY, array() ), popy_defaults() );
	}

	/**
	 * Enqueue frontend CSS and JS only when the popup is enabled.
	 */
	public function enqueue() {
		$o = $this->opts();
		if ( empty( $o['enabled'] ) ) {
			return;
		}

		wp_enqueue_style(
			'popy',
			POPY_URL . 'public/css/popup.css',
			array(),
			POPY_VERSION
		);

		wp_enqueue_script(
			'popy',
			POPY_URL . 'public/js/popup.js',
			array( 'jquery' ),
			POPY_VERSION,
			true
		);

		wp_localize_script(
			'popy',
			'popyConfig',
			array(
				'delay'        => absint( $o['delay'] ) * 1000, // convert to ms
				'cookieDays'   => absint( $o['cookie_days'] ),
				'overlayClose' => ! empty( $o['overlay_close'] ),
			)
		);
	}

	/**
	 * Output the popup markup in the footer.
	 */
	public function render() {
		$o = $this->opts();
		if ( empty( $o['enabled'] ) ) {
			return;
		}

		// Sanitize accent colour for inline styles.
		$accent = sanitize_hex_color( $o['accent_color'] );
		?>
		<div id="popyOverlay" class="popy-overlay" role="dialog" aria-modal="true" aria-labelledby="popyDialogTitle" style="display:none">
			<div class="popy-box" id="popyBox">

				<button class="popy-x" id="popyClose" aria-label="<?php esc_attr_e( 'Close popup', 'popy' ); ?>">&#x2715;</button>

				<?php if ( ! empty( $o['icon'] ) ) : ?>
				<div class="popy-icon" aria-hidden="true"><?php echo esc_html( $o['icon'] ); ?></div>
				<?php endif; ?>

				<?php if ( ! empty( $o['eyebrow'] ) ) : ?>
				<p class="popy-eyebrow" style="color:<?php echo esc_attr( $accent ); ?>"><?php echo esc_html( $o['eyebrow'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $o['title'] ) ) : ?>
				<h2 class="popy-title" id="popyDialogTitle"><?php echo esc_html( $o['title'] ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $o['subtitle'] ) ) : ?>
				<p class="popy-subtitle"><?php echo wp_kses( $o['subtitle'], array( 'strong' => array(), 'em' => array(), 'br' => array() ) ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $o['body'] ) ) : ?>
				<p class="popy-body"><?php echo wp_kses( $o['body'], array( 'strong' => array(), 'em' => array(), 'br' => array() ) ); ?></p>
				<?php endif; ?>

				<hr class="popy-rule">

				<?php if ( ! empty( $o['primary_btn_text'] ) ) : ?>
				<a href="<?php echo esc_url( $o['primary_btn_url'] ); ?>" class="popy-btn-primary" style="background-color:<?php echo esc_attr( $accent ); ?>">
					<?php echo esc_html( $o['primary_btn_text'] ); ?>
				</a>
				<?php endif; ?>

				<?php if ( ! empty( $o['secondary_btn_text'] ) ) : ?>
				<a href="<?php echo esc_url( $o['secondary_btn_url'] ); ?>" class="popy-btn-secondary">
					<?php echo esc_html( $o['secondary_btn_text'] ); ?>
				</a>
				<?php endif; ?>

				<?php if ( ! empty( $o['footnote'] ) || ! empty( $o['show_dismiss'] ) ) : ?>
				<p class="popy-footnote">
					<?php if ( ! empty( $o['footnote'] ) ) : ?>
					<span><?php echo esc_html( $o['footnote'] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $o['show_dismiss'] ) && ! empty( $o['footnote'] ) ) : ?>
					<span class="popy-sep" aria-hidden="true">&nbsp;&middot;&nbsp;</span>
					<?php endif; ?>
					<?php if ( ! empty( $o['show_dismiss'] ) ) : ?>
					<a href="#" id="popyDismiss" class="popy-dismiss"><?php echo esc_html( $o['dismiss_text'] ); ?></a>
					<?php endif; ?>
				</p>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}
}
