<?php

if (!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

Class extension_media_library extends Extension{

	public function about() {
		return array(
			'name' => 'Media Library'
		);
	}

	public function install() {
		try {
			Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_medialibraryfield` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`field_id` INT(11) UNSIGNED NOT NULL,
                	`allow_multiple_selection` enum('yes','no') NOT NULL default 'no',
                	`destination` varchar(255) NOT NULL,
					`validator` VARCHAR(255) DEFAULT NULL,
					`media_ratio` VARCHAR(10) DEFAULT NULL,
					`max_file_size` VARCHAR(10) DEFAULT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `field_id` (`field_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");
		}
		catch (Exception $ex) {
			$extension = $this->about();
			Administration::instance()->Page->pageAlert(__('An error occurred while installing %s. %s', array($extension['name'], $ex->getMessage())), Alert::ERROR);
			return false;
		}

        Symphony::Configuration()->set('min_width', '200', 'media_library');
        Symphony::Configuration()->set('max_width', '1920', 'media_library');
        Symphony::Configuration()->set('min_height', '100', 'media_library');
        Symphony::Configuration()->set('max_height', '1080', 'media_library');
        Symphony::Configuration()->set('min_file_size', '0KB', 'media_library');
        Symphony::Configuration()->set('max_file_size', '1MB', 'media_library');
        Symphony::Configuration()->set('min_image_size', '0KB', 'media_library');
        Symphony::Configuration()->set('max_image_size', '500KB', 'media_library');
        Symphony::Configuration()->set('output_quality', '70', 'media_library');

        return Symphony::Configuration()->write();
	}

	public function update($previousVersion = false){
		if(version_compare($previousVersion, '1.2.0', '<')) {
			try {
				Symphony::Database()->query("
					CREATE TABLE IF NOT EXISTS `tbl_fields_medialibraryfield` (
						`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
						`field_id` INT(11) UNSIGNED NOT NULL,
	                	`allow_multiple_selection` enum('yes','no') NOT NULL default 'no',
	                	`destination` varchar(255) NOT NULL,
						`validator` VARCHAR(255) DEFAULT NULL,
						`media_ratio` VARCHAR(10) DEFAULT NULL,
						`max_file_size` VARCHAR(10) DEFAULT NULL,
						PRIMARY KEY (`id`),
						UNIQUE KEY `field_id` (`field_id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
				");
			}
			catch (Exception $ex) {
				$extension = $this->about();
				Administration::instance()->Page->pageAlert(__('An error occurred while updating %s. %s', array($extension['name'], $ex->getMessage())), Alert::ERROR);
				return false;
			}
		}
		else if(version_compare($previousVersion, '2.0.6', '<')) {
			try {
				Symphony::Database()->query("
					ALTER TABLE  `tbl_fields_medialibraryfield` 
					ADD COLUMN `media_ratio` VARCHAR(10) DEFAULT NULL,
					ADD COLUMN `max_file_size` VARCHAR(10) DEFAULT NULL,
					ADD COLUMN `destination` VARCHAR(255) NOT NULL
				");
			}
			catch (Exception $ex) {
				$extension = $this->about();
				Administration::instance()->Page->pageAlert(__('An error occurred while updating %s. %s', array($extension['name'], $ex->getMessage())), Alert::ERROR);
				return false;
			}
		}
		else if(version_compare($previousVersion, '2.0.7', '<')) {
			try {
				Symphony::Database()->query("
					ALTER TABLE  `tbl_fields_medialibraryfield` 
					ADD COLUMN `destination` VARCHAR(255) NOT NULL
				");
			}
			catch (Exception $ex) {
				$extension = $this->about();
				Administration::instance()->Page->pageAlert(__('An error occurred while updating %s. %s', array($extension['name'], $ex->getMessage())), Alert::ERROR);
				return false;
			}
		}
        
        if (version_compare($previousVersion, '2.0.6', '<')) {
	        Symphony::Configuration()->set('min_width', '200', 'media_library');
	        Symphony::Configuration()->set('max_width', '1920', 'media_library');
	        Symphony::Configuration()->set('min_height', '100', 'media_library');
	        Symphony::Configuration()->set('max_height', '1080', 'media_library');
	        Symphony::Configuration()->set('min_file_size', '0KB', 'media_library');
	        Symphony::Configuration()->set('max_file_size', '1MB', 'media_library');
	        Symphony::Configuration()->set('min_image_size', '0KB', 'media_library');
	        Symphony::Configuration()->set('max_image_size', '500KB', 'media_library');
	        Symphony::Configuration()->set('output_quality', '70', 'media_library');
	    }

        return Symphony::Configuration()->write();
	}

	public function uninstall(){
		if(parent::uninstall() == true){
			try {
				Symphony::Database()->query("DROP TABLE `tbl_fields_medialibraryfield`");

				return true;
			}
			catch (Exception $ex) {
				$extension = $this->about();
				Administration::instance()->Page->pageAlert(__('An error occurred while uninstalling %s. %s', array($extension['name'], $ex->getMessage())), Alert::ERROR);
				return false;
			}
		}

        Symphony::Configuration()->remove('media_library');

		return false;
	}

	public function fetchNavigation() {
		return array(
			array(
				'name' => __('Media Library'),
				'type' => 'content',
				'children' => array(
					array(
						'link' => '/library/',
						'name' => __('Media Library'),
						'visible' => 'yes'
					),
				)
			)
		);
	}

	public function getSubscribedDelegates() {
		return array(
            array(
            	'page' => '/system/preferences/',
                'delegate' => 'AddCustomPreferenceFieldsets',
                'callback' => 'appendPreferences'
            ),
			array(
				'page' => '/backend/',
				'delegate' => 'InitaliseAdminPageHead',
				'callback' => 'appendPageHead'
			)
		);
	}

	/**
     * Append maintenance mode preferences
     *
     * @param array $context
     *  delegate context
     */
    public function appendPreferences($context) {
        // Create preference group
        $group = new XMLElement('fieldset');
        $group->setAttribute('class', 'settings');
        $group->appendChild(new XMLElement('legend', __('Media Library')));

        $p = new XMLElement('p', __('Update the Media Library validation defaults.'), array('class' => 'help'));

        // append intro paragraph
        $group->appendChild($p);

        $wrapper = new XMLElement('div');
        $wrapper->setAttribute('class', 'two columns');

        // Image Validation: Minimum Width
        $label = Widget::Label(__('Image Validation: Minimum Width'), null, 'column');
        $label->appendChild(Widget::Input('settings[media_library][min_width]', General::sanitize(Symphony::Configuration()->get('min_width', 'media_library'))));
        $label->appendChild(new XMLElement('p', __('The minimum image width, numerical only.'), array('class' => 'help')));
        $wrapper->appendChild($label);

        // Image Validation: Maximum Width
        $label = Widget::Label(__('Image Validation: Maximum Width'), null, 'column');
        $label->appendChild(Widget::Input('settings[media_library][max_width]', General::sanitize(Symphony::Configuration()->get('max_width', 'media_library'))));
        $label->appendChild(new XMLElement('p', __('The maximum image width, numerical only.'), array('class' => 'help')));
        $wrapper->appendChild($label);

        // Image Validation: Minimum Height
        $label = Widget::Label(__('Image Validation: Minimum Height'), null, 'column');
        $label->appendChild(Widget::Input('settings[media_library][min_height]', General::sanitize(Symphony::Configuration()->get('min_height', 'media_library'))));
        $label->appendChild(new XMLElement('p', __('The minimum image height, numerical only.'), array('class' => 'help')));
        $wrapper->appendChild($label);

        // Image Validation: Maximum Height
        $label = Widget::Label(__('Image Validation: Maximum Height'), null, 'column');
        $label->appendChild(Widget::Input('settings[media_library][max_height]', General::sanitize(Symphony::Configuration()->get('max_height', 'media_library'))));
        $label->appendChild(new XMLElement('p', __('The maximum image height, numerical only.'), array('class' => 'help')));
        $wrapper->appendChild($label);
        $group->appendChild($wrapper);

        $wrapper = new XMLElement('div');
        $wrapper->setAttribute('class', 'two columns');

        // File Size Validation: Minimum
        $label = Widget::Label(__('File Size Validation: Minimum'), null, 'column');
        $label->appendChild(Widget::Input('settings[media_library][min_file_size]', General::sanitize(Symphony::Configuration()->get('min_file_size', 'media_library'))));
        $label->appendChild(new XMLElement('p', __('The minimum size of a file, for instance 5MB or 750KB.'), array('class' => 'help')));
        $wrapper->appendChild($label);

        // File Size Validation: Maximum
        $label = Widget::Label(__('File Size Validation: Maximum'), null, 'column');
        $label->appendChild(Widget::Input('settings[media_library][max_file_size]', General::sanitize(Symphony::Configuration()->get('max_file_size', 'media_library'))));
        $label->appendChild(new XMLElement('p', __('The maximum size of a file, for instance 5MB or 750KB.'), array('class' => 'help')));
        $wrapper->appendChild($label);

        // Image Size Validation: Minimum
        $label = Widget::Label(__('Image Size Validation: Minimum'), null, 'column');
        $label->appendChild(Widget::Input('settings[media_library][min_image_size]', General::sanitize(Symphony::Configuration()->get('min_image_size', 'media_library'))));
        $label->appendChild(new XMLElement('p', __('The minimum size of an image, for instance 5MB or 750KB.'), array('class' => 'help')));
        $wrapper->appendChild($label);

        // Image Size Validation: Maximum
        $label = Widget::Label(__('Image Size Validation: Maximum'), null, 'column');
        $label->appendChild(Widget::Input('settings[media_library][max_image_size]', General::sanitize(Symphony::Configuration()->get('max_image_size', 'media_library'))));
        $label->appendChild(new XMLElement('p', __('The maximum size of an image, for instance 5MB or 750KB.'), array('class' => 'help')));
        $wrapper->appendChild($label);

        $group->appendChild($wrapper);

        // Output Quality
        $label = Widget::Label(__('Output Quality'));
        $label->appendChild(Widget::Input('settings[media_library][output_quality]', General::sanitize(Symphony::Configuration()->get('output_quality', 'media_library'))));
        $group->appendChild($label);
        $group->appendChild(new XMLElement('p', __('This field only applies when using Doka integration. Value between 0 and 100.'), array('class' => 'help')));

        // Append new preference group
        $context['wrapper']->appendChild($group);
    }

	public function appendPageHead($context) {
		$author = Symphony::Author();
		$callback = Administration::instance()->getPageCallback();
		$page = Administration::instance()->Page;

		$minWidth = (Symphony::Configuration()->get('min_width', 'media_library')) ? Symphony::Configuration()->get('min_width', 'media_library') : 200;
		$maxWidth = (Symphony::Configuration()->get('max_width', 'media_library')) ? Symphony::Configuration()->get('max_width', 'media_library') : 1920;
		$minHeight = (Symphony::Configuration()->get('min_height', 'media_library')) ? Symphony::Configuration()->get('min_height', 'media_library') : 100;
		$maxHeight = (Symphony::Configuration()->get('max_height', 'media_library')) ? Symphony::Configuration()->get('max_height', 'media_library') : 1080;
		$minFileSize = (Symphony::Configuration()->get('min_file_size', 'media_library')) ? Symphony::Configuration()->get('min_file_size', 'media_library') : 100;
		$maxFileSize = (Symphony::Configuration()->get('max_file_size', 'media_library')) ? Symphony::Configuration()->get('max_file_size', 'media_library') : 1080;
		$minImageSize = (Symphony::Configuration()->get('min_image_size', 'media_library')) ? Symphony::Configuration()->get('min_image_size', 'media_library') : 100;
		$maxImageSize = (Symphony::Configuration()->get('max_image_size', 'media_library')) ? Symphony::Configuration()->get('max_image_size', 'media_library') : 1080;
		$outputQuality = (Symphony::Configuration()->get('output_quality', 'media_library')) ? Symphony::Configuration()->get('output_quality', 'media_library') : 70;

		$javascript  = 'var ml_user_id = "' . $author->get('id') . '";';
		$javascript .= 'var ml_doc_root = "' . DOCROOT . '";';
		$javascript .= 'var ml_user_type = "' . $author->get('user_type') . '";';
		$javascript .= 'var ml_driver = "' . $callback['driver'] . '";';
		$javascript .= 'var ml_source_input;';
		$javascript .= 'var ml_image_settings = {';
		$javascript .= 'minWidth: ' . $minWidth . ',';
		$javascript .= 'maxWidth: ' . $maxWidth . ',';
		$javascript .= 'minHeight: ' . $minHeight . ',';
		$javascript .= 'maxHeight: ' . $maxHeight . ',';
		$javascript .= 'minFileSize: "' . $minFileSize . '",';
		$javascript .= 'maxFileSize: "' . $maxFileSize . '",';
		$javascript .= 'minImageSize: "' . $minImageSize . '",';
		$javascript .= 'maxImageSize: "' . $maxImageSize . '",';
		$javascript .= 'outputQuality: ' . $outputQuality;
		$javascript .= '};';
		$javascript .= (isset($_GET['folder']) && $_GET['folder'] !== '') ? 'var ml_folder_path = "' . $_GET['folder'] . '";' : 'var ml_folder_path;';

		$html = new XMLElement('script', $javascript, array('type'=>'text/javascript'));

		$page->addElementToHead($html);

		$page->addScriptToHead('https://unpkg.com/filepond-plugin-image-edit', 665);
		$page->addScriptToHead('https://unpkg.com/filepond-plugin-image-preview', 665);
		$page->addScriptToHead('https://unpkg.com/filepond-plugin-image-exif-orientation', 665);
		$page->addScriptToHead('https://unpkg.com/filepond-plugin-image-crop', 665);
		$page->addScriptToHead('https://unpkg.com/filepond-plugin-image-resize', 665);
		$page->addScriptToHead('https://unpkg.com/filepond-plugin-image-transform', 665);
		$page->addScriptToHead('https://unpkg.com/filepond-plugin-image-validate-size', 665);
		$page->addScriptToHead('https://unpkg.com/filepond-plugin-file-validate-size', 665);
		$page->addScriptToHead('https://unpkg.com/filepond/dist/filepond.min.js', 666);
		if (file_exists(DOCROOT . '/doka/doka.min.js')) {
			$page->addScriptToHead(URL . '/doka/doka.min.js', 667);
		}

		$page->addScriptToHead(URL . '/extensions/media_library/assets/media_library.backend.js', 667);
		$page->addStylesheetToHead(URL . '/extensions/media_library/assets/media_library.backend.css', 'screen', 666);

		if (file_exists(DOCROOT . '/doka/doka.min.css')) {
			$page->addStylesheetToHead(URL . '/doka/doka.min.css', 'screen', 666);
		}
		$page->addStylesheetToHead('https://unpkg.com/filepond/dist/filepond.css', 'screen', 665);
		$page->addStylesheetToHead('https://unpkg.com/filepond-plugin-image-edit/dist/filepond-plugin-image-edit.css', 'screen', 666);
		$page->addStylesheetToHead('https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css', 'screen', 666);
	}

	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/
	public static function appendAssets() {
		if(class_exists('Administration')
			&& Administration::instance() instanceof Administration
			&& Administration::instance()->Page instanceof HTMLPage
		) {
			Administration::instance()->Page->addStylesheetToHead(URL . '/extensions/media_library/assets/media_library.field.css', 'screen', 100, false);
			Administration::instance()->Page->addScriptToHead(URL . '/extensions/media_library/assets/media_library.field.js', 100, false);
		}
	}
}
?>
