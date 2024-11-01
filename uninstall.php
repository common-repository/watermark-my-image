<?php

// *sighs* :(

if ( function_exists('register_uninstall_hook') ) {
  register_uninstall_hook(__FILE__, 'watermark_my_image_uninstall_hook');
}

/**
* Remove all the options from the database and delete the scheduled cron.
*/
function watermark_my_image_uninstall_hook()
{
	delete_option('watermark_my_image_enable_for');
	delete_option('watermark_my_image_background_color');
	delete_option('watermark_my_image_height');
	delete_option('watermark_my_image_text_align');
	delete_option('watermark_my_image_offset_x');
	delete_option('watermark_my_image_offset_y');
	delete_option('watermark_my_image_spacing');
	delete_option('watermark_my_image_jpeg_quality');
	delete_option('watermark_my_image_text1');
	delete_option('watermark_my_image_text2');
	delete_option('watermark_my_image_use_wp_cron');
	delete_option('watermark_my_image_wp_cron_interval');
	delete_option('watermark_my_image_secret_key');
	delete_option('watermark_my_image_images_per_batch');
	delete_option('watermark_my_image_id_gt');
	delete_option('watermark_my_image_id_lt');
	delete_option('watermark_my_image_processed_images');
	delete_option('watermark_my_image_watermarking_status');
	delete_option('watermark_my_image_count');
	delete_option('watermark_my_image_process_finished');
	wp_clear_scheduled_hook('watermark_my_image_cron_hook');
}
