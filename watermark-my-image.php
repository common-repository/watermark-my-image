<?php
/*
Plugin Name: Watermark My Image
Plugin URI: http://marianbucur.com/en/wordpress-projects/watermark-my-image.html
Description: This plugin enables you to watermark your images, by placing a simple, yet very customizable, watermark beneath the original images.
Author: Marian Bucur
Version: 0.21
Author URI: http://marianbucur.com/
*/

/*
* Copyright 2011  Watermark My Image  (email : thebigman@marianbucur.com)
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License, version 2, as
* published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

global $watermark_my_image_configuration_default;

$watermark_my_image_configuration_default = array(
  'watermark_my_image_enable_for' => array(
		'medium' => true,
		'large' => true,
		'fullsize' => true,
		'custom_sizes' => true
  ),
	'watermark_my_image_place_inside' => false,
  'watermark_my_image_background_color' => 'F4F4F4',
  'watermark_my_image_height' => 30,
  'watermark_my_image_text_align' => 'right',
	'watermark_my_image_offset_x' => 9,
  'watermark_my_image_offset_y' => 9,
	'watermark_my_image_spacing' => 9,
	'watermark_my_image_jpeg_quality' => 80,
  'watermark_my_image_text1' => array(
    'values' => 'Enjoy using',
    'font' => 'Verdana.ttf',
    'size' => 7,
    'color' => 'A9A9A9'
  ),
  'watermark_my_image_text2' => array(
    'values' => 'Watermark My Image',
    'font' => 'Verdana.ttf',
    'size' => 12,
    'color' => 'A9A9A9'
  )
);

global $watermark_my_image_apply_default;

$watermark_my_image_apply_default = array(
	'watermark_my_image_use_wp_cron' => 1,
	'watermark_my_image_wp_cron_interval' => 'every_five_minutes',
	'watermark_my_image_secret_key' => '',
	'watermark_my_image_images_per_batch' => 20,
	'watermark_my_image_id_gt' => '',
	'watermark_my_image_id_lt' => ''
);


/**
* Generates a random hash.
*
*	@param int $len What length should the hash string have? Default: 32.
*	@return string
*/
function watermark_my_image_generate_random_hash($len = 32)
{

	if (!function_exists('mt_rand')) {
		function mt_rand($min, $max)
		{
			return rand($min, $max);
		}
	}

	$str = 'abcdefghijklmnopqrstuvwxyz0123456789';
	$str_len = strlen($str);

	$secret_key = '';

	for ($i = 0; $i < $len; $i++) {
		$secret_key .= $str[ mt_rand( 0, $str_len - 1 ) ];
	}

	return $secret_key;
}


/**
* The function called when installing the plugin.
*/
function watermark_my_image_install()
{
  global $watermark_my_image_configuration_default, $watermark_my_image_apply_default;

  foreach ($watermark_my_image_configuration_default as $index => $value) {

		if ($index == 'watermark_my_image_enable_for' && get_option('watermark_my_image_enable_for')) {

			$value = get_option('watermark_my_image_enable_for');

			if (isset($value['thumbnail']))
				unset($value['thumbnail']);

			if (!isset($value['custom_sizes']))
				$value['custom_sizes'] = true;

			delete_option('watermark_my_image_enable_for');
		}

    add_option($index, $value, null, 'no');
  }

	foreach ($watermark_my_image_apply_default as $index => $value) {
		if ($index == 'watermark_my_image_secret_key') {
			$value = watermark_my_image_generate_random_hash();
		}
    add_option($index, $value, null, 'no');
  }

	// Schedule the Wordpress cron job if it isn't already scheduled and if the user chose to
	if ( !wp_next_scheduled('watermark_my_image_cron_hook') && get_option('watermark_my_image_use_wp_cron') == 1 ) {
		wp_schedule_event( time(), $watermark_my_image_apply_default['every_five_minutes'], 'watermark_my_image_cron_hook' );
	}
}

// Register the activation function
register_activation_hook( __FILE__, 'watermark_my_image_install' );


/**
* The function called when deactivating the plugin.
*/
function watermark_my_image_deactivate()
{
	wp_clear_scheduled_hook('watermark_my_image_cron_hook');
}

// Register the deactivation function
register_deactivation_hook( __FILE__, 'watermark_my_image_deactivate' );


/**
* The function responsible with restricting the use of the plugin only to admins
*/
function watermark_my_image_restrict_admin()
{
	if ( !current_user_can('manage_options') ) {
		wp_die( __('You are not allowed to access this part of the site.') );
	}
}


/**
* Add some more links to the plugin row meta.
*
*	@param array $links Already defined links
*	@param string $file File path
* @return array
*/
function watermark_my_image_more_plugin_links($links, $file)
{
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="admin.php?page=watermark-my-image/main.php">' . __('Configuration') . '</a>';
		$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7VE22LCQPTQPU">' . __('Donate') . '</a>';
	}
	return $links;
}

//Additional links on the plugin page
add_filter('plugin_row_meta', 'watermark_my_image_more_plugin_links', 10, 2);


/**
* The function responsible with adding the options page.
*/
function watermark_my_image_admin_menu()
{
	if (function_exists('add_menu_page')) {
		add_menu_page('Watermark My Image', 'Watermark My Image', 'manage_options', 'watermark-my-image/main.php');
	}

	if (function_exists('add_submenu_page')) {
		add_submenu_page('watermark-my-image/main.php', 'Configuration', 'Configuration', 'manage_options', 'watermark-my-image/main.php');
		add_submenu_page('watermark-my-image/main.php', 'Apply watermark', 'Apply Watemark', 'manage_options', 'watermark-my-image/apply.php');
	}
}

// Add admin menu
add_action( 'admin_menu', 'watermark_my_image_admin_menu' );


/**
* Custom intervals used for Wordpress' cron system.
*
*	@param array $schedules Already defined intervals
*	@return array
*/
function watermark_my_image_filter_cron_schedules($schedules)
{

	$schedules['every_minute'] = array(
		'interval' => 60,
		'display' => __( 'Once per minute' )
	);

	$schedules['every_five_minutes'] = array(
		'interval' => 300,
		'display' => __( 'Once five minutes' )
	);

	$schedules['every_ten_minutes'] = array(
		'interval' => 600,
		'display' => __( 'Once ten minutes' )
	);

	$schedules['every_fifteen_minutes'] = array(
		'interval' => 900,
		'display' => __( 'Once fifteen minutes' )
	);

	$schedules['every_half_an_hour'] = array(
		'interval' => 1800,
		'display' => __( 'Once half an hour' )
	);

	return $schedules;
}

// Add custom intervals for Wordpress' cron system
add_filter( 'cron_schedules', 'watermark_my_image_filter_cron_schedules' );


/**
* Returns an array containing all of Watermark My Image's options from the database
*
* @return array
*/
function watermark_my_image_get_options($options)
{
	$options_aux = array();

	foreach ($options as $index => $value) {
		$options_aux[$index] = get_option($index);
	}

	return $options_aux;
}

$fonts_directory  = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR;

/**
* Returns an array containing the names and file names of all found fonts from the  $fonts_directory
*
* @return array
*/
function watermark_my_image_get_fonts()
{
	global $fonts_directory;

	$fonts = array();

	try {
		$dir = new DirectoryIterator($fonts_directory);

		foreach($dir as $file) {
			if($file->isFile()) {
				$font = pathinfo($file->getFilename());

				if(in_array(strtolower($font['extension']), array('ttf', 'otf'))) {
					if(!$file->isReadable()) {
						$fonts['error'] = sprintf('Some fonts might be unreadable, try chmoding contents of the folder <strong>%s</string> to writable and refresh this page.', $fonts_directory);
					}

					$fonts[$font['basename']] = str_replace('_', ' ', $font['filename']);
				}
			}
		}

		ksort($fonts);

	} catch(Exception $e) {}

	return $fonts;
}

/**
* Converts the color from hex to rgb
*
* @param string $hex
* @return array
*/
function watermark_my_image_hex_to_rgb($hex)
{
	$dec = hexdec($hex);

	return array(0xFF & ($dec >> 0x10), 0xFF & ($dec >> 0x8), 0xFF & $dec);
}

/**
* Returns a random string from an array of random strings
*
* @param array $values_array
* @return string
*/
function watermark_my_image_get_random_text($values_array)
{
	return $values_array[mt_rand(0, count($values_array) - 1)];
}


/**
* Returns a new array of strings which have a lower lenght than $max_len
*
* @param array $values_array, int $max_len
* @return array
*/
function watermark_my_image_get_new_text_array($values_array, $max_len)
{
	$values_array_aux = array();

	foreach ($values_array as $value) {
		if (strlen($value) < $max_len) {
			$values_array_aux[] = $value;
		}
	}

	return $values_array_aux;
}


/**
* Checks if a gif is animated or not
*
* @param string $filename
* @return boolean
*/
function watermark_my_image_is_animated_gif($filename)
{
	$filecontents = file_get_contents($filename);

	$str_loc = 0;
	$count = 0;
	while ($count < 2) { # There is no point in continuing after we find a 2nd frame

		$where1 = strpos($filecontents, "\x00\x21\xF9\x04", $str_loc);

		if ($where1 === false) {
			break;
		} else {

			$str_loc = $where1 + 1;
			$where2 = strpos($filecontents, "\x00\x2C", $str_loc);

			if ($where2 === false) {
				break;
			} else {
				if ($where1 + 8 == $where2) {
					$count++;
				}
				$str_loc = $where2+1;
			}

		}
	}

	if ($count > 1) {
		return true ;
	} else {
		return false;
	}
}

/**
* Creates a php image resource from the file found at $filepath
*
* @param string $filepath
* @param string $mime_type
* @return resource|boolean
*/
function watermark_my_image_image($filepath, $mime_type)
{
	switch ( $mime_type ) {
		case 'image/jpeg':
			return imagecreatefromjpeg($filepath);
		case 'image/png':
			return imagecreatefrompng($filepath);
		case 'image/gif':
			return imagecreatefromgif($filepath);
		default:
			return false;
	}
}

/**
* Calculates the top, left, width, height and baseline of the text
*
* @param int $size
* @param int $angle
* @param string $fontfile
* @param string $text
* @return array
*/
function watermark_my_image_calculate_text_box($size , $angle , $fontfile , $text )
{
	$bbox = imagettfbbox($size, $angle, $fontfile, $text);

	$tmp_bbox['left'] = abs($bbox[0]);
	$tmp_bbox['top'] = abs($bbox[5]);
	$tmp_bbox['width'] = abs($tmp_bbox['left'] - $bbox[2]) + 2;
	$tmp_bbox['height'] = abs($tmp_bbox['top'] - $bbox[1]);

	if ($bbox[1] > 0) {
		$letters = array_unique(str_split($text));

		$max = 0;

		foreach ($letters as $letter) {
			$aux_bbox = imagettfbbox($size, $angle, $fontfile, $letter);

			$max = abs($aux_bbox[5]) > $max ? abs($aux_bbox[5]) : $max;
		}

		$bbox2 = imagettfbbox($size, $angle, $fontfile, strtoupper($text));

		$tmp_bbox['baseline'] = abs($max - $bbox2[1]);

	} else {
		$tmp_bbox['baseline'] = 0;
	}

	return $tmp_bbox;
}

/**
* Generates the watermark and combines the original image with it.
* It is also used for generating the preview image of the watermark.
*
* @param array $options
* @param resource $src_img
* @return resource
*/
function watermark_my_image_generate($options, $src_img = null)
{
	global $fonts_directory;

	if (isset($src_img)) {
		$width = imagesx($src_img);
		$height = imagesy($src_img);
		$new_height = $height + ($options['watermark_my_image_place_inside'] ? 0 : $options['watermark_my_image_height']);
	} else {
		$width = 500;
		$height = 0;
		$new_height = $options['watermark_my_image_height'];
	}

	$new_image = imagecreatetruecolor($width, $new_height);

	$rgb = watermark_my_image_hex_to_rgb($options['watermark_my_image_background_color']);

	$background_color = imagecolorallocate ($new_image, $rgb[0], $rgb[1], $rgb[2]);
	imagefill($new_image, 0, 0, $background_color);


	$text1_font = $fonts_directory . $options['watermark_my_image_text1']['font'];
	$text2_font = $fonts_directory . $options['watermark_my_image_text2']['font'];


	$text1_values_array = explode("\n", $options['watermark_my_image_text1']['values']);
	$text2_values_array = explode("\n", $options['watermark_my_image_text2']['values']);

	// Try to get texts which are not wider than the width of the image
	do {

		if (isset($text1)) {
			$text1_values_array_aux = watermark_my_image_get_new_text_array($text1_values_array, strlen($text1));
			$text2_values_array_aux = watermark_my_image_get_new_text_array($text2_values_array, strlen($text2));

			if (empty($text1_values_array_aux) && empty($text2_values_array_aux)) {
				break;
			}

			if (!empty($text1_values_array_aux)) {
				$text1_values_array = $text1_values_array_aux;
			}

			if (!empty($text2_values_array_aux)) {
				$text2_values_array = $text2_values_array_aux;
			}
		}

		$text1 = watermark_my_image_get_random_text($text1_values_array);
		$text2 = watermark_my_image_get_random_text($text2_values_array);

		$bbox1 = watermark_my_image_calculate_text_box($options['watermark_my_image_text1']['size'], 0, $text1_font, $text1);
		$bbox2 = watermark_my_image_calculate_text_box($options['watermark_my_image_text2']['size'], 0, $text2_font, $text2);

	} while ($bbox1['width'] + $bbox2['width'] + $options['watermark_my_image_offset_x'] + $options['watermark_my_image_spacing'] > $width);



	switch ($options['watermark_my_image_text_align']) {

		case 'left':

			$x1 = $bbox1['left'] + $options['watermark_my_image_offset_x'];
			$x2 = $x1 + $bbox1['width'] + $options['watermark_my_image_spacing'];

			break;

		case 'right':

			$x2 = $width - $bbox2['width'] - $options['watermark_my_image_offset_x'];
			$x1 = $x2 - $bbox1['width'] - $options['watermark_my_image_spacing'];

			break;
	}


	if ($bbox1['height'] > $bbox2['height']) {

		$y1 = $height + $bbox1['top'] + $options['watermark_my_image_offset_y'];

		if ($bbox2['baseline'] == 0 && $bbox1['baseline'] != 0)  {
			$y2 = $height + $bbox2['top'] + $options['watermark_my_image_offset_y'] + $bbox1['baseline'] - $bbox2['height'];
		} else if ($bbox2['baseline'] == 0 && $bbox1['baseline'] == 0) {
			$y2 = $height + $bbox2['top'] + $options['watermark_my_image_offset_y'] + $bbox1['height'] - $bbox2['height'];
		} else if ($bbox2['baseline'] != 0 && $bbox1['baseline'] == 0) {
			$y2 = $height + $bbox2['top'] + $options['watermark_my_image_offset_y'] - $bbox2['baseline'] + $bbox1['height'];
		} else {
			$y2 = $height + $bbox2['top'] + $options['watermark_my_image_offset_y'] + $bbox1['baseline'] - $bbox2['baseline'];
		}

	} else {

		if ($bbox1['baseline'] == 0 && $bbox2['baseline'] != 0)  {
			$y1 = $height + $bbox1['top'] + $options['watermark_my_image_offset_y'] + $bbox2['baseline'] - $bbox1['height'];
		} else if ($bbox1['baseline'] == 0 && $bbox2['baseline'] == 0) {
			$y1 = $height + $bbox1['top'] + $options['watermark_my_image_offset_y'] + $bbox2['height'] - $bbox1['height'];
		} else if ($bbox1['baseline'] != 0 && $bbox2['baseline'] == 0) {
			$y1 = $height + $bbox1['top'] + $options['watermark_my_image_offset_y'] - $bbox1['baseline'] + $bbox2['height'];
		} else {
			$y1 = $height + $bbox1['top'] + $options['watermark_my_image_offset_y'] + $bbox2['baseline'] - $bbox1['baseline'];
		}

		$y2 = $height + $bbox2['top'] + $options['watermark_my_image_offset_y'];

	}

	// Do this if the watermark should be placed inside the image
	if ($new_height > $options['watermark_my_image_height']) {
		$y1 -= $options['watermark_my_image_place_inside'] ? $options['watermark_my_image_height'] : 0;
		$y2 -= $options['watermark_my_image_place_inside'] ? $options['watermark_my_image_height'] : 0;
	}


	$text1_rgb = watermark_my_image_hex_to_rgb($options['watermark_my_image_text1']['color']);
	$text2_rgb = watermark_my_image_hex_to_rgb($options['watermark_my_image_text2']['color']);


	$text1_color = ImageColorAllocate($new_image, $text1_rgb[0], $text1_rgb[1], $text1_rgb[2]);
	$text2_color = ImageColorAllocate($new_image, $text2_rgb[0], $text2_rgb[1], $text2_rgb[2]);


	imagettftext($new_image, $options['watermark_my_image_text1']['size'], 0, $x1, $y1, $text1_color, $text1_font, watermark_my_image_unicode($text1));
	imagettftext($new_image, $options['watermark_my_image_text2']['size'], 0, $x2, $y2, $text2_color, $text2_font, watermark_my_image_unicode($text2));


	// Copy the original image over the watermarked image
	if (isset($src_img)) {
		imagecopy ( $new_image, $src_img, 0, 0, 0, 0, $width, $height - ($options['watermark_my_image_place_inside'] ? $options['watermark_my_image_height'] : 0));
	}

	return $new_image;
}


if(!(array_key_exists('post_id', $_REQUEST) && $_REQUEST['post_id'] == -1)) {
	// add filter for watermarking images
	add_filter('wp_generate_attachment_metadata', 'watermark_my_image_apply');
}

/**
* Apply watermark to selected image sizes
*
* @param array $data
* @return array
*/
function watermark_my_image_apply($data)
{

	// Don't do anything if gd is not available or if FreeType isn't supported
	if ( !extension_loaded( 'gd' ) ) {
		return $data;
	} else {
		$gd_info = gd_info();

		if ( !$gd_info['FreeType Support'] ) {
			return $data;
		}
	}


	// get settings for watermarking
	$upload_dir   = wp_upload_dir();
	$watermark_my_image_enable_for = get_option('watermark_my_image_enable_for');


	$fullsize_filepath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $data['file'];


	// just return the attachment metadata if the file is an animated gif
	if (watermark_my_image_is_animated_gif($fullsize_filepath))
		return $data;


	$mime_type = wp_check_filetype($fullsize_filepath);
	$mime_type = $mime_type['type'];

	$src_image = watermark_my_image_image($fullsize_filepath, $mime_type);

	// just return the attachment metadata if the file is not an image
	if (!$src_image)
		return $data;


	global $watermark_my_image_configuration_default;

	$options = watermark_my_image_get_options($watermark_my_image_configuration_default);


	// Process the full size image
	if ($watermark_my_image_enable_for['fullsize']) {
		$data = watermark_my_image_apply_do('fullsize', $data, $options);
	}

	foreach ($data['sizes'] as $size => $values) {
		// Skip any thumbnail
		if (stripos($size, 'thumb') !== false)
			continue;

		// Process the medium and large images
		if ( in_array($size, array('medium', 'large')) ) {
			if ($watermark_my_image_enable_for[$size]) {
				$data = watermark_my_image_apply_do($size, $data, $options);
			}
		// Process the custom sized images
		} else if ($watermark_my_image_enable_for['custom_sizes']) {
			$data = watermark_my_image_apply_do($size, $data, $options);
		}
	}

	// pass forward attachment metadata
	return $data;
}

/**
* Apply watermark to an image
*
* @param string $size
* @param array $data
* @param array $options
* @return array
*/
function watermark_my_image_apply_do($size, $data, $options)
{
	$upload_dir   = wp_upload_dir();

	if ($size == 'fullsize') {
		$filepath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $data['file'];
	} else {
		if (!empty($data['sizes']) && array_key_exists($size, $data['sizes'])) {
			$filepath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . dirname($data['file']) . DIRECTORY_SEPARATOR . $data['sizes'][$size]['file'];
		} else {
			// early getaway
			continue;
		}
	}


	$mime_type = wp_check_filetype($filepath);
	$mime_type = $mime_type['type'];

	$src_image = watermark_my_image_image($filepath, $mime_type);

	// just return the attachment metadata if the file is not an image
	if (!$src_image)
		return $data;


	$src_h = imagesy($src_image);
	$src_w = imagesx($src_image);

	// Resize and resample the image if the height of the image exceeds the maximum allowed height for that size
	// medium & large images
	if ( in_array($size, array('medium', 'large')) && get_option($size . '_size_h') != 0 && $src_h + $options['watermark_my_image_height'] > get_option($size . '_size_h') ) {

		$dst_h = get_option($size . '_size_h') - $options['watermark_my_image_height'];
		$dst_w = $dst_h * $src_w / $src_h;

		$aux_img = imagecreatetruecolor($dst_w, $dst_h);

		imagecopyresampled ( $aux_img, $src_image, 0, 0, 0, 0, $dst_w , $dst_h , $src_w, $src_h );

		$src_image = $aux_img;

	} else if ($size != 'fullsize') {

		global $_wp_additional_image_sizes;

		// custom sized images
		if (isset($_wp_additional_image_sizes[$size]) && !$_wp_additional_image_sizes[$size]['crop'] && $_wp_additional_image_sizes[$size]['height'] != 0 && $src_h + $options['watermark_my_image_height'] > $_wp_additional_image_sizes[$size]['height']) {
			$dst_h = $_wp_additional_image_sizes[$size]['height'] - $options['watermark_my_image_height'];
			$dst_w = $dst_h * $src_w / $src_h;

			$aux_img = imagecreatetruecolor($dst_w, $dst_h);

			imagecopyresampled ( $aux_img, $src_image, 0, 0, 0, 0, $dst_w , $dst_h , $src_w, $src_h );

			$src_image = $aux_img;
		}
	}


	// apply the watermark
	$new_image = watermark_my_image_generate($options, $src_image);

	// save the image file
	watermark_my_image_save_image_file($new_image, $mime_type, $filepath, $options['watermark_my_image_jpeg_quality']);

	// get the new dimensions
	$new_image_w = imagesx($new_image);
	$new_image_h = imagesy($new_image);

	// set the new dimensions
	if ($size == 'fullsize') {
		$data['width'] = $new_image_w;
		$data['height'] = $new_image_h;
	} else {
		$data['sizes'][$size]['width'] = $new_image_w;
		$data['sizes'][$size]['height'] = $new_image_h;
	}

	// rename the file to reflect the new dimensions
	if ( $size != 'fullsize' ) {
		$d1 = explode('-', $data['sizes'][$size]['file']);
		$d2 = explode('.', $d1[count($d1) - 1]);
		$d2[0] = $new_image_w . 'x' . $new_image_h;
		$d1[count($d1) - 1] = implode('.', $d2);
		$new_file = implode('-', $d1);

		if ($file != $new_file) {
			try {
				rename($filepath, $upload_dir['basedir'] . DIRECTORY_SEPARATOR . dirname($data['file']) . DIRECTORY_SEPARATOR . $new_file);
				$data['sizes'][$size]['file'] = $new_file;
			} catch (Exception $e) {}
		}
	}

	return $data;
}


/**
* Save image from image resource
*
* @param resource $image
* @param string $mime_type
* @param string $filepath
* @param int $jpeg_quality
* @return boolean
*/
function watermark_my_image_save_image_file($image, $mime_type, $filepath, $jpeg_quality)
{
	switch ( $mime_type ) {
		case 'image/jpeg':
			return imagejpeg($image, $filepath, apply_filters( 'jpeg_quality', $jpeg_quality ));
		case 'image/png':
			return imagepng($image, $filepath);
		case 'image/gif':
			return imagegif($image, $filepath);
		default:
			return false;
	}
}


/**
* Process post_content and replaces the old image file name to the new image file name and the dimensions (if any of them have been changed)
*
* @param string $post_content
* @param string $file_old
* @param string $file_new
* @param int $width
* @param int $height
* @return string
*/
function watermark_my_image_process_post_content($post_content, $file_old, $file_new, $width, $height)
{
	if ($file_old != $file_new) {
		$post_content = str_replace($file_old, $file_new);
	}

	$doc = new DOMDocument();

	try {
		$doc->loadHTML($post_content);
	} catch (Exception $e) {
		echo $e , ' <br /> ', $post_content;
	}

	// Get all the img tags
	$images = $doc->getElementsByTagName('img');

	// go through all the images
	for ($i = 0; $i < $images->length; $i++) {
		$image = $images->item($i);

		// check if the image needs to be modified
		if ( stripos($image->getAttribute('src'), $file_new) !== false ) {
			$image->setAttribute('width', $width);
			$image->setAttribute('height', $height);
		}
	}

	// Get the new HTML string
	$post_content_new = $doc->saveHTML();

	// Remove the <html> tag if it was added by DOM
	if (stripos($post_content, '<html>') === false) {
		$post_content_new = str_replace(array('<html>', '</html>'), '', $post_content_new);
	}

	// Remove the <body> tag if it was added by DOM
	if (stripos($post_content, '<body>') === false) {
		$post_content_new = str_replace(array('<body>', '</body>'), '', $post_content_new);
	}

	// Remove the <DOCTYPE> tag if it was added by DOM
	if ( preg_match('/^<!DOCTYPE.+?>/', $post_content) == 0 ) {
		$post_content_new = preg_replace('/^<!DOCTYPE.+?>/', '', $post_content_new);
	}

	// Trim the content
	$post_content_new = trim($post_content_new);

	// Remove the <p> tag if it was added by DOM
	if ( substr($post_content, 0, 3) != '<p>' && substr($post_content_new, 0, 3) == '<p>') {
		$post_content_new = substr($post_content_new, 3, strlen($post_content_new) - 7);
	}

	return $post_content_new;
}

/**
* Handle unicode text.
*
* Thanks to: http://stackoverflow.com/questions/198007/php-function-imagettftext-and-unicode#answer-1956361
*
* @param string $item_text
* @return string
*/
function watermark_my_image_unicode($item_text)
{
	if ( function_exists('mb_detect_encoding') && function_exists('mb_convert_encoding') && function_exists('mb_encode_numericentity') )
	{
		// Detect if the string was passed in as unicode.
		$text_encoding = mb_detect_encoding($item_text, 'UTF-8, ISO-8859-1');

		// Make sure it's in unicode.
		if ($text_encoding != 'UTF-8')
			$item_text = mb_convert_encoding($item_text, 'UTF-8', $text_encoding);

		// HTML numerically-escape everything (&#[dec];).
		$item_text = mb_encode_numericentity($item_text, array (0x0, 0xffff, 0, 0xffff), 'UTF-8');
	}

	return $item_text;
}

/**
* The function used by both Wordpress' cron system and the cron.php file
* for sending invites and reminders.
*/
function watermark_my_image_cron()
{

	// Don't do anything if the DOM extension is not available
	if ( !extension_loaded( 'DOM' ) )
		return;


	global $wpdb;


	if (get_option('watermark_my_image_watermarking_status') == 'started') {

		$id_gt = get_option('watermark_my_image_id_gt');
		$id_lt = get_option('watermark_my_image_id_lt');

		if (ctype_digit($id_gt) && ctype_digit($id_lt) && $id_gt <= $id_lt) {
			$processed_images = get_option('watermark_my_image_processed_images');
			$images_per_batch = get_option('watermark_my_image_images_per_batch');

			$query = "SELECT `$wpdb->posts`.`ID`, `$wpdb->postmeta`.`meta_value` FROM `$wpdb->postmeta`
								JOIN `$wpdb->posts` ON `$wpdb->postmeta`.`post_id` = `$wpdb->posts`.`ID`
								WHERE `$wpdb->posts`.`post_type` = 'attachment'
								AND `$wpdb->posts`.`post_mime_type` IN ('image/jpeg', 'image/png', 'image/gif')
								AND `$wpdb->posts`.`ID` >= %d AND `$wpdb->posts`.`ID` <= %d
								AND `$wpdb->postmeta`.`meta_key` = '_wp_attachment_metadata'
								LIMIT %d, %d";

			$results = $wpdb->get_results( $wpdb->prepare($query, $id_gt, $id_lt, $processed_images, $images_per_batch) );

			foreach ($results as $result) {

				$data_old = unserialize($result->meta_value);
				$data_new = watermark_my_image_apply($data_old);

				// check if any modifications were made and continue the process if there were
				if ($data_old != $data_new) {
					// update the metadata
					wp_update_attachment_metadata($result->ID, $data_new);

					// get the upload subdir
					$d1 = explode('/', $data_new['file']);
					array_pop($d1);
					$upload_dir = implode('/', $d1) . '/';

					// init a few variables
					$where_query = array();
					$where_args = array();

					// check if the full size file was modified and build the query accordingly
					if ($data_old['width'] != $data_new['width'] || $data_old['height'] != $data_new['height']) {
						$where_query[] = "`post_content` LIKE '%%%s%%'";
						$where_args[] = $data_old['file'];
					}

					// check if the medium and/or the large files were modified and build the query accordingly
					foreach ($data_old['sizes'] as $size) {
						if ($data_old['sizes'][$size]['file'] != $data_new['sizes'][$size]['file']) {
							$where_query[] = "`post_content` LIKE '%%%s%%'";
							$where_args[] = $upload_dir . $data_old['sizes'][$size]['file'];
						}
					}

					$query = "SELECT `ID`, `post_content` FROM `$wpdb->posts` WHERE " . implode(' OR ', $where_query);

					$content_results = $wpdb->get_results( $wpdb->prepare($query, $where_args));

					foreach ($content_results as $content) {

						// check if the full size file was modified and process the post content accordingly
						if ($data_old['width'] != $data_new['width'] || $data_old['height'] != $data_new['height']) {
							$content->post_content = watermark_my_image_process_post_content($content->post_content, $data_new['file'], $data_new['file'], $data_new['width'], $data_new['height']);
						}

						// check if the medium and/or the large files were modified and process the post content accordingly
						foreach ($data_old['sizes'] as $size) {
							if ($data_old['sizes'][$size]['file'] != $data_new['sizes'][$size]['file']) {
								$content->post_content = watermark_my_image_process_post_content($content->post_content, $upload_dir . $data_old['sizes'][$size]['file'], $upload_dir . $data_new['sizes'][$size]['file'],  $data_new['sizes'][$size]['width'],  $data_new['sizes'][$size]['height']);
							}
						}

						// update the post
						$wpdb->update($wpdb->posts, array('post_content' => $content->post_content), array('ID' => $content->ID));

					}
				}

				$processed_images++;
			}

			if (count($results) < $images_per_batch || $processed_images >= get_option('watermark_my_image_count')) {
				// no more images to process
				delete_option('watermark_my_image_processed_images');
				delete_option('watermark_my_image_watermarking_status');
				delete_option('watermark_my_image_count');
				add_option('watermark_my_image_process_finished', 1);
				wp_clear_scheduled_hook('watermark_my_image_cron_hook');

				return;
			} else {
				// update the number of processed images
				update_option('watermark_my_image_processed_images', $processed_images);

				return;
			}
		}
	}
}

add_action( 'watermark_my_image_cron_hook', 'watermark_my_image_cron' );


/*
* Enqueue the required js and css for the Watermark My Image admin page
*/
function watermark_my_image_admin_scripts($hook)
{
	if( !in_array($hook, array('watermark-my-image/main.php', 'watermark-my-image/apply.php')) )
		return;

	// color picker js
	wp_register_script( 'watermark-my-image-colorpicker-js', plugins_url('/colorpicker/js/colorpicker.js', __FILE__), array('jquery'), null);
	wp_enqueue_script( 'watermark-my-image-colorpicker-js'  );

	// Watermark My Image admin js
	wp_register_script( 'watermark-my-image-admin-js', plugins_url('script.js', __FILE__), array('jquery'), null);
	wp_enqueue_script( 'watermark-my-image-admin-js'  );

	function watermark_my_image_admin_ajax_url()
	{
		echo '<script type="text/javascript">var watermark_my_image_admin_ajax_url = "' . get_bloginfo('url') . '/wp-admin/admin-ajax.php"</script>' , "\n";
	}

	add_action('wp_print_scripts', 'watermark_my_image_admin_ajax_url');

	// colorpicker css
	wp_register_style( 'watermark-my-image-colorpicker-css', plugins_url('/colorpicker/css/colorpicker.css', __FILE__), false, null );
	wp_enqueue_style( 'watermark-my-image-colorpicker-css' );

	// Watermark My Image admin css
	wp_register_style( 'watermark-my-image-admin-css', plugins_url('style.css', __FILE__), false, null );
	wp_enqueue_style( 'watermark-my-image-admin-css' );
}

add_action( 'admin_enqueue_scripts', 'watermark_my_image_admin_scripts' );


/**
* Watermarking progress procent
*/
function watermark_my_image_progress_bar_ajax()
{
	$processed_images = get_option('watermark_my_image_processed_images');
	$count = get_option('watermark_my_image_count');

	$procent = $count > 0 ? ($processed_images * 100) / $count : 0;

	if (get_option('watermark_my_image_process_finished')) {
		echo 'watermark_my_image_process_finished';
	} else {
		echo number_format($procent, 2) , '%';
	}

	die();
}

add_action( 'wp_ajax_watermark_my_image_progress_bar', 'watermark_my_image_progress_bar_ajax' );
