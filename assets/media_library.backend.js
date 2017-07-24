jQuery(window).load(function () {
	/*
	 *	Having a drop down is a bit of overkill, so let's make it a single clickable item
	 */
	var ml_menu_item = jQuery('#nav a[href$="/extension/media_library/library/"]'),
		ml_menu_group = ml_menu_item.parents('li:last');

	ml_menu_group.find('ul').remove();

	ml_menu_group
		.css('cursor', 'pointer')
		.remove()
		.appendTo('#nav ul.content')
		.bind('click', function() {
			window.location.href = ml_menu_item.attr('href');
		});

	(function ($) {
		// Make sure we only execute in the Library
		if (driver !== 'library') return false;

		/*
		 *	Go forward or backwards a directory
		 */
		var base_url = Symphony.Context.get('root') + '/symphony/extension/media_library/library/?folder=';

		// Go back a directory level
		$('.directory-back').on('click', function () {
			var self = $(this),
				// The handle to append, which is the full folder path minus the last folder
				handle = folder_path.replace(folder_path.substring(folder_path.lastIndexOf('/')), '');

			window.location.href = base_url + handle;
		});

		$('.directory-preview').on('click', function () {
			var self = $(this),
				// The handle of the folder
				handle = self.data('handle');

			// If there is an existing folder path, add it to the new handle
			if (folder_path) handle = folder_path + '/' + handle;

			window.location.href = base_url + handle;
		});

		/*
		 *	Open the image in a lightbox
		 */
		 $('.image-preview img').on('click', function () {
 			var self = $(this),
				// Store the image data
 				src = self.attr('src'),
				width = self.data('width'),
				height = self.data('height'),
				// Add the lightbox wrapper
				lightbox = $('body').append('<div id="lightbox" />').find('#lightbox').hide().fadeIn(200),
				// Append the image to the lightbox
				image = lightbox.append('<img src="' + src + '" />').find('img'),
				close = lightbox.append('<span class="close" />').find('.close');

			// Original image width is greater than 90% of window, and less than 90% of window height
			if (width > $(window).width() * 0.8 && height <= $(window).height() * 0.8) {
				image.css({
					width : $(window).width() * 0.8,
					height : 'auto'
				});
			}
			// Otherwise if the image is greater than 90% window height
			else if (height > $(window).height() * 0.8) {
				image.css({
					width : 'auto',
					height : $(window).height() * 0.8
				});
			}
			else {
				image.css({
					width : width,
					height : height
				});
			}

			close.css({
				top : image.offset().top - 40,
				left : image.offset().left + image.outerWidth()
			})

			setTimeout(function () {
                image.addClass('slide-down');
            }, 150);

 		});
	})(jQuery);
});
