<?php

if ( ! defined( 'ABSPATH' ) ) exit;


class displayManager extends apiManager {

    private $variations;
    private $options;
    private static $alert_displayed = false;

    function __construct() {
        // Gets plugin options
        $button_options = get_option( 'ism_stock_alert_button_options', ism_default_button_values() ) + ism_default_button_values();
        $email_options = get_option( 'ism_stock_alert_email_options', ism_default_email_values() ) + ism_default_email_values();
        $this->options = $button_options + $email_options;
    }

    public function add_action_button() {
      // For simple products
      add_action( 'woocommerce_simple_add_to_cart', array($this,'display_notification_btn'), $priority = 31);
      // For variations
      add_action( 'woocommerce_after_add_to_cart_form', array($this,'display_notification_btn'), $priority = 31);
    }

    public function get_user_option( $option_name ) {
      return isset( $this->options[$option_name] ) ? $this->options[$option_name] : '';
    }

    public function generate_alert_form_simple() {

      $user_has_alert = $this->user_has_alert_simple();

      $alert_form = '<form id="in-stock-form" action="">';
      if ( get_current_user_id() ) {
          $alert_form  .= '<a class="instock-notify notify-btn '. ( $user_has_alert ? 'instock-hidden"' : '"' );
          $alert_form  .= ' href="#"><i class="fa fa-bell"></i>&nbsp; '.esc_html( $this->get_user_option('ism_button_text') ).'<div class="ism-loader"></div></a>';
          $alert_form  .= '<a class="instock-notification notify-btn '. ( !$user_has_alert ? 'instock-hidden"' : '"' );
          $alert_form  .= ' href="#"><i class="fa fa-envelope"></i>&nbsp; '.esc_html( $this->get_user_option('ism_button_text_cancel') ).'<div class="ism-loader ism-loader-cancel"></div><br>';
          $alert_form  .= $this->get_user_option('ism_button_cancel_active') ? '<span class="cancel-instock">'.esc_html__( 'cancel?', ISM_DOMAIN ).'</span></a>' : '</a>';
      } else {
          $alert_form  .= '<a class="instock-notify-email notify-btn '. ( $user_has_alert ? 'instock-hidden"' : '"' );
          $alert_form  .= ' href="#"><i class="fa fa-bell"></i>&nbsp; '.esc_html( $this->get_user_option('ism_button_text') ).'<div class="ism-loader"></div></a>';
          $alert_form  .= '<a class="instock-notification notify-btn '. ( !$user_has_alert ? 'instock-hidden"' : '"' );
          $alert_form  .= ' href="#"><i class="fa fa-envelope"></i>&nbsp; '.esc_html( $this->get_user_option('ism_button_text_cancel') ).'<div class="ism-loader ism-loader-cancel"></div><br>';
          $alert_form  .= $this->get_user_option('ism_button_cancel_active') ? '<span class="cancel-instock">'.esc_html__( 'cancel?', ISM_DOMAIN ).'</span></a>' : '</a>';
          $alert_form  .= '<div class="instock-input-section"><input class="hidden-in-stock-field instock-email in-stock-email" style="display:none;" ';
          $alert_form  .= ' value="'.esc_attr( $this->get_user_email() ).'" type="email" placeholder="'. esc_html__( 'Your email', ISM_DOMAIN ) .'" name="in-stock-email" required>';
          $alert_form  .= '<input class="hidden-in-stock-field instock-submit" style="display:none;" type="submit" value="'.esc_html__( 'Submit', ISM_DOMAIN ).'"></div>';

          if ( get_option('mchimp_enabled') ) {
            $alert_form  .= '<label style="display: none;" class="isa-consent-block hidden-in-stock-field"><input class="isa-submit-checkbox" type="checkbox" name="mchimp_consent" value="true" checked><span>'.esc_html__('Subscribe to marketing emails', ISM_DOMAIN).'</span></label>';
          }
      }
      // Let's add a honeypot field..
      $alert_form .= '<input class="its-all-about-honey" type="text" name="hname" placeholder="'.esc_html__( 'Your name here', ISM_DOMAIN ).'">';
      $alert_form .= '</form>';
      return $alert_form;
    }

    public function generate_alert_form_variations( $variation_id ) {

       $user_has_alert = $this->user_has_alert_variation( $variation_id );

       $alert_form  = '<form id="form-variation-id-'. esc_attr( $variation_id );
       $alert_form .= '" data-variation-id="'.esc_attr( $variation_id ).'" class="in-stock-form isa-form-hidden" action=""  >';

       if ( get_current_user_id() ) {
           $alert_form  .= '<a class="instock-notify notify-btn '. ( $user_has_alert ? 'instock-hidden"' : '"' );
           $alert_form  .= ' href="#"><i class="fa fa-bell"></i>&nbsp; '.esc_html( $this->get_user_option('ism_button_text') ).'<div class="ism-loader"></div></a>';
           $alert_form  .= '<a class="instock-notification notify-btn '. ( !$user_has_alert ? 'instock-hidden"' : '"' );
           $alert_form  .= ' href="#"><i class="fa fa-envelope"></i>&nbsp; '.esc_html( $this->get_user_option('ism_button_text_cancel') ).'<div class="ism-loader ism-loader-cancel"></div><br>';
           $alert_form  .= $this->get_user_option('ism_button_cancel_active') ? '<span class="cancel-instock">'.esc_html__( 'cancel?', ISM_DOMAIN ).'</span></a>' : '</a>';
       } else {
           $alert_form  .= '<a class="instock-notify-email notify-btn '. ( $user_has_alert ? 'instock-hidden"' : '"' );
           $alert_form  .= ' href="#"><i class="fa fa-bell"></i>&nbsp; '.esc_html( $this->get_user_option('ism_button_text') ).'<div class="ism-loader"></div></a>';
           $alert_form  .= '<a class="instock-notification notify-btn '. ( !$user_has_alert ? 'instock-hidden"' : '"' );
           $alert_form  .= ' href="#"><i class="fa fa-envelope"></i>&nbsp; '.esc_html( $this->get_user_option('ism_button_text_cancel') ).'<div class="ism-loader ism-loader-cancel"></div><br>';
           $alert_form  .= $this->get_user_option('ism_button_cancel_active') ? '<span class="cancel-instock">'.esc_html__( 'cancel?', ISM_DOMAIN ).'</span></a>' : '</a>';
           $alert_form  .= '<div class="instock-input-section"><input class="hidden-in-stock-field instock-email in-stock-email" style="display:none;" ';
           $alert_form  .= ' value="'.esc_attr( $this->get_user_email() ).'" type="email" placeholder="'. esc_html__( 'Your email', ISM_DOMAIN ) .'" name="in-stock-email" required>';
           $alert_form  .= '<input class="hidden-in-stock-field instock-submit" style="display:none;" type="submit" value="'.esc_html__( 'Submit', ISM_DOMAIN ).'"></div>';

           if ( get_option('mchimp_enabled') ) {
             $alert_form  .= '<label style="display: none;" class="isa-consent-block hidden-in-stock-field"><input class="isa-submit-checkbox" type="checkbox" name="mchimp_consent" value="true" checked><span>'.esc_html__('Subscribe to marketing emails', ISM_DOMAIN).'</span></label>';
           }
       }
       // Let's add a honeypot field..
       $alert_form .= '<input class="its-all-about-honey" type="text" name="hname" placeholder="'.esc_html__( 'Your name here', ISM_DOMAIN ).'">';
       $alert_form .= '</form>';

       return $alert_form;
    }

    public function user_has_alert_variation( $variation_id ) {
      $user_has_email = $this->get_user_email();

      if ( $user_has_email ) {
        global $wpdb, $table_prefix;
        $tblname = self::table_name;
        $wp_table = $table_prefix . $tblname;

        $sql = "SELECT id FROM $wp_table WHERE product_id =  %d AND email = %s AND sent_id IS NULL ";
        $sql = $wpdb->prepare( $sql, $variation_id, $user_has_email );

        return $wpdb->query($sql);
      }
      return false;
    }

    public function user_has_alert_simple() {
      $user_has_email = $this->get_user_email();
      $product_id = get_the_ID();

      if ( $user_has_email ) {
        global $wpdb, $table_prefix;
        $tblname = self::table_name;
        $wp_table = $table_prefix . $tblname;

        $sql = "SELECT id FROM $wp_table WHERE product_id =  %d AND email = %s AND sent_id IS NULL ";
        $sql = $wpdb->prepare( $sql, $product_id, $user_has_email );

        return $wpdb->query($sql);
      }
      return false;
    }

    public function display_notification_btn() {

      if ( !self::$alert_displayed ) {
         $product = wc_get_product( get_the_ID() );

         if ( ! $product->is_type( 'variable' ) &&
                      ( ! $product->is_in_stock() || ( $this->get_user_option('ism_enable_backorder_alert') &&
                                                         $product->is_on_backorder() &&
                                                           ( $product->backorders_require_notification() || ! $product->get_manage_stock() ) ) ) ) {

                  self::$alert_displayed = true;
                  echo $this->generate_alert_form_simple();
                  return;

         }

         elseif ( $product->is_type( 'variable' ) && ! $this->get_user_option('ism_button_individual_variation') ) {

                  $variation_soldout = 0;
                  $variations = $product->get_available_variations();

                  foreach ( $variations as $variation ) {

                    if ( $variation['is_purchasable'] &&
                           $variation['variation_is_active'] &&
                             $variation['variation_is_visible'] ) {

                               $variation_obj = new WC_Product_variation($variation['variation_id']);
                               if ( ! $variation_obj->is_in_stock() || ( $this->get_user_option('ism_enable_backorder_alert') &&
                                                                           $variation_obj->is_on_backorder() &&
                                                                             $variation_obj->backorders_require_notification() ) ) {

                                                                                  $variation_soldout++;

                               }
                    }

                  }

                  if ( count( $variations ) == $variation_soldout ) {
                      self::$alert_displayed = true;
                      echo $this->generate_alert_form_simple();
                      return;
                  }

         }

         elseif ( $product->is_type( 'variable' ) && $this->get_user_option('ism_button_individual_variation') ) {

                  $alert_forms = '';
                  $variations = $product->get_available_variations();

                  foreach ( $variations as $variation ) {

                    if ( $variation['is_purchasable'] &&
                          $variation['variation_is_active'] &&
                            $variation['variation_is_visible'] ) {

                              $variation_obj = new WC_Product_variation($variation['variation_id']);
                              if ( ! $variation_obj->is_in_stock() || ( $this->get_user_option('ism_enable_backorder_alert') &&
                                                                          $variation_obj->is_on_backorder() &&
                                                                            $variation_obj->backorders_require_notification() ) ) {

                                                                                 $alert_forms .= $this->generate_alert_form_variations($variation['variation_id']);

                              }
                    }
                  }

                  self::$alert_displayed = true;
                  echo $alert_forms;

         }
       }

    }

}

add_action( 'wp', function() {
    if ( is_product() ) {
        $display_manager = new displayManager();
        $display_manager->add_action_button();
    }
});


// Adds Textdomain Translation Support
add_action( 'plugins_loaded', function() {
    load_plugin_textdomain( ISM_DOMAIN,  FALSE, basename( ISM_PATH ) . '/languages/' );
});

 
