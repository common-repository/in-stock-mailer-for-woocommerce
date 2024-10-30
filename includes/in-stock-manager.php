<?php

if ( ! defined( 'ABSPATH' ) ) exit;


interface ism_db_setup {
      const table_name = 'ism_out_of_stock_request';
      const email_table_name = 'ism_sent_email';
}

class inStockManager implements ism_db_setup {

      /**
       * Generates the emails and sends them to the requesters once a product is back in stock
       */
      public static function emailRequestsAsync() {
          ini_set('max_execution_time', 600);
          $email_list = self::get_email_list_to_email();

          // if exists It means that some products got back in stock so we need to send some emails
          if ( $email_list ) {

            foreach ($email_list as $requester_alert) {
                $requester_alert->ids = array_map('intval', explode('-*_', $requester_alert->ids));
                $requester_alert->product_id = array_map('intval', explode('-*_', $requester_alert->product_id));
                $requester_alert->product_name= explode('-*_', $requester_alert->product_name);
                $requester_alert->products_merge = array_combine($requester_alert->product_id, $requester_alert->product_name);
                $requester_alert->email = strtolower( sanitize_email( $requester_alert->email ));

                $email_id = intval(self::insert_new_email());
                if ( $email_id > 0 && self::append_sent_requests_to_email( $requester_alert->ids, $email_id ) ) {
                   $email_sent = self::send_email( $requester_alert );
                }

                sleep(1);
            }
          }
          return true;

      }

      public static function emailRequestSingle( $email = '') {

          $alert_list = self::get_single_alert_list( $email );

          if ( $alert_list ) {

                $alert_list->ids = array_map('intval', explode('-*_', $alert_list->ids));
                $alert_list->product_id = array_map('intval', explode('-*_', $alert_list->product_id));
                $alert_list->product_name = explode('-*_', $alert_list->product_name);
                $alert_list->products_merge = array_combine($alert_list->product_id, $alert_list->product_name);
                $alert_list->email = strtolower( sanitize_email( $alert_list->email ));

                $email_id = intval(self::insert_new_email());
                if ( $email_id > 0 && self::append_sent_requests_to_email( $alert_list->ids, $email_id ) ) {
                    $email_sent = self::send_email( $alert_list );
                    return $email_sent;
                }

          }

          return false;
      }

      // Gets requester_alert list for products back in stock
      public static function get_email_list_to_email() {

          global $wpdb, $table_prefix;
          $tblname = self::table_name;
          $wp_table = $table_prefix . $tblname;

          $emailList = $wpdb->get_results(" SELECT $wp_table.id, GROUP_CONCAT($wp_table.id SEPARATOR '-*_' ) as ids,
                                   GROUP_CONCAT( $wp_table.product_id SEPARATOR '-*_' ) as product_id ,
                                   GROUP_CONCAT( {$table_prefix}posts.post_title SEPARATOR '-*_' ) as product_name,
                                   $wp_table.email, {$table_prefix}users.ID as user_ID, {$table_prefix}users.display_name as user_name FROM $wp_table
                             INNER JOIN {$table_prefix}posts ON ( {$table_prefix}posts.ID = $wp_table.product_id )
                             INNER JOIN {$table_prefix}postmeta ON ($wp_table.product_id = {$table_prefix}postmeta.post_id)
                             LEFT JOIN {$table_prefix}users ON ($wp_table.email = {$table_prefix}users.user_email)
                             WHERE ( {$table_prefix}posts.post_type = 'product' OR {$table_prefix}posts.post_type = 'product_variation' ) AND
                                   {$table_prefix}postmeta.meta_value = 'instock' AND
                                   $wp_table.sent_id IS NULL
                             GROUP BY $wp_table.email ");

          wp_reset_postdata();
          return $emailList;
      }

      public static function get_single_alert_list( $email = '' ) {

          global $wpdb, $table_prefix;
          $tblname = self::table_name;
          $wp_table = $table_prefix . $tblname;

          $sql = (" SELECT $wp_table.id, GROUP_CONCAT($wp_table.id SEPARATOR '-*_' ) as ids,
                         GROUP_CONCAT( $wp_table.product_id SEPARATOR '-*_' ) as product_id ,
                         GROUP_CONCAT( {$table_prefix}posts.post_title SEPARATOR '-*_' ) as product_name,
                         $wp_table.email, {$table_prefix}users.ID as user_ID, {$table_prefix}users.display_name as user_name FROM $wp_table
                    INNER JOIN {$table_prefix}posts ON ( {$table_prefix}posts.ID = $wp_table.product_id )
                    INNER JOIN {$table_prefix}postmeta ON ($wp_table.product_id = {$table_prefix}postmeta.post_id)
                    LEFT JOIN {$table_prefix}users ON ($wp_table.email = {$table_prefix}users.user_email)
                    WHERE ( {$table_prefix}posts.post_type = 'product' OR {$table_prefix}posts.post_type = 'product_variation' ) AND
                           {$table_prefix}postmeta.meta_value = 'instock' AND
                           $wp_table.sent_id IS NULL AND
                           $wp_table.email = %s
                    GROUP BY $wp_table.email ");

          $sql = $wpdb->prepare( $sql, array( $email ) );
          $in_stock_list = $wpdb->get_row( $sql );
          wp_reset_postdata();

          return $in_stock_list;
      }

      public static function get_email_list_count( $group_by = 'email' ) {
          global $wpdb, $table_prefix;
          $tblname = self::table_name;
          $wp_table = $table_prefix . $tblname;

          $sql = " SELECT COUNT(*) as total from (SELECT $wp_table.id as id
                                   FROM $wp_table
                                   INNER JOIN {$table_prefix}posts ON ( {$table_prefix}posts.ID = $wp_table.product_id )
                                   INNER JOIN {$table_prefix}postmeta ON ($wp_table.product_id = {$table_prefix}postmeta.post_id)
                                   WHERE ( {$table_prefix}posts.post_type = 'product' OR {$table_prefix}posts.post_type = 'product_variation' ) AND $wp_table.sent_id IS NULL
                                   AND ( {$table_prefix}postmeta.meta_value = 'instock' OR
                                         {$table_prefix}postmeta.meta_value = 'outofstock' OR
                                         {$table_prefix}postmeta.meta_value = 'onbackorder' ) ";
          if ( $group_by == 'email' ) {
            $sql .= " GROUP BY $wp_table.email, {$table_prefix}postmeta.meta_value  ) as subsql ";
          } else {
            $sql .= " GROUP BY $wp_table.id  ) as subsql ";
          }

          $count_result = $wpdb->get_results( $sql, ARRAY_A );
          return array_shift( $count_result )['total'];
      }

      public static function get_sent_email_list_count() {
          global $wpdb, $table_prefix;
          $emailtblname = self::email_table_name;
          $tblname = self::table_name;
          $wp_table = $table_prefix . $tblname; $wp_email_table = $table_prefix . $emailtblname;

          $sql = " SELECT COUNT(*) as total FROM (SELECT COUNT( DISTINCT $wp_email_table.id) FROM $wp_email_table
                   INNER JOIN $wp_table ON ( $wp_email_table.id = $wp_table.sent_id ) GROUP BY $wp_email_table.id ) sub_sent ";
          $count_result = $wpdb->get_results( $sql, ARRAY_A );
          return array_shift( $count_result )['total'];
      }

      public static function get_email_list_paginated( $current_page = 1, $per_page = 10, $order_by = 'status', $order = 'ASC', $group_by = 'email' ) {
          global $wpdb, $table_prefix;
          $tblname = self::table_name;
          $emailtblname = self::email_table_name;
          $wp_table = $table_prefix . $tblname;
          $wp_email_table = $table_prefix . $emailtblname;

          $offset = ( $current_page - 1 ) * $per_page;

          $sql = " SELECT $wp_table.id, GROUP_CONCAT($wp_table.id SEPARATOR '-*_' ) as ids, GROUP_CONCAT( $wp_table.product_id SEPARATOR '-*_' ) as product_id ,
                   GROUP_CONCAT( {$table_prefix}posts.post_title SEPARATOR '-*_' ) as product_name,
                   $wp_table.email as email, $wp_table.chimped, $wp_table.consent as chimp_consent, {$table_prefix}users.ID as user_ID, {$table_prefix}users.display_name as user_name,
                   {$table_prefix}postmeta.meta_value as status, MAX(CONVERT_TZ( $wp_table.created_at, @@session.time_zone, '+00:00')) as created_at
                   FROM $wp_table
                   INNER JOIN {$table_prefix}posts ON ( {$table_prefix}posts.ID = $wp_table.product_id )
                   INNER JOIN {$table_prefix}postmeta ON ($wp_table.product_id = {$table_prefix}postmeta.post_id)
                   LEFT JOIN {$table_prefix}users ON ($wp_table.email = {$table_prefix}users.user_email)
                   WHERE ( {$table_prefix}posts.post_type = 'product' OR {$table_prefix}posts.post_type = 'product_variation' ) AND  $wp_table.sent_id IS NULL AND
                                                            ( {$table_prefix}postmeta.meta_value = 'instock' OR
                                                              {$table_prefix}postmeta.meta_value = 'outofstock' OR
                                                              {$table_prefix}postmeta.meta_value = 'onbackorder' )";
          if ( $group_by == 'email' ) {
            $sql .= " GROUP BY $wp_table.email , {$table_prefix}postmeta.meta_value ";
          } else {
            $sql .= " GROUP BY $wp_table.id ";
          }
          /* Note: $order and $order_by are also whitelisted */
          $sql .= " ORDER BY ". esc_sql($order_by)." ".esc_sql($order). ", $wp_table.created_at DESC LIMIT %d, %d ";

          $sql = $wpdb->prepare( $sql, array( $offset, $per_page ) );
          $emailList = $wpdb->get_results( $sql, OBJECT_K );
          wp_reset_postdata();
          return $emailList;
      }

      public static function get_sent_email_list_paginated( $current_page = 1, $per_page = 10, $order_by = 'sent_at', $order = 'ASC', $group_by = 'email' ) {
          global $wpdb, $table_prefix;
          $tblname = self::table_name;
          $wp_table = $table_prefix . $tblname;
          $emailtblname = self::email_table_name;
          $wp_email_table = $table_prefix . $emailtblname;

          $offset = ( $current_page - 1 ) * $per_page;

          $sql = "  SELECT  $wp_email_table.id, CONVERT_TZ( $wp_email_table.sent_at, @@session.time_zone, '+00:00') as sent_at, GROUP_CONCAT( $wp_table.product_id SEPARATOR '-*_' ) as product_id ,
                    GROUP_CONCAT( {$table_prefix}posts.post_title SEPARATOR '-*_' ) as product_name,
                    $wp_table.email as email, $wp_table.chimped, $wp_table.consent as chimp_consent, {$table_prefix}users.ID as user_ID, {$table_prefix}users.display_name as user_name
                    FROM $wp_email_table
                    INNER JOIN $wp_table ON ( $wp_table.sent_id = $wp_email_table.id )
                    INNER JOIN {$table_prefix}posts ON ( {$table_prefix}posts.ID = $wp_table.product_id )
                    LEFT JOIN {$table_prefix}users ON ($wp_table.email = {$table_prefix}users.user_email)
                    WHERE ( {$table_prefix}posts.post_type = 'product' OR {$table_prefix}posts.post_type = 'product_variation' ) AND  $wp_table.sent_id IS NOT NULL
                    GROUP BY $wp_email_table.id ";
          /* Note: $order and $order_by are also whitelisted */
          $sql .= " ORDER BY ". esc_sql($order_by)." ".esc_sql($order). ", $wp_table.created_at LIMIT %d, %d ";

          $sql = $wpdb->prepare( $sql, array( $offset, $per_page ) );
          $emailList = $wpdb->get_results( $sql, OBJECT_K );
          wp_reset_postdata();
          return $emailList;
      }

      public static function has_user_request($email, $product_id) {

        global $wpdb, $table_prefix;
        $tblname = self::table_name;
        $wp_table = $table_prefix . $tblname;

        $sql = "SELECT id FROM $wp_table WHERE email = %s AND product_id = %d AND sent_id IS NULL";
        $sql = $wpdb->prepare( $sql, [ $email, $product_id ] );
        return $wpdb->get_var($sql) ? true : false;

      }

      public static function remove_user_request($id) {
          global $wpdb, $table_prefix;
          $tblname = self::table_name;
          $wp_table = $table_prefix . $tblname;

          $prepare_val = $product_ids;
          array_unshift( $email, $prepare_val );
          $prepare_ph = '';
          foreach ($product_ids as $value) {
              $prepare_ph .= '%d,';
          }
          $prepare_ph = rtrim($prepare_ph, ',');

          $sql = $wpdb->prepare(" DELETE FROM $wp_table WHERE email = %s AND product_id IN ( ". $prepare_ph ." ) ", $prepare_val );
          return $wpdb->query($sql);
      }

      public static function add_user_request($email, $product_id, $consent) {

          global $wpdb, $table_prefix;
          $tblname = self::table_name;
          $wp_table = $table_prefix . $tblname;

          $sql = "INSERT INTO $wp_table ( product_id, email, consent, sent_id ) VALUES ( %d, %s, %d, null ) ON DUPLICATE KEY UPDATE product_id = %d";
          $sql = $wpdb->prepare( $sql, $product_id, $email, $consent, $product_id );
          return $wpdb->query($sql);

      }

      public static function append_sent_requests_to_email( $request_ids, $email_id ) {
        global $wpdb, $table_prefix;
        $tblname = self::table_name;
        $wp_table = $table_prefix . $tblname;

        $placeholders = '';
        $values = $request_ids;
        foreach ( $request_ids as $value ) {
            $placeholders .= '%d,';
        }
        $placeholders = rtrim($placeholders, ',');
        array_unshift( $values, $email_id );
        $sql = $wpdb->prepare(" UPDATE $wp_table SET sent_id = %d WHERE id IN ( ". $placeholders ." ) ", $values );
        return $wpdb->query($sql);
      }

      public static function insert_new_email() {
        global $wpdb, $table_prefix;
        $emailtblname = self::email_table_name;
        $wp_email_table = $table_prefix . $emailtblname;
        $wpdb->query(" INSERT INTO $wp_email_table VALUES () ");
        return $wpdb->insert_id;
      }

      protected static function get_email_template( $requester ) {

          $display_manager = new displayManager();
          $product_safe = '';
          $site_url = get_site_url();
          $site_name = esc_html( get_bloginfo('name') ) ;
          $user_name = !empty( $requester->user_name ) ? $requester->user_name : '';
          $email_body_safe = wpautop( wp_kses( $display_manager->get_user_option('ism_email_body'), wp_kses_allowed_html( 'post' ) ) );
          $template = file_get_contents( ISM_PATH . 'templates/email/template.phtml');

          if ( !empty( $requester->products_merge ) ) {
            foreach($requester->products_merge as $product_id => $product_name)
            {
                $the_post_thumbnail = get_the_post_thumbnail_url( $product_id , 'thumbnail' );
                $has_parent_id = wp_get_post_parent_id($product_id);
                // If product variation has NO thumbnail gets the parent thumbnail
                if ( !$the_post_thumbnail && $has_parent_id ) {
                  $the_post_thumbnail = get_the_post_thumbnail_url( $has_parent_id , 'thumbnail' );
                }

                $product_safe .= '<td style="font-family: sans-serif;font-size: 14px;vertical-align: top;display: inline-block;float: left;text-align: center;background-color: #ffffff;border-radius: 5px;">
                            <a href="' . esc_url( get_post_permalink( $product_id ) ) . '"
                            style="color: #555;text-decoration: none;background-color: #ffffff;border-radius: 5px;box-sizing: border-box;cursor: pointer;
                            display: inline-block; font-size: 14px;font-weight: bold;margin: 0;text-transform: capitalize;">
                            <figure style="width: 154px; margin: 8px;">
            				            <img width="150" height="150" src="' . esc_url( $the_post_thumbnail ) . '"
                                 class="attachment-thumbnail size-thumbnail wp-post-image product-img" alt="" sizes="(max-width: 150px) 100vw, 150px" style="border: none;-ms-interpolation-mode: bicubic;max-width: 100%;">
                                 <figcaption><br>'. esc_html( $product_name ) .'</figcaption>
            				        </figure>
            			          </a></td>';
            }
          }

          $template = str_replace('{{ add_shop_site_link }}', esc_url( $site_url ), $template);
          $template = str_replace('{{ add_header_img_link }}', esc_url( $display_manager->get_user_option('ism_email_header_img_url') ), $template);
          $template = str_replace('{{ add_user_name }}', esc_html__('Hello', ISM_DOMAIN).' '.esc_html( ucfirst( $user_name ) ), $template);
          $template = str_replace('{{ add_email_body }}', $email_body_safe, $template);
          $template = str_replace('{{ add_products }}', $product_safe, $template);
          $template = str_replace('{{ add_shop_name }}', $site_name, $template);

          return $template;
      }

      public static function send_email( $requester ) {

          $display_manager = new displayManager();

          add_filter('wp_mail_content_type', function() {
            return 'text/html';
          }, 50);

          return wp_mail(
                  esc_attr( $requester->email ),
                  esc_attr( $display_manager->get_user_option('ism_email_subject') ),
                  self::get_email_template( $requester ),
                  $headers = array(),
                  $attachments = array()
          );
      }

}
