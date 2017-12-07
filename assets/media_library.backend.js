jQuery(window).load(function () {

	/*
	 *	Having a drop down is a bit of overkill, so let's make it a single clickable item
	 */
	var ml_menu_item = jQuery('#nav a[href$="/extension/media_library/library/"]'),
		ml_menu_group = ml_menu_item.parents('li:last');

	ml_menu_group.find('ul').remove();

	ml_menu_group
		.css('cursor', 'pointer')
		.append('<span class="media-library-direct" />')
		.remove()
		.appendTo('#nav ul.content');

	ml_menu_group.on('click', function (e) {
		e.preventDefault();

		var href = ml_menu_item.attr('href');

		if ($(e.target).is('span')) {
			window.location.href = href;
			return false;
		}

		// Send data
		$.ajax({
			url: href,
			type: 'GET',
			error: function(result){
				console.log('error', result);
			},
			// Add file
			success: function(result) {
				var doc = $(result),
					// content = doc.find('#contents');
					content = doc.filter('#wrapper');

				content.find('#header').remove();

				$.featherlight(content, {
					afterContent : function() {
						Symphony.Extensions.MediaLibrary.init();
					}
				});
			}
		});

		return false;
	});

	Symphony.Extensions.MediaLibrary = {
		clickEvents : function () {
			/*
			 *	Go forward or backwards a directory
			 */
			var base_url = Symphony.Context.get('root') + '/symphony/extension/media_library/library/?folder=';

			// Backwards
			$('.ml-directory-back').on('click', function () {
				var self = $(this),
					// The handle to append, which is the full folder path minus the last folder
					handle = folder_path.replace(folder_path.substring(folder_path.lastIndexOf('/')), '');

				window.location.href = base_url + handle;
			});

			// Forwards
			$('.ml-subdirectory').on('click', function () {
				var self = $(this),
					// The handle of the folder
					handle = self.data('handle');

				// If there is an existing folder path, add it to the new handle
				if (folder_path) handle = folder_path + '/' + handle;

				window.location.href = base_url + handle;
			});

			/*
			 *	Delete a file
			 */
			$('.ml-file a.delete').on('click', function () {
				var self = $(this),
					src = self.prev('a').data('src');
					check = confirm(Symphony.Language.get('Are you sure you want to delete this file? This action cannot be undone.'));

				// Only remove the files if the user is bloody well sure
				if (check === true) {
					if (folder_path !== '' && folder_path !== undefined) {
						window.location.href = base_url + folder_path + '&unlink=' + src.replace(Symphony.Context.get('root') + '/', '')
					}
					else {
						window.location.href = base_url + '&unlink=' + src.replace(Symphony.Context.get('root') + '/', '')
					}
				}
				// Do nothing
				else {

				}
			});

			/*
			 *	Copy the URL for a file
			 */
		    new Clipboard('.ml-file a.copy', {
		        text: function(trigger) {
					// Update the text momentarily as an indicator something has happened
		            $(trigger).text(Symphony.Language.get('Copied!'));

		            setTimeout(function () {
		            	$(trigger).text(Symphony.Language.get('Copy to clipboard'));
		            }, 2000);

		            return $(trigger).data('src');
		        }
		    });

			/*
			 *	Expand/hide the drag/drop dropzone
			 */
			$('.trigger-upload').on('click', function (e) {
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

		},
		fileUpload : {
			/*
			 *	Upload Files w/ drag and drop
			 */
			init : function () {
		   		var fileAPI = !!window.FileReader,
					element = '<div class="media_library-upload"><div class="media_library-files empty media_library-drop"><ol /></div></div>',
					field = ($('.featherlight-content').length) ? $('.featherlight-content #context').after(element).next() : $('#context').after(element).next();

				if(fileAPI) Symphony.Extensions.MediaLibrary.fileUpload.createDroparea(field);
			},

			createDroparea : function (field) {
				// Append drop area
				$('<div />', {
					class: 'media_library-droparea',
					html: '<span>' + Symphony.Language.get('Drop files') + '</span>',
					on: {
						dragover: Symphony.Extensions.MediaLibrary.fileUpload.drag,
						dragenter: Symphony.Extensions.MediaLibrary.fileUpload.drag,
						dragend: Symphony.Extensions.MediaLibrary.fileUpload.dragend,
						drop: Symphony.Extensions.MediaLibrary.fileUpload.drop
					}
				}).appendTo(field);

				// And a button that refreshes
				$('<button />', {
					class: 'button',
					html: Symphony.Language.get('Refresh page to view files'),
					on: {
						click: Symphony.Extensions.MediaLibrary.fileUpload.refresh
					}
				}).appendTo(field);
			},

			refresh : function (event) {
				Symphony.Extensions.MediaLibrary.fileUpload.stop(event);
				window.location = location.href;
			},

			drag : function (event) {
				Symphony.Extensions.MediaLibrary.fileUpload.stop(event);
				$(event.currentTarget).addClass('media_library-drag');
			},

			dragend : function (event) {
				$(event.currentTarget).removeClass('media_library-drag');
			},

			drop : function (event) {
				Symphony.Extensions.MediaLibrary.fileUpload.stop(event);

				var dragarea = $(event.currentTarget).removeClass('media_library-drag'),
					field = dragarea.parents('.media_library-upload'),
					files = field.find('.media_library-files'),
					list = field.find('ol');

				if (!list.length) {
					field.prepend('<ol />');
					list = field.find('ol');
				}

				// Loop over files
				$.each(event.originalEvent.dataTransfer.files, function(index, file) {
					files.removeClass('empty');

					var item = $('<li />', {
						html: '<header><a>' + file.name + '</a><span class="media_library-progress"></span><a class="destructor">' + Symphony.Language.get('In queue') + '</a></header>',
						class: 'instance queued'
					}).hide().appendTo(list).slideDown('fast');

					Symphony.Extensions.MediaLibrary.fileUpload.send(field, item, file);
				});
			},

			stop : function (event) {
				event.stopPropagation();
				event.preventDefault();
			},

			send : function (field, item, file) {
				var data = new FormData(),
					location = doc_root + '/workspace/uploads/';

				if (folder_path) location = location + folder_path + '/';

				// Set data
				data.append('file', file);
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
						item.removeClass('queued').addClass('error');
						item.find('.destructor').text(Symphony.Language.get('Upload failed'));
					},
					// Add file
					success: function(result) {
						item.removeClass('queued');
						item.find('.media_library-progress').css('width', '100%');
						item.find('.destructor').text(Symphony.Language.get('Complete'));
						item.find('header a:first').attr('href', result.url);
						item.find('header a:first').text(result.name);
					},
					// Upload progress
					xhr: function() {
						// get the native XmlHttpRequest object
						var xhr = $.ajaxSettings.xhr();
						// set the onprogress event handler
						xhr.upload.onprogress = function(progress) {
							item.find('.destructor').text(Symphony.Language.get('Uploading'));
							item.find('.media_library-progress').css('width', Math.floor(100 * progress.loaded / progress.total) + '%');
						};
						// return the customized object
						return xhr;
					}
				});
			}
		},
		init : function () {

			Symphony.Language.add({
				'Copied!': false,
				'Copy to clipboard': false,
				'Are you sure you want to delete this file? This action cannot be undone.': false,
				'Drop files': false,
				'In queue': false,
				'Uploading': false,
				'Remove file': false,
				'Upload failed': false,
				'Refresh page to view files': false
			});

			Symphony.Extensions.MediaLibrary.clickEvents();
			Symphony.Extensions.MediaLibrary.fileUpload.init();

		}
	};

	(function ($) {
		// Make sure we only execute in the Library
		if (driver !== 'library') return false;

		Symphony.Extensions.MediaLibrary.init();
	})(jQuery);
});
