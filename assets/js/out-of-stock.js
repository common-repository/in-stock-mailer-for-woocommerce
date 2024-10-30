jQuery(document).ready(function($) {

var isaTimeout;
function show_variation_form() {
  $('.in-stock-form').addClass('isa-form-hidden');
  clearTimeout(isaTimeout);
  isaTimeout = setTimeout(function(){ $('#form-variation-id-' + get_item_id()).hide().removeClass('isa-form-hidden').slideDown(200); }, 1000);
}
if ( alertData.is_variation && alertData.is_variation_button_active ) {

    show_variation_form();
    $('.variations').find('select').on('change', function() {
      show_variation_form();
    });
}

$('.instock-notify').on('click',function(e) {
    e.preventDefault();
    $(this).find('.ism-loader').show();
    $('.instock-notify-email').off('click', this);
    add_notification_ajax($(this).parent());
});
$('.instock-notify-email').on('click', function(e) {
    e.preventDefault();
    $(this).parent().find('.hidden-in-stock-field').slideDown(300);
});
$('#in-stock-form, .in-stock-form').on('submit', function(e) {
    e.preventDefault();
    $(this).find('.ism-loader').show();
    add_notification_ajax(this, true);
});
$('.instock-notification').on('click', function(e) {
  e.preventDefault();
  if (e.target.localName == 'span') {
    $(this).find('.ism-loader').show();
    remove_notification_ajax($(this).parent());
  }
});

function add_notification_ajax(element, email) {
  if (!email) email = false;
  let hpot = $('.its-all-about-honey').val();
  payload = {'hname' : hpot };
  if (email === true ) {
      emailValue = $($(element).find('.in-stock-email')[0]).val();
      if ( $('.isa-submit-checkbox').length ) {
        consent = $($(element).find('.isa-submit-checkbox')[0]).prop('checked');
      } else {
        consent = false;
      }

      $('.in-stock-email').val(emailValue);
      payload = { 'email' : emailValue, 'mchimp_consent' : consent,  'hname': hpot };
  }

  $.ajax( {
      url: alertData.api_base_url + '/product/' + get_item_id(),
      method: 'POST',
      beforeSend: function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', alertData.nonce );
      },
      data: payload,
      success: function(data) {
        $('.ism-loader').hide();
        $(element).find('.in-stock-email').css('border', 'none');
        $(element).find('.instock-notify, .instock-notify-email').slideUp(300);
        $(element).find('.instock-notification').slideDown(300);
        $('.hidden-in-stock-field').hide();
      },
      error: function(error) {
        $(element).find('.in-stock-email').css('border', '2px solid #ff0000a6');
        $('.ism-loader').hide();
      },

    } ).done( function ( response ) {

    });
}

function remove_notification_ajax(element) {
  let hpot = $('.its-all-about-honey').val();
  payload = {'hname' : hpot };
  $.ajax( {
      url: alertData.api_base_url + '/product/remove/' + get_item_id(),
      method: 'POST',
      beforeSend: function ( xhr ) {
          xhr.setRequestHeader( 'X-WP-Nonce', alertData.nonce );
      },
      data: payload,
      success: function(data) {
        $('.ism-loader').hide();
        $(element).find('.instock-notification').slideUp(300);
        $(element).find('.instock-notify, .instock-notify-email').slideDown(300);
      }
    } ).done( function ( response ) {
      console.log( response );
} );
}

function get_item_id() {

  if ( alertData.is_variation == false || alertData.is_variation_button_active == false ) {
    return alertData.product_id;
  } else {
    return $('.variation_id').val();
  }
}

});
