<?php

	Class extension_media_library extends Extension{

		public function fetchNavigation() {
			return array(
				array(
					'name'		=> __('Media Library'),
					'type'		=> 'content',
					'children'	=> array(
						array(
							'link'		=> '/library/',
							'name'		=> __('Media Library'),
							'visible'	=> 'yes'
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
			$callback = Administration::instance()->getPageCallback();

			$page = Administration::instance()->Page;
			$page->addStylesheetToHead(URL . '/extensions/media_library/assets/media_library.backend.css', 'screen', 666);
			$page->addScriptToHead(URL . '/extensions/media_library/assets/media_library.backend.js', 667);
		}
	}
?>
