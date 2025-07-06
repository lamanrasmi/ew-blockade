<?php
/**
 * Defines the internationalization functionality
 *
 * Loads a plugin's translated strings.
 *
 * @package EW_Blockade
 * @subpackage EW_Blockade/includes
 * @since 1.0.0
 */
class EW_Blockade_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'ew-blockade',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}


}