<?php

  if ( ! defined( 'ABSPATH' ) ) exit;

  /**
  * Adds a new subpage with Plugin options to WooCommerce Options Menu
  */

  function ism_add_link_to_woo_menu() {
      global $submenu;

      add_submenu_page( 'woocommerce', __( 'In Stock Mailer', ISM_DOMAIN ), __( 'In Stock Mailer', ISM_DOMAIN ), 'view_woocommerce_reports', 'wc-ism-settings', 'ism_display_woo_setting_page');

      $woo_menu = $submenu['woocommerce'];
      foreach ($submenu['woocommerce'] as $key=>$page) {
         if ( ! empty($page[0]) && $page[0] == 'In Stock Mailer'  ) {
           $ism_menu_key = $key;
         }
      }
      foreach ($submenu['woocommerce'] as $key=>$page) {
        if ( ! empty($page[0]) && $page[0] == 'Orders'   ) {
          $switch_key = $key +1;
        }
      }

      if ( isset($switch_key) && isset($ism_menu_key) ) {
        $menu_p1 = array_slice( $woo_menu, 0, $switch_key, true ) + array( $switch_key + 1 => $submenu['woocommerce'][$ism_menu_key] );
        unset($submenu['woocommerce'][$ism_menu_key]);
        $menu_p2 = array_slice( $submenu['woocommerce'], $switch_key, count($submenu['woocommerce']), true );
        $new_submenu = array_merge( $menu_p1, $menu_p2);
        $submenu['woocommerce'] = $new_submenu;
      }

  }
  add_action('admin_menu', 'ism_add_link_to_woo_menu', 99);

  function ism_display_woo_setting_page() {

      if ( ! current_user_can( 'manage_options' ) ) return;

      $tab = !empty($_GET['ism_tab']) ? $_GET['ism_tab'] : '';
      ?>

        <!-- Alerts JavaScript that this is Custom WooCommerce Menu Tab -->
        <script> var is_woo_menu_ism_tab = true; </script>
        <!-- Removes Save Button on Custom Tab -->
        <style>.woocommerce-save-button { display: none !important;}</style>

        <div id="isa-logo-header" class="ism-logo-header-woo-tab">
          <img src="<?php echo ISM_URL_PATH . '/includes/options/img/isa-inline-logo.png'; ?>" style="max-width:260px;">
        </div>

        <h2 class="nav-tab-wrapper woo-tab-wrapper">
          <a href="?page=wc-ism-settings&amp;tab=ism_menu" class="nav-tab <?php echo empty($tab) ? 'nav-tab-active' : ''; ?>">General Settings</a>
          <a href="?page=wc-ism-settings&amp;tab=ism_menu&amp;ism_tab=pending&amp;groupby=email" class="nav-tab <?php echo ($tab == 'pending') ? 'nav-tab-active' : ''; ?>">Pending Alerts</a>
          <a href="?page=wc-ism-settings&amp;tab=ism_menu&amp;ism_tab=sent" class="nav-tab <?php echo ($tab == 'sent') ? 'nav-tab-active' : ''; ?>">Sent Alerts</a></h2>
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
      if ( empty($tab) ) :?>
        <div class="ism-wrap">
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
                        <div class="save-loader"></div>
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
