$jx = jQuery.noConflict();


$jx(document).ready(function(){
	$jx('form[name="entry1"]').live('submit', function(){
		$form = $jx(this).serialize();
		$jx.ajax({
			url: $ajax_url, //AJAX file path â�� admin_url("admin-ajax.php")
			type: "POST",
			data: 'action=save_form&' + $form,
			dataType: "json",
			success: function($data){
				console.log($data);
			// if ($data.success) {
			// 	$jx('.msg').html($data.message);
			// 	// document.success_form['_id ?>']($data);
			// 	// if ($data.redirect_to) {
			// 	// 	window.location = $data.redirect_to;
			// 	// 	//$jx('.sending-form').removeAttr('disabled').removeClass('sending-form');
			// 	// }
			// 	// else {
			// 	// 	$jx('.sending-form').removeAttr('disabled').removeClass('sending-form');
			// 	// 	$jx('.msg').html('Changes saved.');
			// 	// }
			// }
			// else {
			// 	// $jx('.sending-form').removeAttr('disabled').removeClass('sending-form');

			// 	// for ($idx in $data.errors) {
			// 	// 	$jx('.field-' + $idx).find('.error').addClass('active');
			// 	// 	$jx('.field-' + $idx).find('.error-msg').html($data.errors[$idx]);
			// 	// }
			// 	// $jx('.msg').html('An error occured. Please try again.');
			// }
		},
		error: function($data, $textStatus, $errorThrown){
			$jx('.sending-form').removeAttr('disabled').removeClass('sending-form');
			$jx('.msg').html('An error occured. Please try again.');
			}
		});

		return false;
	});
});