<?php 
/*
* Plugin Name:       WooCommerce Order Chat
* Plugin URI:        https://keendevs.com
* Description:       WooCommerce Order Chat plugin is a kind of communication bridge between the seller and customer. Customer can message the seller easily just like a chat app and vise-versa. 
* Version:           1.0.0
* Author:            Keendevs
* Author URI:        https://keendevs.com
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       keenwcchat
* Domain Path:       /languages
*/

require plugin_dir_path( __FILE__ ) . 'includes/class-keenwcchat.php';

function run_keenwcchat() {
    // load plugin for logged in users only
    if(!is_user_logged_in()){
        return;
    }
    $plugin = new Keenwcchat();
    $plugin->run();
}
add_action('plugins_loaded', 'run_keenwcchat');
