<?php

if ( ! defined( 'ABSPATH' ) ) exit;

  function sort_links_asc_desc( $order_by ) {
    $is_desc = $is_asc = '';
    if ( isset($_GET['orderby']) &&
          $_GET['orderby'] == $order_by &&
              !empty($_GET['order']) ) {

      switch ($_GET['order']) {
        case 'desc':
            $is_desc = 'sort-select';
            break;
        case 'asc':
            $is_asc = 'sort-select';
            break;
      }

    }
    $links  = '<a href="' . add_query_arg( array( 'orderby' => $order_by, 'order' => 'desc') ).'"><i class="sort-by-desc '.$is_desc.'"></i></a>';
    $links .= '<a href="' . add_query_arg( array( 'orderby' => $order_by, 'order' => 'asc') ).'"><i class="sort-by-asc '.$is_asc.'"></i></a>';
    return $links;
  }

  function ism_get_timezone_obj() {

    $tzstring = get_option( 'timezone_string' );
    $offset   = get_option( 'gmt_offset' );

    if( empty( $tzstring ) && 0 != $offset && floor( $offset ) == $offset ){
       $offset_st = $offset > 0 ? "-$offset" : '+'.absint( $offset );
       $tzstring  = 'Etc/GMT'.$offset_st;
    }
    if( empty( $tzstring ) ){
       $tzstring = 'UTC';
    }
    return new DateTimeZone( $tzstring );
  }

  function ism_default_button_values() {
    return array(
        'ism_button_text' => 'Notify me when available',
        'ism_button_text_cancel' => 'You will be notified when available!',
        'ism_button_color' => '#007600',
        'ism_submit_email_button_color' => false,
        'ism_email_text_color' => false,
        'ism_email_field_back_color' => false,
        'ism_email_field_color' => false,
        'ism_button_text_color' => '#ffffff',
        'ism_button_size' => 'wide',
        'ism_button_individual_variation' => false,
        'ism_enable_backorder_alert' => false,
        'ism_fontawesome' => true,
        'ism_button_cancel_active' => true
    );
  }

  function ism_default_email_values() {
    return array(
        'ism_email_header_img_url' => ISM_URL_PATH . 'assets/img/banner-772x250.png',
        'ism_email_subject' => esc_html( get_bloginfo('name') ) . ' - ' . esc_html__('Back in stock alert!', ISM_DOMAIN),
        'ism_email_body' => 'Good news!!!
                              We recently got back in stock some of the products you were interested in!'
    );
  }

  function ism_default_values() {
    return  array_merge(ism_default_button_values(), ism_default_email_values());
  }

  function ism_get_text_length($id) {
    $text_lengths = array(
          'ism_button_text' => 50,
          'ism_button_text_cancel' => 50,
          'ism_email_header_img_url' => 450,
          'ism_email_subject' => 65,
          'ism_email_body' => 154,
      );
    return isset($text_lengths[$id]) ? $text_lengths[$id] : '';
  }

  function ism_do_settings_section( $page, $selected_section ) {
  	global $wp_settings_sections, $wp_settings_fields;

  	if ( ! isset( $wp_settings_sections[ $page ] ) ) {
  		return;
  	}

  	foreach ( (array) $wp_settings_sections[ $page ] as $section ) {

  		if ( $selected_section == $section['id'] ) {

  			if ( $section['title'] ) {
  				echo "<h2>{$section['title']}</h2>\n";
  			}

  			if ( $section['callback'] ) {
  				call_user_func( $section['callback'], $section );
  			}

  			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
  				continue;
  			}
  			echo '<table class="form-table" role="presentation">';
  			do_settings_fields( $page, $section['id'] );
  			echo '</table>';
  		}
  	}
  }

  function ism_get_chimped_img($request_object) {
    if ( $request_object->chimped ) {
      $chimp_url = ISM_URL_PATH . 'assets/img/mchimp-chimped.png';
    }
    elseif ( $request_object->chimp_consent ) {
      $chimp_url =  ISM_URL_PATH . 'assets/img/mchimp-unchimped.png';
    }
    else {
      $chimp_url =  ISM_URL_PATH . 'assets/img/mchimp-no-consent.png';
    }

    return $chimp_url;
  }

  if ( ! function_exists('ism_support_page_link')) {
    function ism_support_page_link() { ?>
        <a style="margin-left: 20px;" target="_blank" href="<?php echo esc_url_raw( ISM_SUPPORT_URL ); ?>"><b><?php echo ucfirst(esc_html__('Need help?', ISM_DOMAIN )); ?></b></a>
    <?php
    }
  }

  if ( ! function_exists('ism_settings_fields') &&
          ! function_exists('ism_wp_nonce_field') ) {

      function ism_settings_fields( $option_group, $nonce_id ) {
          echo "<input type='hidden' name='option_page' value='" . esc_attr( $option_group ) . "' />";
          echo '<input type="hidden" name="action" value="update" />';
          ism_wp_nonce_field( $nonce_id ,"$option_group-options" );
      }

      function ism_wp_nonce_field( $nonce_id = '', $action = -1, $name = '_wpnonce', $referer = true, $echo = true ) {
          $name        = esc_attr( $name );
          $nonce_field = '<input type="hidden" id="' . $nonce_id . '" name="' . $name . '" value="' . wp_create_nonce( $action ) . '" />';

          if ( $referer ) {
              $nonce_field .= wp_referer_field( false );
          }

          if ( $echo ) {
              echo $nonce_field;
          }

          return $nonce_field;
      }
  }

 
