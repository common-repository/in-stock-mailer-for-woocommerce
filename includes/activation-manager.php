<?php

if ( ! defined( 'ABSPATH' ) ) exit;


register_activation_hook( ISM_FILE_PATH, array( 'activation_loader', 'init_activation' ) );

class activation_loader implements ism_db_setup {

    public static function init_activation() {
      if ( ! current_user_can( 'manage_options' ) ) return;
      update_option( 'ism_version', ISM_VERSION );
      return self::add_cron_event() && self::create_db_table();
    }

    private static function add_cron_event() {
      if ( ! wp_next_scheduled( 'in_stock_email_event' ) ) {
        wp_schedule_event( time(), 'three_daily', 'in_stock_email_event' );
      }
      if ( ! wp_next_scheduled( 'ism_mchimp_event' ) ) {
        wp_schedule_event( time(), 'ten_minutes', 'ism_mchimp_event' );
      }
      return true;
    }

    private static function create_db_table() {
      global $table_prefix, $wpdb;
      $success1 = $success2 = true;

      $emailtblname = self::email_table_name;
      $wp_email_table = $table_prefix . $emailtblname;
      $charset_collate = $wpdb->get_charset_collate();

      if ($wpdb->get_var( "SHOW TABLES LIKE '$wp_email_table' " ) != $wp_email_table)  {

          $sql  = " CREATE TABLE $wp_email_table ( " ;
          $sql .= " id INT(11) NOT NULL AUTO_INCREMENT , ";
          $sql .= " sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , ";
          $sql .= " PRIMARY KEY (id) ) ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci";

          require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
          dbDelta($sql);
          $success1 = empty($wpdb->last_error);
      }

      $tblname = self::table_name;
      $wp_table = $table_prefix . $tblname;

      if ($wpdb->get_var( "SHOW TABLES LIKE '$wp_table' " ) != $wp_table)  {

          $sql =  "  CREATE TABLE  $wp_table  ( ";
          $sql .= "  id  bigint(20)   NOT NULL AUTO_INCREMENT, ";
          $sql .= "  product_id  int(11)   NOT NULL, ";
          $sql .= "  email  varchar(254)   NOT NULL, ";
          $sql .= "  created_at  timestamp NOT NULL DEFAULT current_timestamp, ";
          $sql .= "  sent_id  INT(11) DEFAULT NULL, ";
          $sql .= "  consent BOOLEAN NOT NULL DEFAULT FALSE, ";
          $sql .= "  chimped BOOLEAN NOT NULL DEFAULT FALSE, ";
          $sql .= "  PRIMARY KEY id (id), ";
          $sql .= "  CONSTRAINT uc_prod_email_sent UNIQUE (product_id , email, sent_id), ";
          $sql .= "  CONSTRAINT uc_sent_id FOREIGN KEY (sent_id) REFERENCES $wp_email_table(id) ON DELETE CASCADE ON UPDATE SET NULL ";
          $sql .= ") ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci";

          require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
          dbDelta($sql);
          $success2 = empty($wpdb->last_error);

      }
      return $success1 && $success2;
    }
  }

register_deactivation_hook( ISM_FILE_PATH, array( 'deactivation_loader', 'init_deactivation' ) );

class deactivation_loader implements ism_db_setup  {

    public static function init_deactivation() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        delete_option( 'ism_version' );
        return self::remove_cron_event() && self::drop_db_table() && self::remove_options();
    }

    private static function remove_cron_event() {
        $timestamp = wp_next_scheduled( 'in_stock_email_event' );
        wp_unschedule_event( $timestamp, 'in_stock_email_event' );
        $timestamp = wp_next_scheduled( 'ism_mchimp_event' );
        wp_unschedule_event( $timestamp, 'ism_mchimp_event' );
        wp_clear_scheduled_hook( 'in_stock_email_event' );
        wp_clear_scheduled_hook( 'ism_mchimp_event' );
        return true;
    }

    private static function remove_options() {
        delete_option( 'ism_stock_alert_options' );
        delete_option( 'mchimp_enabled' );
        delete_option( 'ism_mc_api');
        delete_option( 'ism_mc_server_id');
        delete_option( 'ism_mc_set_store');
        delete_option( 'ism_version');
        return true;
    }

    private static function drop_db_table() {
      global $table_prefix, $wpdb;

      $tblname = self::table_name;
      $wp_table = $table_prefix . $tblname;
      $wpdb->query("DROP TABLE IF EXISTS $wp_table ");

      $emailtblname = self::email_table_name;
      $wp_email_table = $table_prefix . $emailtblname;
      $wpdb->query("DROP TABLE IF EXISTS $wp_email_table ");

      return true;
    }
  }
