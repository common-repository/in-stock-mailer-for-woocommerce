<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once(ISM_PATH . 'includes/options/setting-pages/settings-display.php' );
require_once(ISM_PATH . 'includes/options/setting-pages/pending-alerts.php' );
require_once(ISM_PATH . 'includes/options/setting-pages/sent-alerts.php' );


function ism_register_option_menu_page() {

    $menu_page = add_menu_page( 'in-stock-mailer',
                                'In Stock Mailer',
                                'manage_options',
                                'in-stock-mailer',
                                'ism_display_setting_page',
                                'dashicons-email-alt');


    add_submenu_page( 'in-stock-mailer', 'In Stock Mailer', 'Pending', 'manage_options', 'in-stock-mailer' );
    add_submenu_page( 'in-stock-mailer', 'Sent Email', 'Sent', 'manage_options', 'admin.php?page=in-stock-mailer&tab=sent' );
    add_submenu_page( 'in-stock-mailer', 'Settings', 'Settings', 'manage_options', 'admin.php?page=in-stock-mailer&tab=settings' );

    // Enqueues script/style on Plugin Menu
    add_action('admin_print_scripts-' . $menu_page, function() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'out-of-stock', ISM_URL_PATH . 'assets/css/out-of-stock-admin.css');
        // Update / Reset double Message fix
        $custom_css = "#setting-error-settings_updated, #setting-error-success_message { display: none !important; }";
        wp_add_inline_style( 'out-of-stock', $custom_css );

        $display_manager = new displayManager();
        wp_enqueue_script( 'out-of-stock-admin', ISM_URL_PATH . 'assets/js/out-of-stock-admin.js',  array('jquery', 'wp-color-picker'), 1.0, true);
        wp_enqueue_script( 'mchimp-api-admin', ISM_URL_PATH . 'assets/js/mchimp-api-admin.js',  array('jquery'), 1.0, true);
        wp_localize_script( 'out-of-stock-admin', 'alertDataAdmin', array(
          'api_base_url' => esc_url_raw( rest_url('in-stock-mailer/v1') ),
          'nonce' => wp_create_nonce( 'wp_rest' ),
          'current_tab' => !empty($_GET['tab']) ? esc_html($_GET['tab']) : '',
          'default_header_img_url' => esc_url($display_manager->get_user_option( 'ism_email_header_img_url')) ,
          'no_image_link' => esc_url( ISM_URL_PATH . 'assets/img/no-image.jpg') ,
          'current_state_mchimp' => rest_sanitize_boolean( get_option('mchimp_enabled') )
        ) );
    });

}
add_action( 'admin_menu', 'ism_register_option_menu_page', 20 );

function ism_change_menu_order() {
  global $menu;

  foreach ($menu as $key => $array) {
    if ( $array[3] == 'Analytics' ) { $analytics_pos = $key; }
    if ( $array[3] == 'in-stock-mailer' ) { $chatster_pos = $key; }
  }

  if ( isset($analytics_pos) ) {
    $x = 1;
    while( $x <= count($menu)  ) {
        if ( ! isset($menu[$analytics_pos + $x]) && isset($menu[$chatster_pos])) {
          $menu[$analytics_pos + $x] = $menu[$analytics_pos];
          $menu[$analytics_pos] = $menu[$chatster_pos];
          unset($menu[$chatster_pos]);
          break;
        }
        $x++;
    }
  }
}
add_action( 'admin_menu', 'ism_change_menu_order', 99);

function ism_register_settings() {

  register_setting(
          'ism_stock_alert_button_options',
          'ism_stock_alert_button_options',
          'ism_validate_button_options' );

  register_setting(
          'ism_stock_alert_email_options',
          'ism_stock_alert_email_options',
          'ism_validate_email_options' );

      add_settings_section(
              'ism_button_settings',
              'Button Settings',
              'ism_description',
              'in-stock-mailer' );

      add_settings_section(
              'ism_email_settings',
              'Email Settings',
              'ism_description',
              'in-stock-mailer' );

      // Button Settings
      add_settings_field(
              'ism_button_text',
              '',
              'ism_callback_field_text',
              'in-stock-mailer',
              'ism_button_settings',
              ['id'=>'ism_button_text',
               'option' => 'ism_stock_alert_button_options',
               'label'=> 'Notification Text',
               'description'=> 'This will appear on the "notify when available" button.'] );

      add_settings_field(
             'ism_button_text_cancel',
             '',
             'ism_callback_field_text',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_button_text_cancel',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Signed up Text',
              'description'=> 'This will appear on the button on success.'] );

      add_settings_field(
             'ism_button_color',
             '',
             'ism_callback_color_picker_w_image',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_button_color',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Button Background',
              'img_src' =>  ISM_URL_PATH . 'assets/img/isa-btn-example-1.jpg',
              'img_description' => 'Here you can change the default colors of your alert button.<br>',
              'description'=> 'Alert button background color.'] );

      add_settings_field(
             'ism_button_text_color',
             '',
             'ism_callback_color_picker',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_button_text_color',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Button Text',
              'description'=> 'Alert button text color.'] );

      add_settings_field(
             'ism_submit_email_button_color',
             '',
             'ism_callback_color_picker_w_image',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_submit_email_button_color',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Submit Background',
              'img_src' =>  ISM_URL_PATH . 'assets/img/isa-btn-example-2.jpg',
              'img_description' => 'For unregistered users a sign up email form will be displayed.<br>
                                    By default, your <u>Theme original colors</u> will be used. Change them according to your preferences. ',
              'description'=> 'Email Submit Color'] );

      add_settings_field(
             'ism_email_text_color',
             '',
             'ism_callback_color_picker',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_email_text_color',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Submit Text',
              'description'=> 'Email Submit Text Color'] );

      add_settings_field(
             'ism_email_field_back_color',
             '',
             'ism_callback_color_picker',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_email_field_back_color',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Field Background',
              'description'=> 'Email Field Background Color'] );

      add_settings_field(
             'ism_email_field_color',
             '',
             'ism_callback_color_picker',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_email_field_color',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Field Text',
              'description'=> 'Email Field Text Color'] );

      add_settings_field(
             'ism_button_size',
             '',
             'ism_callback_radio_field',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_button_size',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Button Size',
              'options'=> ['normal'=> esc_html__('normal', ISM_DOMAIN),
                           'wide'=> esc_html__('wide', ISM_DOMAIN)],
              'description'=> 'How wide the button will display.'] );

      add_settings_field(
             'ism_fontawesome',
             '',
             'ism_callback_switch',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_fontawesome',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Enable FontAwesome',
              'description'=> 'When enabled, FontAwesome icons will be displayed'] );

      add_settings_field(
             'ism_enable_backorder_alert',
             '',
             'ism_callback_switch',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_enable_backorder_alert',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Enable On Backorder',
              'description'=> 'If <span id="ism-backorder-word-descr">Backorders</span> are enabled: <b>"Allow, but notify customer"</b>, the alert button will be displayed.<br>
               Alert will not be displayed if the notification is disabled, as the customer will assume it\'s in-stock.'] );

      add_settings_field(
             'ism_button_individual_variation',
             '',
             'ism_callback_switch',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_button_individual_variation',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Enable On Individual Variation',
              'description'=> 'Allows nested alerts on each OUT-OF-STOCK product variation. Customers can pick a specific alert request on a specific product that is out of stock.<br>
              <b>IMP: Some Themes/Plugins/Settings hide out of stock variations. If you can\'t see out of stock variations on the page disable this option!</b>'] );

      add_settings_field(
             'ism_button_cancel_active',
             '',
             'ism_callback_switch',
             'in-stock-mailer',
             'ism_button_settings',
             ['id'=>'ism_button_cancel_active',
              'option' => 'ism_stock_alert_button_options',
              'label'=> 'Enable Cancel Alert',
              'description'=> 'When enabled the button will display a cancel option after the notification is requested.'] );

      // Email settings
      add_settings_field(
             'ism_email_header_img_url',
             '',
             'ism_callback_field_text',
             'in-stock-mailer',
             'ism_email_settings',
             ['id'=>'ism_email_header_img_url',
              'option' => 'ism_stock_alert_email_options',
              'label'=> 'Header Image',
              'placeholder'=> 'https://..',
              'description'=> 'You can use a custom image in your email header.<br>
              Please go to Media -> Library -> Add New, copy and paste the link in this field.<br>
              It\'s highly suggested to use an image size of <b>600 X 230 px</b>.'] );

      add_settings_field(
             'ism_email_subject',
             '',
             'ism_callback_field_text',
             'in-stock-mailer',
             'ism_email_settings',
             ['id'=>'ism_email_subject',
              'option' => 'ism_stock_alert_email_options',
              'label'=> 'Email Subject',
              'description'=> 'The email subject. Make this stand out and catch your customer\'s eyes'] );

      add_settings_field(
             'ism_email_body',
             '',
             'ism_callback_field_textarea',
             'in-stock-mailer',
             'ism_email_settings',
             ['id'=>'ism_email_body',
              'option' => 'ism_stock_alert_email_options',
              'label'=> 'Email Body',
              'description'=> 'The email body starts with "Hello {customer name}," ( if present ),<br>
              followed by this block that you can customize as you wish.<br>
              No links are allowed in this field.'] );

}
add_action( 'admin_init', 'ism_register_settings' );

function ism_add_settings_link( array $links ) {
    $url = get_admin_url() . "options-general.php?page=in-stock-mailer&amp;tab=settings";
    $settings_link = '<a href="' . $url . '">' . __('Settings', ISM_DOMAIN) . '</a>';
    $links[] = $settings_link;
    return $links;
  }
add_filter( 'plugin_action_links_' . plugin_basename( ISM_FILE_PATH ), 'ism_add_settings_link' );
