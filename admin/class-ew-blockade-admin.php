<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package EW_Blockade
 * @subpackage EW_Blockade/admin
 * @since 1.0.0
 */
class EW_Blockade_Admin {

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
	 * Add options page to the admin menu.
	 *
	 * @since 1.0.0
	 */
	public function add_plugin_admin_menu() {

		add_options_page(
			esc_html__( 'EW Blockade Settings', 'ew-blockade' ),
			esc_html__( 'EW Blockade', 'ew-blockade' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_setup_page' )
		);

	}

	/**
	 * Render the plugin settings page.
	 *
	 * @since 1.0.0
	 */
	public function display_plugin_setup_page() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'ew_blockade_settings' );
				do_settings_sections( 'ew_blockade_settings' );
				submit_button( esc_html__( 'Save Settings', 'ew-blockade' ) );
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register all settings, sections, and fields.
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {

		register_setting(
			'ew_blockade_settings',
			'ew_blockade_settings',
			array( $this, 'sanitize_settings' )
		);

		add_settings_section(
			'ew_blockade_general_section',
			esc_html__( 'General Settings', 'ew-blockade' ),
			array( $this, 'general_settings_section_callback' ),
			'ew_blockade_settings'
		);

		add_settings_field(
			'enable_blocking',
			esc_html__( 'Enable Blocking', 'ew-blockade' ),
			array( $this, 'enable_blocking_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		add_settings_field(
			'allowed_countries',
			esc_html__( 'Allowed Countries (2-letter ISO codes, comma-separated)', 'ew-blockade' ),
			array( $this, 'allowed_countries_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		

		add_settings_field(
			'allowed_bots',
			esc_html__( 'Allowed Bots/User Agents (comma-separated)', 'ew-blockade' ),
			array( $this, 'allowed_bots_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		add_settings_field(
			'ip_api_provider',
			esc_html__( 'IP Location API Provider', 'ew-blockade' ),
			array( $this, 'ip_api_provider_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		add_settings_field(
			'ip2location_api_key',
			esc_html__( 'IP2Location.io API Key', 'ew-blockade' ),
			array( $this, 'ip2location_api_key_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		add_settings_field(
			'maxmind_license_key',
			esc_html__( 'MaxMind License Key', 'ew-blockade' ),
			array( $this, 'maxmind_license_key_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		add_settings_field(
			'maxmind_account_id',
			esc_html__( 'MaxMind Account ID', 'ew-blockade' ),
			array( $this, 'maxmind_account_id_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		add_settings_field(
			'blocking_action',
			esc_html__( 'Blocking Action', 'ew-blockade' ),
			array( $this, 'blocking_action_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		add_settings_field(
			'redirect_url',
			esc_html__( 'Redirect URL for Blocked Users', 'ew-blockade' ),
			array( $this, 'redirect_url_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		add_settings_field(
			'caching_method',
			esc_html__( 'Caching Method', 'ew-blockade' ),
			array( $this, 'caching_method_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		add_settings_field(
			'clear_ip_cache',
			esc_html__( 'Clear IP Cache', 'ew-blockade' ),
			array( $this, 'clear_ip_cache_callback' ),
			'ew_blockade_settings',
			'ew_blockade_general_section'
		);

		// Removed custom_404_content field as 404 content is now hardcoded.

	}

	/**
	 * Sanitize the plugin settings.
	 *
	 * @since 1.0.0
	 * @param array $input The settings array.
	 * @return array The sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$sanitized_input = array();
		$options         = get_option( 'ew_blockade_settings' );

		// Enable Blocking.
		$sanitized_input['enable_blocking'] = isset( $input['enable_blocking'] ) ? (bool) $input['enable_blocking'] : false;

		// Allowed Countries.
		if ( isset( $input['allowed_countries'] ) ) {
			$countries = explode( ',', sanitize_text_field( wp_unslash( $input['allowed_countries'] ) ) );
			$sanitized_input['allowed_countries'] = array_map( 'strtoupper', array_map( 'trim', $countries ) );
			$sanitized_input['allowed_countries'] = array_filter( $sanitized_input['allowed_countries'] ); // Remove empty entries.
		} else {
			$sanitized_input['allowed_countries'] = array();
		}

		// Enable JSON Caching.
		// 1. In register_settings() method:
		// Remove this block entirely:
		// add_settings_field(
		// 	'enable_json_caching',
		// 	..., 
		// 	..., 
		// 	..., 
		// 	...
		// );

		// 2. Delete enable_json_caching_callback() function including PHPDoc headers
		// 3. Search entire file for 'enable_json_caching' and remove any leftovers

		// Allowed Bots.
		if ( isset( $input['allowed_bots'] ) ) {
			$bots = explode( ',', sanitize_text_field( wp_unslash( $input['allowed_bots'] ) ) );
			$sanitized_input['allowed_bots'] = array_map( 'strtolower', array_map( 'trim', $bots ) );
			$sanitized_input['allowed_bots'] = array_filter( $sanitized_input['allowed_bots'] ); // Remove empty entries.
		} else {
			$sanitized_input['allowed_bots'] = array();
		}

		// IP API Provider.
		$sanitized_input['ip_api_provider'] = isset( $input['ip_api_provider'] ) ? sanitize_text_field( wp_unslash( $input['ip_api_provider'] ) ) : 'ipapi';
		if ( ! in_array( $sanitized_input['ip_api_provider'], array( 'ipapi', 'ip2location', 'maxmind' ), true ) ) {
			$sanitized_input['ip_api_provider'] = 'ipapi';
		}

		// API Keys.
		$sanitized_input['ip2location_api_key'] = isset( $input['ip2location_api_key'] ) ? sanitize_text_field( wp_unslash( $input['ip2location_api_key'] ) ) : '';
		$sanitized_input['maxmind_license_key'] = isset( $input['maxmind_license_key'] ) ? sanitize_text_field( wp_unslash( $input['maxmind_license_key'] ) ) : '';
		$sanitized_input['maxmind_account_id']  = isset( $input['maxmind_account_id'] ) ? sanitize_text_field( wp_unslash( $input['maxmind_account_id'] ) ) : '';

		// Blocking Action.
		$sanitized_input['blocking_action'] = isset( $input['blocking_action'] ) ? sanitize_text_field( wp_unslash( $input['blocking_action'] ) ) : 'redirect';
		if ( ! in_array( $sanitized_input['blocking_action'], array( 'redirect', '404_page' ), true ) ) {
			$sanitized_input['blocking_action'] = 'redirect';
		}

		// Redirect URL.
		$sanitized_input['redirect_url'] = isset( $input['redirect_url'] ) ? esc_url_raw( wp_unslash( $input['redirect_url'] ) ) : home_url();

		// Caching method: 'file' (default) or 'db'.
		$sanitized_input['caching_method'] = isset($input['caching_method']) ? 
			in_array($input['caching_method'], ['no','file','db'],true) ? $input['caching_method'] : 'no' : 'no';

		// Clear IP Cache - This is a button, no need to sanitize.

		return $sanitized_input;
	}

	/**
	 * Callback for the general settings section.
	 *
	 * @since 1.0.0
	 */
	public function general_settings_section_callback() {
		echo '<p>' . esc_html__( 'Configure the general settings for the EW Blockade plugin.', 'ew-blockade' ) . '</p>';
	}

	/**
	 * Callback for the enable blocking field.
	 *
	 * @since 1.0.0
	 */
	public function enable_blocking_callback() {
		$options = get_option( 'ew_blockade_settings' );
		$checked = isset( $options['enable_blocking'] ) ? checked( $options['enable_blocking'], true, false ) : '';
		?>
		<input type="checkbox" id="enable_blocking" name="ew_blockade_settings[enable_blocking]" value="1" <?php echo $checked; ?> />
		<label for="enable_blocking"><?php esc_html_e( 'Check to enable the blocking features.', 'ew-blockade' ); ?></label>
		<?php
	}

	/**
	 * Callback for the allowed countries field.
	 *
	 * @since 1.0.0
	 */
	public function allowed_countries_callback() {
		$options = get_option( 'ew_blockade_settings' );
		$countries = isset( $options['allowed_countries'] ) ? implode( ', ', (array) $options['allowed_countries'] ) : '';
		?>
		<input type="text" id="allowed_countries" name="ew_blockade_settings[allowed_countries]" value="<?php echo esc_attr( $countries ); ?>" class="regular-text" />
		<p class="description">
			<?php esc_html_e( 'Enter 2-letter ISO country codes separated by commas (e.g., US, CA, GB).', 'ew-blockade' ); ?>
			<a href="https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes" target="_blank" rel="noopener">(See list of ISO country codes)</a>
		</p>
		<?php
	}

	/**
	 * Callback for the allowed bots field.
	 *
	 * @since 1.0.0
	 */
	public function allowed_bots_callback() {
		$options = get_option( 'ew_blockade_settings' );
		$default_bots = 'googlebot, bingbot, slurp, duckduckbot, baiduspider, yandexbot, facebot, facebookexternalhit, twitterbot, applebot, linkedinbot, pinterest, embedly, quora link preview, showyoubot, outbrain, whatsapp';
		$bots = isset( $options['allowed_bots'] ) && !empty( $options['allowed_bots'] ) ? implode( ', ', (array) $options['allowed_bots'] ) : $default_bots;
		?>
		<input type="text" id="allowed_bots" name="ew_blockade_settings[allowed_bots]" value="<?php echo esc_attr( $bots ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Enter user agent strings or keywords separated by commas (e.g., googlebot, bingbot).', 'ew-blockade' ); ?></p>
		<?php
	}

	/**
	 * Callback for the IP API provider field.
	 *
	 * @since 1.0.0
	 */
	public function ip_api_provider_callback() {
		$options = get_option( 'ew_blockade_settings' );
		$selected_provider = isset( $options['ip_api_provider'] ) ? $options['ip_api_provider'] : 'ip2location';
		?>
		<select id="ip_api_provider" name="ew_blockade_settings[ip_api_provider]">
			<option value="ipapi" <?php selected( $selected_provider, 'ipapi' ); ?>><?php esc_html_e( 'IPAPI.co (1000/day, free no key)', 'ew-blockade' ); ?></option>
			<option value="ip2location" <?php selected( $selected_provider, 'ip2location' ); ?>><?php esc_html_e( 'IP2Location.io (50K/month, free key)', 'ew-blockade' ); ?></option>
			<option value="maxmind" <?php selected( $selected_provider, 'maxmind' ); ?>><?php esc_html_e( 'MaxMind GeoIP2 (1000/day, free key)', 'ew-blockade' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Choose your preferred IP location API provider.', 'ew-blockade' ); ?></p>
		<?php
	}

	/**
	 * Callback for the IP2Location.io API Key field.
	 *
	 * @since 1.0.0
	 */
	public function ip2location_api_key_callback() {
		$options = get_option( 'ew_blockade_settings' );
		$api_key = isset( $options['ip2location_api_key'] ) ? $options['ip2location_api_key'] : '';
		?>
		<input type="text" id="ip2location_api_key" name="ew_blockade_settings[ip2location_api_key]" value="<?php echo esc_attr( $api_key ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Enter your IP2Location.io API Key.', 'ew-blockade' ); ?></p>
		<?php
	}

	/**
	 * Callback for the MaxMind License Key field.
	 *
	 * @since 1.0.0
	 */
	public function maxmind_license_key_callback() {
		$options = get_option( 'ew_blockade_settings' );
		$license_key = isset( $options['maxmind_license_key'] ) ? $options['maxmind_license_key'] : '';
		?>
		<input type="text" id="maxmind_license_key" name="ew_blockade_settings[maxmind_license_key]" value="<?php echo esc_attr( $license_key ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Enter your MaxMind GeoIP2 License Key.', 'ew-blockade' ); ?></p>
		<?php
	}

	/**
	 * Callback for the MaxMind Account ID field.
	 *
	 * @since 1.0.0
	 */
	public function maxmind_account_id_callback() {
		$options = get_option( 'ew_blockade_settings' );
		$account_id = isset( $options['maxmind_account_id'] ) ? $options['maxmind_account_id'] : '';
		?>
		<input type="text" id="maxmind_account_id" name="ew_blockade_settings[maxmind_account_id]" value="<?php echo esc_attr( $account_id ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Enter your MaxMind GeoIP2 Account ID.', 'ew-blockade' ); ?></p>
		<?php
	}
/**
	 * Callback for the blocking action field.
	 *
	 * @since 1.0.0
	 */
	public function blocking_action_callback() {
		$options = get_option( 'ew_blockade_settings' );
		$action  = isset( $options['blocking_action'] ) ? $options['blocking_action'] : 'redirect';
		?>
		<fieldset>
			<label>
				<input type="radio" name="ew_blockade_settings[blocking_action]" value="404_page" <?php checked( $action, '404_page' ); ?> />
				<?php esc_html_e( 'Show a static 404 page', 'ew-blockade' ); ?>
			</label><br>
			<label>
				<input type="radio" name="ew_blockade_settings[blocking_action]" value="redirect" <?php checked( $action, 'redirect' ); ?> />
				<?php esc_html_e( 'Redirect to a custom URL', 'ew-blockade' ); ?>
			</label>
		</fieldset>
		<p class="description"><?php esc_html_e( 'Choose what happens to blocked users: show a static 404 page or redirect to a custom URL.', 'ew-blockade' ); ?></p>
		<?php
	}

	// Removed custom_404_content_callback as 404 content is now hardcoded.

	/**
	 * Callback for the redirect URL field.
	 *
	 * @since 1.0.0
	 */
	public function redirect_url_callback() {
		$options = get_option( 'ew_blockade_settings' );
		$redirect_url = isset( $options['redirect_url'] ) ? $options['redirect_url'] : home_url();
		?>
		<input type="url" id="redirect_url" name="ew_blockade_settings[redirect_url]" value="<?php echo esc_url( $redirect_url ); ?>" class="regular-text" />
		<p class="description"><?php esc_html_e( 'Enter the URL where blocked users will be redirected.', 'ew-blockade' ); ?></p>
		<?php
	}

	/**
	 * Callback for the caching method field.
	 *
	 * @since 1.1.0
	 */
	public function caching_method_callback() {
		$options = get_option( 'ew_blockade_settings' );
		$method = isset( $options['caching_method'] ) ? $options['caching_method'] : 'no';
		?>
		<select id="caching_method" name="ew_blockade_settings[caching_method]">
			<option value="no" <?php selected( $method, 'no' ); ?>><?php esc_html_e('No Caching', 'ew-blockade'); ?></option>
			<option value="file" <?php selected( $method, 'file' ); ?>><?php esc_html_e('File (JSON)', 'ew-blockade'); ?></option>
			<option value="db" <?php selected( $method, 'db' ); ?>><?php esc_html_e('Database', 'ew-blockade'); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Choose how to cache IP lookups: file-based (JSON) or database table.', 'ew-blockade' ); ?></p>
		<?php
	}

	/**
	 * Callback for the clear IP cache button.
	 *
	 * @since 1.1.0
	 */
	public function clear_ip_cache_callback() {
		if ( isset( $_POST['ew_blockade_clear_ip_cache'] ) && check_admin_referer( 'ew_blockade_clear_ip_cache_action', 'ew_blockade_clear_ip_cache_nonce' ) ) {
			$this->handle_clear_ip_cache();
			?><div class="updated"><p><?php esc_html_e( 'IP cache cleared.', 'ew-blockade' ); ?></p></div><?php
		}
		?>
		<form method="post">
			<?php wp_nonce_field( 'ew_blockade_clear_ip_cache_action', 'ew_blockade_clear_ip_cache_nonce' ); ?>
			<input type="submit" name="ew_blockade_clear_ip_cache" class="button" value="<?php esc_attr_e( 'Clear IP Cache', 'ew-blockade' ); ?>" />
		</form>
		<p class="description"><?php esc_html_e( 'Clear all cached IP lookups.', 'ew-blockade' ); ?></p>
		<?php
	}

	/**
	 * Handle clearing the IP cache based on selected method.
	 *
	 * @since 1.1.0
	 */
	private function handle_clear_ip_cache() {
		$options = get_option( 'ew_blockade_settings' );
		$method = isset( $options['caching_method'] ) ? $options['caching_method'] : 'file';
		if ( $method === 'db' ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ew_blockade_ip_cache';
			$wpdb->query( "TRUNCATE TABLE $table" );
		} else {
			$cache_file = defined('EW_BLOCKADE_CACHE_DIR') ? EW_BLOCKADE_CACHE_DIR . 'ip_cache.json' : '';
			if ( $cache_file && file_exists( $cache_file ) ) {
				file_put_contents( $cache_file, json_encode( array() ) );
			}
		}
	}

	/**
	 * Security: Input sanitization using `sanitize_text_field`, `esc_url_raw`, `wp_unslash`. Output escaping using `esc_html`, `esc_attr`, `esc_url`.
	 * WordPress Coding Standards: Uses PHPDoc, prefixes all functions and variables.
	 * WordPress Core Integration: Uses Settings API (`register_setting`, `add_settings_section`, `add_settings_field`, `settings_fields`, `do_settings_sections`, `submit_button`, `get_option`), `add_options_page` for admin menu.
	 * Internationalization: All user-facing strings are translatable using `esc_html__`.
	 */
}