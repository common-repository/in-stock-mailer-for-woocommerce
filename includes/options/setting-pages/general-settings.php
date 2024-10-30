<?php

if ( ! defined( 'ABSPATH' ) ) exit;


function ism_add_general_settings() {

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
                 'label'=> 'Notification Text',
                 'description'=> 'This will appear on the "notify when available" button.'] );

        add_settings_field(
               'ism_button_text_cancel',
               '',
               'ism_callback_field_text',
               'in-stock-mailer',
               'ism_button_settings',
               ['id'=>'ism_button_text_cancel',
                'label'=> 'Signed up Text',
                'description'=> 'This will appear on the button on success.'] );

        add_settings_field(
               'ism_button_color',
               '',
               'ism_callback_color_picker_w_image',
               'in-stock-mailer',
               'ism_button_settings',
               ['id'=>'ism_button_color',
                'label'=> 'Button Background',
                'img_src' =>  ISM_URL_PATH . 'includes/options/img/isa-btn-example-1.jpg',
                'img_description' => 'Here you can change the default colors of your alert button.<br>',
                'description'=> 'Alert button background color.'] );

        add_settings_field(
               'ism_button_text_color',
               '',
               'ism_callback_color_picker',
               'in-stock-mailer',
               'ism_button_settings',
               ['id'=>'ism_button_text_color',
                'label'=> 'Button Text',
                'description'=> 'Alert button text color.'] );

        add_settings_field(
               'ism_submit_email_button_color',
               '',
               'ism_callback_color_picker_w_image',
               'in-stock-mailer',
               'ism_button_settings',
               ['id'=>'ism_submit_email_button_color',
                'label'=> 'Submit Background',
                'img_src' =>  ISM_URL_PATH . 'includes/options/img/isa-btn-example-2.jpg',
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
                'label'=> 'Submit Text',
                'description'=> 'Email Submit Text Color'] );

        add_settings_field(
               'ism_email_field_back_color',
               '',
               'ism_callback_color_picker',
               'in-stock-mailer',
               'ism_button_settings',
               ['id'=>'ism_email_field_back_color',
                'label'=> 'Field Background',
                'description'=> 'Email Field Background Color'] );

        add_settings_field(
               'ism_email_field_color',
               '',
               'ism_callback_color_picker',
               'in-stock-mailer',
               'ism_button_settings',
               ['id'=>'ism_email_field_color',
                'label'=> 'Field Text',
                'description'=> 'Email Field Text Color'] );

        add_settings_field(
               'ism_button_size',
               '',
               'ism_callback_radio_field',
               'in-stock-mailer',
               'ism_button_settings',
               ['id'=>'ism_button_size',
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
                'label'=> 'Enable FontAwesome',
                'description'=> 'When enabled, FontAwesome icons will be displayed'] );

        add_settings_field(
               'ism_enable_backorder_alert',
               '',
               'ism_callback_switch',
               'in-stock-mailer',
               'ism_button_settings',
               ['id'=>'ism_enable_backorder_alert',
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
                'label'=> 'Image Header',
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
                'label'=> 'Email Subject',
                'description'=> 'The email subject. Make this stand out and catch your customer\'s eyes'] );

        add_settings_field(
               'ism_email_body',
               '',
               'ism_callback_field_textarea',
               'in-stock-mailer',
               'ism_email_settings',
               ['id'=>'ism_email_body',
                'label'=> 'Email Body',
                'description'=> 'The email body starts with "Hello {customer name}," ( if present ),<br>
                followed by this block that you can customize as you wish.<br>
                No links are allowed in this field.'] );
}


?>
