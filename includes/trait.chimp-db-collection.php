<?php

namespace InstockMailer;

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( ISM_PATH . 'includes/trait.ism-table-builder.php' );
use InstockMailer\IsmTableBuilder;


trait ChimpDbCollection {
  use IsmTableBuilder;

  public static function getChimpingUsers() {
    global $wpdb;
    $requests_table = self::get_table_name('out_of_stock_requests');

    $sql = " SELECT * FROM {$requests_table} WHERE  chimped = false AND consent = true GROUP BY email ";

    $result = $wpdb->get_results( $sql );
    wp_reset_postdata();

    return !empty( $result ) ? $result : [];

  }

  public static function setChimpedUsers( $users ) {
    global $wpdb;
    $requests_table = self::get_table_name('out_of_stock_requests');
    $result = false;

    if ( !empty($users) ) {
      $user_emails = array();
      $placeholders = array();
      foreach($users as $key => $user) {
          array_push( $user_emails, $user->email );
          $placeholders []= "%s";
      }

      $sql = " UPDATE {$requests_table} SET chimped = true WHERE email IN ( " . implode(', ', $placeholders) .  ") ";
      $sql = $wpdb->prepare( $sql, $user_emails );

      $result = $wpdb->query( $sql );
      wp_reset_postdata();
    }

    return !empty( $result ) ? $result : [];

  }


}
