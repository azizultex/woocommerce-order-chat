<?php
/**
 * Add Firebase setting menu
 * @var [type]
 */

class Firebase_Admin{
  private static $initiated = false;
  private static $options;

  public static function init(){
    if( ! self::$initiated ) {
      self::init_hooks();
    }
  }

  public static function init_hooks() {
    self::$initiated = true;
    self::$options = get_option("firebase_credentials");
    add_action( "admin_menu", array( "Firebase_Admin", "admin_menu" ) );
    add_action( "admin_init", array ( "Firebase_Admin", "register_settings" ) );
  }

  public static function admin_menu() {
    self::load_menu();
  }

  public static function load_menu() {

    $page_title = 'Firebase for Wordpress';
    $menu_title = 'WC Order Chat';
    $capability = 'manage_options';
    $menu_slug = 'wc-order-chat';
    $function = 'display_page';
    $icon_url = '';
    $position = '';

    add_options_page( __( $page_title, "keenwcchat" ), __( $menu_title, "keenwcchat" ), $capability, $menu_slug, array( "Firebase_Admin", $function ), $icon_url, $position );
  }

  public static function register_settings(){
    // General Setting
    register_setting(
        "firebase_option_group",
        "firebase_credentials",
        array( "Firebase_Admin", "sanitize")
    );

    add_settings_section(
        'setting_section_id', // ID
        'Enter your firebase credentials below:', // Title
        array( "Firebase_Admin", 'print_section_info' ), // Callback
        'general' // Page
    );

    add_settings_field(
        'api_key', // ID
        'API Key', // Title
        array( "Firebase_Admin", 'api_key_callback' ), // Callback
        'general', // Page
        'setting_section_id' // Section
    );

    add_settings_field(
        'auth_domain', // ID
        'Auth Domain', // Title
        array( "Firebase_Admin", 'auth_domain_callback' ), // Callback
        'general', // Page
        'setting_section_id' // Section
    );
  }

/**
 * Sanitize each setting field as needed
 *
 * @param array $input Contains all settings fields as array keys
 */
  public static function sanitize( $input )
  {
      $new_input = array();

      // General

      if( isset( $input['api_key'] ) )
          $new_input['api_key'] = sanitize_text_field( $input['api_key'] );

      if( isset( $input['auth_domain'] ) )
          $new_input['auth_domain'] = sanitize_text_field( $input['auth_domain'] );

      return $new_input;
  }
  
  /**
   * Print the Section text
   */
  public static function print_section_info()
  {
      print "Firebase info can be found in Project <strong>Setting > SERVICE ACCOUNTS</strong> in <a href='https://console.firebase.google.com' target='_blank'>Firebase Console</a>";
  }

  /**
   * Get the settings option array and print one of its values
   */
  public static function api_key_callback()
  {
      self::print_form("api_key");
  }

  /**
   * Get the settings option array and print one of its values
   */
  public static function auth_domain_callback()
  {
      self::print_form("auth_domain");
  }

  /**
   * Get the settings option array and print one of its values
   */
  public static function database_type_callback($args, $database_type = 'database_type')
  {
      printf(
          '<select name="%1$s[%2$s]" id="%3$s">',
          $args['option_name'],
          $args['name'],
          $args['name']
      );

      foreach ( $args['options'] as $val => $title )
          printf(
              '<option value="%1$s" %2$s>%3$s</option>',
              $val,
              selected( $val, $args['value'], FALSE ),
              $title
          );

      print '</select>';
  }


  /**
   * Print input form
   * @param  [type] $form_id [description]
   * @return [type]          [description]
   */
  public static function print_form($form_id){
      printf(
          "<input type='text' id='$form_id' name='firebase_credentials[$form_id]' value='%s'  style='width: %u%%;'/>",
          isset( self::$options[$form_id] ) ? esc_attr( self::$options[$form_id]) : '', 100
      );
  }

  /**
   * Display firebase content on Setting page
   * @return [type] [description]
   */
  public static function display_page() {
    echo "<div class='wrap'>";
        echo "<h1>Firebase Realtime Database Settings</h1>";
        settings_errors();

        $active_tab = isset ( $_GET['tab'] ) ? $_GET['tab'] : 'general';
        $general_class = $active_tab === 'general' ? 'nav-tab-active' : '';
        $database_class = $active_tab === 'database' ? 'nav-tab-active' : '';
        $about_class = $active_tab === 'about' ? 'nav-tab-active' : '';

        echo "<h2 class='nav-tab-wrapper'>";
          echo "<a href='?page=firebase-setting&tab=general' class='nav-tab $general_class'>General</a>";
          echo "<a href='?page=firebase-setting&tab=about' class='nav-tab $about_class'>About</a>";
        echo "</h2>";

        echo "<form method='post' action='options.php'>";
            // This prints out all hidden setting fields
            if( $active_tab === 'general') {
              settings_fields( 'firebase_option_group' );
              do_settings_sections( 'general' );
              submit_button();
            } else if ($active_tab === 'about'){

              printf('<h3>%s<a href="%s" target="_blank">Keendevs</a></h3>', __('This plugin is developed by ', 'keenwcchat'), esc_url('https://keendevs.com'));

              printf('<p>%s<a href="%s" target="_blank">%s</a> ;)</p>', __('If you appreciate the work and want to show your support, just ', 'keenwcchat'), esc_url('https://www.paypal.me/azizultex'), __('buy me a coffee!', 'keenwcchat'));

              printf('<p>%s<a href="%s" target="_blank">Github</a>.</p>', __('If you want to create issues or request features, please post it on ', 'keenwcchat'), esc_url('https://github.com/azizultex/woocommerce-order-chat/issues'));

            }

        echo "</form>";
    echo "</div>";
  }
}
?>