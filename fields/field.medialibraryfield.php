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
							-- `handle` VARCHAR(255) DEFAULT NULL,
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
			return true;
		}

		public function prePopulate(){
			return false;
		}

		public function allowDatasourceParamOutput(){
			return true;
		}

	/*-------------------------------------------------------------------------
		Utilities:
	-------------------------------------------------------------------------*/

		public function applyValidationRules($data) {
			$rule = $this->get('validator');

			return ($rule ? General::validateString($data, $rule) : true);
		}

		public function buildField($value = null, $i = -1) {
			$element_name = $this->get('element_name');

			$li = new XMLElement('li');
			if($i == -1) {
				$li->setAttribute('class', 'template');
			}

			// Header
			$header = new XMLElement('header');
			$label = !is_null($value) ? $value : __('New Field');
			$header->appendChild(new XMLElement('h4', '<strong>' . $label . '</strong>'));
			$li->appendChild($header);

			// Value
			$label = Widget::Label();
			$label->appendChild(
				Widget::Input(
					"fields[$element_name][$i][value]", General::sanitize($value), 'text', array('placeholder' => __('Value'))
				)
			);
			$li->appendChild($label);

			return $li;
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
			$wrapper->appendChild($div);
		}

		/**
		 * Save field settings in section editor.
		 */
		public function commit() {
			if(!parent::commit()) return false;

			$id = $this->get('id');
			$handle = $this->handle();

			if($id === false) return false;

			$fields = array(
				'field_id' => $id,
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
		    $caption = new XMLElement(
		    	'span',
		    	'Click to open the Media Library. From here, navigate your uploads and select the desired file.',
		    	array('class' => 'caption')
		    );

			$label->appendChild($caption);

			$label->appendChild(
				Widget::Input(
					'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[value]',
					(strlen($value) != 0 ? $value : NULL),
					'text',
					array('readonly' => true)
				)
			);
			$label->appendChild(
				Widget::Input(
					'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[name]',
					(strlen($name) != 0 ? $name : NULL),
					'text',
					array('readonly' => true)
				)
			);
			$label->appendChild(
				Widget::Input(
					'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[mime]',
					(strlen($mime) != 0 ? $mime : NULL),
					'text',
					array('readonly' => true)
				)
			);
			$label->appendChild(
				Widget::Input(
					'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[size]',
					(strlen($size) != 0 ? $size : NULL),
					'text',
					array('readonly' => true)
				)
			);
			$label->appendChild(
				Widget::Input(
					'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[unit]',
					(strlen($unit) != 0 ? $unit : NULL),
					'text',
					array('readonly' => true)
				)
			);
			$label->appendChild(
				Widget::Input(
					'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[width]',
					(strlen($width) != 0 ? $width : NULL),
					'text',
					array('readonly' => true)
				)
			);
			$label->appendChild(
				Widget::Input(
					'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix.'[height]',
					(strlen($height) != 0 ? $height : NULL),
					'text',
					array('readonly' => true)
				)
			);

			if($flagWithError != NULL) {
				$wrapper->appendChild(Widget::Error($label, $flagWithError));
			}
			else {
				$wrapper->appendChild($label);
			}

		    // Create the remove link
		    $remove = new XMLElement('a', 'Remove', array('class' => 'remove'));
			$wrapper->appendChild($remove);
		}

		public function checkPostFieldData($data, &$message, $entry_id = null) {
			$message = NULL;

			if($this->get('required') == 'yes' && strlen($data['value']) == 0){
				$message = __('‘%s’ is a required field.', array($this->get('label')));
				return self::__MISSING_FIELDS__;
			}

			if (!$this->applyValidationRules($data['value'])) {
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

			if (!is_array($data)) $data = array();

			$result = array(
				'value' => trim($data['value']),
				'name' => trim($data['name']),
				'mime' => trim($data['mime']),
				'size' => trim($data['size']),
				'unit' => trim($data['unit']),
				'width' => trim($data['width']),
				'height' => trim($data['height'])
			);

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
					'value' => $data['value'],
					'name' => $data['name'],
					'mime' => $data['mime'],
					'size' => $data['size'],
					'unit' => $data['unit'],
					'width' => $data['width'],
					'height' => $data['height']
				);
			}

			if ($data['name'] !== null) $field->setAttribute('name', $data['name']);
			if ($data['mime'] !== null) $field->setAttribute('mime', $data['mime']);
			if ($data['size'] !== null) $field->setAttribute('size', $data['size']);
			if ($data['unit'] !== null) $field->setAttribute('unit', $data['unit']);
			if ($data['width'] !== null) $field->setAttribute('width', $data['width']);
			if ($data['height'] !== null) $field->setAttribute('height', $data['height']);
			$field->setValue(
				General::sanitize($data['value'])
			);

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

			$values = is_array($data['value'])
						? implode(', ', $data['value'])
						: $data['value'];

			return parent::prepareTableValue(array('value' => $values), $link);
		}

	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/

		/**
		 * Returns the keywords that this field supports for filtering. Note
		 * that no filter will do a simple 'straight' match on the value.
		 *
		 * @since Symphony 2.6.0
		 * @return array
		 */
		public function fetchFilterableOperators() {
			return array(
				array(
					'title'				=> 'boolean',
					'filter'			=> 'boolean:',
					'help'				=> __('Find values that match the given query. Can use operators <code>and</code> and <code>not</code>.')
				),
				array(
					'title'				=> 'not-boolean',
					'filter'			=> 'not-boolean:',
					'help'				=> __('Find values that do not match the given query. Can use operators <code>and</code> and <code>not</code>.')
				),

				array(
					'title'				=> 'regexp',
					'filter'			=> 'regexp:',
					'help'				=> __('Find values that match the given <a href="%s">MySQL regular expressions</a>.', array(
						'http://dev.mysql.com/doc/mysql/en/Regexp.html'
					))
				),
				array(
					'title'				=> 'not-regexp',
					'filter'			=> 'not-regexp:',
					'help'				=> __('Find values that do not match the given <a href="%s">MySQL regular expressions</a>.', array(
						'http://dev.mysql.com/doc/mysql/en/Regexp.html'
					))
				),

				array(
					'title'				=> 'contains',
					'filter'			=> 'contains:',
					'help'				=> __('Find values that contain the given string.')
				),
				array(
					'title'				=> 'not-contains',
					'filter'			=> 'not-contains:',
					'help'				=> __('Find values that do not contain the given string.')
				),

				array(
					'title'				=> 'starts-with',
					'filter'			=> 'starts-with:',
					'help'				=> __('Find values that start with the given string.')
				),
				array(
					'title'				=> 'not-starts-with',
					'filter'			=> 'not-starts-with:',
					'help'				=> __('Find values that do not start with the given string.')
				),

				array(
					'title'				=> 'ends-with',
					'filter'			=> 'ends-with:',
					'help'				=> __('Find values that end with the given string.')
				),
				array(
					'title'				=> 'not-ends-with',
					'filter'			=> 'not-ends-with:',
					'help'				=> __('Find values that do not end with the given string.')
				),

				array(
					'title'				=> 'handle',
					'filter'			=> 'handle:',
					'help'				=> __('Find values by exact match of their handle representation only.')
				),
				array(
					'title'				=> 'not-handle',
					'filter'			=> 'not-handle:',
					'help'				=> __('Find values by exact exclusion of their handle representation only.')
				),
			);
		}

		private static function replaceAnds($data) {
			if (!preg_match('/((\W)and)|(and(\W))/i', $data)) {
				return $data;
			}

			// Negative match?
			if (preg_match('/^not(\W)/i', $data)) {
				$mode = '-';

			} else {
				$mode = '+';
			}

			// Replace ' and ' with ' +':
			$data = preg_replace('/(\W)and(\W)/i', '\\1+\\2', $data);
			$data = preg_replace('/(^)and(\W)|(\W)and($)/i', '\\2\\3', $data);
			$data = preg_replace('/(\W)not(\W)/i', '\\1-\\2', $data);
			$data = preg_replace('/(^)not(\W)|(\W)not($)/i', '\\2\\3', $data);
			$data = preg_replace('/([\+\-])\s*/', '\\1', $mode . $data);
			return $data;
		}

		public function buildDSRetrievalSQL($data, &$joins, &$where, $andOperation = false) {
			$field_id = $this->get('id');

			if (self::isFilterRegex($data[0])) {
				$this->buildRegexSQL($data[0], array('value', 'handle'), $joins, $where);
			}

			else if (preg_match('/^(not-)?boolean:\s*/', $data[0], $matches)) {
				$data = trim(array_pop(explode(':', implode(' + ', $data), 2)));
				$negate = ($matches[1] == '' ? '' : 'NOT');

				if ($data == '') return true;

				$data = self::replaceAnds($data);
				$data = $this->cleanValue($data);
				$this->_key++;
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND {$negate}(MATCH (t{$field_id}_{$this->_key}.value) AGAINST ('{$data}' IN BOOLEAN MODE))
				";
			}

			else if (preg_match('/^(not-)?((starts|ends)-with|contains):\s*/', $data[0], $matches)) {
				$data = trim(array_pop(explode(':', $data[0], 2)));
				$negate = ($matches[1] == '' ? '' : 'NOT');
				$data = $this->cleanValue($data);

				if ($matches[2] == 'ends-with') $data = "%{$data}";
				if ($matches[2] == 'starts-with') $data = "{$data}%";
				if ($matches[2] == 'contains') $data = "%{$data}%";

				$this->_key++;
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND {$negate}(
						t{$field_id}_{$this->_key}.handle LIKE '{$data}'
						OR t{$field_id}_{$this->_key}.value LIKE '{$data}'
					)
				";
			}

			else if (preg_match('/^(not-)?handle:\s*/', $data[0], $matches)) {
				$data = trim(array_pop(explode(':', implode(' + ', $data), 2)));
				$op = ($matches[1] == '' ? '=' : '!=');

				if ($data == '') return true;

				$data = $this->cleanValue($data);
				$this->_key++;
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND (t{$field_id}_{$this->_key}.handle {$op} '{$data}')
				";
			}

			else if ($andOperation) {
				foreach ($data as $value) {
					$this->_key++;
					$value = $this->cleanValue($value);
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
							ON (e.id = t{$field_id}_{$this->_key}.entry_id)
					";
					$where .= "
						AND (
							t{$field_id}_{$this->_key}.handle = '{$value}'
							OR t{$field_id}_{$this->_key}.value = '{$value}'
						)
					";
				}
			}

			else {
				if (!is_array($data)) $data = array($data);

				foreach ($data as &$value) {
					$value = $this->cleanValue($value);
				}

				$this->_key++;
				$data = implode("', '", $data);
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND (
						t{$field_id}_{$this->_key}.handle IN ('{$data}')
						OR t{$field_id}_{$this->_key}.value IN ('{$data}')
					)
				";
			}

			return true;
		}

	}
