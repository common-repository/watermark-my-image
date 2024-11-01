<?php
// Restrict the access to the plugin's option page only to administrators
watermark_my_image_restrict_admin();

$options = watermark_my_image_get_options($watermark_my_image_apply_default);
$watermarking_status = get_option('watermark_my_image_watermarking_status');

if ( !extension_loaded( 'DOM' ) ) {
  $error = 'The watermarking process will not work without the PHP extension, DOM.';
}

if (isset($_POST['save']) || isset($_POST['start'])) {

  check_admin_referer( 'watermark-my-image-nonce' );

  // Get the POST values, validate and set them to the default values if invalid

  $options['watermark_my_image_use_wp_cron'] = in_array($_POST['watermark_my_image_use_wp_cron'], array(0, 1)) ? $_POST['watermark_my_image_use_wp_cron'] : $watermark_my_image_apply_default['watermark_my_image_use_wp_cron'];

  $watermark_my_image_wp_cron_interval = $options['watermark_my_image_wp_cron_interval'];

  $options['watermark_my_image_wp_cron_interval'] = in_array($_POST['watermark_my_image_wp_cron_interval'], array('every_minute', 'every_five_minutes', 'every_ten_minutes', 'every_fifteen_minutes', 'every_half_an_hour')) ? $_POST['watermark_my_image_wp_cron_interval'] : $watermark_my_image_apply_default['watermark_my_image_wp_cron_interval'];

  $options['watermark_my_image_secret_key'] = strlen($_POST['watermark_my_image_secret_key']) == 32 ? $_POST['watermark_my_image_secret_key']  : watermark_my_image_generate_random_hash();

  $options['watermark_my_image_images_per_batch'] = ctype_digit($_POST['watermark_my_image_images_per_batch']) ? $_POST['watermark_my_image_images_per_batch'] : $watermark_my_image_apply_default['watermark_my_image_images_per_batch'];

  if ($watermarking_status == '') {
    $options['watermark_my_image_id_gt'] = ctype_digit($_POST['watermark_my_image_id_gt']) ? $_POST['watermark_my_image_id_gt'] : $watermark_my_image_apply_default['watermark_my_image_id_gt'];

    $options['watermark_my_image_id_lt'] = ctype_digit($_POST['watermark_my_image_id_lt']) ? $_POST['watermark_my_image_id_lt'] : $watermark_my_image_apply_default['watermark_my_image_id_lt'];
  }

  if ($options['watermark_my_image_id_gt'] > $options['watermark_my_image_id_lt']) {
    $error = '"Attachment ID &gt;=" needs to be smaller or equal to "Attachment ID &lt;="';
  } else if ($watermarking_status == '') {
    $max = $wpdb->get_var("SELECT MAX(`ID`) FROM `$wpdb->posts` WHERE `post_type` = 'attachment' AND `post_mime_type` IN ('image/jpeg', 'image/png', 'image/gif')");

    if ($options['watermark_my_image_id_lt'] > $max) {
      $error = '"Attachment ID &lt;=" cannot be bigger than ' . $max;
    }
  }

  // Check if the Start button was pressed

  if (isset($_POST['start']) && !isset($error)) {

    if ($options['watermark_my_image_id_gt'] == '' || $options['watermark_my_image_id_lt'] == '') {
      $error = 'Please fill in "Attachment ID &gt;=" and "Attachment ID &lt;="';
    } else {

      $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(`ID`) FROM $wpdb->posts WHERE `post_type` = 'attachment' AND `post_mime_type` IN ('image/jpeg', 'image/png', 'image/gif') AND `ID` >= %s AND `ID` <= %s", $options['watermark_my_image_id_gt'], $options['watermark_my_image_id_lt']) );

      add_option('watermark_my_image_processed_images', 0, null, 'no');
      add_option('watermark_my_image_watermarking_status', 'started', null, 'no');
      add_option('watermark_my_image_count', $count, null, 'no');

      $watermarking_status = 'started';

      $suc = 'Watermarking process started ';

      // Update the options

      foreach ($options as $index => $value) {
        update_option($index, $value);
      }
    }
  } else if (!isset($error)) {
    $suc = 'Configuration successfully saved';

    // Update the options

    foreach ($options as $index => $value) {
      if ( in_array($index, array('watermark_my_image_id_gt', 'watermark_my_image_id_lt')) )
        continue;
      update_option($index, $value);
    }
  }

  // Cron schedule
  if ($suc) {
    if ($options['watermark_my_image_use_wp_cron'] == 0 || $watermark_my_image_wp_cron_interval != $options['watermark_my_image_wp_cron_interval']) {
      wp_clear_scheduled_hook('watermark_my_image_cron_hook');
    }
    if ($options['watermark_my_image_use_wp_cron'] == 1 && !wp_next_scheduled('watermark_my_image_cron_hook')) {
      wp_schedule_event( time(), $options['watermark_my_image_wp_cron_interval'], 'watermark_my_image_cron_hook' );
    }
  }

} else if (isset($_POST['pause'])) {

  update_option('watermark_my_image_watermarking_status', 'paused');

  $watermarking_status = 'paused';

  $suc = 'Watermarking process paused';

} else if (isset($_POST['resume'])) {

  update_option('watermark_my_image_watermarking_status', 'started');

  $watermarking_status = 'started';

  $suc = 'Watermarking process resumed';

} else if (isset($_POST['stop'])) {

  delete_option('watermark_my_image_processed_images');
  delete_option('watermark_my_image_watermarking_status');
  delete_option('watermark_my_image_count');

  wp_clear_scheduled_hook('watermark_my_image_cron_hook');

  $watermarking_status = '';

  $suc = 'Watermarking process stopped';
}

?>

<div class="wrap">
  <h2>Apply watermark</h2>

  <?php

  // Progress bar
  if ( in_array($watermarking_status, array('started', 'paused')) ) {
    $processed_images = get_option('watermark_my_image_processed_images');
    $count = get_option('watermark_my_image_count');

    $procent = $count > 0 ? ($processed_images * 100) / $count : 0;

    echo sprintf('<div id="watermark-my-image-progress-wrapper" title="%.2f%%"><div id="watermark-my-image-progress" style="width:%.2f%%;"></div></div>', $procent, $procent);
  }

  // Process finished - display a message
  if (get_option('watermark_my_image_process_finished')) {
    $suc = 'Watermarking process finished';
    delete_option('watermark_my_image_process_finished');
  }
  ?>

  <p><strong style="text-decoration:underline;">VERY IMPORTANT:</strong> Please backup all the files you are going to watermark and the posts & postmeta mysql tables before you start the watermarking process !</p>

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

    <h3>Cron Settings</h3>

    <table class="form-table">
      <tr valign="top">
        <th scope="row">Use Wordpress cron</th>
        <td>
          <label><input type="radio" name="watermark_my_image_use_wp_cron" value="1"<?php echo $options['watermark_my_image_use_wp_cron'] == 1 ? ' checked="checked"' : '';?> /> Yes</label> &nbsp;
          <label><input type="radio" name="watermark_my_image_use_wp_cron" value="0"<?php echo $options['watermark_my_image_use_wp_cron'] == 0 ? ' checked="checked"' : '';?> /> No</label>
        </td>
      </tr>

      <tr class="yes <?php echo $options['watermark_my_image_use_wp_cron'] == 1 ? 'show' : 'hidden';?>">
        <th scope="row">Wordpress cron inverval</th>
        <td>
          <select name="watermark_my_image_wp_cron_interval">
            <option value="every_minute"<?php echo $options['watermark_my_image_wp_cron_interval'] == 'every_minute' ? ' selected="selected"' : '';?>>Once per minute</option>
            <option value="every_five_minutes"<?php echo $options['watermark_my_image_wp_cron_interval'] == 'every_five_minutes' ? ' selected="selected"' : '';?>>Once five minutes</option>
            <option value="every_ten_minutes"<?php echo $options['watermark_my_image_wp_cron_interval'] == 'every_ten_minutes' ? ' selected="selected"' : '';?>>Once ten minutes</option>
            <option value="every_fifteen_minutes"<?php echo $options['watermark_my_image_wp_cron_interval'] == 'every_fifteen_minutes' ? ' selected="selected"' : '';?>>Once fifteen minutes</option>
            <option value="every_half_an_hour"<?php echo $options['watermark_my_image_wp_cron_interval'] == 'every_half_an_hour' ? ' selected="selected"' : '';?>>Once half an hour</option>
          </select>
        </td>
      </tr>

      <tr class="no <?php echo $options['watermark_my_image_use_wp_cron'] == 0 ? 'show' : 'hidden';?>">
        <th scope="row">Secret key</th>
        <td>
          <input type="text" size="42" name="watermark_my_image_secret_key" maxlength="32" value="<?php echo $options['watermark_my_image_secret_key']; ?>" />
        </td>
      </tr>

      <tr class="no <?php echo $options['watermark_my_image_use_wp_cron'] == 0 ? 'show' : 'hidden';?>">
        <th scope="row">Cron command</th>
        <td>
          <input type="text" size="100" readonly="readonly" value="php <?php echo __DIR__;?>/cron.php secret_key=<?php echo $options['watermark_my_image_secret_key'];?> >/dev/null 2>&1" /> <br />
        </td>
      </tr>

      <tr valign="top">
        <th scope="row">Images per batch</th>
        <td>
          <input type="text" size="5" name="watermark_my_image_images_per_batch" value="<?php echo $options['watermark_my_image_images_per_batch']; ?>" /> <br />
        </td>
      </tr>
    </table>

    <h3>Attachments Settings</h3>

    <table class="form-table">
      <tr valign="top">
        <th scope="row">Attachment ID &gt;=</th>
        <td>
          <input type="text" size="42" name="watermark_my_image_id_gt" value="<?php echo $options['watermark_my_image_id_gt']; ?>"<?php echo $watermarking_status != '' ? ' readonly="readonly"' : '';?>/><?php echo $watermarking_status != '' ? ' <strong>*</strong>' : '';?>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Attachment ID &lt;=</th>
        <td>
          <input type="text" size="42" name="watermark_my_image_id_lt" value="<?php echo $options['watermark_my_image_id_lt']; ?>"<?php echo $watermarking_status != '' ? ' readonly="readonly"' : '';?> /><?php echo $watermarking_status != '' ? ' <strong>*</strong>' : '';?>
        </td>
      </tr>
    </table>

    <?php echo $watermarking_status != '' ? '<p><strong>* Cannot be edited during the watermarking process</strong></p>' : '';?>

    <p class="submit">
      <input type="submit" class="button-primary" name="save" value="<?php _e('Save') ?>" />
      <?php
      if ($watermarking_status == '') {
        ?>
        <input type="submit" class="button-primary" name="start" value="<?php _e('Start') ?>" />
        <?php
      } else if ($watermarking_status == 'started') {
        ?>
        <input type="submit" class="button-primary" name="pause" value="<?php _e('Pause') ?>" />
        <input type="submit" class="button-primary" name="stop" value="<?php _e('Stop') ?>" />
        <?php
      } else if ($watermarking_status == 'paused') {
        ?>
        <input type="submit" class="button-primary" name="resume" value="<?php _e('Resume') ?>" />
        <input type="submit" class="button-primary" name="stop" value="<?php _e('Stop') ?>" />
        <?php
      }
      ?>
    </p>
  </form>
</div>
