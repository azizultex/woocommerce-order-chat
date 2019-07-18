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
		?>
		<div class="keenwcchat">
			<div id="display-chat"><ul></ul></div>
			<div id="keenwcchat-message">
				<p class="typing-status"></p>
				<div class="keenwcchat_box">
					<textarea class="keenwcchat-textarea"></textarea>
				</div>
				<button id="upload_image">Upload image</button>
				<input type="hidden" name="attachment" id="attachment" value=""/>
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
		if(is_admin() && ('shop_order' === get_post_type($_GET['post'])) && ( 'edit' == $_GET['action'])){
			// track the seller id
			update_post_meta(intval(isset($_GET['post'])), 'seller_id', get_current_user_id());
			return true;
		}
		return false;
	}

	public function is_customer_or_subscriber($id){
		$user = get_userdata( $id );
		return !empty(array_intersect(['customer', 'subscriber'], $user->roles));
	}

	public function is_admin_or_shop_manager($id){
		$user = get_userdata( $id );
		return !empty(array_intersect(['shop_manager', 'administrator'], $user->roles));
	}

	public function keenwcchat_push_message(){
		$message = $_POST['message'];
		$orderId = intval($_POST['orderId']);
		$user = wp_get_current_user();
		$chat_history = get_post_meta($orderId, 'order_chat', true);
		// $is_cus_sub = $this->is_customer_or_subscriber($user->ID);
				
		// update basic data structure if not already set
		if(!isset($chat_history['chat'])){
			$customerId = $this->customerId($orderId);
			$chat_data = [
				'id' => $orderId,
				'customer' => [ 
					'id' => $customerId,
					'online' => false,
				],
				'seller' => [
					'online' => false,
				],
				'chat' => [],
			];
			update_post_meta($orderId, 'order_chat', $chat_data);
		}

		// push new chat
		$new_chat = [
			'user' 	 => $user->ID,
			'text'	 => $message,
			'time'	 => time(),
		];
		if(isset($_POST['attachment']) && !empty($_POST['attachment'])) {
			$new_chat['attachment'] = $_POST['attachment'];
		}
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
		$chatingWith = intval($_POST['chatingWith']);
		$status = get_transient('keenwcchat_typing_' . $chatingWith);
		wp_send_json(['typing' => $status]);
		exit;
	}

}
