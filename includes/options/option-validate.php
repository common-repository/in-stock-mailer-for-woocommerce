<?php

if ( ! defined( 'ABSPATH' ) ) exit;


function ism_validate_button_options($input) {

  if ( ! current_user_can( 'manage_options' ) ) return;

  if ( !empty($input['default_settings']) &&
          "reset" === $input['default_settings'] ) {

    delete_option( 'ism_stock_alert_button_options' );

    add_settings_error(
        'ism_stock_alert_button_options', // Setting slug
        'success_message',
        'Button settings have been reset!',
        'success'
    );
    return false;
  }

  $err_msg = '';
	$options = get_option( 'ism_stock_alert_button_options', ism_default_button_values() ) + ism_default_button_values();


  foreach (array( 'ism_button_text', 'ism_button_text_cancel' ) as $value) {
    if ( isset($input[$value]) ) {
      $text = $input[$value];
      if ( strlen($text) > ism_get_text_length($value) ) {
        $input[$value] = isset( $options[$value] ) ? $options[$value] : '';
        $err_msg .= __('Button text exceeds 42 characters <br>', ISM_DOMAIN);
      }
    }
  }

  foreach (array( 'ism_submit_email_button_color', 'ism_email_text_color','ism_email_field_back_color', 'ism_email_field_color' ) as $value) {
    if ( isset($input[$value])) {
      $input[$value] = sanitize_hex_color( $input[$value] );
      if ( empty($input[$value]) ) {
        $input[$value] = false;
      }
    }
   }

  foreach (array( 'ism_button_color', 'ism_button_text_color' ) as $value) {
    if ( isset($input[$value]) ) {
      $input[$value] = $color = sanitize_hex_color( $input[$value] );
      if ( empty($color) ) {
        $input[$value] = isset( $options[$value] ) ? $options[$value] : '';
        $err_msg .= __('Color value is not valid. <br>', ISM_DOMAIN);
      }
    }
  }

  if ( isset($input['ism_button_size']) ) {
    if ( $input['ism_button_size'] == 'normal' ) {
      $input['ism_button'] = 'normal';
    } else {
      $input['ism_button'] = 'wide';
    }
  }

  foreach (array( 'ism_button_individual_variation', 'ism_button_cancel_active', 'ism_fontawesome', 'ism_enable_backorder_alert' ) as $value) {
    if ( !empty($input[$value]) &&  $input[$value] == 'on' ) {
      $input[$value] = true;
    } else {
      $input[$value] = false;
    }
  }


  if ( !empty( $err_msg ) ) {
    add_settings_error(
        'ism_stock_alert_button_options', // Setting slug
        'error_message',
         $err_msg,
        'error'
    );
  }

  return $input;
  // return array_merge( $options, $input );

}

function ism_validate_email_options($input) {

  if ( ! current_user_can( 'manage_options' ) ) return;

  if ( !empty($input['default_settings']) &&
          "reset" === $input['default_settings'] ) {

    delete_option( 'ism_stock_alert_email_options' );
    add_settings_error(
        'ism_stock_alert_email_options', // Setting slug
        'success_message',
        'Email settings have been reset!',
        'success'
    );
    return false;
  }

  $err_msg = '';
	$options = get_option( 'ism_stock_alert_email_options', ism_default_email_values() ) + ism_default_email_values();


    if ( isset($input['ism_email_header_img_url']) ) {
      if( !empty($input['ism_email_header_img_url']) ) {
        $input['ism_email_header_img_url'] = $url = wp_http_validate_url($input['ism_email_header_img_url']);
        if ( !$url ) {
          $input['ism_email_header_img_url'] = isset( $options['ism_email_header_img_url'] ) ? $options['ism_email_header_img_url'] : '';
          $err_msg .= __('The email header image Url is not valid. <br>', ISM_DOMAIN);
        }
      } else {
        $input['ism_email_header_img_url'] = $options['ism_email_header_img_url'];
      }
    }

    if ( isset($input['ism_email_subject']) ) {
      $text = $input['ism_email_subject'];
      if ( strlen($text) > ism_get_text_length('ism_email_subject') ) {
        $input['ism_email_subject'] = isset( $options['ism_email_subject'] ) ? $options['ism_email_subject'] : '';
        $err_msg .= __('The email subject exceeds 65 characters <br>', ISM_DOMAIN);
      }
    }

    if ( isset($input['ism_email_body']) ) {
      $text = $input['ism_email_body'];
      if ( strlen($text) > ism_get_text_length('ism_email_body') ) {
        $input['ism_email_body'] = isset( $options['ism_email_body'] ) ? $options['ism_email_body'] : '';
        $err_msg .= __('The email body text exceeds 254 characters <br>', ISM_DOMAIN);
      }
    }


  if ( !empty( $err_msg ) ) {
    add_settings_error(
        'ism_stock_alert_email_options', // Setting slug
        'error_message',
         $err_msg,
        'error'
    );
  }

  return array_merge( $options, $input );

}
