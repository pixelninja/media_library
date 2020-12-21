<?php
	if(!defined('__IN_SYMPHONY__')) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');

	Class fieldMediaLibraryField extends Field {

		public function __construct() {
			parent::__construct();
			$this->_name = __('Media Library Field');
			$this->_required = true;

			$this->set('required', 'no');
			$this->set('show_column', 'no');
			$this->set('location', 'sidebar');
		}

	/*-------------------------------------------------------------------------
		Setup:
	-------------------------------------------------------------------------*/

		public function createTable() {
			try {
				Symphony::Database()->query(sprintf("
						CREATE TABLE IF NOT EXISTS `tbl_entries_data_%d` (
							`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
							`entry_id` INT(11) UNSIGNED NOT NULL,
							`value` TEXT NULL,
							`name` VARCHAR(255) NULL,
							`mime` VARCHAR(255) NULL,
							`size` VARCHAR(255) NULL,
							`unit` VARCHAR(255) NULL,
							`width` VARCHAR(255) NULL,
							`height` VARCHAR(255) NULL,
							PRIMARY KEY (`id`),
							KEY `entry_id` (`entry_id`)
						) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
					", $this->get('id')
				));

				return true;
			}
			catch (Exception $ex) {
				return false;
			}
		}

		public function canFilter(){
			return false;
		}

		public function prePopulate(){
			return false;
		}

		public function allowDatasourceParamOutput(){
			return false;
		}

	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/

		public function applyValidationRules($data) {
			$rule = $this->get('validator');

			return ($rule ? General::validateString($data, $rule) : true);
		}

	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/

		/**
		 * Displays setting panel in section editor.
		 *
		 * @param XMLElement $wrapper - parent element wrapping the field
		 * @param array $errors - array with field errors, $errors['name-of-field-element']
		 */
		public function displaySettingsPanel(XMLElement &$wrapper, $errors = null) {
			// Initialize field settings based on class defaults (name, placement)
			parent::displaySettingsPanel($wrapper, $errors);

			$order = $this->get('sortorder');

			// Validator
			$div = new XMLElement('div');
			$this->buildValidationSelect(
				$div, 
				$this->get('validator'), 
				"fields[{$order}][validator]",
				'upload'
			);
			$wrapper->appendChild($div);

			$columns = new XMLElement('div', null, array('class' => 'two columns'));

			// Media Ratio
			$div = new XMLElement('div', null, array('class' => 'column'));

			$rules = array('1:1', '16:9', '9:16', '3:2', '2:3', 'landscape', 'portrait');
			$label = Widget::Label(__('Media Ratio'));
			$label->appendChild(new XMLElement('i', __('Optional')));
			$label->appendChild(Widget::Input("fields[{$order}][media_ratio]", $this->get('media_ratio')));

			$ul = new XMLElement('ul', null, array('class' => 'tags singular', 'data-interactive' => 'data-interactive'));

			foreach ($rules as $rule) {
				$ul->appendChild(new XMLElement('li', $rule, array('class' => $rule)));
			}

			$div->appendChild($label);
			$div->appendChild($ul);

			if (isset($errors['media_ratio'])) $div->appendChild(Widget::Error($div, $errors['media_ratio']));

			$columns->appendChild($div);

			// Max File Size
			$div = new XMLElement('div', null, array('class' => 'column'));

			$rules = array('300KB', '750KB', '1MB');
			$label = Widget::Label(__('Maximum File Size'));
			$label->appendChild(new XMLElement('i', __('Optional')));
			$label->appendChild(Widget::Input("fields[{$order}][max_file_size]", $this->get('max_file_size')));

			$ul = new XMLElement('ul', null, array('class' => 'tags singular', 'data-interactive' => 'data-interactive'));

			foreach ($rules as $rule) {
				$ul->appendChild(new XMLElement('li', $rule, array('class' => $rule)));
			}

			$div->appendChild($label);
			$div->appendChild($ul);

			if (isset($errors['max_file_size'])) $div->appendChild(Widget::Error($div, $errors['max_file_size']));

			$columns->appendChild($div);
			$wrapper->appendChild($columns);

			// Destination Folder
			// Add any directories (relative to the root) to ignore here, e.g '/workspace/uploads/protected'
			$ignore = array();

			// Fetch all Upload directories
			$directories = General::listDirStructure(WORKSPACE.'/uploads', null, true, DOCROOT, $ignore);

			$label = Widget::Label(__('Destination Directory'));

			$options = array();
			// Default option of base upload foldter
			$options[] = array('/workspace/uploads', false, '/workspace/uploads');

			// Add each subdirectory of uploads
			if (!empty($directories) && is_array($directories)) {
				foreach ($directories as $d) {
					$d = '/' . trim($d, '/');

					// Skip any ignored folders
					foreach ($ignore as $i) {
						if ($i === substr($d, 0, strlen($i))) {
							continue 2;
						}
					}

					$options[] = array($d, ($this->get('destination') == $d), $d);
				}
			}

			$div = new XMLElement('div');

			$label->appendChild(Widget::Select('fields['.$this->get('sortorder').'][destination]', $options));
			$label->setAttribute('class', 'column');

			if (isset($errors['destination'])) {
				$div->appendChild(Widget::Error($label, $errors['destination']));
			} else {
				$div->appendChild($label);
			}

			$wrapper->appendChild($div);

			// Default options
			$div = new XMLElement('div', null, array('class' => 'two columns'));
			$this->appendRequiredCheckbox($div);
			$this->appendShowColumnCheckbox($div);

			// Allow selection of multiple items
			$label = Widget::Label();
			$label->setAttribute('class', 'column');
			$input = Widget::Input('fields['.$order.'][allow_multiple_selection]', 'yes', 'checkbox');

			if ($this->get('allow_multiple_selection') == 'yes') {
				$input->setAttribute('checked', 'checked');
			}

			$label->setValue($input->generate() . ' ' . __('Allow selection of multiple files'));
			$div->appendChild($label);

			$wrapper->appendChild($div);
		}

		/**
		 * Save field settings in section editor.
		 */
		public function commit() {
			if(!parent::commit()) return false;

			$id = $this->get('id');
			$handle = $this->handle();
			$multiple = 'no';

			if($id === false) return false;
			if($this->get('allow_multiple_selection') !== NULL) $multiple = 'yes';

			$fields = array(
				'field_id' => $id,
				'allow_multiple_selection' => $multiple,
				'validator' => $this->get('validator'),
				'media_ratio' => $this->get('media_ratio'),
				'max_file_size' => $this->get('max_file_size'),
				'destination' => $this->get('destination')
			);

			return Symphony::Database()->insert($fields, "tbl_fields_{$handle}", true);
		}

	/*-------------------------------------------------------------------------
		Input:
	-------------------------------------------------------------------------*/

		public function displayPublishPanel(XMLElement &$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null, $entry_id = null) {
			extension_media_library::appendAssets();

			$value = $data['value'];
			$name = $data['name'];
			$mime = $data['mime'];
			$size = $data['size'];
			$unit = $data['unit'];
			$width = $data['width'];
			$height = $data['height'];

			$label = Widget::Label($this->get('label'));
			if($this->get('required') != 'yes') {
				$label->appendChild(new XMLElement('i', __('Optional')));
			}

			// var_dump($this->get('media_ratio')); exit;
			$caption_text = __('<a href="#">Click here</a> to open the Media Library and select a file.');

			// Create helper caption
			if($this->get('allow_multiple_selection') == 'yes') {
				$wrapper->setAttribute('data-allow-multiple', 'yes');
				$caption_text = __('<a href="#">Click here</a> to open the Media Library and select multiple files.');
			}

			// Add the destination directory to the field
			if (!empty($this->get('destination')) && $this->get('destination') !== '/workspace/uploads') {
				$wrapper->setAttribute('data-destination', str_replace('/workspace/uploads/', '', $this->get('destination')));
			}

			// Add on the file ratio if there is one
			if (!empty($this->get('media_ratio'))) {
				$caption_text = $caption_text . '<br />- ' . __('Media must have a ') . $this->get('media_ratio') . __(' ratio');
			}

			// Add on the max file size if there is one
			if (!empty($this->get('max_file_size'))) {
				$caption_text = $caption_text . '<br />- ' . __('Media must be ') . $this->get('max_file_size') . __(' or smaller');
			}

			$caption = new XMLElement(
				'span',
				$caption_text,
				array('class' => 'caption')
			);

			$label->appendChild($caption);

			$div = new XMLElement(
				'div',
				null,
				array('class' => 'instance', 'data-name' => $this->get('element_name'))
			);

			// No data so set up empty fields
			if (empty($data)) {
				$div->appendChild(
					Widget::Input(
						'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[0][value]',
						'', 'text', array('readonly' => true)
					)
				);
				$div->appendChild(
					Widget::Input(
						'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[0][name]',
						'', 'text', array('readonly' => true)
					)
				);
				$div->appendChild(
					Widget::Input(
						'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[0][mime]',
						'', 'text', array('readonly' => true)
					)
				);
				$div->appendChild(
					Widget::Input(
						'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[0][size]',
						'', 'text', array('readonly' => true)
					)
				);
				$div->appendChild(
					Widget::Input(
						'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[0][unit]',
						'', 'text', array('readonly' => true)
					)
				);
				$div->appendChild(
					Widget::Input(
						'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[0][width]',
						'', 'text', array('readonly' => true)
					)
				);
				$div->appendChild(
					Widget::Input(
						'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[0][height]',
						'', 'text', array('readonly' => true)
					)
				);
			}
			// We have data, so we need to display it
			else {
				// Go over each attached field type and attach the item
				foreach ($data as $key => $item) {
					
					// Make sure it's an array
					if (!is_array($item)) $item = [$item];

					foreach ($item as $k => $field) {
						$div->appendChild(
							Widget::Input(
								'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'['.$k.']['.$key.']',
								(strlen($field) != 0 ? $field : NULL),
								'text',
								array('readonly' => true)
							)
						);
					}
				}
			}

			$label->appendChild($div);

			if($flagWithError != NULL) {
				$wrapper->appendChild(Widget::Error($label, $flagWithError));
			}
			else {
				$wrapper->appendChild($label);
			}

			// Create the clear link
			$clear = new XMLElement('a', 'Clear', array('class' => 'clear'));
			$wrapper->appendChild($clear);
		}

		public function checkPostFieldData($data, &$message, $entry_id = null) {
			$message = NULL;
	
			if (!is_array($data)) $data = (array) $data;

			// Check if the field is required
			if ($this->get('required') == 'yes' && strlen($data[0]['value']) == 0) {
				$message = __('‘%s’ is a required field.', array($this->get('label')));
				return self::__MISSING_FIELDS__;
			}

			// Check each media file to see if it validates
			foreach($data as $i => $field) {
				// Check if the file format is allowed
				if (!$this->applyValidationRules($field['value'])) {
					$message = __("The file chosen in ‘%s’ does not match allowable file types for that field.", array(
							$this->get('label')
						)
					);

					return self::__INVALID_FIELDS__;
				}

				// Check if the file is within ratio
				if (!empty($this->get('media_ratio')) && !empty($field['width']) && !empty($field['height'])) {
					$image_width = (float)$field['width'];
					$image_height = (float)$field['height'];
					$max_size = $this->get('max_file_size');

					if ($this->get('media_ratio') === 'landscape') {
						if ($image_width <= $image_height) {
							$message = __("Incorrect media ratio. The file chosen in ‘%s’ should be %s.", array(
									$this->get('label'),
									$this->get('media_ratio')
								)
							);

							return self::__INVALID_FIELDS__;
						}
					}
					else if ($this->get('media_ratio') === 'portrait') {
						if ($image_width >= $image_height) {
							$message = __("Incorrect media ratio. The file chosen in ‘%s’ should be %s.", array(
									$this->get('label'),
									$this->get('media_ratio')
								)
							);

							return self::__INVALID_FIELDS__;
						}
					}
					else {
						$ratio = explode(':', $this->get('media_ratio'));
						$ratio_width = (float)$ratio[0];
						$ratio_height = (float)$ratio[1];

						if (number_format($image_width / $image_height, 2) != number_format($ratio_width / $ratio_height, 2)) {
							$message = __("Incorrect media ratio. The file chosen in ‘%s’ should be %s.", array(
									$this->get('label'),
									$this->get('media_ratio')
								)
							);

							return self::__INVALID_FIELDS__;
						}
					}
				}

				// Check if the file is under the required size
				if (!empty($this->get('max_file_size')) && !empty($field['size']) && !empty($field['unit'])) {
					$file_size = $field['size'];
					$file_unit = $field['unit'];
					$field_size = strtolower($this->get('max_file_size'));

					// convert the size of the file into bytes
					if (strtolower($file_unit) === 'gb') {
						$file_size = (int) $file_size * 1073741824;
					}
					else if (strtolower($file_unit) === 'mb') {
						$file_size = (int) $file_size * 1048576;
					}
					else if (strtolower($file_unit) === 'kb') {
						$file_size = (int) $file_size * 1024;
					}
					else if (strtolower($file_unit) === 'b') {
						$file_size = (int) $file_size;
					}

					// Convert the maximum field size into bytes
					if (substr($field_size, -2) === 'gb') {
						$field_size = (int)str_replace('gb', '', $field_size) * 1073741824;
					}
					else if (substr($field_size, -2) === 'mb') {
						$field_size = (int)str_replace('mb', '', $field_size) * 1048576;
					}
					else if (substr($field_size, -2) === 'kb') {
						$field_size = (int)str_replace('kb', '', $field_size) * 1024;
					}
					else if (substr($field_size, -1) === 'b') {
						$field_size = (int)str_replace('b', '', $field_size);
					}

					// Return an error if the file is larger than allowed
					if ($file_size > $field_size) {
						$message = __("The file chosen in ‘%s’ is too large. It should be less than %s.", array(
								$this->get('label'),
								$this->get('max_file_size')
							)
						);

						return self::__INVALID_FIELDS__;
					}
				}
			}

			return self::__OK__;
		}

		public function processRawFieldData($data, &$status, &$message=null, $simulate = false, $entry_id = null) {
			$status = self::__OK__;

			$result = array();

			if(is_array($data)) {
				if (strlen($data[0]['value']) == 0) {
					return null;
				}
				foreach($data as $i => $field) {
					$result['value'][$i] = trim($field['value']);
					$result['name'][$i] = trim($field['name']);
					$result['mime'][$i] = trim($field['mime']);
					$result['size'][$i] = trim($field['size']);
					$result['unit'][$i] = trim($field['unit']);
					$result['width'][$i] = trim($field['width']);
					$result['height'][$i] = trim($field['height']);
				}
			}

			// If there's no values, return null:
			if(empty($result)) return null;

			return $result;
		}

	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/

		public function fetchIncludableElements() {
			return array(
				$this->get('element_name')
			);
		}

		public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = null, $entry_id = null) {
			if(!is_array($data) || empty($data)) return;

			// Tag file location
			$tag_file = DOCROOT . '/extensions/media_library/json/tags.json';
			$tag_json = false;

			// If file exists, get the contents
			if (file_exists($tag_file)) {
				$tags = file_get_contents($tag_file);
				$tag_json = json_decode($tags, true);
			}

			// Alt file location
			$alts_file = DOCROOT . '/extensions/media_library/json/alts.json';
			$alts_json = false;

			// If file exists, get the contents
			if (file_exists($alts_file)) {
				$alts = file_get_contents($alts_file);
				$alts_json = json_decode($alts, true);
			}

			$field = new XMLElement($this->get('element_name'));

			if(!is_array($data['value'])) {
				$data = array(
					'value' => array($data['value']),
					'name' => array($data['name']),
					'mime' => array($data['mime']),
					'size' => array($data['size']),
					'unit' => array($data['unit']),
					'width' => array($data['width']),
					'height' => array($data['height'])
				);
			}

			for($i = 0, $ii = count($data['value']); $i < $ii; $i++) {
				$value = new XMLElement('item');
				$value->setAttribute('mime', $data['mime'][$i]);
				$value->setAttribute('size', $data['size'][$i]);
				$value->setAttribute('unit', $data['unit'][$i]);
				if ($data['width'][$i] !== null) $value->setAttribute('width', $data['width'][$i]);
				if ($data['height'][$i] !== null) $value->setAttribute('height', $data['height'][$i]);

				// If content's of alt has been retrieved, and there is a match to this image, add an alt node
				if ($alts_json && !empty($alts_json[$data['value'][$i]])) {
					$alts = new XMLElement('alt', $alts_json[$data['value'][$i]]);
					$value->appendChild($alts);
				}

				$name = new XMLElement('name', General::sanitize($data['name'][$i]));
				$filepath = new XMLElement('filepath', General::sanitize($data['value'][$i]));
				$jitfilepath = new XMLElement('jit-filepath', str_replace('/workspace', '', General::sanitize($data['value'][$i])));
				$value->appendChild($name);
				$value->appendChild($filepath);
				$value->appendChild($jitfilepath);

				// If content's of tag has been retrieved, and there is a match to this image, add a tags node
				if ($tag_json && !empty($tag_json[$data['value'][$i]])) {
					$tags = new XMLElement('tags');
					$value->appendChild($tags);

					// Add each tag as a node
					$tags_array = explode(',', $tag_json[$data['value'][$i]]);

					foreach ($tags_array as $key => $tag) {
						$tags->appendChild(new XMLElement('item', $tag));
					}
				}

				$field->appendChild($value);
			}

			$wrapper->appendChild($field);
		}

		/**
		 * At this stage we will just return the Value
		 */
		public function getParameterPoolValue(array $data, $entry_id=NULL) {
			return $data['value'];
		}

		public function prepareTableValue($data, XMLElement $link = null, $entry_id = null) {
			if(is_null($data)) return __('None');

			// $values = is_array($data['value']) ? implode(', ', $data['value']) : $data['value'];
			$values = is_array($data['value']) ? count($data['value']) . ' items' : $data['value'];

			return parent::prepareTableValue(array('value' => $values), $link);
		}
	}
