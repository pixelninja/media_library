jQuery(document).ready(function() {
	// Trigger the ML modal
	$('.field-medialibraryfield').on('click', function (e) {
		// Remove the file
		if ($(e.target).is('.remove')) {
			// Remove the preview
			$(this).find('.preview').remove();
			// Empty the inputs
			$(this).find('input').val('');
			return false;
		}

		ml_source_input = this;
		localStorage.setItem('add-to-field', 'yes');
		$('#nav .ml-link').trigger('click');
	});

	// Add a file preview
	$('.field-medialibraryfield').each(function () {
		var self = $(this),
			file_path = self.find('input:first-of-type').val(),
			file_type = file_path.split('.')[1],
			image_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'],
			video_types = ['mp4', 'webm'],
			audio_types = ['mp3'];

		if (image_types.includes(file_type)) {
			self.find('.remove').before('<div class="preview"><img src="' + Symphony.Context.get('root') + file_path + '" /></div>')
		}
		else if (video_types.includes(file_type)) {
			self.find('.remove').before('<div class="preview"><video src="' + Symphony.Context.get('root') + file_path + '" controls /></div>')
		}
		else if (audio_types.includes(file_type)) {
			self.find('.remove').before('<div class="preview"><audio src="' + Symphony.Context.get('root') + file_path + '" controls /></div>')
		}
	});
});