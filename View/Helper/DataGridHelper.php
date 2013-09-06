<?php
/**
 * DataGrid helper
 * ===
 *
 * @author Marc-Jan Barnhoorn <github-bradcrumb@marc-jan.nl>
 * @copyright 2013 (c), Marc-Jan Barnhoorn
 * @package CakeDataGridder
 * @license http://opensource.org/licenses/GPL-3.0 GNU GENERAL PUBLIC LICENSE
 */
class DataGridHelper extends AppHelper {

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array('Html', 'Paginator', 'ImageCropResize.Image');

/**
 * All the columns to render
 *
 * @var array
 */
	private $__columns = array();

/**
 * Actions to add to the Actions column of a row
 *
 * @var array
 */
	private $__actions = array();

/**
 * Current filters that will be rendered
 *
 * @var array
 */
	private $__filters = array();

/**
 * The elements directory to search for elements
 *
 * @var string
 */
	private $__elementsDir = 'datagrid';

/**
 * The name of the plugin
 *
 * @var String
 */
	private $__pluginName = null;

/**
 * Default settings of the helper
 *
 * @var array
 */
	private $__defaults = array(
		'ajax' => true,							//Do we use AJAX for pagination, sorting and switching
		'update' => '#content',					//Conainer to update when we do an AJAX request
		'column' => array(						//Default settings for columns
			'sort'				=> false,		//Sorting on or off
			'type'				=> 'string',	//Type of the column
			'htmlAttributes'	=> false,		//Other HTML attributes
			'header'			=> false,		//Header settings
			'iconClass'			=> 'icon',		//Icon class
			'indentOnThread'	=> false,		//Indent on threaded data
			'indentSize'		=> 2,			//Indent size for nested grids
			'rawData'			=> false		//Place this data one on one inside the field instead of searching for data
		),
		'grid' => array(						//Default grid settings
			'class' => 'data_grid',				//Class for datagrid
		),
		'pagination' => array(					//Default settings for pagination
			'numbers' => array(					//Default settings for numbers, default wrap into an <ul>
				'tag' => 'li',
				'before' => '<ul>',
				'after' => '</ul>'
			)
		),
		'filter' => array(						//Default settings for filters
			'submit' => array()					//Settings for submit
		)
	);

/**
 * Constructor
 *
 * @param View $View The View this helper is being attached to.
 * @param array $settings Configuration settings for the helper.
 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);

		//Merge given options with the default
		$this->__defaults = array_merge($this->__defaults, $settings);

		$explode = explode('/', realpath(__DIR__ . DS . '..' . DS . '..'));
		$this->__pluginName = end($explode);
	}

/**
 * Add a column to the Grid
 * ---
 *
 * @param String $label Label for the column
 * @param String $valuePath The path of the value to search for inside the array: Hash::get compatible
 * @param array  $options Column options
 *
 * @return String Slug of the column
 */
	public function addColumn($label, $valuePath, array $options = array()) {
		//Merge given options with the default
		$options = array_merge($this->__defaults['column'], $options);

		$slug = Inflector::slug($label);

		$this->__columns[$slug] = array(
			'label' => $label,
			'value_path' => $valuePath,
			'options' => $options
		);

		return $slug;
	}

/**
 * Add multiple columns in one call
 * ---
 *
 * This method loops through all the given columns and simply calls the addColumn method
 *
 * @param array $columns multiple column data
 *
 * @throws CakeException No column label specified
 */
	public function addColumns($columns) {
		foreach ($columns as $column) {
			if (!isset($column['label'])) {
				throw new CakeException(__('No column label specified'));
			}

			$this->addColumn($column['label'], isset($column['valuePath']) ? $column['valuePath'] : null, isset($column['options']) ? $column['options'] : array());
		}
	}

/**
 * Add a search filter
 * ---
 *
 * This method adds a filter to the grid for a specified fieldName. $options are Form::input compatible
 *
 * @param String $fieldName Fieldname to filter
 * @param array  $options Form::input compatible options
 *
 * @return String Fieldname
 */
	public function addFilter($fieldName, array $options = array()) {
		$options = array_merge($this->__defaults['filter'], $options);

		$this->__filters[$fieldName] = array(
			'fieldName' => $fieldName,
			'options' => $options
		);

		return $fieldName;
	}

/**
 * Add an action to the Grid
 * ---
 *
 * This method adds an action to the datagrid. This action can be value dependend and can contain a confirm message.
 *
 * @param String  $name Name of the action
 * @param array   $url Base Url of the action
 * @param array   $trailingParams Trailing parameters of the URL, with Hash::get the correct value will be retreived
 * @param array   $options Extra options to the action link
 * @param boolean $confirmMessage Confirm message
 *
 * @return String Slug of the added action
 */
	public function addAction($name, array $url, array $trailingParams = array(), array $options = array(), $confirmMessage = false) {
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

/**
 * Checks if there is already an actions column
 * ---
 *
 * @return boolean Action columns exists or not
 */
	private function __hasActionsColumn() {
		foreach ($this->__columns as $column) {
			if ($column['options']['type'] == 'actions') {
				return true;
			}
		}

		return false;
	}

/**
 * Render the header
 * ---
 *
 * Renders the header of the grid. Also checks if an action columns is needed to render.
 *
 * @return String Rendered header
 */
	public function header() {
		//Check if we already have an actions column
		if (!empty($this->__actions) && !$this->__hasActionsColumn()) {
			$this->addColumn(__('Actions'), null, array('type' => 'actions'));
		}

		$columns = $this->__columns;

		foreach ($this->__columns as $key => $column) {
			if ($column['options']['header']) {
				$columns[$key]['options']['header'] = $this->_parseAttributes($column['options']['header']);
			}
		}

		return $this->_View->element($this->__elementsDir . DS . 'headers', array(
			'headers' => $columns
		),
		array(
			'plugin' => $this->__pluginName
		));
	}

/**
 * Render all the data rows
 * ---
 *
 * Renders all the data rows, and checks if we have to deal with threaded data.
 * When there are children the method will be recursively called until there are no children.
 *
 * @param array $dataRows Data rows
 * @param boolean $returnAsArray If we want an array of rows to return
 * @param integer $depth Current Depth of the rows
 *
 * @return String/array Rendered rows inside a String or array
 */
	public function rows($dataRows, $returnAsArray = false, $depth = 0) {
		$rows = array();
		foreach ($dataRows as $row) {
			$renderedRow = $this->row($row, $depth);

			$rows[] = $renderedRow;

			//Check if there are children and also render these rows
			$children = isset($row['children']) ? $row['children'] : null;
			if (!empty($children)) {
				$rows = array_merge($rows, $this->rows($children, true, $depth + 1));
			}
		}

		if ($returnAsArray) {
			return $rows;
		}

		return implode("\n", $rows);
	}

/**
 * Render a single row
 * ---
 *
 * @param array $data Row data
 * @param integer $depth Current depth
 *
 * @return String The rendered row
 */
	public function row($data, $depth = 0) {
		$rowData = array(
			'columns' => array(),
			'depth' => $depth
		);
		foreach ($this->__columns as $column) {
			$rowData['columns'][] = array(
				'text' => $this->__generateColumnData($data, $column),
				'htmlAttributes' => $this->_parseAttributes($column['options']['htmlAttributes']),
				'indentSize' => $column['options']['indentSize'],
				'indentOnThread' => $column['options']['indentOnThread']
			);
		}

		return $this->_View->element($this->__pluginName . '.' . $this->__elementsDir . DS . 'row', array(
			'rowData' => $rowData
		));
	}

/**
 * Render the filter
 * ---
 *
 * @param array $options Filter options
 *
 * @return String The rendered filter
 */
	public function filter(array $options = array()) {
		$options = array_merge($this->__defaults['filter'], $options);

		return $this->_View->element($this->__pluginName . '.' . $this->__elementsDir . DS . 'filter', array(
			'filters' => $this->__filters,
			'options' => $options
		));
	}

/**
 * Generate a data column
 * ---
 *
 * This method generates a data column according to it's type. The types are (switcher, actions, image, conditional, link, string)
 *
 * @param array $data Data record
 * @param array $column The column to generate
 *
 * @return String The generated column
 */
	private function __generateColumnData($data, $column) {
		$value = null;

		if (isset($column['options']['rawData']) && $column['options']['rawData']) {
			$value = $column['options']['rawData'];
		} elseif (!empty($column['value_path'])) {
			$value = Hash::get($data, $column['value_path']);
		}

		//Generate the correct column data
		switch($column['options']['type']) {
			case 'switcher':
				return $this->__switcherColumnData($value, $data, $column);
			case 'actions':
				return $this->__actionsColumnData($data);
			case 'image':
				return $this->__imageColumnData($value, $column);
			case 'conditional':
				return $this->__conditionalColumnData($data, $column);
			case 'link':
				return $this->Html->link($value, $value);
			case 'string':
			default:
				return $this->__stringColumnData($value, $data, $column);
		}
	}

/**
 * Generate a String column
 * ---
 *
 * Generates a column with String data. As extra option a url can be set so the String wil become a link.
 *
 * @param String $value Data value
 * @param array $data Data record
 * @param array $column Column options
 *
 * @return String The generated column
 */
	private function __stringColumnData($value, $data, $column) {
		if (isset($column['options']['url'])) {
			$trailingParams = array();
			if (!empty($column['options']['trailingParams'])) {
				foreach ($column['options']['trailingParams'] as $key => $param) {
					$trailingParams[$key] = Hash::get($data, $param);
				}
			}

			$url = $column['options']['url'];
			if (is_array($url)) {
				$url = $url + $trailingParams;
			}

			$value = $this->Html->link($value, $url);
		}

		return $value;
	}

/**
 * Generate a Switcher column
 * ---
 *
 * A switcher column is a field where a field can be switched between 2 states: for example active/inactive.
 * This method prepares the column so the Javascript can also handle the switch.
 *
 * @param String $value Data value
 * @param array $data Data record
 * @param array $column Column options
 *
 * @return String The generated column
 */
	private function __switcherColumnData($value, $data, $column) {
		$value = intval($value);
		$link = isset($column['options']['url']) ? $column['options']['url'] : '#';
		$icon = isset($column['options']['icon']) ? ' ' . $column['options']['iconClass'] . ' ' . $column['options']['icon'] : '';

		$class = $value == 1 ? 'enabled' : 'disabled';

		$trailingParams = array();
		if (!empty($column['options']['trailingParams'])) {
			foreach ($column['options']['trailingParams'] as $key => $param) {
				$trailingParams[$key] = Hash::get($data, $param);
			}
		}

		if (is_array($link)) {
			$link = array_merge($link, $trailingParams);
		}

		//Set the enabled/disabled labels
		$enabledLabel = isset($column['options']['label']['enabled']) ? $column['options']['label']['enabled'] : __('Enabled');
		$disabledLabel = isset($column['options']['label']['disabled']) ? $column['options']['label']['disabled'] : __('Disabled');

		$label = $value == 1 ? $enabledLabel : $disabledLabel;

		return $this->Html->link($label, $link, array('class' => 'switcher ' . $class . $icon, 'data-enabled_label' => $enabledLabel, 'data-disabled_label' => $disabledLabel));
	}

/**
 * Generate the Actions column
 * ---
 *
 * The actions column is a special column where all the actions will be added.
 *
 * @param array $data Data record
 *
 * @return String The generated actions column
 */
	private function __actionsColumnData($data) {
		$actions = array();
		foreach ($this->__actions as $action) {
			$trailingParams = array();
			if (!empty($action['trailingParams'])) {
				foreach ($action['trailingParams'] as $key => $param) {
					$trailingParams[$key] = Hash::get($data, $param);
				}
			}

			//When there is a confirm message, check the confirm message for variables and replace
			if ($action['confirmMessage']) {
				preg_match_all('/{(.*?)}/', $action['confirmMessage'], $confirmVariables);

				foreach ($confirmVariables[1] as $key => $valuePath) {
					$action['confirmMessage'] = str_replace($confirmVariables[0][$key], Hash::get($data, $valuePath), $action['confirmMessage']);
				}

				$action['options']['data-confirm_message'] = $action['confirmMessage'];

				$action['options']['class'] .= ' confirm_message';
			}

			$actions[] = array(
				'name' => $action['name'],
				'url' => $action['url'] + $trailingParams,
				'options' => $action['options']
			);
		}

		return $this->_View->element($this->__pluginName . '.' . $this->__elementsDir . DS . 'actions_column', array(
			'actions' => $actions
		));
	}

/**
 * Generate a image column
 * ---
 *
 * This method creates an image of the value that is supplied.
 *
 * @param String $value Data value
 * @param array $column Column options
 *
 * @return String The generated image column
 */
	private function __imageColumnData($value, $column) {
		if (isset($column['options']['resize']) && $column['options']['resize']) {
			$image = $this->Image->resize($value, $column['options']['resize']);
		} else {
			$image = $this->Html->image($value, $column['options']);
		}

		//When an url is supplied, wrap the image inside a link
		if (isset($column['url'])) {
			$image = $this->Html->link($image, $column['url'], array('escape' => false));
		}

		return $image;
	}

/**
 * Generate a Conditional column
 * ---
 *
 * With the Conditional column it is possible to show a value according to 1 or more conditions.
 *
 * @param array $data Data record
 * @param array $column Column options
 *
 * @return String The generated conditional column
 */
	private function __conditionalColumnData($data, $column) {
		$result = 'true';

		foreach ($column['options']['conditions'] as $key => $value) {
			if (Hash::get($data, $key) != $value) {
				$result = 'false';
				break;
			}
		}

		if (!is_array($column['options'][$result])) {
			return $column['options'][$result];
		}

		unset($column['options']['rawData']);
		$column['options'] = array_merge($column['options'], $column['options'][$result]);

		return $this->__generateColumnData($data, $column);
	}

/**
 * Generate the DataGrid
 * ---
 *
 * Generates the full DataGrid, with headers, rows and filter
 *
 * @param array $data Data record
 * @param array  $options Grid options
 *
 * @return String Full generated DataGrid
 */
	public function generate($data, array $options = array()) {
		//Render all the needed elements
		$header = $this->header();
		$rows = $this->rows($data);
		$pagination = $this->pagination();
		$filter = $this->filter();

		$options = array_merge($this->__defaults['grid'], $options);

		$options['data-update'] = $this->__defaults['update'];
		$options['data-ajax'] = $this->__defaults['ajax'];

		//Load DataGrid javascript
		$this->Html->script($this->__pluginName . '.DataGrid', array('inline' => false));

		return $this->_View->element($this->__pluginName . '.' . $this->__elementsDir . DS . 'grid', array(
			'header' => $header,
			'rows' => $rows,
			'pagination' => $pagination,
			'filter' => $filter,
			'options' => $this->_parseAttributes($options)
		));
	}

/**
 * Get the paginator numbers
 * ---
 *
 * @param array  $options paginator numbers options
 *
 * @return String Paginator numbers
 */
	public function pagination(array $options = array()) {
		$options = array_merge($this->__defaults['pagination'], $options);

		if ($this->Paginator->hasPage(1)) {
			return $this->Paginator->numbers($options['numbers']);
		}
	}

/**
 * Reset the DataGrid Columns, Actions and Filters
 * ---
 */
	public function reset() {
		$this->__columns = array();
		$this->__actions = array();
		$this->__filters = array();
	}

/**
 * Overwrite default settings
 * ---
 *
 * @param array $options Options to overwrite
 */
	public function defaults($options) {
		$this->__defaults = array_merge($this->__defaults, $options);
	}
}