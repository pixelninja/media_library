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

			// Default options
			$div = new XMLElement('div', null, array('class' => 'two columns'));
			$this->appendRequiredCheckbox($div);
			$this->appendShowColumnCheckbox($div);
			$div->appendChild($label);

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
				'validator' => $this->get('validator')
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

		    // Create helper caption
			if($this->get('allow_multiple_selection') == 'yes') {
				$wrapper->setAttribute('data-allow-multiple', 'yes');
			    $caption = new XMLElement(
			    	'span',
			    	'Click to open the Media Library. From here, navigate your uploads and select the desired file. Add multiple files.',
			    	array('class' => 'caption')
			    );
			}
			else {
			    $caption = new XMLElement(
			    	'span',
			    	'Click to open the Media Library. From here, navigate your uploads and select the desired file.',
			    	array('class' => 'caption')
			    );
			}

			$label->appendChild($caption);

		    $div = new XMLElement(
		    	'div',
		    	'',
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

			if($this->get('required') == 'yes' && strlen($data[0]['value']) == 0){
				$message = __('‘%s’ is a required field.', array($this->get('label')));
				return self::__MISSING_FIELDS__;
			}

			if (!$this->applyValidationRules($data[0]['value'])) {
				$message = __(
					"File chosen in ‘%s’ does not match allowable file types for that field.", array(
						$this->get('label')
					)
				);

				return self::__INVALID_FIELDS__;
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
				$value->setAttribute('name', General::sanitize($data['name'][$i]));
				$value->setAttribute('mime', $data['mime'][$i]);
				$value->setAttribute('size', $data['size'][$i]);
				$value->setAttribute('unit', $data['unit'][$i]);
				if ($data['width'][$i] !== null) $value->setAttribute('width', $data['width'][$i]);
				if ($data['height'][$i] !== null) $value->setAttribute('height', $data['height'][$i]);
				$value->setValue(
					General::sanitize($data['value'][$i])
				);

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
