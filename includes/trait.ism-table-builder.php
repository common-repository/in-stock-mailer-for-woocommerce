<?php
namespace InstockMailer;

if ( ! defined( 'ABSPATH' ) ) exit;

trait IsmTableBuilder {

    private static $out_of_stock_requests = 'ism_out_of_stock_request';
    private static $sent_email = 'ism_sent_email';

    public static function get_table_name($table = '') {
      global $table_prefix;
      return $table_prefix . self::$$table;
    }

}
