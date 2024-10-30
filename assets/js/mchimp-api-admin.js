jQuery(document).ready(function($) {


  $('#in-stock-mailer-form-mchimp').on('submit', function(e) {
    e.preventDefault();
    $('.mchimp-loader').show();
    $('#mchimp-setting-input').hide();
    mchimp_setup();
  });

  $('#submit-reset-mchimp').on('click', function(e) {
    e.preventDefault();
    $('.mchimp-loader').show();
    $('#mchimp-setting-input').hide();
    mchimp_reset();
  });

  $( document ).on( "click", '.selectable-store:not(.selected-store)', function() {
    $('.mchimp-loader').show();
    $('#mchimp-setting-input').hide();
    let store = $(this).attr('data-store-id');
    mchimp_set_store(store);
  });

  var enableAjax = true;
  var currentState = alertDataAdmin.current_state_mchimp;
  $('#mchimp-enable').find('input').change(function(e) {
      e.stopPropagation();
      if ( !enableAjax ) {
         $(this).prop('checked', !currentState);
         return;
      }
      if ( $(this).is(':checked') ) {
         enable_mchimp(true);
         $('#mchimp-options-container').slideDown(200);
      } else {
         enable_mchimp(false);
         $('#mchimp-options-container').slideUp(200);
      }
    });


function enable_mchimp( state ) {

    let chimpSlide = $('#mchimp-enable').find('input');
    let payload = { 'mchimp_enabled' : state };

    if ( enableAjax ) {
      enableAjax = false;

      $.ajax( {
      url: alertDataAdmin.api_base_url + '/mchimp-enable',
      method: 'POST',
      beforeSend: function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', alertDataAdmin.nonce );
      },
      data: payload,
      success: function(data) {
        currentState = data.state;

        if( $(chimpSlide).prop('checked') != state ) {
           $(chimpSlide).prop( 'checked', state );
        }

      },
      error: function(status, error) {

        $(chimpSlide).prop( 'checked', !state ).change();
        enableAjax = true;
      }

      } ).done( function ( response ) {
        enableAjax = true;
      });
    }
}

function reset_fields() {
  $('.mchimp-loader').hide();
  $('.mchimp-setup').hide();
  $('#ism_custom_option_mchimp_api_key').val('').attr('disabled', false);
  $('#ism_custom_option_mchimp_server').attr('disabled', false).val('');
  $('#empty-store-container').show();
  $('.selectable-store').remove();
  $('#mchimp-setting-input').show();
}

function mchimp_status_check() {

  $.ajax( {
    url: alertDataAdmin.api_base_url + '/mchimp-status',
    method: 'GET',
    beforeSend: function ( xhr ) {
        xhr.setRequestHeader( 'X-WP-Nonce', alertDataAdmin.nonce );
    },
    success: function(data) {
      if ( data.status && data.set_store) {
        $('.mchimp-success').show(200);
      }
      else if ( data.status && !data.set_store) {
        $('.mchimp-alert').show(200);
      }
      else if ( !data.status ) {
        reset_fields();
      }

      build_stores(data);
      $('.mchimp-loader').hide();
    },
    error: function(status, error) {
      console.log(error);
    }

    } ).done( function ( response ) {

  });
}

function mchimp_setup() {
  let apiKey = $('#ism_custom_option_mchimp_api_key').val();
  let server = $('#ism_custom_option_mchimp_server').val();
  let payload = { 'api_key' : apiKey, 'server' : server };

  $.ajax( {
  url: alertDataAdmin.api_base_url + '/mchimp-config',
  method: 'POST',
  beforeSend: function ( xhr ) {
      xhr.setRequestHeader( 'X-WP-Nonce', alertDataAdmin.nonce );
  },
  data: payload,
  success: function(data) {

    if ( data.status ) {
      $('.mchimp-setup').hide();
      $('.mchimp-alert').show(200);
      $('#ism_custom_option_mchimp_api_key').attr('disabled', true);
      $('#ism_custom_option_mchimp_server').attr('disabled', true);
    } else {
      reset_fields();
      $('.mchimp-error').show(200);
    }
    build_stores(data);
    $('.mchimp-loader').hide();
  },
  error: function(status, error) {
    console.log(error);
  }

  } ).done( function ( response ) {
  });
}

function mchimp_reset() {

  $.ajax( {
  url: alertDataAdmin.api_base_url + '/mchimp-reset',
  method: 'POST',
  beforeSend: function ( xhr ) {
      xhr.setRequestHeader( 'X-WP-Nonce', alertDataAdmin.nonce );
  },
  success: function(data) {
    reset_fields();
  },
  error: function(status, error) {
    console.log(error);
  }

  } ).done( function ( response ) {

  });
}

function style_checkmark() {
  return '<span class="checkmark"><div class="checkmark_stem"></div><div class="checkmark_kick"></div></span>';
}

function build_stores(data) {
  if (data.stores.total_items > 0 ) {
    $('#empty-store-container').hide();
    let $store_container = $('#selected-store-container');
    $.each(data.stores.stores, function( index, store ) {
      let is_selected = (data.set_store == store.id) ? 'selected-store' : '';
      let $storeBlock = $('<div/>', { id: 'store-'+store.id, 'data-store-id': store.id, "class": 'selectable-store'+' '+is_selected }).text(store.name);

      if (is_selected.length) {
        let checkmark = style_checkmark();
        $(checkmark).appendTo($storeBlock);
      }

      $storeBlock.appendTo($store_container);
    });

  }
}

function mchimp_get_stores() {

  let payload = {};

  $.ajax( {
  url: alertDataAdmin.api_base_url + '/mchimp-get-stores',
  method: 'POST',
  beforeSend: function ( xhr ) {
      xhr.setRequestHeader( 'X-WP-Nonce', alertDataAdmin.nonce );
  },
  data: payload,
  success: function(data) {

    build_stores(data);

  },
  error: function(status, error) {
    console.log(error);
  }

  } ).done( function ( response ) {



  });
}

function mchimp_set_store(store) {

  let payload = {'store' : store};
  let $store = $('#store-'+store);
  $store.addClass('selected-store');

  $.ajax( {
  url: alertDataAdmin.api_base_url + '/mchimp-set-store',
  method: 'POST',
  beforeSend: function ( xhr ) {
      xhr.setRequestHeader( 'X-WP-Nonce', alertDataAdmin.nonce );
  },
  data: payload,
  success: function(data) {
    $('.mchimp-loader').hide();
    if (data.status) {
       let checkmark = style_checkmark();
       $('.mchimp-setup').hide();
       $('.checkmark').remove();
       $(checkmark).appendTo($store);
       $('.mchimp-success').show(200);
    } else {
       $('.mchimp-error').show(200);
        $store.removeClass('selected-store');
    }



  },
  error: function(status, error) {
    console.log(error);
  }

  } ).done( function ( response ) {



  });
}


mchimp_status_check();

});
