jQuery(document).ready(function($) {
	var menu_item = $('#nav a[href$="/extension/media_library/library/"]');
	var menu_group = menu_item.parents('li:last');

	// kill the subnav
	menu_group.find('ul').remove();

	menu_group
		.css('cursor', 'pointer')
		.remove()
		.appendTo('#nav ul.content')
		.bind('click', function() {
			window.location.href = menu_item.attr('href');
		});

});
