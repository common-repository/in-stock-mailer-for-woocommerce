<?php

if ( ! defined( 'ABSPATH' ) ) exit;


function ism_display_setting_page() {

    if ( ! current_user_can( 'manage_options' ) ) return;

      $tab = !empty($_GET['tab']) ? strtolower($_GET['tab']) : ''; ?>
      <div id="isa-logo-header" >
        <img src="<?php echo ISM_URL_PATH . 'assets/img/isa-inline-logo.png'; ?>" style="max-width:500px;">
      </div>
      <h2 class="nav-tab-wrapper">
      <a href="?page=in-stock-mailer&amp;tab=pending&amp;groupby=email" class="nav-tab <?php echo ($tab == 'pending' || empty($tab)) ? 'nav-tab-active' : ''; ?>">Pending Alerts</a>
      <a href="?page=in-stock-mailer&amp;tab=sent" class="nav-tab <?php echo ($tab == 'sent') ? 'nav-tab-active' : ''; ?>">Sent Alerts</a>
      <a href="?page=in-stock-mailer&amp;tab=settings" class="nav-tab <?php echo $tab == 'settings' ? 'nav-tab-active' : ''; ?>">General Settings</a>
      </h2>
      <?php
          $notices = get_settings_errors();
          $admin_notices = '';
          if ( !empty($notices) ) {
            foreach ( $notices as $notice ) {
              $type = $notice['type'] != 'success' ? 'error' : '';
              $admin_notices .= '<div id="message" class="updated inline '.$type.'"><p><strong>'.esc_html( $notice['message'] ).'</strong></p></div>';
            }
          }
          echo $admin_notices;
      ?>
    <?php
    if (  $tab == 'settings'  ) :?>
      <div class="wrap">

        <div class="ism-option-block">
          <div class="ism-option-title" style="width: 100%;">Button Settings</div>
          <div id="button-option-container" class="ism-option-container" style="display:none;">
            <form id="in-stock-mailer-form-button" action="options.php#button-option-container" method="post">
              <?php
                settings_errors('ism_stock_alert_button_options');
                settings_fields( 'ism_stock_alert_button_options' ); ?>
                <div class="ism-button-settings">
                  <?php ism_do_settings_section( 'in-stock-mailer', 'ism_button_settings' ); ?>
                </div>


                <div class="isa-submit-container" style="display:flex; flex-wrap: no-wrap; ">
                <?php

                  submit_button($text = null, $type = 'primary', $name = 'submit-settings', $wrap = true, $other_attributes = ['id' => 'submit-settings-button'] ); ?>
                  <input style="display: none;" id="ism_stock_alert_options_default_settings_button" name="ism_stock_alert_button_options[default_settings]" type="text" size="50" value="false">
                  <p class="submit" style="margin-left: 20px;"><input type="submit" name="submit-default" id="submit-reset-button" class="button button-primary" value="Reset Settings"></p>
                </div>
            </form>
          </div>
        </div>

        <div class="ism-option-block">
          <div class="ism-option-title" style="width: 100%;">Email Settings</div>
          <div id="email-option-container" class="ism-option-container" style="display:none;">
        <form id="in-stock-mailer-form-email" action="options.php#email-option-container" method="post">
          <?php
            settings_errors('ism_stock_alert_email_options');
            ism_settings_fields( 'ism_stock_alert_email_options', '_wpnonce_email' ); ?>

            <div class="ism-email-settings">
              <?php ism_do_settings_section( 'in-stock-mailer', 'ism_email_settings' ); ?>
            </div>

            <div class="isa-submit-container" style="display:flex; flex-wrap: no-wrap; ">
            <?php
              submit_button($text = null, $type = 'primary', $name = 'submit-settings', $wrap = true, $other_attributes = ['id' => 'submit-settings-email'] ); ?>
              <input style="display: none;" id="ism_stock_alert_options_default_settings_email" name="ism_stock_alert_email_options[default_settings]" type="text" size="50" value="false">
              <p class="submit" style="margin-left: 20px;"><input type="submit" name="submit-default" id="submit-reset-email" class="button button-primary" value="Reset Settings"></p>
            </div>
        </form>
      </div>
    </div>

    <div class="ism-option-block">
      <div class="ism-option-title" style="width: 100%;">Mailchimp Config</div>
      <div id="mchimp-option-container" class="ism-option-container" style="display:none;">
        <form id="in-stock-mailer-form-mchimp" action="options.php" method="post">

          <div id="mchimp-title-block">
            <img id="mchimp-title-logo" src="<?php echo ISM_URL_PATH . 'assets/img/mc_logo.jpg'; ?>" >
            <h2 class="">Mailchimp Config</h2>
          </div>
          <table class="form-table" role="presentation">
            <tbody>
            <?php ism_callback_switch( [
                                        'id' => 'mchimp-enable',
                                        'option' => 'ism_custom_option',
                                        'label' => 'Enable/Disable',
                                        'bypass_value' => rest_sanitize_boolean( get_option('mchimp_enabled') ) ] ); ?>
            </tbody>
          </table>
          <div id="mchimp-options-container" class="<?php echo rest_sanitize_boolean( get_option('mchimp_enabled') )  ? '' : 'hidden' ?>">

          <p><div class="mchimp-loader"></div></p>
          <p class="mchimp-setup mchimp-success hidden">Configured</p>
          <p class="mchimp-setup mchimp-error hidden">Something went wrong</p>
          <p class="mchimp-setup mchimp-alert hidden">Please review your configuration</p>


            <table class="form-table" role="presentation">
              <tbody>
                <?php ism_callback_field_pass(['id'=>'mchimp_api_key',
                                               'option'=> 'ism_custom_option',
                                               'label' => 'API KEY',
                                               'bypass_value' => get_option( 'ism_mc_api') ? '**************************' : '',
                                               'required' => true,
                                               'disabled' => true,
                                               'description' => 'Get your API KEY from your Profile -> Extras -> Api Key. <br/>Or simply follow this <a target="_blank" href="https://admin.mailchimp.com/account/api/"><b>link</b></a>.',
                                               'placeholder'=>'API Key']); ?>

                <?php ism_callback_field_text(['id'=>'mchimp_server',
                                               'option'=> 'ism_custom_option',
                                               'label' => 'Server Prefix',
                                               'bypass_value' => esc_attr(get_option( 'ism_mc_server_id')),
                                               'required' => true,
                                               'disabled' => true,
                                               'description' => 'Log into your Mailchimp account and look at the URL in your browser. <br/>You’ll see something like https://us4.admin.mailchimp.com/,<br/>the us4 part is the server prefix.',
                                               'placeholder'=>'Ex: us4']); ?>


                <tr valign="top">
                  <th scope="row">Select a store</th>
                    <td>
                      <div id="selected-store-container" data-store-id="<?php echo esc_attr(get_option( 'ism_mc_set_store')); ?>">
                        <div id="empty-store-container">Your stores will be shown here</div>
                      </div>
                    </td>
                </tr>

               </tbody>
             </table>
              <div class="isa-submit-container" style="display:flex; flex-wrap: no-wrap; ">
              <?php

                submit_button($text = null, $type = 'primary', $name = 'submit-settings', $wrap = true, $other_attributes = ['id' =>'mchimp-setting-input'] ); ?>
                <input style="display: none;" name="ism_stock_alert_mchimp_default" type="text" size="50" value="false">
                <p class="submit" style="margin-left: 20px;"><input type="submit" name="submit-default" id="submit-reset-mchimp" class="button button-primary" value="Reset Settings"></p>
              </div>
            </div>
          </div>
        </div>

        </form>


        <div class="ism-option-block">
          <div class="ism-option-title" style="width: 100%;">Test Functionalities</div>
          <div id="mchimp-option-container" class="ism-option-container" style="display:none;">
              <h2 class="send-test-title">Send a Test Email</h2>
              <p class="isa-error-message"></p><p class="isa-success-message"></p>
              <form id="email-notification-test" action="options.php" method="post">
              <?php
                ism_send_test_email_to_field();
                submit_button($text = 'Send Email', $type = 'primary', $name = 'submit-email'); ?>
                <div class="loader-email"></div>
              </form>
            </div>
          </div>


          <div style="margin-top: 20px;" ><?php ism_support_page_link(); ?></div>
      </div>
    <?php
    elseif ($tab == 'pending' || empty($tab) ) :?>
    <div style="margin-top:0px; margin-right:1%">
      <div class="ism-wrap">
        <?php ism_show_pending_status(); ?>
        <div style="margin-top: 20px;" ><?php ism_support_page_link(); ?></div>
      </div>
    </div>
    <?php

    elseif ($tab == 'sent') :?>
    <div style="margin-top:0px; margin-right:1%;">
      <div class="ism-wrap">
        <?php ism_show_sent_status(); ?>
        <div style="margin-top: 20px;" ><?php ism_support_page_link(); ?></div>
      </div>
    </div>
    <?php
    else :
        $location = admin_url() . '/options-general.php?page=in-stock-mailer';
        wp_safe_redirect( $location, $status = 302 );
    endif;
}
