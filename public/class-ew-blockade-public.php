<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package EW_Blockade
 * @subpackage EW_Blockade/public
 * @since 1.0.0
 */
class EW_Blockade_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Get user's IP address.
	 *
	 * @since 1.0.0
	 * @return string User's IP address.
	 */
	private function get_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = wp_unslash( $_SERVER['HTTP_CLIENT_IP'] );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		} else {
			$ip = wp_unslash( $_SERVER['REMOTE_ADDR'] );
		}
		return sanitize_text_field( $ip );
	}

	/**
	 * Check if request is likely from an allowed bot.
	 *
	 * @since 1.0.0
	 * @param array $allowed_bots Array of allowed bot user agent patterns.
	 * @return bool True if it's an allowed bot, false otherwise.
	 */
	private function is_allowed_bot( $allowed_bots ) {
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$user_agent = sanitize_text_field( $user_agent );

		if ( empty( $user_agent ) ) {
			return false; // Treat empty user agent as not an allowed bot.
		}

		foreach ( $allowed_bots as $pattern ) {
			if ( strpos( $user_agent, $pattern ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get country code using IP2Location.io API.
	 *
	 * @since 1.0.0
	 * @param string $ip The IP address to lookup.
	 * @param string $api_key The IP2Location.io API key.
	 * @return string|false Country code on success, false on failure.
	 */
	private function get_country_code_ip2location( $ip, $api_key ) {
		$api_url = add_query_arg(
			array(
				'key' => $api_key,
				'ip'  => $ip,
			),
			'https://api.ip2location.io/'
		);

		$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) ) {
			error_log( 'EW Blockade: IP2Location.io API error: ' . $response->get_error_message() );
			return false;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		$body      = wp_remote_retrieve_body( $response );

		if ( 200 === $http_code ) {
			$data = json_decode( $body, true );
			return isset( $data['country_code'] ) ? sanitize_text_field( $data['country_code'] ) : false;
		} else {
			error_log( "EW Blockade: IP2Location.io API failed for IP $ip: HTTP $http_code, Response: $body" );
			return false;
		}
	}

	/**
	 * Get country code using MaxMind GeoIP2 API (requires MaxMind DB file or API).
	 * For simplicity, this example assumes a direct API call or a local DB lookup.
	 * A full implementation would require integrating with MaxMind's SDK or a local DB.
	 *
	 * @since 1.0.0
	 * @param string $ip The IP address to lookup.
	 * @param string $account_id MaxMind Account ID.
	 * @param string $license_key MaxMind License Key.
	 * @return string|false Country code on success, false on failure.
	 */
	private function get_country_code_maxmind( $ip, $account_id, $license_key ) {
		// This is a placeholder. A full MaxMind integration would involve:
		// 1. Including MaxMind's GeoIP2 PHP library.
		// 2. Downloading the GeoLite2-Country.mmdb or GeoIP2-Country.mmdb database.
		// 3. Using the Reader class to query the database.
		// Or, using their web service API.

		// For demonstration, we'll simulate a call to their web service.
		// In a real scenario, you'd use the MaxMind API or local DB.
		$api_url = add_query_arg(
			array(
				'ip'       => $ip,
				'license_key' => $license_key,
				'account_id'  => $account_id,
			),
			'https://geoip.maxmind.com/geoip/v2.1/country/' . $ip // Example endpoint, actual might vary.
		);

		$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

		if ( is_wp_error( $response ) ) {
			error_log( 'EW Blockade: MaxMind API error: ' . $response->get_error_message() );
			return false;
		}

		$http_code = wp_remote_retrieve_response_code( $response );
		$body      = wp_remote_retrieve_body( $response );

		if ( 200 === $http_code ) {
			$data = json_decode( $body, true );
			// MaxMind API response structure might be different, adjust accordingly.
			return isset( $data['country']['iso_code'] ) ? sanitize_text_field( $data['country']['iso_code'] ) : false;
		} else {
			error_log( "EW Blockade: MaxMind API failed for IP $ip: HTTP $http_code, Response: $body" );
			return false;
		}
	}

	/**
	 * Get cached country code from a physical JSON file.
	 *
	 * @since 1.0.0
	 * @param string $ip The IP address.
	 * @param int    $cache_timeout The cache timeout in seconds.
	 * @return string|false Country code on success, false if not found or expired.
	 */
	private function get_cached_country_code_from_file( $ip, $cache_timeout ) {
		$cache_file = EW_BLOCKADE_CACHE_DIR . 'ip_cache.json';

		if ( file_exists( $cache_file ) && is_readable( $cache_file ) ) {
			$file_content = file_get_contents( $cache_file );
			$cache_data   = json_decode( $file_content, true );

			if ( $cache_data && isset( $cache_data[ $ip ] ) ) {
				$ip_data = $cache_data[ $ip ];
				if ( isset( $ip_data['country'] ) && isset( $ip_data['time'] ) ) {
					if ( ( time() - $ip_data['time'] ) < $cache_timeout ) {
						return sanitize_text_field( $ip_data['country'] );
					} else {
						// Cache expired for this IP.
						unset( $cache_data[ $ip ] );
						$this->write_cache_file( $cache_file, $cache_data );
					}
				}
			}
		}
		return false;
	}

	/**
	 * Set country code to a physical JSON file cache.
	 *
	 * @since 1.0.0
	 * @param string $ip The IP address.
	 * @param string $country_code The country code to cache.
	 * @return bool True on success, false on failure.
	 */
	private function set_cached_country_code_to_file( $ip, $country_code ) {
		$cache_file = EW_BLOCKADE_CACHE_DIR . 'ip_cache.json';
		$cache_data = array();

		if ( file_exists( $cache_file ) && is_readable( $cache_file ) ) {
			$file_content = file_get_contents( $cache_file );
			$decoded_data = json_decode( $file_content, true );
			if ( $decoded_data ) {
				$cache_data = $decoded_data;
			}
		}

		$cache_data[ $ip ] = array(
			'country' => $country_code,
			'time'    => time(),
		);

		return $this->write_cache_file( $cache_file, $cache_data );
	}

	/**
	 * Write data to the cache file.
	 *
	 * @since 1.0.0
	 * @param string $file_path The full path to the cache file.
	 * @param array  $data The data to write.
	 * @return bool True on success, false on failure.
	 */
	private function write_cache_file( $file_path, $data ) {
		if ( ! file_exists( dirname( $file_path ) ) || ! is_writable( dirname( $file_path ) ) ) {
			error_log( 'EW Blockade: Cache directory does not exist or is not writable: ' . dirname( $file_path ) );
			return false;
		}

		$json_data = wp_json_encode( $data, JSON_PRETTY_PRINT );

		if ( false === $json_data ) {
			error_log( 'EW Blockade: Failed to encode JSON data for cache file: ' . $file_path );
			return false;
		}

		$result = file_put_contents( $file_path, $json_data );

		if ( false === $result ) {
			error_log( 'EW Blockade: Failed to write cache file: ' . $file_path );
			return false;
		}

		return true;
	}

	/**
	 * Get cached country code from the database.
	 *
	 * @since 1.1.0
	 * @param string $ip The IP address.
	 * @param int    $cache_timeout The cache timeout in seconds.
	 * @return string|false Country code on success, false if not found or expired.
	 */
	private function get_cached_country_code_from_db( $ip, $cache_timeout ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ew_blockade_ip_cache';
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT country, time FROM $table WHERE ip = %s", $ip ), ARRAY_A );
		if ( $row && ( time() - intval( $row['time'] ) ) < $cache_timeout ) {
			return sanitize_text_field( $row['country'] );
		} elseif ( $row ) {
			$wpdb->delete( $table, array( 'ip' => $ip ) );
		}
		return false;
	}

	/**
	 * Set country code to the database cache.
	 *
	 * @since 1.1.0
	 * @param string $ip The IP address.
	 * @param string $country_code The country code to cache.
	 * @return bool True on success, false on failure.
	 */
	private function set_cached_country_code_to_db( $ip, $country_code ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ew_blockade_ip_cache';
		$data = array(
			'ip' => $ip,
			'country' => $country_code,
			'time' => time(),
		);
		$formats = array( '%s', '%s', '%d' );
		return false !== $wpdb->replace( $table, $data, $formats );
	}

	/**
	 * Block access based on IP and bot detection.
	 *
	 * @since 1.0.0
	 */
	public function block_access_by_ip_and_bot() {
		$options = get_option( 'ew_blockade_settings' );

		// If blocking is not enabled, do nothing.
		if ( ! isset( $options['enable_blocking'] ) || ! $options['enable_blocking'] ) {
			return;
		}

		$user_ip             = $this->get_user_ip();
		$allowed_countries   = isset( $options['allowed_countries'] ) ? (array) $options['allowed_countries'] : array();
		$enable_json_caching = isset( $options['enable_json_caching'] ) ? (bool) $options['enable_json_caching'] : false;
		$allowed_bots        = isset( $options['allowed_bots'] ) ? (array) $options['allowed_bots'] : array();
		$ip_api_provider     = isset( $options['ip_api_provider'] ) ? $options['ip_api_provider'] : 'ip2location';
		$ip2location_api_key = isset( $options['ip2location_api_key'] ) ? $options['ip2location_api_key'] : '';
		$maxmind_license_key = isset( $options['maxmind_license_key'] ) ? $options['maxmind_license_key'] : '';
		$maxmind_account_id  = isset( $options['maxmind_account_id'] ) ? $options['maxmind_account_id'] : '';
		$redirect_url        = isset( $options['redirect_url'] ) ? esc_url_raw( $options['redirect_url'] ) : home_url();

		// Skip check for local development.
		if ( '127.0.0.1' === $user_ip || '::1' === $user_ip ) {
			return;
		}

		// Check if request is from an allowed bot.
		if ( $this->is_allowed_bot( $allowed_bots ) ) {
			return; // Allowed bots are not blocked.
		}

		$country_code  = false;
		$cache_timeout = YEAR_IN_SECONDS; // Cache for 1 year.

		$caching_method = isset( $options['caching_method'] ) ? $options['caching_method'] : 'file';

		if ( $caching_method === 'db' && $enable_json_caching ) {
			$country_code = $this->get_cached_country_code_from_db( $user_ip, $cache_timeout );
		} elseif ( $enable_json_caching ) {
			$country_code = $this->get_cached_country_code_from_file( $user_ip, $cache_timeout );
		}

		if ( false === $country_code ) {
			if ( 'ip2location' === $ip_api_provider && ! empty( $ip2location_api_key ) ) {
				$country_code = $this->get_country_code_ip2location( $user_ip, $ip2location_api_key );
			} elseif ( 'maxmind' === $ip_api_provider && ! empty( $maxmind_license_key ) && ! empty( $maxmind_account_id ) ) {
				$country_code = $this->get_country_code_maxmind( $user_ip, $maxmind_account_id, $maxmind_license_key );
			} elseif ( 'ipapi' === $ip_api_provider ) {
				$country_code = $this->get_country_code_ipapi( $user_ip );
			}

			// If country code is still empty, something went wrong.
			if ( empty( $country_code ) ) {
				return false;
			}

			if ( $enable_json_caching && false !== $country_code ) {
				if ( $caching_method === 'db' ) {
					$this->set_cached_country_code_to_db( $user_ip, $country_code );
				} else {
					$this->set_cached_country_code_to_file( $user_ip, $country_code );
				}
			}
		}

		// If country code is not retrieved or not in allowed list, take action.
		if ( false === $country_code || ! in_array( $country_code, $allowed_countries, true ) ) {
			$blocking_action = isset( $options['blocking_action'] ) ? $options['blocking_action'] : 'redirect';
			if ( $blocking_action === '404_page' ) {
				// Output a static 404 page with custom content.
				status_header( 404 );
				nocache_headers();
				$custom_404 = isset( $options['custom_404_content'] ) && trim( $options['custom_404_content'] ) !== '' ? $options['custom_404_content'] : '<h1>404 Not Found</h1><p>You are not allowed to access this page.</p>';
				echo wp_kses_post( $custom_404 );
				exit;
			} else {
				// Use header() for external redirects, wp_safe_redirect for internal.
				$site_url = parse_url( home_url(), PHP_URL_HOST );
				$target_url = parse_url( $redirect_url, PHP_URL_HOST );
				if ( $target_url && $target_url !== $site_url ) {
					header( 'Location: ' . $redirect_url, true, 302 );
					exit;
				} else {
					wp_safe_redirect( $redirect_url );
					exit;
				}
			}
		}
	}

	private function get_country_code_ipapi($ip) {
		$api_url = "https://ipapi.co/{$ip}/country/";
		$response = wp_remote_get($api_url, array(
			'timeout' => 5,
			'headers' => array('User-Agent' => 'EWBlockadePlugin/1.0')
		));

		if (is_wp_error($response)) {
			error_log('EW Blockade: ipapi.co API error: ' . $response->get_error_message());
			return false;
		}

		$http_code = wp_remote_retrieve_response_code($response);
		$body = trim(wp_remote_retrieve_body($response));

		if ($http_code === 200 && strlen($body) === 2) {
			return sanitize_text_field(strtoupper($body));
		}

		error_log("EW Blockade: ipapi.co API failed for IP $ip: HTTP $http_code, Response: $body");
		return false;
	}
}
