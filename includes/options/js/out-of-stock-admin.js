jQuery(document).ready(function($) {

    // Instantiates color-picker
    $('.my-color-field').wpColorPicker();

    // Resets all options
    $('#submit-reset').on('click', function(e) {
      e.preventDefault();
      if ( confirm("Reset All settings?") ) {
        $('#ism_stock_alert_options_default_settings').val("reset");
        $("#in-stock-mailer-form").submit();
        $("#mainform").submit();
        // ism_woo_menu_post_option();
      }
    });

    // Sends the test email and handles the response
    $('#email-notification-test').on('submit', function(e) {
      e.preventDefault();
      $(this).find('.loader-email').show();
      send_test_email_ajax();
    });
    function send_test_email_ajax() {
          emailValue = $('#recipient_email_field').val();
          payload = { 'test_email' : emailValue };
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
        if ( $('.switch').find('input').attr('checked') == 'checked' ) {
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

        payload = { 'email' : user_email };
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
      payload = { 'request_ids' : ids };

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

});
