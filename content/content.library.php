<?php

	require_once(TOOLKIT . '/class.administrationpage.php');

	Class ContentExtensionMedia_LibraryLibrary extends AdministrationPage{

		public function view() {
			$this->setTitle(__('Media Library'));

			$h2 = new XMLElement('h2', __('Media Library'));

			$fieldset = new XMLElement('fieldset', null, array('class'=>'primary column'));

			$this->Context->appendChild($h2);

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
				$image_name = str_replace('-', ' ', explode('.', basename($file))[0]);

				$container = new XMLElement('div', null, array('class' => 'image-preview'));
				$figure = new XMLElement('figure', null, array('data-imagesrc' => $image_src));

				$meta = new XMLElement('div');
				$name = new XMLElement('p', '<strong>Name: </strong><span>' . $image_name . '</span>');
				$width = new XMLElement('p', '<strong>Width: </strong><span>' . $image_dimensions[0] . 'px</span>');
				$height = new XMLElement('p', '<strong>Height: </strong><span>' . $image_dimensions[1] . 'px</span>');

				$meta->appendChild($name);
				$meta->appendChild($width);
				$meta->appendChild($height);

				$image = new XMLElement('img', null, array('src' => $image_src));
				$image->setAttribute('data-width', $image_dimensions[0]);
				$image->setAttribute('data-height', $image_dimensions[1]);

				$figure->appendChild($image);
				$container->appendChild($figure);
				$container->appendChild($meta);
				$fieldset->appendChild($container);
			}

			$this->Form->appendChild($fieldset);
			$this->Form->setAttribute('class', 'media-library');
		}

		public function action() {

		}
	}
