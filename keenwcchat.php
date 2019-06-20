<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://keendevs.com
 * @since             1.0.0
 * @package           Keenwcchat
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Order Chat
 * Plugin URI:        https://keendevs.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Keendevs
 * Author URI:        https://keendevs.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       keenwcchat
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'KEENWCCHAT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-keenwcchat-activator.php
 */
function activate_keenwcchat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-keenwcchat-activator.php';
	Keenwcchat_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-keenwcchat-deactivator.php
 */
function deactivate_keenwcchat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-keenwcchat-deactivator.php';
	Keenwcchat_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_keenwcchat' );
register_deactivation_hook( __FILE__, 'deactivate_keenwcchat' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-keenwcchat.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function keenwcchat_admin_notice(){
		$class = 'notice notice-error';
		$message = __( 'requires WooCommerce plugin to be installed and activated.', 'keenwcchat' );
		printf( '<div class="%1$s"><p><strong>%2$s</strong>%3$s</p></div>', esc_attr( $class ), esc_html__('WooCommerce Order Chat ', 'keenwcchat'), $message );
}

function run_keenwcchat() {
	if(class_exists( 'WooCommerce' )){
		$plugin = new Keenwcchat();
		$plugin->run();
	} else {
		add_action( 'admin_notices', 'keenwcchat_admin_notice' );
	}
}

add_action('plugins_loaded', 'run_keenwcchat');