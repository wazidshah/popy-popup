<?php
/**
 * Popy – Uninstall
 *
 * Runs when the plugin is deleted from wp-admin → Plugins.
 * Removes all options stored by the plugin.
 *
 * @package Popy
 */

// WordPress must call this file during uninstall — abort otherwise.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Note: uninstall.php uses WP_UNINSTALL_PLUGIN instead of ABSPATH by design.
// See: https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/

delete_option( 'popy_settings' );

// Remove cached update transient if present.
delete_transient( 'popy_update_' . md5( 'wazidshah/popy-popup' ) );
