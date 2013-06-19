<?php

class DataGridHelper extends AppHelper {

	public $helpers = array('Html');

	private $__columns = array();

	private $__elementsDir = 'datagrid';

	private $__pluginName = null;

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);

		$explode = explode('/',realpath(__DIR__ . DS . '..' . DS . '..'));
		$this->__pluginName = end($explode);
	}

	public function addColumn($label, $valuePath, array $options = array()) {
		$defaults = array(
			'sort' => false
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

	public function header() {
		return $this->_View->element($this->__elementsDir . DS . 'headers', array(
			'headers' => $this->__columns
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
		return Set::extract($column['value_path'], $data);
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
	}
}