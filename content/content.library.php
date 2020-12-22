<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

	Class ContentExtensionMedia_LibraryLibrary extends AdministrationPage {
		private $breadcrumbs = null;
		private $container = null;
		private $fieldset = null;

		public function view() {
			$root_url = SYMPHONY_URL . '/extension/media_library/library/';
			$root_path = DOCROOT . '/workspace/uploads/';

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
			$actions->appendChild(new XMLElement('button', __('Create Folder'), array('class' => 'button ml-trigger-directory')));
			$actions->appendChild(new XMLElement('button', __('Upload File'), array('class' => 'button ml-trigger-upload')));

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
				exit;
			}

			/*
			 *	Are we creating a folder?
			 */
			// Store the file path
			$folder_to_create = $_GET['mkdir'];
			// Make sure the folder doesn't already exist
			if (isset($folder_to_create)) {
				if (!file_exists($root_path . General::createHandle($folder_to_create))) {
					if (!mkdir($root_path . General::createHandle($folder_to_create), 0755, true)) {
						echo 'error';
					}
					else {
						echo 'success';
					}
				}
				else {
					echo 'already exists';
				}

				exit;
			}

			/*
			 *	Store any subfolder we may be in,
			 *	and the uploads file path
			 */
			$subfolder = $_GET['folder'];
			$directory_path = $root_path;

			/*
			 *	Make sure the folder exists and is writable
			 */
			if (!file_exists($directory_path) || !is_writable($directory_path)) {
				$empty = new XMLElement('div', __('The uploads folder doesn\'t exist, or isn\'t writable. Please check it exists.'), array('class' => 'ml-empty-directory'));
				$this->fieldset->appendChild($empty);

				$this->Form->appendChild($this->fieldset);
				$this->Form->setAttribute('class', 'media-library');

				return;
			}

			/*
			 *	We're within subfolders, so update the directory path to include them
			 */
			if (isset($subfolder) && $subfolder !== '') {
				$directory_path = $directory_path . $subfolder . '/';
			}

			/*
			 *	Use glob function to filter only directories
			 */
			$directories = glob($directory_path . '*', GLOB_ONLYDIR);


			/*
			 *	Container for the breadcrumbs and search field
			 */
			$header = new XMLElement('div', null, array('class' => 'ml-header'));
			$this->fieldset->appendChild($header);

			/*
			 *	Set up breadcrumb container
			 */
			$this->breadcrumbs = new XMLElement('div', null, array('class' => 'ml-breadcrumbs'));
			$header->appendChild($this->breadcrumbs);
			// Add uploads/ as root direction
			$rootBreadcrumb = new XMLElement('a', 'Uploads', array('class' => (!isset($subfolder)) ? 'button selected' : 'button', 'href' => $root_url));
			$this->breadcrumbs->appendChild($rootBreadcrumb);

			// Add all parent folders as links
			if (isset($subfolder) && is_array(preg_split('#/#', $subfolder))) {
				$subfolder_path = '?folder=';

				foreach (preg_split('#/#', $subfolder) as $key => $folder) {
					// Store the name for the anchor text
					$name = $folder;
					// Add a slash if not the first folder
					if ($key > 0) $folder = '/' . $folder;
					// Add to the folder path so the anchor is correct
					$subfolder_path .= $folder;

					// Disable the button if it's the current folder (which should always be the last one)
					$class = ($subfolder_path === '?folder=' . $subfolder) ? 'button selected' : 'button';

					// Add to the breadcrumb
					$breadcrumb = new XMLElement('a', $name, array('class' => $class, 'href' => $root_url . $subfolder_path));
					$this->breadcrumbs->appendChild($breadcrumb);
				}
			}

			// Add child directories as a selectbox
			if (!empty($directories)) {
				// The base select element with an empty first option
				$forward_directories = new XMLElement('select', null, array('class' => ''));
				$base_options = new XMLElement('option', 'Go to ...', array('value' => '0'));

				$forward_directories->appendChild($base_options);
				$this->breadcrumbs->appendChild($forward_directories);

				// Add each child folder as an option element
				// var_dump($subfolder); exit;
				foreach($directories as $directory) {
					$this_directory_path = (isset($subfolder)) ? $root_url . '?folder=' . $subfolder . '/' . basename($directory) : $root_url . '?folder=' . basename($directory);
					$this_directory = new XMLElement('option', basename($directory), array('value' => $this_directory_path));
					$forward_directories->appendChild($this_directory);
				}
			}

			/*
			 *	Add a search input
			 */
			$filter_input = new XMLElement('input', null, array('class' => 'ml-filter-files', 'placeholder' => 'Start typing to filter by name or tags'));
			$header->appendChild($filter_input);

			/*
			 *	Preview the files
			 */
			// A container holding all files
			$all_files = new XMLElement('div', null, array('class' => 'ml-files'));
			$this->fieldset->appendChild($all_files);

			// Get any file that has an extension
			$files = glob($directory_path . '*.{*}', GLOB_BRACE);
			usort($files, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));

			foreach($files as $file) {
				// $this->container = new XMLElement('div', null, array('class' => 'file-preview'));
				$this->container = new XMLElement('div');
				$this->container->setAttribute('class', 'ml-file');

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

			// Add file name
			$name = new XMLElement('p', $filename, array('class' => 'name', 'data-lower' => strtolower($filename)));

			// Add file size + extension
			$size = new XMLElement('p', $fileextension . ' / ' . $filesize, array('class' => 'size'));
			// Mime attr for searching files on
			$size->setAttribute('data-mime', $fileextension);

			// Add file resolution if it's an image
			if (in_array($fileextension, array('png', 'jpg', 'gif', 'bmp'))) {
				$imagedimensions = getimagesize($file);
				$dimensions = new XMLElement('p', $imagedimensions[0] . 'x' . $imagedimensions[1] . 'px', array('class' => 'size'));
			}

			// Add tag
			$tags = new XMLElement('a', 'Tags', array('class' => 'tags', 'href' => str_replace(URL, '', $filesrc)));

			// Add alt
			$alts = new XMLElement('a', 'Alt', array('class' => 'alts', 'href' => str_replace(URL, '', $filesrc)));

			// Add a preview link to certain file types
			if (in_array($fileextension, array('png', 'jpg', 'gif', 'bmp', 'svg', 'mp4', 'webm'))) {
				$preview = new XMLElement('a', 'Preview', array('class' => 'preview', 'href' => $filesrc, 'target' => '_blank'));
			}

			// Add an edit link to certain file types
			if (in_array($fileextension, array('png', 'jpg', 'gif'))) {
				$edit = new XMLElement('a', 'Edit', array('class' => 'edit', 'href' => $filesrc));
			}

			// Add copy and delete options
			$copy = new XMLElement('a', __('Copy file URL'), array('class' => 'copy', 'data-src' => $filesrc));
			$delete = new XMLElement('a', __('Delete'), array('class' => 'delete'));

			// Add a hidden 'meta' paragraph for storing file data
			$meta = new XMLElement('p', '', array('class' => 'meta', 'data-mime' => $fileextension, 'data-size' => $filesize));
			if (in_array($fileextension, array('png', 'jpg', 'gif', 'bmp'))) {
				$imagedimensions = getimagesize($file);
				$meta->setAttribute('data-dimensions', $imagedimensions[0] . 'x' . $imagedimensions[1] . 'px');
			}

			// Add file resolution if it's an image
			if (in_array($fileextension, array('png', 'jpg', 'gif', 'bmp'))) {
				$imagedimensions = getimagesize($file);
				$dimensions = new XMLElement('p', $imagedimensions[0] . 'x' . $imagedimensions[1] . 'px', array('class' => 'size'));
			}

			// Append all the data to the page
			$div = new XMLElement('div');
			$div->appendChild($name);
			$div->appendChild($size);
			$div->appendChild($dimensions);
			$this->container->appendChild($div);
			$div = new XMLElement('div');
			$div->appendChild($tags);
			$div->appendChild($alts);
			if (in_array($fileextension, array('png', 'jpg', 'gif'))) $div->appendChild($edit);
			if (in_array($fileextension, array('png', 'jpg', 'gif', 'bmp', 'svg', 'mp4', 'webm'))) $div->appendChild($preview);
			$div->appendChild($copy);
			$div->appendChild($delete);
			$div->appendChild($meta);
			$this->container->appendChild($div);
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
