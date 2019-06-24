<?php

class Keenwcchat_Functions {

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
	
    /*
	 * rendering message box frontend
	*/
	public function keenwcchat_message_box(){

		// echo '<pre>';
		// print_r(get_post_meta(847, 'order_chat', true));
		// echo '</pre>';
		?>
		<div class="keenwcchat">
			<div id="display-chat"></div>
			<div id="keenwcchat-message">
				<p class="typing-status"></p>
				<textarea class="keenwcchat-textarea" name="message" placeholder="Type Message" style="width: 100%;"></textarea>
				<a href="#" class="keenwcchat-send">Send message</a>
			</div>
		</div>
		<?php
	}

	/*
	 * rendering meta box in orders for convos
	*/
	function keenwcchat_admin_message_box() {
		add_meta_box( 'orders_convo', 'Conversation',
				array($this,'keenwcchat_message_box'),
				'shop_order', 'side', 'low');
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

	public function is_order_edit_page(){
		return ('shop_order' === get_post_type($_GET['post'])) && ( 'edit' == $_GET['action']);
	}

	public function is_customer_or_subscriber($id){
		$user = get_userdata( $id );
		return !empty(array_intersect(['customer', 'subscriber'], $user->roles));
	}

	public function is_admin_or_shop_manager($id){
		$user = get_userdata( $id );
		return !empty(array_intersect(['shop_manager', 'administrator'], $user->roles));
	}

	public function keenwcchat_chat_basic_data($order_id){
		$customerId = $this->customerId($order_id);
		$chat_data = [
			'id' => $order_id,
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
		update_post_meta($order_id, 'order_chat', $chat_data);
	}

	public function keenwcchat_push_message(){
		$message = $_POST['message'];
		$orderId = intval($_POST['orderId']);
		$chat_history = get_post_meta($orderId, 'order_chat', true);
		$user = wp_get_current_user();
		// $is_cus_sub = $this->is_customer_or_subscriber($user->ID);
		// push new chat
		$new_chat = [
			'user' 	 => $user->ID,
			'text'	 => $message,
			'time'	 => time(),
		];
		$chat_history['chat'][] = $new_chat;

		// update the chat to db
		update_post_meta($orderId, 'order_chat', $chat_history);

		// send json chat history to js
		wp_send_json(array($new_chat));
		exit;
	}

	public function keenwcchat_load_chat(){
		$orderId = intval($_POST['orderId']);
		$displayed = intval($_POST['displayed']);

		// retrieve database history
		$chat_history = get_post_meta($orderId, 'order_chat', true);

		// track the history displayed so far
		$send_history = array_slice($chat_history['chat'], $displayed);

		// slice new chat to send 
		wp_send_json($send_history);
		exit;
	}

	public function keenwcchat_typing(){
		$user = wp_get_current_user();
		set_transient('keenwcchat_typing_' . $user->ID, true, 120); 
		wp_send_json('typing');
		exit;
	}

	public function keenwcchat_not_typing(){
		$user = wp_get_current_user();
		delete_transient('keenwcchat_typing_' . $user->ID);
		wp_send_json('not typing. transient deleted.');
		exit;
	}

	public function get_typing_status(){
		$user = wp_get_current_user();
		$status = get_transient('keenwcchat_typing_' . $user->ID);
		wp_send_json(['typing' => $status]);
		exit;
	}

}
