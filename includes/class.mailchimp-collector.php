<?php

namespace InstockMailer;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( ISM_PATH . 'includes/class.encrypter.php' );
require_once( ISM_PATH . 'includes/trait.chimp-db-collection.php' );

use Ramsey\Uuid\Uuid;
use InstockMailer\ChimpDbCollection;

class MailchimpCollector
{
  use ChimpDbCollection;


  private $api_key;

  public $user_queue = 4;
  public $errors;
  public $store;
  public $server;
  public $user_id;
  public $users = [];

  public function __construct()
  {

    $this->encrypter = new Encrypter();
    $this->client = new \MailchimpMarketing\ApiClient();
    $this->user_id = $this->genUuid();
    $this->api_key = get_option( 'ism_mc_api') ? $this->encrypter->decryptString( get_option( 'ism_mc_api' ) ) : false;
    $this->server = get_option( 'ism_mc_server_id');
    $this->store = get_option( 'ism_mc_set_store');
    $this->setConfig();

  }

  private function genUuid() {
    $uuid = Uuid::uuid4();
    return $uuid->toString();
  }

  // Static Cron Call ( ism_mchimp_event )
  public static function addCustomersAsync() {
    ini_set('max_execution_time', 300);

    if ( rest_sanitize_boolean( get_option('mchimp_enabled') ) ) {

      $mchimp = new MailchimpCollector();

      if ( $mchimp->statusCheck() && $mchimp->getStoreInfo() ) {

        $x = 0;
        $mchimp->setUsers(MailchimpCollector::getChimpingUsers());

        while($x < count($mchimp->users)) {

          $user_queue_arr = array_slice($mchimp->users, $x, $mchimp->user_queue);

          if ( $chimped_users = $mchimp->addCustomers($user_queue_arr) ) {
           MailchimpCollector::setChimpedUsers($chimped_users);
          }

          $arr_length = count($user_queue_arr) ? count($user_queue_arr) : 1;
          $x += $arr_length;
        }
      }
    }
  }

  // Setup Methods
  public function setCredential( $api_key, $server ) {

    $this->client = new \MailchimpMarketing\ApiClient();
    $this->api_key = $api_key;
    $this->server = $server;
    $this->setConfig();

    if ( $this->statusCheck() ) {
      update_option( 'ism_mc_api', $this->encrypter->encryptString($this->api_key) );
      update_option( 'ism_mc_server_id', $server );
      return true;
    }

    return false;
  }

  public function reset() {

    $this->api_key = $this->server = $this->store = false;

    delete_option( 'ism_mc_api');
    delete_option( 'ism_mc_server_id');
    delete_option( 'ism_mc_set_store');

    return true;
  }

  public function setConfig() {

    $this->client->setConfig([
      'apiKey' => $this->api_key,
      'server' => $this->server
    ]);
  }

  public function setStore( $store_id ) {

    $this->store = $store_id;

    if ( $this->statusCheck() && $this->getStoreInfo() ) {
      update_option( 'ism_mc_set_store', $store_id );
      return true;
    }

    return false;
  }

  public function setUsers( $users ) {
      $this->users = $users;
  }

  // External Api Requests
  private function makeApiCall( $callable, $request, $params = [] ) {

    $this->errors = [];

    try {
       $response = call_user_func_array( array( $callable , $request ), $params);
    }
    catch (\MailchimpMarketing\ApiException $e) {
       $this->errors[] = $e->getMessage();
    } catch (\ClientErrorResponseException $e) {
       $this->errors[] = $e->getMessage();
    } catch (\GuzzleHttp\Exception\ConnectException $e) {
       $this->errors[] = $e->getMessage();
    } catch (\GuzzleHttp\Exception\ClientException $e) {
       $this->errors[] = $e->getMessage();
    }
    if ( !empty( $this->errors ) ) {
       return false;
    }

    return $response;

  }

  public function statusCheck() {
    return $this->makeApiCall( $this->client->ping, 'get' );
  }

  public function getStoreList() {
    return $this->makeApiCall( $this->client->ecommerce, 'stores' );
  }

  public function getStoreInfo() {
    return $this->makeApiCall( $this->client->ecommerce, 'getStore', [$this->store] );
  }

  public function addCustomers($users) {

    $chimped_users = [];

    if ( !empty($users)) {
      foreach ($users as $user) {
        $this->user_id = $this->genUuid();
        $result = $this->makeApiCall( $this->client->ecommerce,
                                    'addStoreCustomer',
                                    [ $this->store, [   "id" => $this->user_id,
                                                        "email_address" => $user->email,
                                                        "opt_in_status" => true,
                                                  ]
                                    ]
                            );
        if ( $result ) {
            $chimped_users []= $user;
        }
      }
    }

    return !empty($chimped_users) ? $chimped_users : false;
  }

}
