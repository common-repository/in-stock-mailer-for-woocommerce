<?php

if ( ! defined( 'ABSPATH' ) ) exit;

// REGISTERS WP_CRON INTERVAL
function wp_cron_intervals($schedules) {

  if (!isset($schedules['three_daily'])) {

    $three_daily_cron = ['interval'=> 28800,
                         'display' => 'Three times daily'];

    $schedules['three_daily'] = $three_daily_cron;
  }

  if (!isset($schedules['ten_minutes'])) {

    $ten_mins_cron = ['interval'=> 600,
                         'display' => 'Every 10 minutes'];

    $schedules['ten_minutes'] = $ten_mins_cron;
  }

  return $schedules;
}
add_filter( 'cron_schedules', 'wp_cron_intervals' );


// ADDS WP_CRON EVENT that FIRES after wp_loaded
add_action( 'in_stock_email_event', array('inStockManager', 'emailRequestsAsync'));
add_action( 'ism_mchimp_event', array('InstockMailer\\MailchimpCollector', 'addCustomersAsync'));

if ( ! wp_next_scheduled( 'in_stock_email_event' ) ) {
  wp_schedule_event( time(), 'three_daily', 'in_stock_email_event' );
}
if ( ! wp_next_scheduled( 'ism_mchimp_event' ) ) {
  wp_schedule_event( time(), 'ten_minutes', 'ism_mchimp_event' );
}
