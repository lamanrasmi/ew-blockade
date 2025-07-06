<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package EW_Blockade
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = get_option( 'ew_blockade_settings' );

// Only proceed with deletion if the user has opted in.
if ( ! isset( $options['remove_data_on_uninstall'] ) || ! $options['remove_data_on_uninstall'] ) {
	exit;
}

// Delete plugin options.
delete_option( 'ew_blockade_settings' );

// Clear any scheduled cron jobs.
wp_clear_scheduled_hook( 'ew_blockade_daily_cron_event' );

// Include the main plugin file to access constants.
if ( ! defined( 'EW_BLOCKADE_PLUGIN_DIR' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'ew-blockade.php';
}

// Remove the cache file and directory if empty.
if ( defined( 'EW_BLOCKADE_CACHE_DIR' ) ) {
	$cache_file = EW_BLOCKADE_CACHE_DIR . 'ip_cache.json';
	global $wp_filesystem;

	// Initialize WP_Filesystem if not already initialized.
	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();
	}

	if ( ! empty( $wp_filesystem ) ) {
		if ( $wp_filesystem->exists( $cache_file ) ) {
			$wp_filesystem->delete( $cache_file );
		}
		// Attempt to remove the directory if it's empty.
		if ( $wp_filesystem->exists( EW_BLOCKADE_CACHE_DIR ) && $wp_filesystem->is_dir( EW_BLOCKADE_CACHE_DIR ) ) {
			$files_in_dir = $wp_filesystem->dirlist( EW_BLOCKADE_CACHE_DIR );
			if ( empty( $files_in_dir ) ) {
				$wp_filesystem->rmdir( EW_BLOCKADE_CACHE_DIR );
			}
		}
	}
}

