<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package EW_Blockade
 * @subpackage EW_Blockade/includes
 * @since 1.0.0
 */
class EW_Blockade_Activator {

	/**
	 * Short Description. (e.g. 'Upon activation, do this.')
	 *
	 * Long Description.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		// Placeholder for activation tasks, e.g., setting default options.
		$default_settings = array(
			'enable_blocking'       => false,
			'allowed_countries'     => array( 'US' ),
			'enable_json_caching'   => true,
			'allowed_bots'          => array( 'googlebot', 'bingbot' ),
			'ip_api_provider'       => 'ip2location', // 'ip2location' or 'maxmind'
			'ip2location_api_key'   => '',
			'maxmind_license_key'   => '',
			'maxmind_account_id'    => '',
			'redirect_url'          => home_url(), // Default to home URL.
		);
		add_option( 'ew_blockade_settings', $default_settings );

		// Create the cache directory if it doesn't exist.
		if ( ! file_exists( EW_BLOCKADE_CACHE_DIR ) ) {
			wp_mkdir_p( EW_BLOCKADE_CACHE_DIR );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ew_blockade_ip_cache';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			ip VARCHAR(45) NOT NULL PRIMARY KEY,
			country CHAR(2) NOT NULL,
			time INT(11) NOT NULL
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	
dbDelta( $sql );
	}


}