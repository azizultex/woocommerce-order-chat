<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://keendevs.com
 * @since      1.0.0
 *
 * @package    Keenwcchat
 * @subpackage Keenwcchat/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Keenwcchat
 * @subpackage Keenwcchat/public
 * @author     Keendevs <keendevs@gmail.com>
 */
class Keenwcchat_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Keenwcchat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Keenwcchat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if(is_view_order_page() || $this->is_thankyou_page()){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/keenwcchat-public.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Keenwcchat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Keenwcchat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if(is_view_order_page() || $this->is_thankyou_page()){
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/keenwcchat-public.js', array( 'jquery' ), $this->version, false );
			// localize necessary data
			wp_localize_script($this->plugin_name, 'keenwcchat', $this->localize_data());
		}

	}

	public function localize_data(){
		global $wp;
		$orderId = $wp->query_vars['view-order'] ? $wp->query_vars['view-order'] : $wp->query_vars['order-received'];
		return [
			'orderId' => $orderId
		];
	}

	public function keenwcchat_message_box(){

		echo '<pre>';
		print_r(get_post_meta(842, 'order_chat', true));
		echo '</pre>';
		?>
		<div id="display-chat"></div>
		<form id="keenwcchat-message">
			<textarea class="keenwcchat-textarea" name="message" placeholder="Type Message" style="width: 100%;"></textarea>
			<input name="submit" type="submit" class="keenwcchat-send"  value="Send message">
		</form>
		<?php
	}

	public function customerId($orderId){
		$order = wc_get_order( $orderId );
		// $user = $order->get_user();
		return $order->get_user_id();
	}

	public function is_thankyou_page(){
		global $wp;
		return (is_checkout() && !empty( $wp->query_vars['order-received']));
	}

	public function keenwcchat_push_message(){
		$message = $_POST['message'];
		$orderId = intval($_POST['orderId']);
		$customerId = $this->customerId($orderId);
		$prevChat = get_post_meta($orderId, 'order_chat', true);
		$chat_data = [
			'id' => $orderId,
			'customer' => [ 
				'id' => $customerId,
				'online' => false,
				'typing' => false,
			],
			'seller' => [
				'online' => false,
				'typing' => false,
			],
			'chat' => [],
		];

		// bring chat history
		if($prevChat){
			$chat_data['chat'] = $prevChat['chat'];
		}

		// push new chat
		$chat_data['chat'][] = [
			'customer' => true,
			'text'	   => $message,
			'time'	   => time(),
		];

		// update the chat to db
		update_post_meta($orderId, 'order_chat', $chat_data);
		wp_send_json($chat_data);
		exit;
	}

	public function keenwcchat_load_chat(){
		// $orderId = intval($_POST['orderId']);
		// $chat_history = get_post_meta($orderId, 'order_chat', true);
		// wp_send_json('hello');
		echo 'Hello';
		exit;
	}

}
