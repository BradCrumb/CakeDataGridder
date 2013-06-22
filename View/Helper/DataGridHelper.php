<?php

class DataGridHelper extends AppHelper {

	public $helpers = array('Html');

	private $__columns = array();

	private $__actions = array();

	private $__elementsDir = 'datagrid';

	private $__pluginName = null;

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);

		$explode = explode('/',realpath(__DIR__ . DS . '..' . DS . '..'));
		$this->__pluginName = end($explode);
	}

	public function addColumn($label, $valuePath, array $options = array()) {
		$defaults = array(
			'sort' => false,
			'type' => 'string',
			'htmlAttributes' => false
		);

		$options = array_merge($defaults, $options);

		$slug = Inflector::slug($label);

		$this->__columns[$slug] = array(
			'label' => $label,
			'value_path' => $valuePath,
			'options' => $options
		);

		return $slug;
	}

	public function addAction($name, array $url, array $trailingParams = array(), array $options = array(), $confirmMessage = false) {
		//Check if we already have an actions column
		if (!$this->__hasActionsColumn()) {
			$this->addColumn(__('Actions'), null, array('type' => 'actions'));
		}

		$slug = Inflector::slug($name);

		$this->__actions[$slug] = array(
			'name' => $name,
			'url' => $url,
			'trailingParams' => $trailingParams,
			'options' => $options,
			'confirmMessage' => $confirmMessage
		);

		return $slug;
	}

	private function __hasActionsColumn() {
		foreach ($this->__columns as $column) {
			if ($column['options']['type'] == 'actions') {
				return true;
			}
		}

		return false;
	}

	public function header() {
		$columns = $this->__columns;

		foreach ($this->__columns as $key => $column) {
			if ($column['options']['htmlAttributes']) {
				$columns[$key]['options']['htmlAttributes'] = $this->_parseAttributes($column['options']['htmlAttributes']);
			}
		}

		return $this->_View->element($this->__elementsDir . DS . 'headers', array(
			'headers' => $columns
		),
		array(
			'plugin' => $this->__pluginName
		));
	}

	public function rows($dataRows) {
		$rows = array();
		foreach ($dataRows as $row) {
			$row = $this->row($row);

			$rows[] = $row;
		}

		return implode("\n", $rows);
	}

	public function row($data) {
		$rowData = array();
		foreach ($this->__columns as $column) {
			$rowData[] = $this->__generateColumnData($data, $column);
		}

		return $this->_View->element($this->__pluginName . '.' . $this->__elementsDir . DS . 'row', array(
			'rowData' => $rowData
		));
	}

	private function __generateColumnData($data, $column) {
		switch($column['options']['type']) {
			case 'actions':
				$actions = array();
				foreach ($this->__actions as $action) {
					$trailingParams = array();
					if (!empty($action['trailingParams'])) {
						foreach ($action['trailingParams'] as $key => $param) {
							$trailingParams[$key] = Set::extract($param, $data);
						}
					}

					if ($action['confirmMessage']) {
						preg_match_all('/{(.*?)}/', $action['confirmMessage'], $confirmVariables);

						foreach ($confirmVariables[1] as $key => $valuePath) {
							$action['confirmMessage'] = str_replace($confirmVariables[0][$key], Set::extract($valuePath, $data), $action['confirmMessage']);
						}
					}
					$actions[] = array(
						'name' => $action['name'],
						'url' => Router::url($action['url'] + $trailingParams),
						'options' => $action['options'],
						'confirmMessage' => $action['confirmMessage']
					);
				}

				return $this->_View->element($this->__pluginName . '.' . $this->__elementsDir . DS . 'actions_column', array(
					'actions' => $actions
				));

				break;
			case 'string':
			default:
				return Set::extract($column['value_path'], $data);
		}
	}

	public function generate($data) {
		$header = $this->header();
		$rows = $this->rows($data);

		return $this->_View->element($this->__pluginName . '.' . $this->__elementsDir . DS . 'grid', array(
			'header' => $header,
			'rows' => $rows
		));
	}

	public function reset() {
		$this->__columns = array();
		$this->__actions = array();
	}
}