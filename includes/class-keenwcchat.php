<?php 

class Keenwcchat {

    protected $loader;
    protected $version;
    protected $plugin_name;
    
    function __construct(){
        if ( defined( 'KEENWCCHAT_VERSION' ) ) {
			$this->version = KEENWCCHAT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
        $this->plugin_name = 'keenwcchat';
        
        $this->load_dependencies();
        $this->load_styles_scripts();
        $this->load_functions();
    }

    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-keenwcchat-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-keenwcchat-functions.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-keenwcchat-styles-scripts.php';
        $this->loader = new Keenwcchat_Loader();
    }

    private function load_styles_scripts(){
        $scripts = new Load_Scripts_Styles( $this->plugin_name, $this->version );
        // frontend
        $this->loader->add_action( 'wp_enqueue_scripts', $scripts, 'enqueue_scripts' );
        $this->loader->add_action( 'wp_enqueue_scripts', $scripts, 'enqueue_styles' );
        // backend
        $this->loader->add_action( 'admin_enqueue_scripts', $scripts, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_enqueue_scripts', $scripts, 'enqueue_styles' );
    }

    private function load_functions(){
        $functions = new Keenwcchat_Functions( $this->plugin_name, $this->version );
        $this->loader->add_action( 'woocommerce_order_details_before_order_table', $functions, 'keenwcchat_message_box' );
        // push basic chat data on new order
		$this->loader->add_action( 'woocommerce_new_order', $functions, 'keenwcchat_chat_basic_data' );
		// update chat history
		$this->loader->add_action( 'wp_ajax_keenwcchat_push_message', $functions, 'keenwcchat_push_message' );
		// load chat history
        $this->loader->add_action( 'wp_ajax_keenwcchat_load_chat', $functions, 'keenwcchat_load_chat' );
        // update typing
        $this->loader->add_action( 'wp_ajax_keenwcchat_typing', $functions, 'keenwcchat_typing' );
        // update not typing
        $this->loader->add_action( 'wp_ajax_keenwcchat_not_typing', $functions, 'keenwcchat_not_typing' );
        // get typing status
        $this->loader->add_action( 'wp_ajax_get_typing_status', $functions, 'get_typing_status' );
        // load chat widget for seller
        $this->loader->add_action( 'admin_init', $functions, 'keenwcchat_admin_message_box' );
        // allow customers upload media on chat
        $this->loader->add_action( 'admin_init', __CLASS__, 'allow_customers_upload_media' );
        // set seller_id to enable chat features
        // $this->loader->add_action( 'admin_init', $functions, 'set_seller_id' );
        // filter only self media 
        $this->loader->add_filter( 'ajax_query_attachments_args', __CLASS__, 'filter_to_self_media_only' );
    }

    public function run() {
        if(class_exists( 'WooCommerce' )){ // if wc installed and activated
            $this->loader->run();
        } else {
            add_action( 'admin_notices', array($this, 'keenwcchat_admin_notice') );
        }
    }

    // allow customers upload media on chat
    static function allow_customers_upload_media() {
        $customer = get_role('customer');
        $customer->add_cap('upload_files');
        $customer->add_cap('edit_published_posts');
        $customer->add_cap('edit_others_posts');
    }

    // filter only self media
    public function filter_to_self_media_only($query){
        // admins get to see everything
        if ( ! current_user_can( 'manage_options' ) )
            $query['author'] = get_current_user_id();
        return $query;
    }
    
    // admin notice if wc not active
    public function keenwcchat_admin_notice(){
		$class = 'notice notice-error';
		$message = __( 'Requires WooCommerce plugin to be installed and activated.', 'keenwcchat' );
		printf( '<div class="%1$s"><p><strong>%2$s</strong>%3$s</p></div>', esc_attr( $class ), esc_html__('WooCommerce Order Chat ', 'keenwcchat'), $message );
    }


}