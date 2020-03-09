jQuery(document).ready(function() {
	// Trigger the ML modal
	$('.field-medialibraryfield').on('click', function (e) {
		var container = $(this);

		// Clear the field
		if ($(e.target).is('.clear')) {
			var self = $(this),
				preview = self.find('.preview'),
				fields = self.find('.instance');

			// Remove the preview
			preview.remove();

			// Empty the inputs
			fields.find('input').remove();

			return false;
		}

		// Remove the file
		if ($(e.target).closest('.item').length && container.data('allow-multiple') === 'yes') {
			var index = $(e.target).closest('.item').index();

			// Remove each relevant input
			$(this).find('input[name*="[' + index + ']"]').remove();

			// Update the key on items after this one
			$(e.target).closest('.item').nextAll().each(function () {
				var i = $(this).index();

				container.find('input[name*="[' + i + ']"]').each(function () {
					var name = $(this).attr('name').split('['+i+']');
					$(this).attr('name', name[0] + '[' + (i - 1) + ']' + name[1]);
				})
			});

			// Remove the placeholder image
			$(e.target).closest('.item').remove();

			return false;
		}

		// Open the media library
		ml_source_input = this;
		localStorage.setItem('add-to-field', 'yes');
		$('.ml-link').trigger('click');

		return false;
	});

	// Add a file preview
	$('.field-medialibraryfield[data-allow-multiple!="yes"]').each(function () {
		var self = $(this),
			file_path = self.find('input:first-of-type').val(),
			file_type = file_path.split('.')[1],
			image_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'],
			video_types = ['mp4', 'webm'],
			audio_types = ['mp3'];

		if (image_types.includes(file_type)) {
			self.find('.clear').before('<div class="preview"><div class="item image"><img src="' + Symphony.Context.get('root') + file_path + '" /></div></div>');
		}
		else if (video_types.includes(file_type)) {
			self.find('.clear').before('<div class="preview"><div class="item video"><video src="' + Symphony.Context.get('root') + file_path + '" controls /></div></div>');
		}
		else if (audio_types.includes(file_type)) {
			self.find('.clear').before('<div class="preview"><div class="item audio"><audio src="' + Symphony.Context.get('root') + file_path + '" controls /></div></div>');
		}
		else if (file_type !== undefined ) {
			self.find('.clear').before('<div class="preview"><div class="item other"><p>' + file_path + '</p></div></div>');
		}
	});

	// Add a multi file preview
	$('.field-medialibraryfield[data-allow-multiple="yes"]').each(function () {
		var self = $(this),
			values = self.find('input[name*="[value]"]'),
			preview = ($(values.get(0)).val() !== '') ? self.find('.clear').before('<div class="preview" />').prev('.preview') : null;

		values.each(function () {
			var self = $(this),
				file_path = self.val(),
				file_type = file_path.split('.')[1],
				image_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'],
				video_types = ['mp4', 'webm'],
				audio_types = ['mp3'];

			if (image_types.includes(file_type)) {
				preview.append('<div class="item image"><img src="' + Symphony.Context.get('root') + file_path + '" /></div>')
			}
			else if (video_types.includes(file_type)) {
				preview.append('<div class="item video"><video src="' + Symphony.Context.get('root') + file_path + '" controls /></div>')
			}
			else if (audio_types.includes(file_type)) {
				preview.append('<div class="item audio"><audio src="' + Symphony.Context.get('root') + file_path + '" controls /></div>')
			}
			else if (file_type !== undefined ) {
				var string = file_path.split('/');
				preview.append('<div class="item other"><p>' + string[string.length-1] + '</p></div>');
			}
		});
	});
});
