<?php


if ( ! defined( 'ABSPATH' ) ) exit;


class updateManager implements ism_db_setup {


  public static function updateDB() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $version = get_option('ism_version');
    global $table_prefix, $wpdb;

    // No version option fix
    if ( !$version ) {

      // Update tables
      $tblname = self::table_name;
      $wp_table = $table_prefix . $tblname;

      $sql  = " ALTER TABLE {$wp_table} ";
      $sql .= " ADD COLUMN consent BOOLEAN NOT NULL DEFAULT FALSE, ";
      $sql .= " ADD COLUMN chimped BOOLEAN NOT NULL DEFAULT FALSE ";

      if ( $wpdb->get_var( "SHOW COLUMNS FROM {$wp_table} LIKE 'consent' " ) != 'consent' )  {
          $wpdb->query( $sql );
      }



      // Split Options
      $former_options = get_option( 'ism_stock_alert_options' ) ? get_option( 'ism_stock_alert_options' ) : [];
      $button_options = get_option( 'ism_stock_alert_button_options', ism_default_button_values() ) ;
      $email_options = get_option( 'ism_stock_alert_email_options', ism_default_email_values() ) ;

      foreach ($button_options as $key => $value) {
        if( isset($former_options[$key])) {
          $button_options[$key] = $former_options[$key];
        }
      }

      foreach ($email_options as $key => $value) {
        if( isset($former_options[$key])) {
          $email_options[$key] = $former_options[$key];
        }
      }

      update_option( 'ism_stock_alert_button_options', $button_options );
      update_option( 'ism_stock_alert_email_options', $email_options );

    }


    // Sets email field to lowercase
    if ( $version < '2.0.2' ) {

      $tblname = self::table_name;
      $wp_table = $table_prefix . $tblname;
      $wpdb->suppress_errors( true );
      // Win
      $sql = " UPDATE {$wp_table} SET email = BINARY LOWER(email) ";
      $wpdb->query( $sql );
      // Unix
      $sql = " UPDATE {$wp_table} SET email = LOWER(email) ";
      $wpdb->query( $sql );
      // Unsuppress errors
      $wpdb->suppress_errors(  false );

    }

    // Removes duplicates from table, fixed in 2.1.0
    if ( $version < '2.1.0' ) {

      $tblname = self::table_name;
      $wp_table = $table_prefix . $tblname;

      $sql = "SELECT email, product_id, COUNT(*) AS c FROM {$wp_table}  WHERE sent_id IS NULL GROUP BY email, product_id HAVING c > 1";
      $wpdb->suppress_errors( true );
      $duplicates = $wpdb->get_results( $sql );

      if ( $duplicates ) {
        // Prevents system crash lock on failure
        update_option( 'ism_version', '2.1.0' );

        foreach ( $duplicates as $duplicate ) {
          $sql =  "DELETE FROM {$wp_table} WHERE  email = %s AND product_id = %d LIMIT 1";
          $sql = $wpdb->prepare( $sql, [$duplicate->email, intval($duplicate->product_id)] );
          $wpdb->query( $sql );
        }
      }

      $wpdb->suppress_errors( false );
    }

    // // Updates from this version
    // if ( $version < '2.1.0' ) {
    //
    // }


    update_option( 'ism_version', ISM_VERSION );
  }

}

add_action( 'plugins_loaded', array( 'updateManager', 'updateDB' ) );
