jQuery(document).ready(function($) {
	// Trigger the ML modal
	$('.field-medialibraryfield').on('click', function (e) {
		var container = $(this),
			href = Symphony.Context.get('symphony') + '/extension/media_library/library/';

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
		if ($(e.target).hasClass('remove') && container.data('allow-multiple') === 'yes') {
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

		// View the file
		if ($(e.target).hasClass('view')) {
			if ($(e.target).attr('href').includes(Symphony.Context.get('root'))) {
				window.open($(e.target).attr('href'))
			}
			else {
				window.open(Symphony.Context.get('root') + $(e.target).attr('href'))
			}

			return false;
		}

		// Open the media library
		if ($(e.target).is('label') || $(e.target).closest('label').length) {
			ml_source_input = this;
			localStorage.setItem('add-to-field', 'yes');

			if ($(ml_source_input).data('allow-multiple') === 'yes') localStorage.setItem('allow-multiple', 'yes');

			if (container.data('destination')) ml_folder_path = container.data('destination');
			if (ml_folder_path) href = href + '?folder=' + ml_folder_path;

			Symphony.Extensions.MediaLibrary.openLibrary(href);
		}

		return false;
	});

	// Add a file preview
	$('.field-medialibraryfield[data-allow-multiple!="yes"]').each(function () {
		var self = $(this),
			file_path = self.find('input:first-of-type').val(),
			file_type = file_path.split('.')[1],
			file_name = self.find('input[name*="[name]"]').val(),
			file_mime = self.find('input[name*="[mime]"]').val(),
			image_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'],
			video_types = ['mp4', 'webm'],
			audio_types = ['mp3'];

		if (file_path !== '') {
			self.find('.clear').before(`<div class="preview"><div class="item"><p><strong>${file_name}</strong>${file_mime}</p><a class="view" href="${file_path}">View</a></div></div>`);

			if (image_types.includes(file_type)) {
				self.find('.item').addClass('image').prepend('<img src="' + Symphony.Context.get('root') + file_path + '" />');
			}
			else if (video_types.includes(file_type)) {
				self.find('.item').addClass('video').prepend('<video src="' + Symphony.Context.get('root') + file_path + '" autoplay loop muted />');
			}
			else if (audio_types.includes(file_type)) {
				self.find('.item').addClass('audio').prepend('<audio src="' + Symphony.Context.get('root') + file_path + '" controls  />');
			}
			else if (file_type !== undefined ) {
				self.find('.item').addClass('other');
			}
		}
	});

	// Add a multi file preview
	$('.field-medialibraryfield[data-allow-multiple="yes"]').each(function () {
		var container = $(this),
			values = container.find('input[name*="[value]"]'),
			preview = null;

		if ($(values.get(0)).val() !== '') {
			preview = container.find('.clear').before('<div class="preview" />').prev('.preview');

			values.each(function () {
				var self = $(this),
					file_path = self.val(),
					file_type = file_path.split('.')[1],
					file_name = container.find('input[name*="[' + self.index() + '][name]"]').val(),
					file_mime = container.find('input[name*="[' + self.index() + '][mime]"]').val(),
					image_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'],
					video_types = ['mp4', 'webm'],
					audio_types = ['mp3'],
					item = preview.append(`<div class="item"><p><strong>${file_name}</strong>${file_mime}</p><a class="view" href="${file_path}">View</a><a class="remove">Remove</a></div>`).find('.item:last-child');
				
				if (image_types.includes(file_type)) {
					item.addClass('image').prepend(`<img src="${Symphony.Context.get('root') + file_path}" />`);
				}
				else if (video_types.includes(file_type)) {
					item.addClass('video').prepend(`<video src="${Symphony.Context.get('root') + file_path}" autoplay loop muted />`);
				}
				else if (audio_types.includes(file_type)) {
					item.addClass('audio').prepend('<audio src="' + Symphony.Context.get('root') + file_path + '" controls  />');
				}
				else if (file_type !== undefined ) {
					item.addClass('other');
				}
			});
		}
	});

	/* Add draggable functionality to multiple items so we can reorder them*/
	var ml_multiple_elements = document.querySelectorAll('.field-medialibraryfield[data-allow-multiple="yes"]');

	ml_multiple_elements.forEach((el) => {
		if (el.querySelector('.preview') === null) return false;

		var container = el.querySelector('.preview'),
			item_length = container.querySelectorAll('.item').length - 1;

		var sortable = Sortable.create(container, {
			onEnd: function (e) {
				// Store the inputs that match the dragged item
				var dragged_input = $(container).parent().find('input[name*="[' + e.oldIndex + ']"]');

				// Mark the names for later
				dragged_input.each(function () {
					var name = $(this).attr('name'),
						new_name = name.replace('[' + e.oldIndex + ']', '[replace]');
					$(this).attr('name', new_name);
				});

				// Dragging up the list
				if (e.newIndex < e.oldIndex) {
					// Update each item
					for (var i = item_length; i >= e.newIndex; i--) {
						if (i >= e.oldIndex) continue;

						var input = $(container).parent().find('input[name*="[' + i + ']"]');

						input.each(function () {
							var name = $(this).attr('name'),
								new_name = name.replace('[' + i + ']', '[' + (i+1) + ']');

							$(this).attr('name', new_name);
						});
					}
				}
				// Dragging down the list
				else {
					// Update each item
					for (var i = e.oldIndex+1; i <= e.newIndex; i++) {
						var input = $(container).parent().find('input[name*="[' + i + ']"]');

						input.each(function () {
							var name = $(this).attr('name'),
								new_name = name.replace('[' + i + ']', '[' + (i-1) + ']');

							$(this).attr('name', new_name);
						});
					}
				}

				// Update the moved items name and position
				dragged_input.each(function () {
					var name = $(this).attr('name'),
						new_name = name.replace('[replace]', '[' + e.newIndex + ']');

					$(this).attr('name', new_name);

					// We only need to update the position of the value inputs to update the order
					if (name.indexOf('[value]') > -1) {
						// Dragging up the list means insert before
						if (e.newIndex < e.oldIndex) {
							$(this).insertBefore($(container).parent().find('input:nth-child('+(e.newIndex+1)+')'));
						}
						// Dragging down the list means insert after
						else {
							$(this).insertAfter($(container).parent().find('input:nth-child('+(e.newIndex+1)+')'));
						}
					}
				});
			}
		});
	});
});
