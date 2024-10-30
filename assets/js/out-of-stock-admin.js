jQuery(document).ready(function($) {

    // Instantiates color-picker
    $('.my-color-field').wpColorPicker();

    /**
    * Automatically opens the Current Saved tab to show the options menu as it reloads
    */
    function go_to_option() {
      if ( window.location.hash ) {
        let open_option = window.location.hash.substr(1);
        $("#"+open_option).delay(200).slideDown(300);
        $("#"+open_option).parent().find('.ism-option-title').animate({width:"100%"},200);
        history.pushState("", document.title, window.location.pathname + window.location.search);
      }
    }
    go_to_option();


  /**
   * Animates Option windows slide up and down
   */
    $('.ism-option-title').on('click', function(e) {
      e.preventDefault();
      if ( e.target.className == 'ism-option-title' ||  e.target.className == 'mobile-removable' ) {
        $(this).parent().find('.ism-option-container').slideToggle(300,"linear", function() {
        });
      }
    });

    // Resets options
    $('#submit-reset-button').on('click', function(e) {
      e.preventDefault();
      if ( confirm("Reset Button settings?") ) {
        $('#ism_stock_alert_options_default_settings_button').val("reset");
        $("#in-stock-mailer-form-button").submit();
        $("#mainform").submit();
      }
    });
    $('#submit-reset-email').on('click', function(e) {
      e.preventDefault();
      if ( confirm("Reset Email settings?") ) {
        $('#ism_stock_alert_options_default_settings_email').val("reset");
        $("#in-stock-mailer-form-email").submit();
        $("#mainform").submit();
      }
    });

    /**
    * Preview Header Image
    */
      var header_img_input = $('#ism_stock_alert_email_options_ism_email_header_img_url');
      var header_container = $('<div>', { id: 'header_img_input_container' });
      $(header_container).insertAfter(header_img_input);
      $(header_img_input).appendTo(header_container);
      function update_header_img( source ) {
          $('.header_img').remove();
          let $img_display = $('<img>', { class: 'header_img' });
          $img_display.hide();
          $img_display.on('load', function() {
                                                $('.header_img').remove();
                                                $(this).appendTo(header_container);
                                                $img_display.show(400);
                                              })
                      .on('error', function() {
                                                $(this).attr('src', alertDataAdmin.no_image_link );
                                                $(this).addClass('no-image-found');
                                                $img_display.show();
                                              })
                      .attr("src", source);
      }
      $('#ism_stock_alert_email_options_ism_email_header_img_url').on('paste keyup', function() {
        let new_source = $(this).val();
        if ( new_source ) {
           update_header_img( new_source );
        } else {
           update_header_img( alertDataAdmin.default_header_img_url );
        }
      }).trigger('paste');

    // Sends the test email and handles the response
    $('#email-notification-test').on('submit', function(e) {
      e.preventDefault();
      $(this).find('.loader-email').show();
      send_test_email_ajax();
    });
    function send_test_email_ajax() {
          let emailValue = $('#recipient_email_field').val();
          let payload = { 'test_email' : emailValue };
          $('.loader-email').show(300);
      $.ajax( {
          url: alertDataAdmin.api_base_url + '/email-test',
          method: 'POST',
          beforeSend: function ( xhr ) {
              xhr.setRequestHeader( 'X-WP-Nonce', alertDataAdmin.nonce );
          },
          data: payload,
          success: function(data) {
            $('.loader-email').hide();
            if ( data == false ) {
              $err_message = 'Please check your email configuration!<br> You must install and configure an email provider to use this plugin.'
              $('.isa-error-message').html( $err_message ).slideDown(300).delay(8000).hide(500);
            } else {
              $('.isa-success-message').html( 'Success!!! Now check your email address.' ).slideDown(300).delay(8000).hide(500);
            }

          },
          error: function(status, error) {
            $('.loader-email').hide();
            $err_message = 'Please first verify that your email address is correct!<br> You must install and configure an email provider to use this plugin.'
            $('.isa-error-message').stop().html( $err_message ).slideDown(300).delay(8000).hide(500);
          }

        } ).done( function ( response ) {
          console.log( response );
        });
    }

    // Switch to email grouping/no grouping
    $('.slider-groupby').on('click', function(e) {
      e.stopPropagation();
      setTimeout( function() {
        if ( $('.switch').find('input').prop('checked') ) {
           window.location.href = go_to_groupby_email;
        } else {
           window.location.href = go_to_no_groupby;
        }
      }, 410);
    });

    // Send Single Pending Email
    function reload_empty_list() {
      if ( $('#the-list tr').length ) return;
      location.reload();
      return false;

    }
    $('.isa-send-alert-btn').on('click', function(e) {
        let user_email = $(this).attr('data-user_email');
        let block_id = $(this).attr('data-block-id');
        let $pending_obj = $(this).parent();
        $pending_obj.find('.loader').removeClass('hidden-loader');

        let payload = { 'email' : user_email };

        $.ajax( {
            url: alertDataAdmin.api_base_url + '/alert/send',
            method: 'POST',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', alertDataAdmin.nonce );
            },
            data: payload,
            success: function(data) {
              $pending_obj.find('.loader').addClass('hidden-loader');
              if ( data.payload == true ) {
                $('#block-'+block_id).hide(300, function(e) {
                  $(this).remove();
                  reload_empty_list();
                });
              }
            },
            error: function(status, error) {
              console.log(error);
            }

          } ).done( function ( response ) {
            console.log( response );
          });
      });

    // Delete (Pending) request/requests
    $('.isa-delete-btn').on('click', function(e) {
      if (  !confirm("Are you sure you want to delete this request?") ) return;

      $(this).parent().find('.loader').removeClass('hidden-loader');
      let ids = $(this).attr('data-proudct-ids');
      let block_id = $(this).attr('data-block-id');
      let payload = { 'request_ids' : ids };

      $.ajax( {
          url: alertDataAdmin.api_base_url + '/requests/remove',
          method: 'POST',
          beforeSend: function ( xhr ) {
              xhr.setRequestHeader( 'X-WP-Nonce', alertDataAdmin.nonce );
          },
          data: payload,
          success: function(data) {
            $('.loader').addClass('hidden-loader');
            $('#block-'+block_id).hide(300, function(e) {
              $(this).remove();
              reload_empty_list();
            });
          }

        } ).done( function ( response ) {
          console.log( response );
        });
    });

    // Delete Sent Email
    $('.email-action-btn').on('click', function(e) {
        $(this).parent().find('.loader').removeClass('hidden-loader');
        let email_id = $(this).attr('data-email-id');
        let block_id = $(this).attr('data-block-id');
        $.ajax( {
            url: alertDataAdmin.api_base_url + '/email/remove/' + email_id,
            method: 'POST',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', alertDataAdmin.nonce );
            },
            success: function(data) {
              $('.loader').addClass('hidden-loader');
              $('#block-'+block_id).hide(300, function(e) {
                $(this).remove();
                reload_empty_list();
              });
            }
          } ).done( function ( response ) {
            console.log( response );
          });
    });

    // ( WooCommerce Custom Menu Tab only )
    if ( typeof is_woo_menu_ism_tab !== 'undefined' ) {

          $('#mainform').attr('action', 'options.php');

    }

    // Reassignes current color on the submenu
    $('#toplevel_page_in-stock-mailer li').removeClass('current');
    let $links = $('#toplevel_page_in-stock-mailer').find('a');

    $.each( $links, function( index, value ) {

      let page = $(value).text();
      let current_tab = alertDataAdmin.current_tab;

      if ( !current_tab.toLowerCase() && page.toLowerCase() === 'pending') {
        $(value).parent().addClass('current');
      }
      if ( page.toLowerCase() === current_tab.toLowerCase() ) {
        $(value).parent().addClass('current');
      }

    });


});
