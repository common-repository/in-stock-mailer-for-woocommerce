<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function ism_stock_alert_js() {

    if ( is_product() ) {

      $display_manager = new displayManager();
      $product = wc_get_product( get_the_ID() );
      $is_variation = $product->is_type( 'variable' );
      $is_variation_button_active = $display_manager->get_user_option('ism_button_individual_variation');

    	wp_enqueue_script( 'out-of-stock', ISM_URL_PATH . 'assets/js/out-of-stock.js',  array('jquery'), 1.0, true);
      wp_localize_script( 'out-of-stock', 'alertData', array(
        'api_base_url' => esc_url_raw( rest_url('in-stock-mailer/v1') ),
        'product_id' => esc_attr( get_the_ID() ),
        'is_variation' => rest_sanitize_boolean( $is_variation ),
        'is_variation_button_active' => rest_sanitize_boolean( $is_variation_button_active ),
        'nonce' => wp_create_nonce( 'wp_rest' ),
        'is_user_registered' => esc_attr( get_current_user_id() )
      ) );

      if ( !wp_style_is( 'fontawesome' ) && $display_manager->get_user_option( 'ism_fontawesome' ) ) {
          wp_enqueue_style( 'fontawesome', ISM_FONTAWESOME_URL, false, '4.7.0' );
      }

      wp_enqueue_style( 'out-of-stock-style', ISM_URL_PATH . 'assets/css/out-of-stock.css');
      $bg_color = $display_manager->get_user_option('ism_button_color');
      $text_color = $display_manager->get_user_option('ism_button_text_color');

      $custom_css = ".product .notify-btn { background-color: ".esc_attr( $bg_color )."; color: ".esc_attr( $text_color )."; }";

      if ( $display_manager->get_user_option('ism_button_size') == 'normal' ) {
        $custom_css .= ".product .in-stock-form, .product #in-stock-form { width: 90% !important; max-width: 440px !important; }";
      }
      if ( $email_button_color = $display_manager->get_user_option('ism_submit_email_button_color') ) {
        $custom_css .= ".product .instock-input-section .instock-submit { background-color: ".esc_attr( $email_button_color )." !important;}";
      }
      if ( $email_text_color = $display_manager->get_user_option('ism_email_text_color') ) {
        $custom_css .= ".product .instock-input-section .instock-submit { color: ".esc_attr( $email_text_color )." !important;}";
      }
      if ( $email_field_back_color = $display_manager->get_user_option('ism_email_field_back_color') ) {
        $custom_css .= ".product .in-stock-email { background-color: ".esc_attr( $email_field_back_color )." !important;}";
      }
      if ( $email_field_color = $display_manager->get_user_option('ism_email_field_color') ) {
        $custom_css .= ".product .in-stock-email { color: ".esc_attr( $email_field_color)." !important;}";
      }

      wp_add_inline_style( 'out-of-stock-style', $custom_css );

    }
}
add_action( 'wp_enqueue_scripts', 'ism_stock_alert_js', 30 );
