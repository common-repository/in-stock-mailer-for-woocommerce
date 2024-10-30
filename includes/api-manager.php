<?php

if ( ! defined( 'ABSPATH' ) ) exit;


class apiManager extends inStockManager {

  public function __construct() {
    $this->bypass_nonce_caching();
    $this->add_alert_route();
    $this->remove_alert_route();
    $this->remove_bulk_alert_route();
    $this->remove_sent_email_route();
    $this->sent_alert_email_route();
    $this->send_test_email_route();
    $this->status_mchimp();
    $this->reset_mchimp();
    $this->set_config_mchimp();
    $this->set_store_mchimp();
    $this->get_store_mchimp();
    $this->add_customer_mchimp();
    $this->enable_mchimp();


  }

  /**
   * Anonymous user nonce caching fix
   */
  private function bypass_nonce_caching(){
    add_action( 'rest_api_init', function() {
    if ( defined('REST_REQUEST') && strpos( $_SERVER['REQUEST_URI'], 'in-stock-mailer' ) ) {
        $send_no_cache_headers = apply_filters('rest_send_nocache_headers', is_user_logged_in());
        if (!$send_no_cache_headers && !is_user_logged_in() ) {
                  $_SERVER['HTTP_X_WP_NONCE'] = wp_create_nonce('wp_rest');
        }
      }
    }, $priority = 1 );
  }

  /**
   * Public Routes
   */
  public function add_alert_route() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'product/(?P<product_id>\d+)',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'add_alert_request' ),
                    'args' => [
                         'product_id' => [
                             'validate_callback' => function($product_id) {
                                     return get_post($product_id) ? true : false;
                                 },
                         ]
                     ],
                     'permission_callback' => array( $this, 'validate_payload' )
          ));
    });
  }

  public function remove_alert_route() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'product/remove/(?P<product_id>\d+)',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'remove_alert_request' ),
                    'args' => [
                         'product_id' => [
                             'validate_callback' => function($product_id) {
                                     return get_post($product_id) ? true : false;
                                 },
                         ],
                     ],
                     'permission_callback' => array( $this, 'validate_payload' )
          ));
    });
  }

  /**
   * Admin Routes
   */
  public function remove_bulk_alert_route() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'requests/remove',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'remove_request_by_ids' ),
                    'permission_callback' => array( $this, 'validate_request_ids' )
          ));
    });
  }

  public function remove_sent_email_route() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'email/remove/(?P<email_id>\d+)',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'remove_sent_email' ),
                        'args' => [
                             'email_id' => [
                                 'validate_callback' => function($email_id) {
                                         return is_numeric($email_id);
                                     },
                             ],
                         ],
                    'permission_callback' => function ($request) {
                            if ( !current_user_can( 'manage_options') ) return false;
                              if ( $request['email_id'] = filter_var($request['email_id'], FILTER_VALIDATE_INT ) )  {
                                  return true;
                                }
                            return false;
                            },
          ));
     });
  }

  public function sent_alert_email_route() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'alert/send/',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'send_alert_email' ),
                    'permission_callback' => function ($request) {
                            if ( !current_user_can( 'manage_options') || !is_email( trim($request['email']) ) ) return false;
                            return true;
                            },
          ));
     });
  }


  public function send_test_email_route() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'email-test/',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'send_test_email' ),
                    'permission_callback' => array( $this, 'test_email_permission_validate' )
          ));
    });
  }


  public function enable_mchimp() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'mchimp-enable/',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'mchimp_enable' ),
                    'permission_callback' => array( $this, 'mchimp_config_validate' )
          ));
    });
  }

  public function status_mchimp() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'mchimp-status/',array(
                    'methods'  => 'GET',
                    'callback' => array( $this, 'mchimp_status' ),
                    'permission_callback' => array( $this, 'mchimp_config_validate' )
          ));
    });
  }

  public function reset_mchimp() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'mchimp-reset/',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'mchimp_reset' ),
                    'permission_callback' => array( $this, 'mchimp_config_validate' )
          ));
    });
  }

  public function set_config_mchimp() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'mchimp-config/',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'mchimp_setup' ),
                    'permission_callback' => array( $this, 'mchimp_config_validate' )
          ));
    });
  }

  public function set_store_mchimp() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'mchimp-set-store/',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'mchimp_set_store' ),
                    'permission_callback' => array( $this, 'mchimp_config_validate' )
          ));
    });
  }

  public function get_store_mchimp() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'mchimp-get-stores/',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'mchimp_get_stores' ),
                    'permission_callback' => array( $this, 'mchimp_config_validate' )
          ));
    });
  }

  public function add_customer_mchimp() {
    add_action('rest_api_init', function () {
      register_rest_route( 'in-stock-mailer/v1', 'mchimp-add-customer/',array(
                    'methods'  => 'POST',
                    'callback' => array( $this, 'mchimp_add_customer' ),
                    'permission_callback' => array( $this, 'mchimp_config_validate' )
          ));
    });
  }

/**
 * Validation Callbacks
 */

  public function get_user_email($email = '') {
      $current_user = wp_get_current_user();
      if ( $current_user && get_current_user_id() ) {
        return $current_user->user_email;
      }
      // if ( !empty( $email ) && email_exists( $email ) ) {
      //   return false;
      // }
      if ( !empty($email) ) {
        return $email;
      }
      if ( isset($_COOKIE['reference_email'])) {
          return unserialize(base64_decode($_COOKIE['reference_email']), ["allowed_classes" => false]);
      }
      return $email;
  }

  public function set_user_email($email) {
       setcookie('reference_email', base64_encode(serialize($email)), (time() + 8419200), "/");
  }

  public function validate_email($email) {
    $getEmail = strtolower(sanitize_email( $this->get_user_email($email)) );
    if ( $getEmail &&
           is_email( $getEmail ) &&
              strlen( $getEmail ) < 254 ) {
       $this->set_user_email($getEmail);
       return $getEmail;
    }
    return false;
  }

  public function validate_payload( $request ) {
    $email = !empty($request['email']) ? trim($request['email']) : '';
    $email = $this->validate_email($email);
    // Checks if email is valid and honeypot field was sent and it is empty
    if ( $email && ( isset( $request['hname'] ) && empty( $request['hname'] ) ) ) {
        $request['email'] =  $email;
        return true;
    }
    return false;
  }

  public function validate_request_ids( $request ) {
    if ( !current_user_can( 'manage_options') ) return false;
      if ( !empty($request['request_ids']) && is_string($request['request_ids']) ) {
        $ids = explode('-', $request['request_ids'] );
        $request['request_ids'] = $ids;
        return true;
      }
      return false;
  }

  public function test_email_permission_validate( $request ) {
    if ( !current_user_can( 'manage_options') ) return false;
    $email = !empty($request['test_email']) ? $request['test_email'] : '';
    $request['test_email'] = sanitize_email( $email );
    if ( is_email($request['test_email']) ) {
        return true;
    }
    return false;
  }

  public function mchimp_config_validate( $request ) {
    if ( !current_user_can( 'manage_options') ) return false;

    return true;
  }

/**
 * Action Callbacks
 */

  public function mchimp_enable( WP_REST_Request $data ) {
    $state = rest_sanitize_boolean($data['mchimp_enabled']);
    update_option('mchimp_enabled', $state);
    return array( 'action'=> 'mchimp_enable', 'state'=> $state );

  }

  public function mchimp_reset( WP_REST_Request $data ) {

    $mailchimp = new InstockMailer\MailchimpCollector();
    $mailchimp->reset();

    return array( 'action'=> 'mchimp_setup', 'reset'=> true );

  }

  public function mchimp_status( WP_REST_Request $data ) {

    $mailchimp = new InstockMailer\MailchimpCollector();
    $status = $mailchimp->statusCheck();
    $stores = $mailchimp->getStoreList();

    return array( 'action'=> 'mchimp_setup',
                  'status' =>$status,
                  'stores' =>$stores,
                  'set_store' => esc_attr($mailchimp->store) );

  }

  public function mchimp_setup( WP_REST_Request $data ) {

    $mailchimp = new InstockMailer\MailchimpCollector();
    $result = $mailchimp->setCredential(  $data['api_key'],  $data['server'] );
    $stores = $mailchimp->getStoreList();

    return array( 'action'=> 'mchimp_setup',
                  'stores' =>$stores,
                  'status' => $result );

  }

  public function mchimp_get_stores( WP_REST_Request $data ) {

    $mailchimp =  new InstockMailer\MailchimpCollector();
    $result = $mailchimp->getStoreList();
    return array( 'action'=> 'mchimp_get_store',
                  'stores' => $result,
                  'set_store' => esc_attr($mailchimp->store) );

  }

  public function mchimp_set_store( WP_REST_Request $data ) {

    $mailchimp = new InstockMailer\MailchimpCollector();
    $result = $mailchimp->setStore(  $data['store'] );
    return array( 'action'=> 'mchimp_store_set', 'status' => $result );

  }

  public function mchimp_add_customer( WP_REST_Request $data ) {

    $users = [ $data['user'] ];
    $mailchimp = new MailchimpCollector();
    $result = false;

    if ( $mailchimp->statusCheck() && !empty($mailchimp->store) ) {
      $result = $mailchimp->addCustomers( $users );
    }

    return array( 'action'=> 'mchimp_add_customer', 'status' => $result );

  }



  public function remove_request_by_ids( WP_REST_Request $data ) {
    global $wpdb, $table_prefix;
    $request_ids = $data['request_ids'];

    $tblname = self::table_name;
    $wp_table = $table_prefix . $tblname;

    $plc_hold = '';
    foreach ($request_ids as $id) {
        $plc_hold .= '%d,';
    }
    $plc_hold = rtrim($plc_hold, ',');

    $sql = " DELETE FROM $wp_table WHERE id IN(" . $plc_hold . ") ";
    $sql = $wpdb->prepare( $sql, $request_ids );
    $wpdb->query($sql);
    return array('action'=> 'remove');
  }

  public function add_alert_request( WP_REST_Request $data) {

      $product_id = intval($data['product_id']);
      $email = $data['email'];
      $consent = rest_sanitize_boolean($data['mchimp_consent']) ? 1 : 0;

      if ( !self::has_user_request ($email, $product_id)) {
        self::add_user_request($email, $product_id, $consent);
      }

      return array( 'action'=> 'add' );
  }

  public function remove_alert_request( WP_REST_Request $data) {
      global $wpdb, $table_prefix;
      $product_id = intval($data['product_id']);
      $email = $data['email'];

      $tblname = self::table_name;
      $wp_table = $table_prefix . $tblname;

      $sql = "DELETE FROM $wp_table WHERE product_id = %d AND email =  %s AND sent_id IS NULL ";
      $sql = $wpdb->prepare( $sql, $product_id, $email );
      $wpdb->query($sql);
      return array('action'=> 'remove');
  }

  public function remove_sent_email(WP_REST_Request $data) {
    global $wpdb, $table_prefix;
    $tblname = self::email_table_name;
    $wp_email_table = $table_prefix . $tblname;
    $id = $data['email_id'];
    $sql = $wpdb->prepare( " DELETE FROM $wp_email_table WHERE id = %d ", array( $id ) );
    $wpdb->query( $sql );
    return array('action'=> 'remove');
  }

  public function send_alert_email( WP_REST_Request $data ) {

    $email = strtolower(sanitize_email( $data['email'] )) ;
    $result = self::emailRequestSingle( $email );
    return array('action'=> 'send_single_alert', 'payload' => $result);

  }

  public function send_test_email( WP_REST_Request $data ) {

    // Grab a random product
    global $wpdb, $table_prefix;

    $sql = " SELECT {$table_prefix}posts.id as product_id , {$table_prefix}posts.post_title as product_name
             FROM {$table_prefix}posts
             INNER JOIN {$table_prefix}postmeta ON ( {$table_prefix}posts.id = {$table_prefix}postmeta.post_id)
             WHERE {$table_prefix}posts.post_type = 'product' AND {$table_prefix}postmeta.meta_value = 'instock'
             ORDER BY RAND() LIMIT 3 ";

    $products = $wpdb->get_results( $sql );
    wp_reset_postdata();

    $email = strtolower(sanitize_email( $data['test_email'] )) ;
    $tblname = self::table_name;
    $wp_table = $table_prefix . $tblname;

    foreach ( $products as $product ) {

      $sql = "INSERT INTO $wp_table ( product_id, email, sent_id, consent ) VALUES ( %d, %s, null, 1 ) ON DUPLICATE KEY UPDATE product_id = %d ";
      $sql = $wpdb->prepare( $sql, $product->product_id, $email, $product->product_id );
      $wpdb->query($sql);
      wp_reset_postdata();

    }

    $result = self::emailRequestSingle( $email );
    return $result;

  }
}

new apiManager();
