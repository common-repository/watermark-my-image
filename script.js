function watermark_my_image_trim(str)
{
	return str.replace(/^\s+|\s+$/g, '');
}

function watermark_my_image_progress()
{
	jQuery.post(
		watermark_my_image_admin_ajax_url,
		{action: 'watermark_my_image_progress_bar'},
		function(msg) {
			if (watermark_my_image_trim(msg) == 'watermark_my_image_process_finished') {
				// Refresh the page if the process is finished
				window.location.href = window.location.href;
			} else {
				// Update the progress
				jQuery('#watermark-my-image-progress-wrapper').attr('title', msg);
				jQuery('#watermark-my-image-progress').css('width', msg);
			}
		}
	);

	t = setTimeout('watermark_my_image_progress()', 5000);
}


jQuery(document).ready(function(){


  jQuery('#background-color-selector').ColorPicker({
		color: jQuery('input[name="watermark_my_image_background_color"]').val(),
		onChange: function(hsb, hex, rgb, el) {
			jQuery('#background-color-selector div').css('background-color', '#' + hex);
			jQuery('input[name="watermark_my_image_background_color"]').val(hex);
		}
	});


  jQuery('#text1-color-selector').ColorPicker({
		color: jQuery('input[name="watermark_my_image_text1_color"]').val(),
		onChange: function(hsb, hex, rgb, el) {
			jQuery('#text1-color-selector div').css('background-color', '#' + hex);
			jQuery('input[name="watermark_my_image_text1_color"]').val(hex);
		}
	});


  jQuery('#text2-color-selector').ColorPicker({
		color: jQuery('input[name="watermark_my_image_text2_color"]').val(),
		onChange: function(hsb, hex, rgb, el) {
			jQuery('#text2-color-selector div').css('background-color', '#' + hex);
			jQuery('input[name="watermark_my_image_text2_color"]').val(hex);
		}
	});


	jQuery('input[name=watermark_my_image_use_wp_cron]').click(function(){

		if (jQuery('.yes').hasClass('show')) {
			jQuery('.yes').fadeOut('slow', function(){jQuery(this).removeClass('show').addClass('hidden');});
		} else {
			jQuery('.yes').fadeIn('slow', function(){jQuery(this).removeClass('hidden').addClass('show');});
		}

		if (jQuery('.no').hasClass('show')) {
			jQuery('.no').fadeOut('slow', function(){jQuery(this).removeClass('show').addClass('hidden');});
		} else {
			jQuery('.no').fadeIn('slow', function(){jQuery(this).removeClass('hidden').addClass('show');});
		}

	});


	if (jQuery('#watermark-my-image-progress-wrapper').length > 0) {
		watermark_my_image_progress();
	}

});
