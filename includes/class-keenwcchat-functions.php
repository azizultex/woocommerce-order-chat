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
			<div id="display-chat">
			</div>
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

	// update seller_id to start chat with
	public function keenwcchat_chat_set_seller_id($order_id){
		update_post_meta($order_id, 'seller_id', 1);
	}

	/*
	 * rendering meta box in orders for convos
	*/
	function keenwcchat_admin_message_box() {
		add_meta_box( 'orders_convo', 'Conversation',
				array($this,'keenwcchat_message_box'),
				'shop_order', 'advanced', 'high');
	}

	// set seller_id for live chat
	public function set_seller_id(){
		if($this->is_admin_order_edit_page()){
			// track the seller id
			update_post_meta(intval($_GET['post']), 'seller_id', get_current_user_id());
		}
	}
	
	public function is_admin_order_edit_page(){
		if(is_admin() && ('shop_order' === get_post_type($_GET['post'])) && ( 'edit' == $_GET['action'])){
			return true;
		}
		return false;
	}

	public function retrieve_order_id(){
		global $wp;
		if($this->is_admin_order_edit_page()){
			return intval($_GET['post']);
		} else {
			return $wp->query_vars['view-order'] ? $wp->query_vars['view-order'] : $wp->query_vars['order-received'];
		}
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

	public function is_customer_or_subscriber($id){
		$user = get_userdata( $id );
		return !empty(array_intersect(['customer', 'subscriber'], $user->roles));
	}

	public function is_admin_or_shop_manager($id){
		$user = get_userdata( $id );
		return !empty(array_intersect(['shop_manager', 'administrator'], $user->roles));
	}

	public function keenwcchat_typing(){
		$user = get_current_user_id();
		set_transient('keenwcchat_typing_' . $user, true, 120); 
		wp_send_json($user);
	}

	public function keenwcchat_not_typing(){
		$user = get_current_user_id();
		delete_transient('keenwcchat_typing_' . $user);
		wp_send_json('not typing. transient deleted.');
	}

	public function get_typing_status(){
		$chatingWith = intval($_POST['chatingWith']);
		$status = get_transient('keenwcchat_typing_' . $chatingWith);
		wp_send_json(['typing' => $status]);
	}

}
