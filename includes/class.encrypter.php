<?php

namespace InstockMailer;

if ( ! defined( 'ABSPATH' ) ) exit;

class Encrypter
{

  private $key;
  public $is_secure = false;

  public function __construct()
  {

    if ( defined('SECURE_AUTH_KEY') ) {
      $this->is_secure = true;
      $this->key =  base64_encode(defined('SECURE_AUTH_KEY'));
    } else {
      $this->is_secure = false;
      $this->key = get_option('ism_crkey') ? base64_decode(get_option('ism_crkey')) : openssl_random_pseudo_bytes(16);
      update_option( 'ism_crkey', base64_encode($this->key) );
    }

  }

  public function encryptString( $string ) {

    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);

    $ciphertext_raw = openssl_encrypt($string, $cipher, $this->key, $options=OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $this->key, $as_binary=true);
    $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );

    return $ciphertext;
  }

  public function decryptString( $ciphertext ) {

    $c = base64_decode($ciphertext);
     $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
     $iv = substr($c, 0, $ivlen);
     $hmac = substr($c, $ivlen, $sha2len=32);
     $ciphertext_raw = substr($c, $ivlen+$sha2len);
     $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $this->key, $options=OPENSSL_RAW_DATA, $iv);
     $calcmac = hash_hmac('sha256', $ciphertext_raw, $this->key, $as_binary=true);
     if (hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
     {
         return $original_plaintext;
     }
     return false;
  }

}
