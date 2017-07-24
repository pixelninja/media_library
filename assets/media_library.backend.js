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
	})(jQuery);
});
