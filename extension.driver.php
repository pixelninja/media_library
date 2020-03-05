<?php

	Class extension_media_library extends Extension{

		public function install() {
			try {
				Symphony::Database()->query("
					CREATE TABLE IF NOT EXISTS `tbl_fields_medialibraryfield` (
						`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
						`field_id` INT(11) UNSIGNED NOT NULL,
						`file_path` TEXT DEFAULT NULL,
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

			return true;
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
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'appendPageHead'
				)
			);
		}

		public function appendPageHead($context) {
			$author = Symphony::Author();
			$callback = Administration::instance()->getPageCallback();
			$page = Administration::instance()->Page;

			$javascript  = 'var ml_user_id = "' . $author->get('id') . '";';
			$javascript .= 'var ml_doc_root = "' . DOCROOT . '";';
			$javascript .= 'var ml_user_type = "' . $author->get('user_type') . '";';
			$javascript .= 'var ml_driver = "' . $callback['driver'] . '";';
			$javascript .= 'var ml_source_input;';
			$javascript .= (isset($_GET['folder']) && $_GET['folder'] !== '') ? 'var ml_folder_path = "' . $_GET['folder'] . '";' : 'var ml_folder_path;';

			$html = new XMLElement('script', $javascript, array('type'=>'text/javascript'));

			$page->addElementToHead($html);
			$page->addScriptToHead(URL . '/extensions/media_library/assets/media_library.backend.js', 667);
			$page->addStylesheetToHead(URL . '/extensions/media_library/assets/media_library.backend.css', 'screen', 666);
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
