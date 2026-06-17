<?php
/**
 * Plugin Name:       Popy – Simple Popups
 * Description:       Timed Popups That Respect Your Visitors. Show a beautiful timed popup with cookie-based dismissal. Compatible with WPBakery Page Builder.
 * Version:           1.0.0
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Author:            Wazid Shah
 * Author URI:        https://github.com/wazidshah
 * Text Domain:       popy-popup
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'POPY_VERSION',    '1.0.0' );
define( 'POPY_PATH',       plugin_dir_path( __FILE__ ) );
define( 'POPY_URL',        plugin_dir_url( __FILE__ ) );
define( 'POPY_OPTION_KEY', 'popy_settings' );

require_once POPY_PATH . 'includes/class-popy-settings.php';
require_once POPY_PATH . 'includes/class-popy-frontend.php';

/**
 * Bootstrap.
 */
function popy_init() {
	Popy_Settings::get_instance();
	Popy_Frontend::get_instance();

	// Load translations.
	load_plugin_textdomain( 'popy-popup', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
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
		'eyebrow'            => 'SPRING SALE — ENDS SOON',
		'title'              => 'Get 30% Off Your First Order',
		'subtitle'           => 'Use code <strong>WELCOME30</strong> at checkout',
		'body'               => 'Valid on all <strong>Lumora</strong> products — today only',
		'footnote'           => 'One use per customer',
		'primary_btn_text'   => '🛍 Shop the Sale',
		'primary_btn_url'    => 'https://example.com/shop',
		'secondary_btn_text' => '📞 +1-800-555-0192',
		'secondary_btn_url'  => 'tel:+18005550192',
		'show_dismiss'       => 1,
		'dismiss_text'       => 'No thanks',
		'overlay_close'      => 1,
		'accent_color'       => '#1e4d3b',
		'icon'               => '🎉',
	);
}
