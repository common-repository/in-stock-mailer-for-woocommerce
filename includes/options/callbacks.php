<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function ism_callback_field_text( $args ) {

  $option = !empty( $args['option'] ) ? $args['option'] : 'any';
	$options = get_option( $option, ism_default_values() ) + ism_default_values();
	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	$required = isset( $args['required'] ) && $args['required'] ? 'required' : '';
  $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
	$description = isset( $args['description'] ) ? $args['description'] : '';
	$disabled =  isset( $args['disabled'] ) && $args['disabled'] ? 'disabled'  : '';
	$value = isset( $options[$id] ) ? sanitize_text_field( $options[$id] ) : '';
	$bypass_value = !empty( $args['bypass_value'] ) ? esc_attr( $args['bypass_value'] ) : '';  ?>

  <tr valign="top">
    <th scope="row"><?php echo $label; ?></th>
    <td>
        <input id="<?php echo $option . '_' . $id; ?>" name="<?php echo $option . '['. $id . ']'; ?>"
               placeholder="<?php echo $placeholder; ?>" type="text" size="50" <?php echo $required .' '.$disabled.' '; ?>
							 maxlength="<?php echo esc_attr(ism_get_text_length($id)) ?>" value="<?php echo $value ? $value : $bypass_value; ?>"><br />
        <p class="description"><?php echo $description; ?></p>
    </td>
  </tr>
  <?php
}

function ism_callback_field_pass( $args ) {
  $option = !empty( $args['option'] ) ? $args['option'] : 'any';
	$options = get_option( $option, ism_default_values() ) + ism_default_values();
	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	$required = isset( $args['required'] ) && $args['required'] ? 'required' : '';
  $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
	$description = isset( $args['description'] ) ? $args['description'] : '';
	$disabled =  isset( $args['disabled'] ) && $args['disabled'] ? 'disabled'  : '';
	$value = isset( $options[$id] ) ? sanitize_text_field( $options[$id] ) : '';
	$bypass_value = !empty( $args['bypass_value'] ) ? esc_attr( $args['bypass_value'] ) : '';  ?>

  <tr valign="top">
    <th scope="row"><?php echo $label; ?></th>
    <td>
        <input id="<?php echo $option . '_' . $id; ?>" name="<?php echo $option . '['. $id . ']'; ?>"
               placeholder="<?php echo $placeholder; ?>" type="password" autocomplete="new-password" size="50" <?php echo $required .' '.$disabled.' '; ?>
							 maxlength="<?php echo esc_attr(ism_get_text_length($id)) ?>" value="<?php echo $value ? $value : $bypass_value; ?>"><br />
        <p class="description"><?php echo $description; ?></p>
    </td>
  </tr>
  <?php
}

function ism_callback_field_textarea( $args ) {
  $option = !empty( $args['option'] ) ? $args['option'] : 'any';
	$options = get_option( $option, ism_default_values() ) + ism_default_values();

	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	$description = isset( $args['description'] ) ? $args['description'] : '';
	$allowed_tags = wp_kses_allowed_html( 'post' );

	$value = isset( $options[$id] ) ? wp_kses( stripslashes_deep( $options[$id] ), $allowed_tags ) : '';
  $value = trim( preg_replace( '/\h+/', ' ',  $value )  );

  ?>

  <tr valign="top">
    <th scope="row"><?php echo $label; ?></th>
    <td>
        <textarea id="<?php echo $option . '_' . $id; ?>" name="<?php echo $option . '['. $id . ']'; ?>" rows="5" cols="53" maxlength="<?php echo esc_attr(ism_get_text_length($id));?>"><?php echo $value; ?></textarea><br />
        <p class="description"><?php echo $description; ?></p>
    </td>
  </tr>
  <?php
}

function ism_callback_radio_field( $args ) {
  $option = !empty( $args['option'] ) ? $args['option'] : 'any';
	$options = get_option( $option, ism_default_values() ) + ism_default_values();

	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	$description = isset( $args['description'] ) ? $args['description'] : '';
	$value = isset( $options[$id] ) ? $options[$id] : '';
	$radio_options = $args['options'];

	if ( $radio_options ) :?>

		<tr valign="top">
			<th scope="row"><?php echo $label; ?></th>
			 <td>

				 <div id="<?php echo $option . '_' . esc_attr($id); ?>" class="ch-inline-radio"><?php
						foreach ( $radio_options as $radio_value => $radio_label ) : ?>
							<label><input name="<?php echo $option . '['. esc_attr($id) . ']'; ?>"
							type="radio" value="<?php echo esc_attr($radio_value); ?>" <?php echo checked( $value == $radio_value, true, false ); ?> >
							<br/><span><?php echo esc_html(ucfirst($radio_label)); ?></span></label><br/><?php
						endforeach; ?>
				 </div><br/>
				 <p class="description"><?php echo $description; ?></p>
			 </td>
		</tr><?php

	endif;
}

function ism_callback_color_picker( $args ) {
  $option = !empty( $args['option'] ) ? $args['option'] : 'any';
	$options = get_option( $option, ism_default_values() ) + ism_default_values();

	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
	$description = isset( $args['description'] ) ? $args['description'] : '';
	$value = isset( $options[$id] ) ? sanitize_hex_color( $options[$id] ) : '';
  ?>
  <tr valign="top">
    <th scope="row"><?php echo $label; ?></th>
    <td>
      <input id="<?php echo $option . '_' . $id; ?>" name="<?php echo $option . '['. $id . ']'; ?>"  type="text" value="<?php echo $value; ?>" class="my-color-field"
      data-default-color="<?php echo sanitize_hex_color(ism_default_values()[$id]); ?>" />
      <p class="description"><?php echo $description; ?></p>
    </td>
  </tr>
  <?php
}

function ism_callback_color_picker_w_image( $args ) {
	$img_src= isset( $args['img_src'] )    ? $args['img_src']    : '';
	$img_description = isset( $args['img_description'] )  ? $args['img_description']    : ''; ?>
	  <img src="<?php echo esc_url( $img_src ); ?>" style="max-width:500px; box-shadow: 1px 1px 7px -4px rgba(0, 0, 0, 0.43);">
		<p class="description"><?php echo $img_description; ?></p>
	<?php
 	ism_callback_color_picker( $args );
}

function ism_callback_switch( $args = []) {
  $option = !empty( $args['option'] ) ? $args['option'] : 'any';
	$options = get_option( $option, ism_default_values() ) + ism_default_values();
	$id    = isset( $args['id'] )    ? $args['id']    : '';
	$label = isset( $args['label'] ) ? $args['label'] : '';
  $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
	$description = isset( $args['description'] ) ? $args['description'] : '';
	$safe_value = ( isset( $options[$id] ) && rest_sanitize_boolean( $options[$id] ) ) ? 'checked': '';
	$bypass_value = !empty( $args['bypass_value'] ) ? 'checked' : '';
	?>
  <tr valign="top">
    <th scope="row"><?php echo $label; ?></th>
    <td>
			<label id="<?php echo $id; ?>" class="switch" style="margin-top: 6px;">
				<input id="<?php echo $option . '_' . $id; ?>" name="<?php echo $option . '['. $id . ']'; ?>" type="checkbox" <?php echo !empty($safe_value) ? $safe_value : $bypass_value; ?> >
				<span class="slider round"></span></label><br/>
      <p class="description"><?php echo $description; ?></p>
    </td>
  </tr>
  <?php
}


function ism_send_test_email_to_field() {
	?>
	  <table class="form-table" role="presentation">
	    <tbody>
	        <tr valign="top">
	          <th scope="row">Recipent</th>
	          <td>
	              <input id="recipient_email_field" name="recipient_email_field" type="email" size="50" value="" placeholder="Your email.." required><br />
	              <p class="description">1. Type in an email address and check if you receive the test email.<br/>
								  2. Then go to <b><i>Sent Alerts</i></b> and it should be listed there.<br/>
								  3. If Mailchimp is enabled you should see the email added to Mailchimp withing 10 minutes ( Yellow Mailchimp logo ).<br/><br/>
		              If the test is successful the application is all set! <i>( Please remember to check your spam folder! )</i>
								</p>
	          </td>
	        </tr>
	      </tbody>
	  </table>

	  <?php
}

function ism_description() {
  // LEAVE BLANK
}
