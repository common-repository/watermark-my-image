<?php

// Restrict the access to the plugin's option page only to administrators
watermark_my_image_restrict_admin();

$options = watermark_my_image_get_options($watermark_my_image_configuration_default);
$fonts = watermark_my_image_get_fonts();


if (isset($_POST['save'])) {

  check_admin_referer( 'watermark-my-image-nonce' );

  foreach ($options['watermark_my_image_enable_for'] as $size => $on) {
    if (in_array($size, $_POST['watermark_my_image_enable_for'])) {
      $options['watermark_my_image_enable_for'][$size] = true;
    } else {
      $options['watermark_my_image_enable_for'][$size] = false;
    }
  }

  // Get the POST values, validate and set them to the default values if invalid

  // Configuration

  $options['watermark_my_image_place_inside'] = isset($_POST['watermark_my_image_place_inside']);

  $options['watermark_my_image_background_color'] = ctype_xdigit($_POST['watermark_my_image_background_color']) ? $_POST['watermark_my_image_background_color'] : $watermark_my_image_options_default['watermark_my_image_background_color'];

  $options['watermark_my_image_height'] = ctype_digit($_POST['watermark_my_image_height']) && $_POST['watermark_my_image_height'] < 100 ? $_POST['watermark_my_image_height'] : $watermark_my_image_options_default['watermark_my_image_height'];

  $options['watermark_my_image_text_align'] = in_array($_POST['watermark_my_image_text_align'], array('left', 'right')) ? $_POST['watermark_my_image_text_align'] : $watermark_my_image_options_default['watermark_my_image_text_align'];

  $options['watermark_my_image_offset_x'] = ctype_digit($_POST['watermark_my_image_offset_x']) && $_POST['watermark_my_image_offset_x'] < 100 ? $_POST['watermark_my_image_offset_x'] : $watermark_my_image_options_default['watermark_my_image_offset_x'];

  $options['watermark_my_image_offset_y'] = ctype_digit($_POST['watermark_my_image_offset_y']) && $_POST['watermark_my_image_offset_y'] < 100 ? $_POST['watermark_my_image_offset_y'] : $watermark_my_image_options_default['watermark_my_image_offset_y'];

  $options['watermark_my_image_spacing'] = ctype_digit($_POST['watermark_my_image_spacing']) && $_POST['watermark_my_image_spacing'] < 100 ? $_POST['watermark_my_image_spacing'] : $watermark_my_image_options_default['watermark_my_image_spacing'];

  $options['watermark_my_image_jpeg_quality'] = ctype_digit($_POST['watermark_my_image_jpeg_quality']) && $_POST['watermark_my_image_jpeg_quality'] <= 100 ? $_POST['watermark_my_image_jpeg_quality'] : $watermark_my_image_options_default['watermark_my_image_jpeg_quality'];

  // Text 1

  $options['watermark_my_image_text1']['values'] = strip_tags(preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", trim($_POST['watermark_my_image_text1_values'])));

  $options['watermark_my_image_text1']['font'] = array_key_exists($_POST['watermark_my_image_text1_font'], $fonts) ? $_POST['watermark_my_image_text1_font'] : $watermark_my_image_options_default['watermark_my_image_text1']['font'];

  $options['watermark_my_image_text1']['size'] = ctype_digit($_POST['watermark_my_image_text1_size']) && $_POST['watermark_my_image_text1_size'] < 100 ? $_POST['watermark_my_image_text1_size'] : $watermark_my_image_options_default['watermark_my_image_text1']['size'];

  $options['watermark_my_image_text1']['color'] = ctype_xdigit($_POST['watermark_my_image_text1_color']) && $_POST['watermark_my_image_text1_color'] < 100 ? $_POST['watermark_my_image_text1_color'] : $watermark_my_image_options_default['watermark_my_image_text1']['color'];

  // Text 2

  $options['watermark_my_image_text2']['values'] = strip_tags(preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", trim($_POST['watermark_my_image_text2_values'])));

  $options['watermark_my_image_text2']['font'] = array_key_exists($_POST['watermark_my_image_text2_font'], $fonts) ? $_POST['watermark_my_image_text2_font'] : $watermark_my_image_options_default['watermark_my_image_text2']['font'];

  $options['watermark_my_image_text2']['size'] = ctype_digit($_POST['watermark_my_image_text2_size']) && $_POST['watermark_my_image_text2_size'] < 100 ? $_POST['watermark_my_image_text2_size'] : $watermark_my_image_options_default['watermark_my_image_text2']['size'];

  $options['watermark_my_image_text2']['color'] = ctype_xdigit($_POST['watermark_my_image_text2_color']) && $_POST['watermark_my_image_text2_color'] < 100 ? $_POST['watermark_my_image_text2_color'] : $watermark_my_image_options_default['watermark_my_image_text2']['color'];


  // Update the options

  foreach ($options as $index => $value) {
    update_option($index, $value);
  }

  $suc = 'Configuration successfully saved';

}

if (isset($fonts['error'])) {
  $error = $fonts['error'];
} else if ( !extension_loaded( 'gd' ) ) {
  $error = 'Watermark My Image will not work without the PHP extension, GD.';
} else {
  $gd_info = gd_info();

  if ( !$gd_info['FreeType Support'] ) {
    $error = 'Text watermarking requires FreeType Library.';
  } else {
    $error = null;
  }
}

?>

<div class="wrap">

  <div class="icon32" id="icon-options-general"><br /></div>
  <h2>Watermark My Image</h2>

  <form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
    <?php wp_nonce_field( 'watermark-my-image-nonce' ); ?>

    <?php

    // If there is an error message set, display it
    if (isset($error)) {
      echo '<div class="error"><p>' , $error , '</p></div>';
    }

    // If it is a success message set, display it
    if (isset($suc)) {
      echo '<div class="updated settings-error"><p>' , $suc , '</p></div>';
    }
    ?>

    <h3>Preview</h3>

    <p><img src="<?php echo plugins_url('preview.php', __FILE__);?>" alt="" width="" height="" /></p>

    <h3>Configuration</h3>

    <table class="form-table">
      <tr valign="top">
        <th scope="row">Enable watermark for</th>
        <td>
          <label><input type="checkbox" name="watermark_my_image_enable_for[]" value="medium"<?php echo $options['watermark_my_image_enable_for']['medium'] ? ' checked="checked"' : ''; ?> /> Medium</label> <br />
          <label><input type="checkbox" name="watermark_my_image_enable_for[]" value="large"<?php echo $options['watermark_my_image_enable_for']['large'] ? ' checked="checked"' : ''; ?> /> Large</label> <br />
          <label><input type="checkbox" name="watermark_my_image_enable_for[]" value="fullsize"<?php echo $options['watermark_my_image_enable_for']['fullsize'] ? ' checked="checked"' : ''; ?> /> Full size</label> <br />
          <label><input type="checkbox" name="watermark_my_image_enable_for[]" value="custom_sizes"<?php echo $options['watermark_my_image_enable_for']['custom_sizes'] ? ' checked="checked"' : ''; ?> /> Custom sizes</label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Place watermark inside image</th>
        <td>
          <input type="checkbox" name="watermark_my_image_place_inside" <?php echo $options['watermark_my_image_place_inside'] ? ' checked="checked"' : ''; ?> />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Background color</th>
        <td>
          # <input type="text" maxlength="6" name="watermark_my_image_background_color" value="<?php echo $options['watermark_my_image_background_color'];?>" />

          <div id="background-color-selector" class="color-selector">
            <div style="background-color:#<?php echo $options['watermark_my_image_background_color'];?>"></div>
          </div>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Height</th>
        <td>
          <input type="text" size="22" maxlength="2" name="watermark_my_image_height" value="<?php echo $options['watermark_my_image_height'];?>" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Text align</th>
        <td>
          <label style="margin-right:10px;"><input type="radio" name="watermark_my_image_text_align" value="left"<?php echo $options['watermark_my_image_text_align'] == 'left' ? ' checked="checked"' : ''; ?> /> Left</label>
          <label><input type="radio" name="watermark_my_image_text_align" value="right"<?php echo $options['watermark_my_image_text_align'] == 'right' ? ' checked="checked"' : ''; ?> /> Right</label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Offset x</th>
        <td>
          <input type="text" size="22" maxlength="2" name="watermark_my_image_offset_x" value="<?php echo $options['watermark_my_image_offset_x'];?>" /></label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Offset y</th>
        <td>
          <input type="text" size="22" maxlength="2" name="watermark_my_image_offset_y" value="<?php echo $options['watermark_my_image_offset_y'];?>" /></label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Spacing</th>
        <td>
          <input type="text" size="22" maxlength="2" name="watermark_my_image_spacing" value="<?php echo $options['watermark_my_image_spacing'];?>" /></label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">JPEG quality</th>
        <td>
          <input type="text" size="22" maxlength="3" name="watermark_my_image_jpeg_quality" value="<?php echo $options['watermark_my_image_jpeg_quality'];?>" /></label> (0-100)
        </td>
      </tr>
    </table>

    <h3>Text 1</h3>

    <table class="form-table">
      <tr valign="top">
        <th scope="row">Values</th>
        <td>
          <textarea cols="80" rows="10" name="watermark_my_image_text1_values"><?php echo $options['watermark_my_image_text1']['values'];?></textarea>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Font</th>
        <td>
          <select name="watermark_my_image_text1_font">
            <?php
            foreach ($fonts as $index => $value) {
              ?>
              <option value="<?php echo $index;?>"<?php echo $options['watermark_my_image_text1']['font'] == $index ? ' selected="selected"' : '';?>><?php echo $value;?></option>
              <?php
            }
            ?>
          </select>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Size</th>
        <td>
          <input type="text" size="22" maxlength="2" name="watermark_my_image_text1_size" value="<?php echo $options['watermark_my_image_text1']['size'];?>" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Color</th>
        <td>
          # <input type="text" maxlength="6" name="watermark_my_image_text1_color" value="<?php echo $options['watermark_my_image_text1']['color'];?>" />

          <div id="text1-color-selector" class="color-selector">
            <div style="background-color:#<?php echo $options['watermark_my_image_text1']['color'];?>"></div>
          </div>
        </td>
      </tr>
    </table>

    <h3>Text 2</h3>

    <table class="form-table">
      <tr valign="top">
        <th scope="row">Values</th>
        <td>
          <textarea cols="80" rows="10" name="watermark_my_image_text2_values"><?php echo $options['watermark_my_image_text2']['values'];?></textarea>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Font</th>
        <td>
          <select name="watermark_my_image_text2_font">
            <?php
            foreach ($fonts as $index => $value) {
              ?>
              <option value="<?php echo $index;?>"<?php echo $options['watermark_my_image_text2']['font'] == $index ? ' selected="selected"' : '';?>><?php echo $value;?></option>
              <?php
            }
            ?>
          </select>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Size</th>
        <td>
          <input type="text" size="22" maxlength="2" name="watermark_my_image_text2_size" value="<?php echo $options['watermark_my_image_text2']['size'];?>" />
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Color</th>
        <td>
          # <input type="text" maxlength="6" name="watermark_my_image_text2_color" value="<?php echo $options['watermark_my_image_text2']['color'];?>" />

          <div id="text2-color-selector" class="color-selector">
            <div style="background-color:#<?php echo $options['watermark_my_image_text2']['color'];?>"></div>
          </div>
        </td>
      </tr>
    </table>

    <p class="submit">
      <input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes') ?>" />
    </p>

  </form>
</div>
