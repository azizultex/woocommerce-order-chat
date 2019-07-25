<?php

class Load_Scripts_Styles {

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

		$this->functions = new Keenwcchat_Functions($this->plugin_name, $this->version);

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if(is_view_order_page() || $this->functions->is_thankyou_page() || $this->functions->is_admin_order_edit_page()){
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __DIR__ ) . 'assets/css/keenwcchat-style.css', array(), $this->version, 'all' );
			// emojione picker
			wp_enqueue_style( 'emoji-one-picker', plugin_dir_url( __DIR__ ) . 'assets/css/emojione.picker.css', array(), null, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if(is_view_order_page() || $this->functions->is_thankyou_page() || $this->functions->is_admin_order_edit_page()){
			// emojione picker
			wp_enqueue_script( 'emoji-one-picker', plugin_dir_url( __DIR__ ) . 'assets/js/emojione.picker.min.js', array( 'jquery'), null, false );
			// keenchat scripts
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __DIR__ ) . 'assets/js/keenwcchat-scripts.js', array( 'jquery' ), $this->version, false );
			// localize necessary data
			wp_localize_script($this->plugin_name, 'keenwcchat', $this->localize_data());

			wp_enqueue_media();

		}
    }
    
    public function localize_data(){
		$orderId = $this->functions->retrieve_order_id();
		$userId = $this->functions->customerId($orderId);
		$sellerId = get_post_meta($orderId, 'seller_id', true);
		$chatingWith = is_admin() ? $userId : $sellerId; // used to get typing status

		return [
			'user'	  		=> get_current_user_id(),
			'chatingWith' 	=> $chatingWith,
			'user_img'		=> get_avatar_url($userId),
			'seller_img' 	=> get_avatar_url($sellerId),
			'orderId' 		=> $orderId,
			'ajax' 	  		=> admin_url( 'admin-ajax.php' ),
		];
	}

}
