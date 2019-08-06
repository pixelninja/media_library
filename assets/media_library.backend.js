/*!
 * clipboard.js v1.6.1
 * https://zenorocha.github.io/clipboard.js
 *
 * Licensed MIT © Zeno Rocha
 */
!function(e){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=e();else if("function"==typeof define&&define.amd)define([],e);else{var t;t="undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this,t.Clipboard=e()}}(function(){var e,t,n;return function e(t,n,o){function i(a,c){if(!n[a]){if(!t[a]){var l="function"==typeof require&&require;if(!c&&l)return l(a,!0);if(r)return r(a,!0);var u=new Error("Cannot find module '"+a+"'");throw u.code="MODULE_NOT_FOUND",u}var s=n[a]={exports:{}};t[a][0].call(s.exports,function(e){var n=t[a][1][e];return i(n?n:e)},s,s.exports,e,t,n,o)}return n[a].exports}for(var r="function"==typeof require&&require,a=0;a<o.length;a++)i(o[a]);return i}({1:[function(e,t,n){function o(e,t){for(;e&&e.nodeType!==i;){if(e.matches(t))return e;e=e.parentNode}}var i=9;if("undefined"!=typeof Element&&!Element.prototype.matches){var r=Element.prototype;r.matches=r.matchesSelector||r.mozMatchesSelector||r.msMatchesSelector||r.oMatchesSelector||r.webkitMatchesSelector}t.exports=o},{}],2:[function(e,t,n){function o(e,t,n,o,r){var a=i.apply(this,arguments);return e.addEventListener(n,a,r),{destroy:function(){e.removeEventListener(n,a,r)}}}function i(e,t,n,o){return function(n){n.delegateTarget=r(n.target,t),n.delegateTarget&&o.call(e,n)}}var r=e("./closest");t.exports=o},{"./closest":1}],3:[function(e,t,n){n.node=function(e){return void 0!==e&&e instanceof HTMLElement&&1===e.nodeType},n.nodeList=function(e){var t=Object.prototype.toString.call(e);return void 0!==e&&("[object NodeList]"===t||"[object HTMLCollection]"===t)&&"length"in e&&(0===e.length||n.node(e[0]))},n.string=function(e){return"string"==typeof e||e instanceof String},n.fn=function(e){var t=Object.prototype.toString.call(e);return"[object Function]"===t}},{}],4:[function(e,t,n){function o(e,t,n){if(!e&&!t&&!n)throw new Error("Missing required arguments");if(!c.string(t))throw new TypeError("Second argument must be a String");if(!c.fn(n))throw new TypeError("Third argument must be a Function");if(c.node(e))return i(e,t,n);if(c.nodeList(e))return r(e,t,n);if(c.string(e))return a(e,t,n);throw new TypeError("First argument must be a String, HTMLElement, HTMLCollection, or NodeList")}function i(e,t,n){return e.addEventListener(t,n),{destroy:function(){e.removeEventListener(t,n)}}}function r(e,t,n){return Array.prototype.forEach.call(e,function(e){e.addEventListener(t,n)}),{destroy:function(){Array.prototype.forEach.call(e,function(e){e.removeEventListener(t,n)})}}}function a(e,t,n){return l(document.body,e,t,n)}var c=e("./is"),l=e("delegate");t.exports=o},{"./is":3,delegate:2}],5:[function(e,t,n){function o(e){var t;if("SELECT"===e.nodeName)e.focus(),t=e.value;else if("INPUT"===e.nodeName||"TEXTAREA"===e.nodeName){var n=e.hasAttribute("readonly");n||e.setAttribute("readonly",""),e.select(),e.setSelectionRange(0,e.value.length),n||e.removeAttribute("readonly"),t=e.value}else{e.hasAttribute("contenteditable")&&e.focus();var o=window.getSelection(),i=document.createRange();i.selectNodeContents(e),o.removeAllRanges(),o.addRange(i),t=o.toString()}return t}t.exports=o},{}],6:[function(e,t,n){function o(){}o.prototype={on:function(e,t,n){var o=this.e||(this.e={});return(o[e]||(o[e]=[])).push({fn:t,ctx:n}),this},once:function(e,t,n){function o(){i.off(e,o),t.apply(n,arguments)}var i=this;return o._=t,this.on(e,o,n)},emit:function(e){var t=[].slice.call(arguments,1),n=((this.e||(this.e={}))[e]||[]).slice(),o=0,i=n.length;for(o;o<i;o++)n[o].fn.apply(n[o].ctx,t);return this},off:function(e,t){var n=this.e||(this.e={}),o=n[e],i=[];if(o&&t)for(var r=0,a=o.length;r<a;r++)o[r].fn!==t&&o[r].fn._!==t&&i.push(o[r]);return i.length?n[e]=i:delete n[e],this}},t.exports=o},{}],7:[function(t,n,o){!function(i,r){if("function"==typeof e&&e.amd)e(["module","select"],r);else if("undefined"!=typeof o)r(n,t("select"));else{var a={exports:{}};r(a,i.select),i.clipboardAction=a.exports}}(this,function(e,t){"use strict";function n(e){return e&&e.__esModule?e:{default:e}}function o(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var i=n(t),r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},a=function(){function e(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}return function(t,n,o){return n&&e(t.prototype,n),o&&e(t,o),t}}(),c=function(){function e(t){o(this,e),this.resolveOptions(t),this.initSelection()}return a(e,[{key:"resolveOptions",value:function e(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};this.action=t.action,this.emitter=t.emitter,this.target=t.target,this.text=t.text,this.trigger=t.trigger,this.selectedText=""}},{key:"initSelection",value:function e(){this.text?this.selectFake():this.target&&this.selectTarget()}},{key:"selectFake",value:function e(){var t=this,n="rtl"==document.documentElement.getAttribute("dir");this.removeFake(),this.fakeHandlerCallback=function(){return t.removeFake()},this.fakeHandler=document.body.addEventListener("click",this.fakeHandlerCallback)||!0,this.fakeElem=document.createElement("textarea"),this.fakeElem.style.fontSize="12pt",this.fakeElem.style.border="0",this.fakeElem.style.padding="0",this.fakeElem.style.margin="0",this.fakeElem.style.position="absolute",this.fakeElem.style[n?"right":"left"]="-9999px";var o=window.pageYOffset||document.documentElement.scrollTop;this.fakeElem.style.top=o+"px",this.fakeElem.setAttribute("readonly",""),this.fakeElem.value=this.text,document.body.appendChild(this.fakeElem),this.selectedText=(0,i.default)(this.fakeElem),this.copyText()}},{key:"removeFake",value:function e(){this.fakeHandler&&(document.body.removeEventListener("click",this.fakeHandlerCallback),this.fakeHandler=null,this.fakeHandlerCallback=null),this.fakeElem&&(document.body.removeChild(this.fakeElem),this.fakeElem=null)}},{key:"selectTarget",value:function e(){this.selectedText=(0,i.default)(this.target),this.copyText()}},{key:"copyText",value:function e(){var t=void 0;try{t=document.execCommand(this.action)}catch(e){t=!1}this.handleResult(t)}},{key:"handleResult",value:function e(t){this.emitter.emit(t?"success":"error",{action:this.action,text:this.selectedText,trigger:this.trigger,clearSelection:this.clearSelection.bind(this)})}},{key:"clearSelection",value:function e(){this.target&&this.target.blur(),window.getSelection().removeAllRanges()}},{key:"destroy",value:function e(){this.removeFake()}},{key:"action",set:function e(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"copy";if(this._action=t,"copy"!==this._action&&"cut"!==this._action)throw new Error('Invalid "action" value, use either "copy" or "cut"')},get:function e(){return this._action}},{key:"target",set:function e(t){if(void 0!==t){if(!t||"object"!==("undefined"==typeof t?"undefined":r(t))||1!==t.nodeType)throw new Error('Invalid "target" value, use a valid Element');if("copy"===this.action&&t.hasAttribute("disabled"))throw new Error('Invalid "target" attribute. Please use "readonly" instead of "disabled" attribute');if("cut"===this.action&&(t.hasAttribute("readonly")||t.hasAttribute("disabled")))throw new Error('Invalid "target" attribute. You can\'t cut text from elements with "readonly" or "disabled" attributes');this._target=t}},get:function e(){return this._target}}]),e}();e.exports=c})},{select:5}],8:[function(t,n,o){!function(i,r){if("function"==typeof e&&e.amd)e(["module","./clipboard-action","tiny-emitter","good-listener"],r);else if("undefined"!=typeof o)r(n,t("./clipboard-action"),t("tiny-emitter"),t("good-listener"));else{var a={exports:{}};r(a,i.clipboardAction,i.tinyEmitter,i.goodListener),i.clipboard=a.exports}}(this,function(e,t,n,o){"use strict";function i(e){return e&&e.__esModule?e:{default:e}}function r(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function a(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function c(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}function l(e,t){var n="data-clipboard-"+e;if(t.hasAttribute(n))return t.getAttribute(n)}var u=i(t),s=i(n),f=i(o),d=function(){function e(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}return function(t,n,o){return n&&e(t.prototype,n),o&&e(t,o),t}}(),h=function(e){function t(e,n){r(this,t);var o=a(this,(t.__proto__||Object.getPrototypeOf(t)).call(this));return o.resolveOptions(n),o.listenClick(e),o}return c(t,e),d(t,[{key:"resolveOptions",value:function e(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};this.action="function"==typeof t.action?t.action:this.defaultAction,this.target="function"==typeof t.target?t.target:this.defaultTarget,this.text="function"==typeof t.text?t.text:this.defaultText}},{key:"listenClick",value:function e(t){var n=this;this.listener=(0,f.default)(t,"click",function(e){return n.onClick(e)})}},{key:"onClick",value:function e(t){var n=t.delegateTarget||t.currentTarget;this.clipboardAction&&(this.clipboardAction=null),this.clipboardAction=new u.default({action:this.action(n),target:this.target(n),text:this.text(n),trigger:n,emitter:this})}},{key:"defaultAction",value:function e(t){return l("action",t)}},{key:"defaultTarget",value:function e(t){var n=l("target",t);if(n)return document.querySelector(n)}},{key:"defaultText",value:function e(t){return l("text",t)}},{key:"destroy",value:function e(){this.listener.destroy(),this.clipboardAction&&(this.clipboardAction.destroy(),this.clipboardAction=null)}}],[{key:"isSupported",value:function e(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:["copy","cut"],n="string"==typeof t?[t]:t,o=!!document.queryCommandSupported;return n.forEach(function(e){o=o&&!!document.queryCommandSupported(e)}),o}}]),t}(s.default);e.exports=h})},{"./clipboard-action":7,"good-listener":4,"tiny-emitter":6}]},{},[8])(8)});

jQuery(window).load(function () {
	localStorage.removeItem('add-to-editor');

	/*
	 *	Having a drop down is a bit of overkill, so let's make it a single clickable item
	 */
	var ml_menu_item = jQuery('#nav a[href$="/extension/media_library/library/"]'),
		ml_menu_group = ml_menu_item.parents('li:last');

	ml_menu_group.find('ul').remove();

	ml_menu_group
		.addClass('ml-link')
		.css('cursor', 'pointer')
		.append('<span class="media-library-direct" />')
		.remove()
		.appendTo('#nav ul.content');

	ml_menu_group.on('click', function (e) {
		e.preventDefault();

		var href = ml_menu_item.attr('href');

		// Clicking the icon should take you to the actual page
		if ($(e.target).is('span')) {
			window.location.href = href;
			return false;
		}

		// Otherwise, let's open it in a lightbox
		var jqxhr = $.get(href, function () {
			var lightbox = $('body').append('<div class="ml-lightbox"><div class="ml-lightbox-content" /></div>').find('.ml-lightbox');
			setTimeout(function() {
				lightbox.addClass('is-visible');
			}, 10);
		});

		jqxhr.done(function(data) {
			var parser = new DOMParser(),
				doc = parser.parseFromString(data, "text/html"),
				lightbox = $('body').find('.ml-lightbox-content');

			lightbox.append($(doc).find('#context, #contents'));

			if (localStorage.getItem('add-to-editor') === 'yes') {
				lightbox.find('.ml-file .copy').text('Add to editor').addClass('add-to-editor');
			}

			Symphony.Extensions.MediaLibrary.fileUpload.init();
			Symphony.Extensions.MediaLibrary.events();
			Symphony.Extensions.MediaLibrary.getTags();
		});

		jqxhr.fail(function() {
			alert('Something went wrong. Try again.');
		});

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

			if (getUrlParameter('folder') !== '' || getUrlParameter('folder') !== undefined) ml_folder_path = getUrlParameter('folder');

			Symphony.Extensions.MediaLibrary.fileUpload.init();
			Symphony.Extensions.MediaLibrary.events();
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
		getTags : function () {
			/*
			 *	Add tags from JSON file to each row
			 */
			$.getJSON(Symphony.Context.get('root') + '/extensions/media_library/tags/tags.json', function( data ) {
				$('.ml-file .tags').each(function () {
					var src = $(this).attr('href');

					if (data[src]) $(this).attr('data-tags', data[src]);
				});
			});
		},
		events : function () {
			/*
			 *	Go forward or backwards a directory
			 */
			var base_url = Symphony.Context.get('root') + '/symphony/extension/media_library/library/?folder=';

			// Make sure 'enter' doesn't fire submission
			$('.ml-filter-files').on('keydown', function (e) {
				if (e.keyCode === 13) return false;
			});

			// Filter items
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
				$('p.name').parent().hide();
				$('p.name[data-lower*="' + value + '"], a.tags[data-tags*="' + value + '"]').parent().show();
			});

			// Toggle directories
			$('.ml-toggle-directories').on('click', function () {
				var self = $(this);

				self.prevAll('.ml-subdirectory').toggleClass('expanded');
				self.toggleClass('expanded');
			});

			// Backwards
			$('.ml-directory-back').on('click', function (e) {
				var self = $(this),
					// The handle to append, which is the full folder path minus the last folder
					handle = ml_folder_path.replace(ml_folder_path.substring(ml_folder_path.lastIndexOf('/')), '');

				if ($('.ml-lightbox').length) {
					loadMediaPage(base_url + handle);
				}
				else {
					window.location.href = base_url + handle;
				}

				return false;
			});

			// Forwards
			$('.ml-subdirectory').on('click', function () {
				var self = $(this),
					// The handle of the folder
					handle = self.data('handle');

				// If there is an existing folder path, add it to the new handle
				if (ml_folder_path) handle = ml_folder_path + '/' + handle;

				if ($('.ml-lightbox').length) {
					loadMediaPage(base_url + handle);
				}
				else {
					window.location.href = base_url + handle;
				}

				return false;
			});

			/*
			 *	Delete a file
			 */
			$('.ml-file a.delete').on('click', function (e) {
				e.preventDefault();

				var self = $(this),
					src = self.prev('a').data('src');
					check = confirm(Symphony.Language.get('Are you sure you want to delete this file? This action cannot be undone.')),
					unlink = '&unlink=' + src.replace(Symphony.Context.get('root') + '/', ''),
					href = (ml_folder_path !== '' && ml_folder_path !== undefined) ? base_url + ml_folder_path + unlink : base_url + unlink;

				// Only remove the files if the user is bloody well sure
				if (check === true) {
					if ($('.ml-lightbox').length) {
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

				return false;
			});

			$('.ml-file a.tags').on('click', 'button', function (e) {
				e.preventDefault();

				var self = $(this),
					parent = self.parent(),
					key = parent.attr('href'),
					value = self.prev().val(),
					script = Symphony.Context.get('root') + '/extensions/media_library/tags/add_tags.php';

				parent.addClass('loading');

				var jqxhr = $.get(script, {image: key, tags: value});

				jqxhr.done(function(data) {
					parent
						.removeClass('loading')
						.attr('data-tags', data)
						.trigger('click');
				});

				jqxhr.fail(function(data) {
					parent.removeClass('loading');

					alert('Something went wrong. Try again.');
					console.log(data);
				});

				return false;
			});

			/*
			 *	Copy the URL for a file
			 */
			new Clipboard('.ml-file a.copy', {
				text: function(trigger) {
					// Update the text momentarily as an indicator something has happened
					$(trigger).text(Symphony.Language.get('Copied!'));

					// Switch the text back after 2s
					setTimeout(function () {
						$(trigger).text(Symphony.Language.get('Copy to clipboard'));
					}, 2000);

					// If we are using the TinyMCE plugin as well, then add the sourse to the popup window
					if ($(trigger).hasClass('add-to-editor') && typeof ml_source_input === 'function') {
						ml_source_input($(trigger).data('src'), {alt: $(trigger).prevAll('.name').text()})
						closeLightbox();
					}

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

			/*
			 *	Close the lightbox
			 */
			function closeLightbox() {
				localStorage.removeItem('add-to-editor');
				$('body .ml-lightbox').removeClass('is-visible');
				setTimeout(function () {
					$('body .ml-lightbox').remove();
				}, 200);
			}

			// pressing ESC should close the lightbox
			$(window).on('keyup', function (e) {
				if (e.keyCode === 27 && $('.ml-lightbox').length) {
					closeLightbox();
				}
			});

			// clicking on the lightbox area should also close the lightbox
			$('.ml-lightbox').on('click', function (e) {		        
		        var container = $(this).find('> *');

		        // if the target of the click isn't the container, or a descendant of the container
		        if (!container.is(e.target) && container.has(e.target).length === 0) {
					closeLightbox();
		        }
			});
		},
		fileUpload : {
			/*
			 *	Upload Files w/ drag and drop
			 */
			init : function () {
				var fileAPI = !!window.FileReader,
					element = '<div class="media_library-upload"><div class="media_library-files empty media_library-drop"><ol /></div></div>',
					field = ($('body').find('.ml-lightbox').length) ? $('.ml-lightbox-content #context').after(element).next() : $('#context').after(element).next();

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
				var base_url = Symphony.Context.get('root') + '/symphony/extension/media_library/library/?folder=';

				Symphony.Extensions.MediaLibrary.fileUpload.stop(event);

				if ($('.ml-lightbox').length) {
					loadMediaPage(base_url + ml_folder_path);
				}
				else {
					window.location = location.href;
				}
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
					location = ml_doc_root + '/workspace/uploads/';

				if (ml_folder_path) location = location + ml_folder_path + '/';

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

			Symphony.Extensions.MediaLibrary.getTags();
			Symphony.Extensions.MediaLibrary.events();
			Symphony.Extensions.MediaLibrary.fileUpload.init();
		}
	};

	(function ($) {
		// Make sure we only execute in the Library
		if (ml_driver !== 'library') return false;

		Symphony.Extensions.MediaLibrary.init();
	})(jQuery);
});
