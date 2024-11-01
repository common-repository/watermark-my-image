=== Plugin Name ===
Contributors: G0dLik3
Tags: watermark, 9GAG, images, post, upload
Requires at least: 2.9
Tested up to: 3.4.1
Stable tag: 0.21

This plugin enables you to watermark your images, by placing a simple, yet very customizable watermark beneath the original images (much like the 9GAG watermark).

== Description ==

This plugin enables you to watermark your images, by placing a simple, yet very customizable watermark beneath the original images (much like the 9GAG watermark).

You can customize the height, the background color of the watermark, you can also choose which image sizes to apply the watermark to and the JPEG quality of the image (if the image is in JPEG format).

Watermark My Image enables you to independently customize two texts, by modifying their font family, font size and color. You can specify a number of values for each text from which a random row will be selected for each image.

You can also customize the text alignment, the spacing between the two texts and the x (if the text is aligned to the left, it's calculated from the left of the image to the left of text 1; if the text is aligned to the right, it's calculated from the right of the image to the right of text 2) and y (calculated from the top of the watermark to the heighest point in both texts) offset.


**Requirements:**

* GD extension for PHP
* FreeType Library
* DOM (if you want to generate watermarks for past images)


P.S.: You can upload your own fonts into the `/wp-content/plugins/watermark-my-image/fonts/` directory.

**NOTE:** If the folder `/wp-content/plugins/watermark-my-image/` contains `wp-watermark.php`, please rename it to `watermark-my-image.php`. I'm sorry for the trouble.

**NOTE:** When uploading images, the plugin will not watermark cropped custom sized images or custom sizes which contain the string "thumb".

**IMPORTANT:** This plugin is meant to place a watermark underneath the original image. It is simply a clone of the system 9GAG uses. It is not meant to place a watemark over the original image. If you need something else, consider downloading another plugin. Please rate it for what it's supposed to do. Thank you.

**VERY IMPORTANT:**  Please backup all the files you are going to watermark and the posts & postmeta mysql tables before you start the watermarking process !

== Installation ==

1. Upload the `watermark-my-image/` folder to the `/wp-content/plugins/` directory.
2. Activate Watermark My Image through the 'Plugins' menu in WordPress.
3. Go to the Watermark My Image settings page and customize the settings.

== FAQ ==

= Where can I get the attachment ids from ? = 

You can see the attachment ids simply by going to yoursite/wp-admin/upload.php and hovering over the images. It's the number right after the attachment_id parameter.

= I do not like the watermarks anymore ! How do I remove them ? =

In this case, I'm sorry to say, but you will have to do this by yourself. You should have read that **VERY IMPORTANT** note :(

= I cannot hide the watermark! Can you help me with it? =

I would happily help you, but as my time is limited and precious, I cannot do that for free anymore.

== Screenshots ==

1. Watermark My Image configuration
2. Watermark My Image text 1 configuration
3. Example of watermarked picture
4. Apply watermark to images that were uploaded before the plugin was installed

== Changelog ==

= 0.21 =

* New feature: the plugin now processes custom sized images
* New feature: the watermark can now be placed inside the original image

= 0.2 =

* New feature: watermarking images that were uploaded before the plugin was installed
* Added a PayPal donate link on the **Plugins** page
* Fixed a few bugs

= 0.11 =

* Animated gifs will now be ignored
