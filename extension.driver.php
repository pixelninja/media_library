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
			$author = Symphony::Author();
			$callback = Administration::instance()->getPageCallback();
			$page = Administration::instance()->Page;

			$javascript = 'var user_id = "' . $author->get('id') . '",';
			$javascript .= ' user_type = "' . $author->get('user_type') . '",';
			$javascript .= ' driver = "' . $callback['driver'] . '"';
			$javascript .= (isset($_GET['folder']) && $_GET['folder'] !== '') ? ', folder_path = "' . $_GET['folder'] . '"' : ', folder_path';
			$javascript .= ';';

			$html = new XMLElement('script', $javascript, array('type'=>'text/javascript'));

			$page->addElementToHead($html);
			$page->addScriptToHead(URL . '/extensions/media_library/assets/featherlight.min.js', 667);
			$page->addScriptToHead(URL . '/extensions/media_library/assets/clipboard.min.js', 667);
			$page->addScriptToHead(URL . '/extensions/media_library/assets/media_library.backend.js', 667);
			$page->addStylesheetToHead(URL . '/extensions/media_library/assets/featherlight.min.css', 'screen', 666);
			$page->addStylesheetToHead(URL . '/extensions/media_library/assets/media_library.backend.css', 'screen', 666);
		}
	}
?>
