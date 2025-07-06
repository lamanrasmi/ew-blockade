<?php
/**
 * Plugin Name: EW Blockade
 * Plugin URI: https://www.ewallzsolutions.com
 * Description: A WordPress plugin to block site access based on country IP and user agent, with configurable settings.
 * Version: 1.0.0
 * Author: eWallz Devs
 * Author URI: https://www.ewallzsolutions.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ew-blockade
 * Domain Path: /languages
 *
 * @package EW_Blockade
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current plugin version.
 *
 * @since 1.0.0
 */
define( 'EW_BLOCKADE_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 *
 * @since 1.0.0
 */
define( 'EW_BLOCKADE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 *
 * @since 1.0.0
 */
define( 'EW_BLOCKADE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin cache directory path.
 *
 * @since 1.0.0
 */
define( 'EW_BLOCKADE_CACHE_DIR', WP_CONTENT_DIR . '/uploads/blockade-cache/' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once EW_BLOCKADE_PLUGIN_DIR . 'includes/class-ew-blockade-core.php';

/**
 * The class responsible for orchestrating the plugin's admin-specific functionality.
 */
require_once EW_BLOCKADE_PLUGIN_DIR . 'admin/class-ew-blockade-admin.php';

/**
 * The class responsible for orchestrating the plugin's public-facing functionality.
 */
require_once EW_BLOCKADE_PLUGIN_DIR . 'public/class-ew-blockade-public.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file means
 * that the plugin is working.
 *
 * @since 1.0.0
 */
function run_ew_blockade() {

	$plugin = new EW_Blockade_Core();
	$plugin->run();

}
run_ew_blockade();

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ew-blockade-activator.php
 */
function activate_ew_blockade() {
	require_once EW_BLOCKADE_PLUGIN_DIR . 'includes/class-ew-blockade-activator.php';
	EW_Blockade_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_ew_blockade' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ew-blockade-deactivator.php
 */
function deactivate_ew_blockade() {
	require_once EW_BLOCKADE_PLUGIN_DIR . 'includes/class-ew-blockade-deactivator.php';
	EW_Blockade_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_ew_blockade' );

