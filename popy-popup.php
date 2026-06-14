<?php
/**
 * Plugin Name:       Popy – Simple WordPress Popups
 * Plugin URI:        https://github.com/wazidshah/popy-popup
 * Description:       Timed Popups That Respect Your Visitors. Show a beautiful timed popup with cookie-based dismissal. Compatible with WPBakery Page Builder.
 * Version:           1.0.0
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Author:            Wazid Shah
 * Author URI:        https://wazidshah.com
 * Text Domain:       popy
 * Domain Path:       /languages
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'POPY_VERSION',    '1.0.0' );
define( 'POPY_FILE',       __FILE__ );
define( 'POPY_PATH',       plugin_dir_path( __FILE__ ) );
define( 'POPY_URL',        plugin_dir_url( __FILE__ ) );
define( 'POPY_OPTION_KEY', 'popy_settings' );

// GitHub repo for auto-updates (owner/repo format).
define( 'POPY_GITHUB_REPO', 'wazidshah/popy-popup' );

require_once POPY_PATH . 'includes/class-popy-settings.php';
require_once POPY_PATH . 'includes/class-popy-frontend.php';
require_once POPY_PATH . 'includes/class-popy-updater.php';

/**
 * Bootstrap.
 */
function popy_init() {
	Popy_Settings::get_instance();
	Popy_Frontend::get_instance();

	// Auto-updater — checks GitHub releases.
	new Popy_Updater( POPY_FILE, POPY_GITHUB_REPO, POPY_VERSION );

	// Load translations.
	load_plugin_textdomain( 'popy', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'popy_init' );

/**
 * Set default options on first activation.
 */
function popy_activate() {
	if ( ! get_option( POPY_OPTION_KEY ) ) {
		add_option( POPY_OPTION_KEY, popy_defaults() );
	}
}
register_activation_hook( __FILE__, 'popy_activate' );


/**
 * Default settings.
 *
 * @return array
 */
function popy_defaults() {
	return array(
		'enabled'            => 1,
		'delay'              => 10,
		'cookie_days'        => 7,
		'eyebrow'            => 'LIMITED TIME OFFER',
		'title'              => 'A Headline That Grabs Attention',
		'subtitle'           => 'Total value: <strong>$1,000</strong>',
		'body'               => 'Available with any <strong>qualifying</strong> purchase',
		'footnote'           => 'Valid this month only',
		'primary_btn_text'   => '✉ Send us an Email',
		'primary_btn_url'    => 'mailto:hello@example.com',
		'secondary_btn_text' => '📞 Call Us',
		'secondary_btn_url'  => 'tel:+10000000000',
		'show_dismiss'       => 1,
		'dismiss_text'       => 'No thanks',
		'overlay_close'      => 1,
		'accent_color'       => '#1e4d3b',
		'icon'               => '🌟',
	);
}
