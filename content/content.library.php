<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

	Class ContentExtensionMedia_LibraryLibrary extends AdministrationPage {
		private $container = null;
		private $fieldset = null;

		public function view() {
			$this->setTitle(__('Media Library'));

			$h2 = new XMLElement('h2', __('Media Library'));
			$this->Context->appendChild($h2);

			$this->fieldset = new XMLElement('fieldset', null, array('class'=>'primary column'));

			$subfolder = $_GET['folder'];
			$directory_path = DOCROOT . '/workspace/uploads/';

			/*
			 *	Add a back button if we're within a subfolder
			 */
			if (isset($subfolder) && $subfolder !== '') {
				$directory_path = $directory_path . $subfolder . '/';

				$back = new XMLElement('div', '<p>Back</p>', array('class' => 'directory-back'));
				$this->fieldset->appendChild($back);
			}

			/*
			 *	Add each directory
			 */
			// Use glob function to filter only directories
			$directories = glob($directory_path . '*', GLOB_ONLYDIR);
			// Empty value we will increment with each directory for tracking total number of directories
			$directory_increment = 0;

			foreach($directories as $directory) {
				// Add directory container
				$this->container = new XMLElement('div', null, array('class' => 'directory-preview', 'data-handle' => basename($directory)));

				// Increment the directory counter
				$directory_increment++;

				// Add in the directory info
				// (just the name at the moment. What else should I display? File count?)
				$meta = new XMLElement('div');
				$name = new XMLElement('p', '<strong>' . basename($directory) . '</strong>');

				// Attach to the page
				$meta->appendChild($name);
				$this->container->appendChild($meta);
				$this->fieldset->appendChild($this->container);
			}

			// If there are directories or a back button, show a divider between the files and directories
			if ($directory_increment > 0 || (isset($subfolder) && $subfolder !== '')) {
				$divider = new XMLElement('div', null, array('class' => 'divider'));
				$this->fieldset->appendChild($divider);
			}

			/*
			 *	Preview the files
			 */
			// Get any file that has an extension
			$files = glob($directory_path . '*.{*}', GLOB_BRACE);

			foreach($files as $file) {
				// Store the file info and create the main container
				$file_info = pathinfo($file);
				$this->container = new XMLElement('div', null, array('class' => 'file-preview'));

				// Each file type needs to be treated differently
				switch($file_info['extension']) {
					case 'jpg':
					case 'png':
					case 'gif':
						$this->previewImage($file, $file_info);

						break;
					case 'svg':
						$this->previewSVG($file, $file_info);

						break;
					case 'mp4':
						$this->previewVideo($file, $file_info);

						break;
					case 'pdf':
						$this->previewPDF($file, $file_info);

						break;
					default:
						$this->previewOther($file, $file_info);

						break;
				}

				$this->fieldset->appendChild($this->container);
			}

			$this->Form->appendChild($this->fieldset);
			$this->Form->setAttribute('class', 'media-library');
		}

		/*
		 * Display image preview and info
		 */
		public function previewImage($file, $info) {
			// Get the image dimensions
			$image_dimensions = getimagesize($file);
			// And the image source
			$image_src = str_replace(DOCROOT, URL, $file);
			// And the image name
			$image_name = str_replace('-', ' ', explode('.', basename($file))[0]);

			$figure = new XMLElement('figure', null, array('data-imagesrc' => $image_src));

			$meta = new XMLElement('div');
			$extension = new XMLElement('p', '<strong>' . $info['extension'] . '</strong>');
			$name = new XMLElement('p', $image_name);
			$dimensions = new XMLElement('p', $image_dimensions[0] . 'x' . $image_dimensions[1] . 'px');

			$meta->appendChild($extension);
			$meta->appendChild($name);
			$meta->appendChild($dimensions);

			$image = new XMLElement('img', null, array('src' => $image_src));
			$image->setAttribute('data-width', $image_dimensions[0]);
			$image->setAttribute('data-height', $image_dimensions[1]);
			$image->setAttribute('data-featherlight', $image_src);

			$figure->appendChild($image);
			$this->container->appendChild($figure);
			$this->container->appendChild($meta);
		}

		/*
		 * Display video preview and info
		 */
		public function previewVideo($file, $info) {
		}

		/*
		 * Display SVG preview and info
		 */
		public function previewSVG($file, $info) {
		}

		/*
		 * Display PDF preview and info
		 */
		public function previewPDF($file, $info) {
		}

		/*
		 * Catchall for all other file types
		 */
		public function previewOther($file, $info) {
		}

		public function action() {

		}
	}
