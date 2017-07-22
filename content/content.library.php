<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

	Class ContentExtensionMedia_LibraryLibrary extends AdministrationPage{

		public function view() {
			$this->setTitle(__('Media Library'));

			$h2 = new XMLElement('h2', __('Media Library'));

			$fieldset = new XMLElement('fieldset', null, array('class'=>'primary column'));

			$this->Context->appendChild($h2);


			// $files = glob(URL . '/workspace/uploads/*');
			// var_dump($files);

			$files = glob(DOCROOT . '/workspace/uploads/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
			$directories = glob(DOCROOT . '/workspace/uploads/*', GLOB_ONLYDIR);

			foreach($directories as $directory) {

			}

			/*
			 * TODO preview files too, not just images
			 */
			// Preview the images
			foreach($files as $file) {
				$image_dimensions = getimagesize($file);
				$image_src = str_replace(DOCROOT, URL, $file);

				$container = new XMLElement('div', null, array('class' => 'image-preview'));

				$image = new XMLElement('img', null, array('src' => $image_src));
				$image->setAttribute('data-width', $image_dimensions[0]);
				$image->setAttribute('data-height', $image_dimensions[1]);

				$container->appendChild($image);
				$fieldset->appendChild($container);
			}

			$this->Form->appendChild($fieldset);
			$this->Form->setAttribute('class', 'media-library');
		}

		public function action() {

		}
	}
