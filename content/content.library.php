<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

	Class ContentExtensionMedia_LibraryLibrary extends AdministrationPage {
		private $container = null;
		private $fieldset = null;

		public function view() {

			/*
			 *	Some config
			 */
			$this->setTitle(__('Media Library'));

			$h2 = new XMLElement('h2', __('Media Library'));
			$this->Context->appendChild($h2);

			$this->fieldset = new XMLElement('fieldset', null, array('class' => 'primary column'));

			/*
			 *	Trigger button for uploading a file
			 */
			$actions = new XMLElement('div', null, array('class' => 'actions'));
			$actions->appendChild(new XMLElement('button', __('Upload File'), array('class' => 'button trigger-upload')));

			$this->Context->appendChild($actions);

			/*
			 *	Are we deleting a file?
			 */
			// Store the file path
			$file_to_delete = $_GET['unlink'];
			// Make sure the file exists
			if (isset($file_to_delete) && file_exists($file_to_delete)) {
				// Bye bye
				unlink($file_to_delete);
			}

			/*
			 *	Store any subfolder we may be in,
			 *	and the uploads file path
			 */
			$subfolder = $_GET['folder'];
			$directory_path = DOCROOT . '/workspace/uploads/';

			/*
			 *	Make sure the folder exists and is writable
			 */
			if (!file_exists($directory_path) || !is_writable($directory_path)) {
				$empty = new XMLElement('div', __('The uploads folder doesn\'t exist, or isn\'t writable. Please check it exists.'), array('class' => 'empty-directory'));
				$this->fieldset->appendChild($empty);

				$this->Form->appendChild($this->fieldset);
				$this->Form->setAttribute('class', 'media-library');

				return;
			}

			/*
			 *	Add a back button if we're within a subfolder
			 */
			if (isset($subfolder) && $subfolder !== '') {
				// We're within subfolders, so update the directory path to include them
				$directory_path = $directory_path . $subfolder . '/';

				$backcontainer = new XMLElement('div', null, array('class' => 'directory-back'));
				$back = new XMLElement('p', __('Back'));
				$backcontainer->appendChild($back);
				$this->fieldset->appendChild($backcontainer);
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
				$this->container = new XMLElement('div', null, array('class' => 'subdirectory', 'data-handle' => basename($directory)));

				// Increment the directory counter
				$directory_increment++;

				// Add in the directory info
				// (just the name at the moment. What else should I display? File count?)
				$name = new XMLElement('p', '<strong>' . basename($directory) . '</strong>');

				// Attach to the page
				$this->container->appendChild($name);
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
			// A container holding all files
			$all_files = new XMLElement('div', null, array('class' => 'files'));
			$this->fieldset->appendChild($all_files);

			// Get any file that has an extension
			$files = glob($directory_path . '*.{*}', GLOB_BRACE);

			foreach($files as $file) {
				// $this->container = new XMLElement('div', null, array('class' => 'file-preview'));
				$this->container = new XMLElement('div');
				$this->container->setAttribute('class', 'file');

				$this->showFile($file);

				$all_files->appendChild($this->container);
			}

			$this->Form->appendChild($this->fieldset);
			$this->Form->setAttribute('class', 'media-library');
		}

		/*
		 *
		 */
		public function showFile($file) {
			// File data
			$fileinfo = pathinfo($file);
			$filesize = $this->formatBytes(filesize($file), 0);
			$filename = str_replace('-', ' ', $fileinfo['filename']);
			$fileextension = $fileinfo['extension'];
			$filesrc = str_replace(DOCROOT, URL, $file);

			// Add the icon container
			$icon = new XMLElement('span', null, array('class' => 'icon-'.$fileinfo['extension']));

			// Add file name
			$name = new XMLElement('p', $filename, array('class' => 'name'));

			// Add file size + extension
			$size = new XMLElement('p', $fileextension . ' / ' . $filesize, array('class' => 'size'));

			// Add file resolution if it's an image
			if (in_array($fileextension, array('png', 'jpg', 'gif', 'bmp'))) {
				$imagedimensions = getimagesize($file);
				$dimensions = new XMLElement('p', $imagedimensions[0] . 'x' . $imagedimensions[1] . 'px', array('class' => 'size'));
			}

			// Add a preview link to certain file types
			if (in_array($fileextension, array('png', 'jpg', 'gif', 'bmp', 'svg'))) {
				$preview = new XMLElement('a', 'Preview', array('class' => 'preview', 'data-featherlight' => $filesrc));
			}

			if (in_array($fileextension, array('mp4', 'webm'))) {
				$preview = new XMLElement('a', __('Preview'), array(
					'class' => 'preview',
					'href' => $filesrc,
					'data-featherlight' => 'iframe'
				));
			}

			// Add copy and delete options
			$copy = new XMLElement('a', __('Copy to clipboard'), array('class' => 'copy', 'data-src' => $filesrc));
			$delete = new XMLElement('a', __('Delete'), array('class' => 'delete'));

			// Append all the data to the page
			$this->container->appendChild($icon);
			$this->container->appendChild($name);
			$this->container->appendChild($size);
			$this->container->appendChild($dimensions);
			if (in_array($fileextension, array('png', 'jpg', 'gif', 'bmp', 'svg', 'mp4', 'webm'))) $this->container->appendChild($preview);;
			$this->container->appendChild($copy);
			$this->container->appendChild($delete);
		}

		/*
		 * Convert bytes into readable format
		 */
		public function formatBytes($bytes, $precision = 2) {
		    $units = array('B', 'KB', 'MB', 'GB', 'TB');
		    $bytes = max($bytes, 0);
		    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));

		    $pow = min($pow, count($units) - 1);
		    $bytes /= pow(1024, $pow);

		    return round($bytes, $precision) . ' ' . $units[$pow];
		}
	}
