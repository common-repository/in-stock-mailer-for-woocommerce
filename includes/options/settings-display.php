<?php

if ( ! defined( 'ABSPATH' ) ) exit;


function ism_display_setting_page() {

    if ( ! current_user_can( 'manage_options' ) ) return;

    $tab = !empty($_GET['tab']) ? $_GET['tab'] : '';
    ?>
      <div id="isa-logo-header" >
        <img src="<?php echo ISM_URL_PATH . '/includes/options/img/isa-inline-logo.png'; ?>" style="max-width:260px;">
      </div>
      <h2 class="nav-tab-wrapper">
      <a href="?page=in-stock-mailer" class="nav-tab <?php echo empty($tab) ? 'nav-tab-active' : ''; ?>">General Settings</a>
      <a href="?page=in-stock-mailer&amp;tab=pending&amp;groupby=email" class="nav-tab <?php echo ($tab == 'pending') ? 'nav-tab-active' : ''; ?>">Pending Alerts</a>
      <a href="?page=in-stock-mailer&amp;tab=sent" class="nav-tab <?php echo ($tab == 'sent') ? 'nav-tab-active' : ''; ?>">Sent Alerts</a></h2>
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
    if ( empty($tab) ) :?>
      <div class="wrap">
        <form id="in-stock-mailer-form" action="options.php" method="post">
          <?php
            settings_fields( 'ism_stock_alert_options' );
            do_settings_sections( 'in-stock-mailer' );
          ?>
          <div class="isa-submit-container" style="display:flex; flex-wrap: no-wrap; ">
          <?php
            submit_button($text = null, $type = 'primary', $name = 'submit-settings'); ?>
            <input style="display: none;" id="ism_stock_alert_options_default_settings" name="ism_stock_alert_options[default_settings]" type="text" size="50" value="false">
            <p class="submit" style="margin-left: 20px;"><input type="submit" name="submit-default" id="submit-reset" class="button button-primary" value="Reset Settings"></p>
          </div>
        </form>
        <hr>
        <h1 class="send-test-title">Send a Test Email</h1>
        <p class="isa-error-message"></p><p class="isa-success-message"></p>
        <form id="email-notification-test" action="options.php" method="post">
        <?php
          ism_send_test_email_to_field();
          submit_button($text = 'Send Email', $type = 'primary', $name = 'submit-email'); ?>
          <div class="loader-email"></div>
        </form>

      </div>
    <?php
    elseif ($tab == 'pending') :?>
    <div style="margin-top:40px; margin-right:1%">
      <?php ism_show_pending_status(); ?>
    </div>
    <?php

    elseif ($tab == 'sent') :?>
    <div style="margin-top:40px; margin-right:1%;">
      <?php ism_show_sent_status(); ?>
    </div>
    <?php
    else :
        $location = admin_url() . '/options-general.php?page=in-stock-mailer';
        wp_safe_redirect( $location, $status = 302 );
    endif;
}
 ?>
