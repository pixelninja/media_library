(function($, Symphony) {
	'use strict';

	$(window).load(function () {
		localStorage.removeItem('add-to-editor');

		var clipboard;

		/*
		 *	Having a drop down is a bit of overkill, so let's make it a single clickable item
		 */
		var ml_menu_item = $('#nav a[href$="/extension/media_library/library/"]'),
				ml_menu_group = ml_menu_item.parents('li:last');

		// Check if the nav item actually exists, some extensions strip this out (e.g Entry Relationship FIeld)
		if (!ml_menu_group.length) {
			ml_menu_group = $('#header').append('<div class="ml-link" data-href="' + Symphony.Context.get('symphony') + '/extension/media_library/library/" />').find('.ml-link');
		}
		else {
			ml_menu_group.find('ul').remove();

			ml_menu_group
				.addClass('ml-link')
				.css('cursor', 'pointer')
				.remove()
				.appendTo('#nav ul.content');
		}

		ml_menu_group.on('click', function (e) {
			e.preventDefault();

			var href = ml_menu_item.attr('href') || ml_menu_group.attr('data-href');

			if (ml_folder_path) href = href + '?folder=' + ml_folder_path;

			Symphony.Extensions.MediaLibrary.openLibrary(href);

			return false;
		});

		// Function for loading in ML content into the lightbox
		function loadMediaPage(href) {
			$('.ml-lightbox-content').addClass('show-loader');
			$('.ml-lightbox-content').find('#contents').remove();

			var jqxhr = $.get(href);

			jqxhr.done(function(data) {
				var parser = new DOMParser(),
					doc = parser.parseFromString(data, "text/html"),
					header = $(doc).find('#context'),
					content = $(doc).find('#contents');

				$('.ml-lightbox-content').removeClass('show-loader').append(content);
				$('.ml-lightbox-content').find('.media_library-upload, #context').remove();
				$('.ml-lightbox-content').prepend(header);

				if (localStorage.getItem('add-to-editor') === 'yes') {
					$('.ml-lightbox .ml-file .copy').text('Add to editor').addClass('add-to-editor');
				}

				if (localStorage.getItem('add-to-field') === 'yes') {
					$('.ml-lightbox .ml-file .copy').addClass('select-file');

					if (localStorage.getItem('allow-multiple') === 'yes') {
						$('.ml-lightbox .ml-file .copy').text('Select file(s)').after('<input type="checkbox" name="select-files" />')
					}
					else {
						$('.ml-lightbox .ml-file .copy').text('Select file');
					}
				}

				if (typeof Doka === 'undefined') {
					$('.ml-lightbox .ml-file .edit').remove();
				}

				if (getUrlParameter('folder') !== '' || getUrlParameter('folder') !== undefined) ml_folder_path = getUrlParameter('folder');

				Symphony.Extensions.MediaLibrary.fileUpload.init();
				Symphony.Extensions.MediaLibrary.events();
				Symphony.Extensions.MediaLibrary.getTags();
				Symphony.Extensions.MediaLibrary.getAlts();
			});

			jqxhr.fail(function(data) {
				alert('Something went wrong. Try again.');
				console.log(data);
			});

			function getUrlParameter(name) {
				name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");

				var regexS = "[\\?&]" + name + "=([^&#]*)";
				var regex = new RegExp(regexS);
				var results = regex.exec(href);

				if(results === null) {
					return false;
				}
				else {
					return decodeURIComponent(results[1].replace(/\+/g, " "));
				}
			}
		}

		Symphony.Extensions.MediaLibrary = {
			openLibrary : function (href) {
				var lightbox = $('body').append('<div class="ml-lightbox"><div class="ml-lightbox-content show-loader" /></div>').find('.ml-lightbox');

				setTimeout(function() {
					lightbox.addClass('is-visible');
				}, 100);

				var req = fetch(href).then(function (response) {
					// The API call was successful!
					return response.text();
				})
				.then(function (data) {
					// This is the HTML from our response as a text string
					var parser = new DOMParser(),
						doc = parser.parseFromString(data, "text/html"),
						lightbox = $('body').find('.ml-lightbox-content');

					lightbox.addClass('loaded').append($(doc).find('#context, #contents'));

					if (localStorage.getItem('add-to-editor') === 'yes') {
						lightbox.find('.ml-file .copy').text('Add to editor').addClass('add-to-editor');
					}

					if (localStorage.getItem('add-to-field') === 'yes') {
						lightbox.find('.ml-file .copy').addClass('select-file');

						if (localStorage.getItem('allow-multiple') === 'yes') {
							lightbox.find('.ml-file .copy').text('Select file(s)').after('<input type="checkbox" name="select-files" />')

							lightbox.find('.ml-header').after(`
								<div class="ml-select-all">
									<a href="#" class="button">Attach selected file(s)</a>
									<label for="ml-select-all">Select all files</label>
									<input type="checkbox" id="ml-select-all" name="select-all-files" />
							`);
						}
						else {
							lightbox.find('.ml-file .copy').text('Select file');
						}
					}

					if (typeof Doka === 'undefined') {
						$('.ml-file .edit').remove();
					}

					lightbox.removeClass('show-loader');

					Symphony.Extensions.MediaLibrary.fileUpload.init();
					Symphony.Extensions.MediaLibrary.events();
					Symphony.Extensions.MediaLibrary.getTags();
					Symphony.Extensions.MediaLibrary.getAlts();
				})
				.catch(function (err) {
					// There was an error
					console.warn('Something went wrong.', err);
					alert('Something went wrong. Try again.');
				});
			},
			getTags : function () {
				/*
				 *	Add tags from JSON file to each row
				 */
				$.getJSON(Symphony.Context.get('root') + '/extensions/media_library/json/tags.json', function(data) {
					$('.ml-file .tags').each(function () {
						var src = $(this).attr('href'),
							tags = data[src],
							count = 0;

						if (tags) {
							count = tags.split(',').length;
							$(this).attr('data-tags', tags).text('Tags (' + count + ')');
						}
					});
				});
			},
			getAlts : function () {
				/*
				 *	Add alt values from JSON file to each row
				 */
				$.getJSON(Symphony.Context.get('root') + '/extensions/media_library/json/alts.json', function(data) {
					$('.ml-file .alts').each(function () {
						var src = $(this).attr('href'),
							alts = data[src];

						if (alts) {
							// count = alts.length;
							$(this).attr('data-alts', alts).text('Alt (1)');
						}
					});
				});
			},
			events : function () {
				// Array of valid mime types used later
				var image_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'],
						video_types = ['mp4', 'webm'],
						audio_types = ['mp3'];

				/*
				 *	Go forward or backwards a directory
				 */
				// Set up the base URL 
				var base_url = Symphony.Context.get('root') + '/symphony/extension/media_library/library/?folder=';

				// Make sure 'enter' doesn't fire submission
				$('.ml-filter-files').off('keydown');
				$('.ml-filter-files').on('keydown', function (e) {
					if (e.keyCode === 13) return false;
				});

				// Filter items
				$('.ml-filter-files').off('keyup');
				$('.ml-filter-files').on('keyup', function (e) {
					var self = $(this),
							value = self.val().toLowerCase();

					if (value.length < 3) {
						if ($('.ml-files').hasClass('filtered')) {
							$('.ml-files').removeClass('filtered')
							$('.ml-file').show()
						}
						return;
					}

					$('.ml-files').addClass('filtered')
					$('.ml-file').hide();
					$('p.name[data-lower*="' + value + '"], p.size[data-mime*="' + value + '"], a.tags[data-tags*="' + value + '"], a.alts[data-alts*="' + value + '"]').closest('.ml-file').show();
				});

				// Backwards
				$('.ml-breadcrumbs a').off('click');
				$('.ml-breadcrumbs a').on('click', function () {
					var self = $(this),
							url = self.attr('href');

					if ($('.ml-lightbox').length) loadMediaPage(url);
					else window.location.href = url;

					return false;
				});

				// Forwards
				$('.ml-breadcrumbs select').off('change');
				$('.ml-breadcrumbs select').on('change', function () {
					var self = $(this),
							option = self.find('option:selected'),
							url = option.val();

					if ($('.ml-lightbox').length) loadMediaPage(url);
					else window.location.href = url;

					return false;
				});

				/*
				 *	Delete a file
				 */
				$('.ml-file a.delete').off('click');
				$('.ml-file a.delete').on('click', function (e) {
					e.preventDefault();

					var self = $(this),
						src = self.siblings('.copy').data('src'),
						check = confirm(Symphony.Language.get('Are you sure you want to delete this file? This action cannot be undone.')),
						unlink = '&unlink=' + src.replace(Symphony.Context.get('root') + '/', ''),
						href = (ml_folder_path !== '' && ml_folder_path !== undefined) ? base_url + ml_folder_path + unlink : base_url + unlink;

					// Only remove the files if the user is bloody well sure
					if (check === true) {
						if ($('.ml-lightbox').length) {
							// clipboard.destroy();
							loadMediaPage(href);
						}
						else {
							window.location.href = href;
						}
					}
					// Do nothing
					else {

					}

					return false;
				});

				/*
				 *	Add tags to a file
				 */
				// Adds/removes the inputs necessary
				$('.ml-file a.tags').off('click');
				$('.ml-file a.tags').on('click', function (e) {
					e.preventDefault();

					if ($(e.target).is('input') || $(e.target).is('button')) return false;

					var self = $(this),
						input = self.append('<input name="tags[list]" placeholder="Add comma separated tags" />').find('input'),
						button = self.append('<button name="tags[add]">Add</button>').find('button');

					if (self.hasClass('active')) {
						self.removeClass('active');
						input.add(button).remove();
						return false;
					}

					if (self.data('tags')) input.val(self.attr('data-tags'));

					self.addClass('active');

					button.on('click', function (e) {
						e.preventDefault();

						var self = $(this),
							parent = self.parent(),
							key = parent.attr('href'),
							value = self.prev().val(),
							script = Symphony.Context.get('root') + '/extensions/media_library/lib/add_tags.php',
							count = 0;

						parent.addClass('loading');

						var jqxhr = $.get(script, {image: key, tags: value});

						jqxhr.done(function(data) {
							count = data.split(',').length;

							parent
								.removeClass('loading')
								.attr('data-tags', data)
								.text('Tags (' + count + ')')
								.trigger('click');
						});

						jqxhr.fail(function(data) {
							parent.removeClass('loading');

							alert('Something went wrong. Try again.');
							console.log(data);
						});

						return false;
					});

					return false;
				});

				/*
				 *	Add alt attributes to a file
				 */
				// Adds/removes the inputs necessary
				$('.ml-file a.alts').off('click');
				$('.ml-file a.alts').on('click', function (e) {
					e.preventDefault();

					if ($(e.target).is('input') || $(e.target).is('button')) return false;

					var self = $(this),
						input = self.append('<input name="alts[list]" placeholder="Alternative text" />').find('input'),
						button = self.append('<button name="alts[add]">Add</button>').find('button');

					if (self.hasClass('active')) {
						self.removeClass('active');
						input.add(button).remove();
						return false;
					}

					if (self.data('alts')) input.val(self.attr('data-alts'));

					self.addClass('active');

					button.on('click', function (e) {
						e.preventDefault();

						var self = $(this),
							parent = self.parent(),
							key = parent.attr('href'),
							value = self.prev().val(),
							script = Symphony.Context.get('root') + '/extensions/media_library/lib/add_alts.php',
							count = 0;

						parent.addClass('loading');

						var jqxhr = $.get(script, {image: key, alts: value});

						jqxhr.done(function(data) {
							count = data.split(',').length;

							parent
								.removeClass('loading')
								.attr('data-alts', data)
								.text('Alt (' + count + ')')
								.trigger('click');
						});

						jqxhr.fail(function(data) {
							parent.removeClass('loading');

							alert('Something went wrong. Try again.');
							console.log(data);
						});

						return false;
					});

					return false;
				});

				/*
				 *	Toggle all files for selection
				 */
				$('.ml-select-all input').off('change');
				$('.ml-select-all input').on('change', function (e) {
					e.preventDefault();

					let state = ($(this).is(':checked')) ? true : false;

					if (state) {
						$(this).closest('fieldset').find('.ml-file:visible input[name="select-files"]').prop('checked', true);
					}
					else {
						$(this).closest('fieldset').find('.ml-file:visible input[name="select-files"]').prop('checked', false);
					}

					return false;
				});

				// Update the select all toggle when interacting with individual checkbox
				$('.ml-file input[name="select-files"]').off('change');
				$('.ml-file input[name="select-files"]').on('change', function (e) {
					e.preventDefault();

					if ($('.ml-file input[name="select-files"]:checked').length < $('.ml-file input[name="select-files"]').length) {
						$('.ml-select-all input').prop('checked', false);
					}
					else {
						$('.ml-select-all input').prop('checked', true);
					}

					return false;
				});

				// Pse
				$('.ml-select-all .button').off('click');
				$('.ml-select-all .button').on('click', function (e) {
					e.preventDefault();

					addFieldFiles($('.ml-file .select-file').first());
					closeLightbox();

					return false;
				});

				/*
				 *	Copy the URL for a file
				 */
				if (typeof clipboard === 'object') clipboard.destroy();
				clipboard = new Clipboard('.ml-file a.copy', {
					text: function(trigger) {
						// If we are using the TinyMCE plugin as well, then add the source to the popup window
						if ($(trigger).hasClass('add-to-editor') && typeof ml_source_input === 'function') {
							ml_source_input(
								$(trigger).data('src'), {
									alt: ($(trigger).closest('.ml-file').find('.alts[data-alts]').length) ? $(trigger).closest('.ml-file').find('.alts[data-alts]').data('alts') : $(trigger).closest('.ml-file').find('.name').text()
								}
							);

							closeLightbox();

							return $(trigger).data('src');
						}
						// If we are using the media library field, then add the data to the fields
						else if ($(trigger).hasClass('select-file') && $(ml_source_input).is('div')) {
							addFieldFiles(trigger);
							closeLightbox();

							return false;
						}

						// Update the text momentarily as an indicator something has happened
						$(trigger).text(Symphony.Language.get('Copied!'));

						// Switch the text back after 2s
						setTimeout(function () {
							$(trigger).text(Symphony.Language.get('Copy file URL'));
						}, 2000);

						return $(trigger).data('src');
					}
				});

				function addFieldFiles(trigger) {
					var meta = $(trigger).nextAll('.meta'),
							data = {
								name : $(trigger).closest('.ml-file').find('.name').text(),
								src : $(trigger).data('src'),
								mime : meta.data('mime'),
								size : meta.data('size'),
								dimensions : meta.data('dimensions') || false
							};

					// If multiple is allowed, then we need to add to it rather than replace it
					if ($(ml_source_input).data('allow-multiple') === 'yes') {
						// If multiple have been selected then we need to loop over them and add all files
						if ($(trigger).closest('.ml-files').find('input[name="select-files"]:checked').length) {
							var inputs = $(trigger).closest('.ml-files').find('input[name="select-files"]:checked');

							inputs.each(function (i, e) {
								var this_data = {
									name : $(e).closest('.ml-file').find('.name').text(),
									src : $(e).prev('.select-file').data('src'),
									mime : $(e).nextAll('.meta').data('mime'),
									size : $(e).nextAll('.meta').data('size'),
									dimensions : $(e).nextAll('.meta').data('dimensions') || false
								};

								setTimeout(function() {
									addFieldItem(this_data);
								}, 100 * i);
							});
						}
						// Otherwise just add the one
						else {
							addFieldItem(data);
						}
					}
					// Only one item is allowed
					else {
						var fields = $(ml_source_input).find('.instance'),
								preview = $(ml_source_input).find('.preview');

						// Add the fields if they don't exist
						if (!fields.find('input').length) {
							fields.append(`
								<input name="fields[${fields.data('name')}][0][value]" />
								<input name="fields[${fields.data('name')}][0][name]" />
								<input name="fields[${fields.data('name')}][0][mime]" />
								<input name="fields[${fields.data('name')}][0][size]" />
								<input name="fields[${fields.data('name')}][0][unit]" />
								<input name="fields[${fields.data('name')}][0][width]" />
								<input name="fields[${fields.data('name')}][0][height]" />
							`);
						}

						// Update the values
						fields.find('input[name*="[name]"]').val(data.name);
						fields.find('input[name*="[value]"]').val(data.src.split(Symphony.Context.get('root'))[1]);
						fields.find('input[name*="[mime]"]').val(data.mime);
						fields.find('input[name*="[size]"]').val(data.size.split(' ')[0]);
						fields.find('input[name*="[unit]"]').val(data.size.split(' ')[1].toLowerCase());

						if (data.dimensions) {
							fields.find('input[name*="[width]"]').val(data.dimensions.split('x')[0]);
							fields.find('input[name*="[height]"]').val(data.dimensions.split('x')[1].replace('p', ''));
						}
						else {
							fields.find('input[name*="[width]"]').val('');
							fields.find('input[name*="[height]"]').val('');	
						}

						// Remove any existing previews
						$(ml_source_input).find('.preview').remove();

						// Add the new preview
						$(ml_source_input).find('.clear').before(`<div class="preview"><div class="item"><p><strong>${data.name}</strong>${data.mime}</p><a class="view" href="${data.src}">View</a></div></div>`);

						if (image_types.includes(data.mime)) {
							$(ml_source_input).find('.item').addClass('image').prepend(`<img src="${data.src}" />`);
						}
						else if (video_types.includes(data.mime)) {
							$(ml_source_input).find('.item').addClass('video').prepend(`<video src="${data.src}" autoplay loop muted />`);
						}
						else if (audio_types.includes(data.mime)) {
							$(ml_source_input).find('.item').addClass('audio').prepend(`<audio src="${data.src}" controls  />`);
						}
						else if (data.mime !== undefined ) {
							$(ml_source_input).find('.item').addClass('other');
						}
					}
				}

				function addFieldItem(data) {
					var item_length = $(ml_source_input).find('input[name*="[name]"]').length,
							fields = $(ml_source_input).find('.instance'),
							preview = $(ml_source_input).find('.preview');

					// There's no preview, so there is no attached item
					if (!preview.length) {
						// Add the preview
						preview = $(ml_source_input).find('.clear').before('<div class="preview" />').prev('.preview');
						// And update the values of the default empty inputs
						fields.find('input[name*="[value]"]').val(data.src.split(Symphony.Context.get('root'))[1]);
						fields.find('input[name*="[name]"]').val(data.name);
						fields.find('input[name*="[mime]"]').val(data.mime);
						fields.find('input[name*="[size]"]').val(data.size.split(' ')[0]);
						fields.find('input[name*="[unit]"]').val(data.size.split(' ')[1].toLowerCase());

						if (data.dimensions) {
							fields.find('input[name*="[width]"]').val(data.dimensions.split('x')[0]);
							fields.find('input[name*="[height]"]').val(data.dimensions.split('x')[1].replace('p', ''));
						}
					}
					// There is a preview, so we need to add to it
					else {
						fields.append(`
							<input name="fields[${fields.data('name')}][${item_length}][value]" value="${data.src.split(Symphony.Context.get('root'))[1]}" type="text" readonly />
							<input name="fields[${fields.data('name')}][${item_length}][name]" value="${data.name}" type="text" readonly />
							<input name="fields[${fields.data('name')}][${item_length}][mime]" value="${data.mime}" type="text" readonly />
							<input name="fields[${fields.data('name')}][${item_length}][size]" value="${data.size.split(' ')[0]}" type="text" readonly />
							<input name="fields[${fields.data('name')}][${item_length}][unit]" value="${data.size.split(' ')[1].toLowerCase()}" type="text" readonly />
						`);

						if (data.dimensions) {
							fields.append(`
								<input name="fields[${fields.data('name')}][${item_length}][width]" value="${data.dimensions.split('x')[0]}" type="text" readonly />
								<input name="fields[${fields.data('name')}][${item_length}][height]" value="${data.dimensions.split('x')[1].replace('p', '')}" type="text" readonly />
							`);
						}
					}

					var item = preview.append(`<div class="item"><p><strong>${data.name}</strong>${data.mime}</p><a class="view" href="${data.src}">View</a><a class="remove">Remove</a></div>`).find('.item:last-child');
					
					if (image_types.includes(data.mime)) {
						item.addClass('image').prepend(`<img src="${data.src}" />`);
					}
					else if (video_types.includes(data.mime)) {
						item.addClass('video').prepend(`<video src="${data.src}" autoplay loop muted />`);
					}
					else if (audio_types.includes(data.mime)) {
						item.addClass('audio').prepend(`<audio src="${data.src}" controls  />`);
					}
					else if (data.mime !== undefined ) {
						item.addClass('other');
					}
				}

				/*
				 *	Add a directory
				 */
				$('.ml-trigger-directory').off('click');
				$('.ml-trigger-directory').on('click', function (e) {
					e.preventDefault();

					let location;
					// Prompt user for a folder name
					const dir_name = prompt('What would you like to name your new folder? (Letters and numbers only)', 'new-folder');

					if (dir_name !== null && dir_name !== "") {
						location = (ml_folder_path) ? ml_folder_path + '/' + dir_name : dir_name;
					}
					else {
						return false;
					}

					let href = Symphony.Context.get('symphony') + '/extension/media_library/library/?mkdir=' + location;

					var req = fetch(href).then(function (response) {
						return response.text();
					})
					.then(function (data) {
						if (data === 'already exists') {
							alert('The folder you are trying to create already exists. Please choose a different name');
						}
						else if (data === 'error') {
							alert('There was an error trying to create your folder. Please try again or contact support.');
						}
						else if (data === 'success') {
							loadMediaPage(Symphony.Context.get('symphony') + '/extension/media_library/library/?folder=' + location);
						}
					})
					.catch(function (err) {
						// There was an error
						console.warn('Something went wrong.', err);
						alert('Something went wrong. Try again.');
					});

					return false;
				});

				/*
				 *	Expand/hide the drag/drop dropzone
				 */
				$('.ml-trigger-upload').off('click');
				$('.ml-trigger-upload').on('click', function (e) {
					e.preventDefault();

					var self = $(this),
						upload = $('.media_library-upload');

					// Already expanded? Reset it
					if (self.hasClass('is-active')) {
						self.text('Upload File');
						self.removeClass('is-active');
						upload.removeClass('is-active');
						return false;
					}

					self.text('Close');
					self.addClass('is-active');
					upload.addClass('is-active');

					return false;
				});

				/*
				 *	Close the lightbox
				 */
				function closeLightbox() {
					// End the clipboard instance
					clipboard.destroy();
					// Reset localstorage settings
					localStorage.removeItem('add-to-editor');
					localStorage.removeItem('add-to-field');
					localStorage.removeItem('allow-multiple');
					// Reset the subdirectory
					ml_folder_path = null;
					// Hide it
					$('body .ml-lightbox').removeClass('is-visible');
					setTimeout(function () {
						$('body .ml-lightbox').remove();
					}, 200);
				}

				// pressing ESC should close the lightbox
				$(window).off('keyup');
				$(window).on('keyup', function (e) {
					if (e.keyCode === 27 && $('.ml-lightbox').length) {
						closeLightbox();
					}
				});

				// clicking on the lightbox area should also close the lightbox
				$('.ml-lightbox').off('click');
				$('.ml-lightbox').on('click', function (e) {
					var container = $(this).find('> *');

					// if the target of the click isn't the container, or a descendant of the container
					if (!container.is(e.target) && container.has(e.target).length === 0) {
						closeLightbox();
					}
				});

				/*
				 *	Toggle multi or single upload
				 */
				$('#droparea_toggle').off('change');
				$('#droparea_toggle').on('change', function (e) {
				// $('body').on('change', '#droparea_toggle', function (e) {
					$('.media_library-droparea').toggleClass('switch-method');
				});

				// Edit an image and add option to rename file
				if (typeof Doka === 'object') {
					$('.ml-file .edit').off('click');
					$('.ml-file .edit').on('click', function (e) {
						e.preventDefault();

						let edit_image = Doka.create({
							outputQuality : ml_image_settings.outputQuality,
							utils : ['crop', 'filter','resize'],
							cropAspectRatioOptions: [
								{
									label: 'Free',
									value: null
								},
								{
									label: '3:2',
									value: 1.5
								},
								{
									label: '2:3',
									value: 0.67
								},
								{
									label: '16:9',
									value: 1.778
								},
								{
									label: '9:16',
									value: 0.5625
								},
								{
									label: '1:1',
									value: 1
								}
							]
						});

						edit_image
							.edit($(this).attr('href'))
					    .then(output => {
								if(!output) return false;

								let data = new FormData();
								let location = ml_doc_root + '/workspace/uploads/';
								let src = $(this).attr('href');
								let custom_name, name, extension;

								if (ml_folder_path) location = location + ml_folder_path + '/';

								name = src.split('/');
								name = name[name.length - 1];
								extension = name.split('.');
								extension = extension[extension.length - 1];

								// Confirm the existing name or type a new one
								custom_name = prompt('Confirm the file name (existing files will be overwritten):', name.replace('.' + extension, ''));
							  if (custom_name !== null || custom_name !== "") {
							    name = custom_name + '.' + extension;
							  }

								// Set data
								data.append('file', output.file, name);
								data.append('location', location);
								data.append('overwrite', 'yes'); // Override existing file instead of adding a new one

								// Send data
								$.ajax({
									url: Symphony.Context.get('root') + '/extensions/media_library/lib/upload.php',
									data: data,
									cache: false,
									contentType: false,
									dataType: 'json',
									processData: false,
									type: 'POST',
									error: function(result){
										console.log(result)
										alert('There was an error uploading this file. Please try again or contact your support team.');
									},
									success: function(result) {
										console.log(result);
										Symphony.Extensions.MediaLibrary.fileUpload.refresh();
									}
								});

					    });

						return false;
					});
				}
			},
			fileUpload : {
				/*
				 *	Upload Files w/ drag and drop
				 */
				init : function () {
					var fileAPI = !!window.FileReader,
						element = '<div class="media_library-upload"></div>',
						field = ($('body').find('.ml-lightbox').length) ? $('.ml-lightbox-content #context').after(element).next() : $('#context').after(element).next();

					if(fileAPI) Symphony.Extensions.MediaLibrary.fileUpload.createDroparea(field);
				},

				createDroparea : function (field) {
					let html;

					if (typeof Doka === 'object') {
						html = `
							<div class="media_library-droparea">
								<div class="media_library-droparea_toggle">
									<p>
										<label for="droparea_toggle">Single Image</label>
										<i>Manipulate and upload a single image</i>
									</p>
									<input type="checkbox" id="droparea_toggle" />
									<span />
									<p>
										<label for="droparea_toggle">Multi file</label>
										<i>Upload multiple files of any format at a time</i>
									</p>
								</div>

								<div class="media_library-droparea_columns">
									<div class="media_library-droparea_column">
										<input type="file" class="fireDoka" name="doka" />
									</div>

									<div class="media_library-droparea_column">
										<input type="file" class="fireFilepond" name="filepond" />
									</div>
								</div>
							</div>
						`;
					}
					else {
						html = `
							<div class="media_library-droparea">
								<div class="media_library-droparea_columns">
									<div class="media_library-droparea_column">
										<input type="file" class="fireFilepond" name="filepond" />
									</div>
								</div>
							</div>
						`;
					}

					field.append(html);

					// Initiate FilePond
					Symphony.Extensions.MediaLibrary.filePond();
				},

				refresh : function () {
					var base_url = Symphony.Context.get('root') + '/symphony/extension/media_library/library/',
						// url = (ml_folder_path === undefined) ? base_url : base_url + '?folder=' + ml_folder_path;
						url = (!ml_folder_path) ? base_url : base_url + '?folder=' + ml_folder_path;

					// Send data
					$.ajax({
						url: url,
						cache: false,
						type: 'GET',
						error: function(result){
							console.log('error');
						},
						success: function(result) {
							// Parse the new document
							var parser = new DOMParser(),
								doc = parser.parseFromString(result, 'text/html'),
								new_content = $(doc).find('.ml-files');

							if (localStorage.getItem('add-to-editor') === 'yes') {
								new_content.find('.ml-file .copy').text('Add to editor').addClass('add-to-editor');
							}

							if (localStorage.getItem('add-to-field') === 'yes') {
								new_content.find('.ml-file .copy').addClass('select-file');

								if (localStorage.getItem('allow-multiple') === 'yes') {
									new_content.find('.ml-file .copy').text('Select file(s)').after('<input type="checkbox" name="select-files" />')
								}
								else {
									new_content.find('.ml-file .copy').text('Select file');
								}
							}

							if (typeof Doka === 'undefined') {
								$('.ml-files .edit').remove();
							}

							$('.ml-files').html(new_content.html());

							Symphony.Extensions.MediaLibrary.getTags();
							Symphony.Extensions.MediaLibrary.getAlts();
							Symphony.Extensions.MediaLibrary.events();
						}
					});
				}
			},
			filePond : function () {
				let doka;
				let pond;

				// First register any plugins
				FilePond.registerPlugin(
					FilePondPluginFileValidateSize,
					FilePondPluginImageExifOrientation,
					FilePondPluginImagePreview,
					FilePondPluginImageCrop,
					FilePondPluginImageResize,
					FilePondPluginImageTransform,
					FilePondPluginImageEdit,
					FilePondPluginImageValidateSize
				);

				FilePond.setOptions({
					allowImageValidateSize: true,
					imageValidateSizeMinWidth: ml_image_settings.minWidth,
					imageValidateSizeMaxWidth: ml_image_settings.maxWidth,
					imageValidateSizeMinHeight: ml_image_settings.minHeight,
					imageValidateSizeMaxHeight: ml_image_settings.maxHeight,
					server: {
						process : function (fieldName, file, metadata, load, error, progress, abort) {
							var data = new FormData(),
								location = ml_doc_root + '/workspace/uploads/';

							if (ml_folder_path) location = location + ml_folder_path + '/';

							// Set data
							data.append('file', file, file.name);
							data.append('location', location);

							// Send data
							$.ajax({
								url: Symphony.Context.get('root') + '/extensions/media_library/lib/upload.php',
								data: data,
								cache: false,
								contentType: false,
								dataType: 'json',
								processData: false,
								type: 'POST',
								error: function(result){
									error();
								},
								success: function(result) {
									load();
								}
							});

							// Abort method so the request can be cancelled
							return {
								abort: () => {
									// This function is entered if the user has tapped the cancel button
									request.abort();
									// Let FilePond know the request has been cancelled
									abort();
								}
							};
						}
					}
				});
		
				// console.log(FilePond.getOptions());

				pond = FilePond.create(document.querySelector('.fireFilepond'), {
					allowFileSizeValidation: true,
					minFileSize: ml_image_settings.minFileSize,
					maxFileSize: ml_image_settings.maxFileSize,
					allowMultiple: true,
					allowImagePreview: false,
					imageTransformImageFilter: (file) => new Promise(resolve => {
						/* Stop animated GIFs being converted into PNGs */
						// no gif mimetype, do transform
						if (!/image\/gif/.test(file.type)) return resolve(true);

						const reader = new FileReader();
						reader.onload = () => {
							var arr = new Uint8Array(reader.result),
							i, len, length = arr.length, frames = 0;

							// make sure it's a gif (GIF8)
							if (arr[0] !== 0x47 || arr[1] !== 0x49 || 
								arr[2] !== 0x46 || arr[3] !== 0x38) {
								// it's not a gif, we can safely transform it
								resolve(true);
								return;
							}

							for (i=0, len = length - 9; i < len, frames < 2; ++i) {
								if (arr[i] === 0x00 && arr[i+1] === 0x21 &&
									arr[i+2] === 0xF9 && arr[i+3] === 0x04 &&
									arr[i+8] === 0x00 && 
									(arr[i+9] === 0x2C || arr[i+9] === 0x21)) {
									frames++;
								}
							}

							// if frame count > 1, it's animated, don't transform
							if (frames > 1) {
								return resolve(false);
							}

							// do transform
							resolve(true);
						}

						reader.readAsArrayBuffer(file);
					}),
					onprocessfiles: function () {
						Symphony.Extensions.MediaLibrary.fileUpload.refresh();
					}
				});

				if (typeof Doka === 'object') {
					doka = FilePond.create(document.querySelector('.fireDoka'), {
						allowFileSizeValidation: true,
						minFileSize: ml_image_settings.minImageSize,
						maxFileSize: ml_image_settings.maxImageSize,
						labelIdle: 'Drag & Drop your image or <span class="filepond--label-action">Browse</span>',
						// open editor on image drop
						imageEditInstantEdit: true,
						// configure Doka
						imageEditEditor: Doka.create({
							outputQuality : ml_image_settings.outputQuality,
							utils : ['crop', 'filter','resize'],
							cropAspectRatioOptions: [
								{
									label: 'Free',
									value: null
								},
								{
									label: '3:2',
									value: 1.5
								},
								{
									label: '2:3',
									value: 0.67
								},
								{
									label: '16:9',
									value: 1.778
								},
								{
									label: '9:16',
									value: 0.5625
								},
								{
									label: '1:1',
									value: 1
								}
							]
						}),
						onprocessfiles: function () {
							Symphony.Extensions.MediaLibrary.fileUpload.refresh();
						}
					});
				}
			},
			init : function () {
				Symphony.Language.add({
					'Multi File Upload': false,
					'Image Editor': false,
					'Copied!': false,
					'Copy file URL': false,
					'Are you sure you want to delete this file? This action cannot be undone.': false
				});

				Symphony.Extensions.MediaLibrary.getTags();
				Symphony.Extensions.MediaLibrary.getAlts();
				Symphony.Extensions.MediaLibrary.events();
				Symphony.Extensions.MediaLibrary.fileUpload.init();
			}
		};

		(function () {
			// Make sure we only execute in the Library
			if (ml_driver !== 'library') return false;
			// Initialise the extension
			Symphony.Extensions.MediaLibrary.init();
		})();
	});
})(window.jQuery, window.Symphony);