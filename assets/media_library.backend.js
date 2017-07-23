jQuery(window).load(function () {
	var ml_menu_item = jQuery('#nav a[href$="/extension/media_library/library/"]'),
		ml_menu_group = ml_menu_item.parents('li:last');

	// kill the subnav
	ml_menu_group.find('ul').remove();

	ml_menu_group
		.css('cursor', 'pointer')
		.remove()
		.appendTo('#nav ul.content')
		.bind('click', function() {
			window.location.href = ml_menu_item.attr('href');
		});

	(function ($) {
		if (driver !== 'library') return false;

		$('.directory-back').on('click', function () {
			var self = $(this),
				url = Symphony.Context.get('root') + '/symphony/extension/media_library/library/?folder=',
				handle = folder_path.replace(folder_path.substring(folder_path.lastIndexOf('/')), '');

			window.location.href = url + handle
		});

		$('.directory-preview').on('click', function () {
			var self = $(this),
				handle = self.data('handle'),
				url = Symphony.Context.get('root') + '/symphony/extension/media_library/library/?folder=';

			if (folder_path) handle = folder_path + '/' + handle;

			window.location.href = url + handle
		});
	})(jQuery);
});
