<?php
ob_start();
require_once('../../../wp-load.php');
ob_end_clean();

header('Content-Type: image/jpeg');

$options = watermark_my_image_get_options($watermark_my_image_configuration_default);

$image = watermark_my_image_generate($options);

imagejpeg($image, null, $options['watermark_my_image_jpeg_quality']);
imagedestroy($image);
