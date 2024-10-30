<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function ism_show_sent_status() {
  if ( ! current_user_can( 'manage_options' ) ) return;

  /* Finds and WHITE-LISTS the sort vars as they come from the array */
  $per_page = 7;
  $order = array('asc', 'desc');
  $order_by = array('sent_at', 'user_name', 'email');
  $order =  isset($_GET['order']) && in_array($_GET['order'], $order ) ? $order[array_search($_GET['order'],$order)] : 'DESC';
  $order_by = isset($_GET['orderby']) && in_array($_GET['orderby'], $order_by ) ? $order_by[array_search($_GET['orderby'], $order_by)] : 'sent_at';
  /* Finds the total pages and validates the current page */
  $total_count = inStockManager::get_sent_email_list_count();
  $request_page = !empty($_GET['cpage']) ? filter_var($_GET['cpage'], FILTER_VALIDATE_INT ) : 1;
  $current_page = ( $request_page > 0 && $request_page <= ceil( $total_count / $per_page ) ) ? $request_page : 1;
  /* Calculates pagination vars */

  $total_pages = ceil($total_count / $per_page);
  /* Retrieves the paginated list from the database */
  $request_list = inStockManager::get_sent_email_list_paginated( $current_page, $per_page, $order_by, $order );

  /* Mailchimp Enabled */
  $mchimp_enabled_class = rest_sanitize_boolean( get_option('mchimp_enabled') ) ? '' : 'hidden';




  /**
   * If No Results
   */
  if ( !$request_list ) { ?>
    <table class="wp-list-table widefat fixed striped posts sent-alert">
      <tbody id="the-list"><tr><td style="vertical-align: middle; text-align: center;">
        <div class="alert-view-no-results">In stock alerts emailed to customers will be shown here!</div></td></tr>
      </tbody>
    </table>
    <?php
    return;
  }
   /**
   * Table Header
   */
   ?>
   <h2>Sent Alerts</h2>
   <p>In stock alerts emailed to customers will be shown here!</p>
   <table class="wp-list-table widefat fixed striped posts sent-alert isa-alert-table">
     <thead>
       <tr>
         <th scope="col" class="isa-th-title" style="text-align:center;" ><div>Product Images</siv></th>
         <th scope="col" class="isa-th-title" ><div class="isa-th-title">Products</div></th>
         <th scope="col" class="isa-th-title" ><div>Sent Date</div>
            <div class="sort-selectors"><?php echo sort_links_asc_desc( 'sent_at' ); ?><div></th>
         <th scope="col" class="isa-th-title" ><div>User</div>
            <div class="sort-selectors"><?php echo sort_links_asc_desc( 'user_name' ); ?><div></th>
         <th scope="col" class="isa-th-title" ><div>Requester Email</div>
            <div class="sort-selectors"><?php echo sort_links_asc_desc( 'email' ); ?><div></th>
         <th scope="col" class="isa-th-title <?php echo $mchimp_enabled_class; ?>" ><div>Chimped</div></th>
         <th scope="col" class="isa-th-title" ><div>Action</div></th>
         <th scope="col" class="isa-th-title-mobile" style="text-align:center;" ><div>All Sent Emails</div></th>
       </tr>
     </thead>
     <tbody id="the-list">
   <?php
   /**
   * Product Request List Loop
   */
   foreach ($request_list as $request_key => $request_object) {

       $request_object->product_id = explode('-*_', $request_object->product_id);
       $request_object->product_name= explode('-*_', $request_object->product_name);
       $request_object->products = array_combine($request_object->product_id, $request_object->product_name);
       $item_count = count($request_object->product_id);

         echo '<tr id="block-'.esc_attr( $request_key ).'" class="iedit author-self level-0 post-74 type-product status-publish has-post-thumbnail hentry product_cat-posters">';
         /**
         * Display Images loop
         */
         $i = 0;
         echo '<td style="width:10%;">';
         foreach ($request_object->products as $product_id => $product_name) {
           $i++;
           $the_post_thumbnail = get_the_post_thumbnail_url( $product_id , 'thumbnail' );
           $has_parent_id = wp_get_post_parent_id($product_id);
           // Get parent thumb url if it is a variation with no thumbnail
           if ( !$the_post_thumbnail && $has_parent_id ) {
             $the_post_thumbnail = get_the_post_thumbnail_url( $has_parent_id , 'thumbnail' );
           }
           echo '<a href="' . esc_url( get_post_permalink($product_id) ) . '"><img class="isa-prod-img" width="70" height="70" src="' . esc_url( $the_post_thumbnail ). '"
           class="attachment-thumbnail size-thumbnail wp-post-image" alt="" sizes="(max-width: 70px) 100vw, 70px" /></a>';
           if ($i >= 4) break;
         }
         echo ($item_count - $i) > 0 ? '..' : '';
         echo '</td>';
         /**
         * Display Products Name Loop
         */
         echo '<td style="width:10%;">';
         $product_names = '';
         foreach ($request_object->products as $product_id => $product_name) {
           $product_names .= '<a href="' . esc_url( get_post_permalink($product_id) ) . '" target="_blank" title="Opens in a new tab"><b>'.esc_html( $product_name ).'</b></a>';
           $product_names .= ', ';
         }
         $product_names = rtrim($product_names, ', ');
         echo $product_names .'</td>';

         /**
         * Display Status
         */
         $dt = new DateTime("now", ism_get_timezone_obj() );
         $dt->setTimestamp(strtotime($request_object->sent_at));
         echo '<td style="width:10%;">';
         echo '<div  title="'.esc_attr( $dt->format('F d, Y h:i A') ).'">
              <span style="background: #c6e1c6;color: #5b841b;padding:5px 10px; border-radius: 2px;">'
              . esc_html( $dt->format('M d, Y') ) .'</span></div>';
         echo '</td>';

         /**
         * Username
         */
         echo '<td style="width:10%;">';
         $username = !empty($request_object->user_name) ? esc_html( $request_object->user_name ) : 'Unregistered/Anonymous';
         echo '<b>'.ucfirst($username).'</b>';
         echo '</td>';

         /**
         * Email Address
         */
         echo '<td style="width:10%;">';
         $email = !empty($request_object->email) ? esc_html( strtolower( sanitize_email( $request_object->email ) ) ) : 'Email could not be found!';
         echo '<b>'.$email.'</b>';
         echo '</td>';

         /**
         * Chimped
         */
         $title = $request_object->chimped ? 'Customer email added to Mailchimp' : 'Customer will be added to Mailchimp shortly';
         $title = $request_object->chimp_consent ? $title : 'Customer denied marketing consent';
         echo '<td class="'.$mchimp_enabled_class.'" style="width:10%;">';
         echo '<div class="isa-action-container"><div class="isa-chimped-img" title="'.esc_attr( $title ).'" ';
         echo ' data-block-id="'.esc_attr( $request_key ).'" >';
         echo '<img src="'.esc_url(ism_get_chimped_img($request_object)).'"></div><div class="loader hidden-loader"></div></div>';
         echo '</td>';

         /**
         * Action
         */
         echo '<td style="width:10%;">';
         $email_id = esc_attr($request_object->id);
         echo '<div class="isa-action-container"><div class="email-action-btn isa-email-delete-btn" data-block-id="'.esc_attr( $request_key ).'" data-email-id="'.$email_id.'">Delete</div><div class="loader hidden-loader"></div></div>';
         echo '</td>';
         /**
         * Checkbox
         */
         // echo '<td style="width:1%!important; text-align: center;" class="manage-column column-cb"><input id="cb-select-all-1" type="checkbox"></td>';

      echo '</tr>';
   }
   echo '</tbody></table>';

   /**
   * Pagination Block
   */
   if ( $total_count > $per_page ) {
     echo '<div class="isa-pagination">';
     echo paginate_links( array(
             'base' => add_query_arg( 'cpage', '%#%' ),
             'format' => '?page=%#%',
             'prev_text' => __('&laquo;'),
             'next_text' => __('&raquo;'),
             'end_size' => 1,
             'mid_size' => 3,
             'show_all'=> false,
             'total' => $total_pages,
             'current' => $current_page,
             'type' => 'list'
     ));
     echo '</div>';
   }

 
}
