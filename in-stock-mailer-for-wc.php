<?php
/**
 * Plugin Name: In Stock Mailer for WooCommerce
 * Plugin URI: https://wordpress.org/support/plugin/in-stock-mailer-for-woocommerce
 * Description: Sends in stock email alert to requesting customers. It adds a customizable button on the product page.
 * Author: Frank Pagano
 * Author URI: https://frankspress.com
 * Text Domain: in-stock-mailer-for-wc
 * Domain Path: /languages/
 * Version: 2.1.1
 * WC requires at least: 3.5
 * WC tested up to: 4.9.2
 * Copyright (c) 2020 Frankspress
 * License: GPLv2 or later
 *
 * If you don't have a copy of the license please go to <http://www.gnu.org/licenses/>.
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;
// If WooCommerce is not active do nothing.
if ( ! in_array('woocommerce/woocommerce.php', get_option( 'active_plugins') ) ) {
  return;
}

define( 'ISM_VERSION', '2.1.1' );
define( 'ISM_DOMAIN', 'in-stock-mailer-for-wc' );
define( 'ISM_FILE_PATH', __FILE__ );
define( 'ISM_PATH', plugin_dir_path( __FILE__ ) );
define( 'ISM_URL_PATH', plugin_dir_url( __FILE__ ) );
define( 'ISM_FONTAWESOME_URL', ISM_URL_PATH . 'lib/font-awesome-4.7.0/css/font-awesome.min.css' );
define( 'ISM_SUPPORT_URL', 'https://wordpress.org/support/plugin/in-stock-mailer-for-woocommerce/' );


require_once( ISM_PATH . 'includes/ism-loader.php' );
