<?php

if ( ! defined( 'ABSPATH' ) ) exit;


function ism_show_pending_status() {
  if ( ! current_user_can( 'manage_options' ) ) return;

  /* Finds and WHITE-LISTS the sort vars as they come from the array */
  $per_page = 7;
  $group_by = array('email');
  $order = array('asc', 'desc');
  $order_by = array('status', 'user_name', 'email');

  if (!isset($_GET['groupby']) || $_GET['groupby'] != 'false' ) {
    $group_by = 'email';
  } else {
    $group_by =  isset($_GET['groupby']) && in_array($_GET['groupby'], $group_by ) ? $group_by[array_search($_GET['groupby'],$group_by)] : false;
  }

  $order =  isset($_GET['order']) && in_array($_GET['order'], $order ) ? $order[array_search($_GET['order'],$order)] : 'ASC';
  $order_by = isset($_GET['orderby']) && in_array($_GET['orderby'], $order_by ) ? $order_by[array_search($_GET['orderby'], $order_by)] : 'status';

  $total_count = inStockManager::get_email_list_count( $group_by );

  $request_page = !empty($_GET['cpage']) ? filter_var($_GET['cpage'], FILTER_VALIDATE_INT ) : 1;
  $current_page = ( $request_page > 0 && $request_page <= ceil( $total_count / $per_page ) ) ? $request_page : 1;

  $total_pages = ceil($total_count / $per_page);
  $request_list = inStockManager::get_email_list_paginated( $current_page, $per_page, $order_by, $order, $group_by );

  /* Mailchimp Enabled */
  $mchimp_enabled_class = rest_sanitize_boolean( get_option('mchimp_enabled') ) ? '' : 'hidden';


  /**
   * If No Results
   */
  if ( !$request_list ) { ?>
    <table class="wp-list-table widefat fixed striped posts sent-alert isa-alert-table">
      <tbody id="the-list"><tr><td style="vertical-align: middle; text-align: center;">
        <div class="alert-view-no-results">You have no pending requests yet!</div></td></tr>
      </tbody>
    </table>
    <?php
    return;
  }
  /**
  * Header Description
  */
  ?>
  <h2>Pending Alerts</h2>
  <p>In Stock alert requests that are not yet sent will be shown here.<br>
    You can delete requests singularly or in bulk ( by email &amp; status ).<br>
    If multiple items become available, the system will only send one email
    per customer, grouping the products back in stock.<br>
    The cron runs approximately <i>3 times a day</i> and sends emails automatically.
  </p>
  <!-- Grouping switch -->
  <script>
    var go_to_groupby_email = '<?php echo add_query_arg(array('groupby'=>'email')); ?>';
    var go_to_no_groupby = '<?php echo add_query_arg(array('groupby'=>'false')); ?>';
  </script>
  <div class="switch-container">
    <label class="switch">
      <?php $is_groupby_checked = $group_by == 'email' ? 'checked' : '';  ?>
      <input type="checkbox" <?php echo $is_groupby_checked; ?>>
      <span class="slider round slider-groupby"></span>
    </label>
    <span> &nbsp; Group by Email address and status.</span>
  </div>
  <!-- Table Header -->
  <table class="wp-list-table widefat fixed striped posts">
    <thead>
      <tr>
        <th scope="col" class="isa-th-title" style="text-align:center;" ><div>Product Images</div></th>
        <th scope="col" class="isa-th-title" ><div>Products</div></th>
        <th scope="col" class="isa-th-title" ><div>Status</div>
          <div class="sort-selectors"><?php echo sort_links_asc_desc( 'status' ); ?><div></th>
        <th scope="col" class="isa-th-title" ><div>User</div>
          <div class="sort-selectors"><?php echo sort_links_asc_desc( 'user_name' ); ?><div></th>
        <th scope="col" class="isa-th-title" ><div>Requester Email</div>
          <div class="sort-selectors"><?php echo sort_links_asc_desc( 'email' ); ?><div></th>
        <th scope="col" class="isa-th-title" ><div><?php echo empty($is_groupby_checked) ? 'Date Requested' : 'Latest Requested on'; ?></div></th>
        <th scope="col" class="isa-th-title <?php echo $mchimp_enabled_class; ?>" ><div>Chimped</div></th>
        <th scope="col" class="isa-th-title" ><div>Action</div></th>
        <th scope="col" class="isa-th-title-mobile" style="text-align:center;" ><div>All Product Requests</div></th>
      </tr>
    </thead>

    <tbody id="the-list">
  <?php
  /**
  * Product Request List Loop
  */
  foreach ($request_list as $request_key => $request_object) {
      $request_object->ids = explode('-*_', $request_object->ids);
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
        $product_names_safe = '';
        foreach ($request_object->products as $product_id => $product_name) {
          $product_names_safe .= '<a href="' . esc_url( get_post_permalink($product_id) ) . '" target="_blank" title="Opens in a new tab"><b>'.esc_html( $product_name ).'</b></a>';
          $product_names_safe .= ', ';
        }
        $product_names_safe = rtrim($product_names_safe, ', ');
        echo $product_names_safe.'</td>';
        /**
        * Display Status
        */
        echo '<td style="width:10%;">';
        switch ( $request_object->status ) {
            case 'instock':
                echo '<b style="color:#7ad03a; font-weight:700;">IN STOCK <span style="font-size: .7em;"> (Awaiting email delivery)</span></b>';
                break;
            case 'outofstock':
                echo '<b style="color:#a44;">OUT OF STOCK</b>';
                break;
            case 'onbackorder':
                echo '<b style="color:#eaa600;">ON BACKORDER</b>';
                break;
        }
        echo '</td>';
        /**
        * Username
        */
        echo '<td style="width:10%;">';
        $username = !empty($request_object->user_name) ? esc_html( $request_object->user_name ) : 'Anonymous/Not Signed-in';
        echo '<b>'.ucfirst($username).'</b>';
        echo '</td>';
        /**
        * Email Address
        */
        echo '<td style="width:10%;">';
        $email = !empty($request_object->email) ? esc_html( strtolower( sanitize_email( $request_object->email) ) ) : 'Email could not be found!';
        echo '<b>'.$email.'</b>';
        echo '</td>';
        /**
        * Date Request
        */
        echo '<td style="width:10%;">';
        $dt = new DateTime("now", ism_get_timezone_obj() );
        $dt->setTimestamp(strtotime($request_object->created_at));
        echo '<span title="'.esc_attr( $dt->format('F d, Y h:i A') ) .'">'. esc_html( $dt->format('M d, Y') ) . '</span>';
        echo '</td>';
        /**
        * Chimped
        */
        $title = $request_object->chimped ? 'Customer email added to Mailchimp' : 'Customer will be added to Mailchimp shortly';
        $title = $request_object->chimp_consent ? $title : 'Customer denied marketing consent';
        echo '<td class="'.$mchimp_enabled_class.'" style="width:10%;">';
        echo '<div class="isa-action-container"><div class="isa-chimped-img" title="'.esc_attr( $title ).'" ';
        echo ' data-block-id="'.esc_attr( $request_key ).'" >';
        echo '<img src="'.esc_url( ism_get_chimped_img($request_object) ).'"></div><div class="loader hidden-loader"></div></div>';
        echo '</td>';
        /**
        * Action
        */
        echo '<td style="width:10%;">';
        $request_ids = esc_attr( implode('-',$request_object->ids));
        if ( $group_by == 'email' && $request_object->status == 'instock' ) {
          echo '<div class="isa-action-container" style="margin-bottom: 15px;"><div class="action-btn isa-send-alert-btn" data-block-id="'.esc_attr( $request_key ).'" data-user_email="'.esc_attr( $email ).'">Send Now</div>';
          echo '<div class="loader hidden-loader"></div></div>';
        }
        echo '<div class="isa-action-container"><div class="action-btn isa-delete-btn" data-block-id="'.esc_attr( $request_key ).'" data-proudct-ids="'.esc_attr( $request_ids ).'">Delete</div><div class="loader hidden-loader"></div></div>';
        echo '</td>';

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
