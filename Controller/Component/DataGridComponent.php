<?php
App::uses('PaginatorComponent', 'Controller/Component');

/**
 * DataGrid Component
 * ===
 *
 * @author Marc-Jan Barnhoorn <github-bradcrumb@marc-jan.nl>
 * @copyright 2013 (c), Marc-Jan Barnhoorn
 * @package CakeDataGridder
 * @license http://opensource.org/licenses/GPL-3.0 GNU GENERAL PUBLIC LICENSE
 */
class DataGridComponent extends PaginatorComponent {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Session');

/**
 * The Current Controller
 *
 * @var Controller
 */
	private $__controller;

/**
 * The Current DataGrid Session name
 *
 * @var String
 */
	private $__sessionName;

/**
 * Default settings for Paginator
 *
 * @var array
 */
	public $defaultSettings = array(
		'page' => 1,
		'limit' => 10,
		'maxLimit' => 100,
		'paramType' => 'named'
	);

/**
 * Initialize the component
 * ---
 *
 * Initialize the Paginator Component and Controller paginate params, from the Session.
 * In the Session we track all params so we can show the latest state on a refresh.
 *
 * @param  Controller $controller Current Controller
 */
	public function initialize(Controller $controller) {
		parent::initialize($controller);

		$this->__controller = $controller;

		$options = array_merge($controller->request->params, $controller->params['url'], $controller->passedArgs);

		$vars = array('page', 'sort', 'direction', 'filter', 'limit');

		$keys = array_keys($options);

		$count = count($keys);

		//Filter out all the unnecessary values
		for ($i = 0; $i < $count; $i++) {
			if (!in_array($keys[$i], $vars) || !is_string($keys[$i])) {
				unset($options[$keys[$i]]);
			}
		}

		//Set the correct session name
		$this->__sessionName = "DataGrid.{$controller->name}.{$controller->action}";

		//Save the options into the session
		if ($options) {
			if ($this->Session->check("{$this->__sessionName}.options")) {
				$options = array_merge($this->Session->read("{$this->__sessionName}.options"), $options);
			}

			$this->Session->write("{$this->__sessionName}.options", $options);
		}

		//Recall previous options
		$this->__recallOptions();

		if ($this->Session->check("{$this->__sessionName}.settings")) {
			$this->settings = array_merge($this->Session->read("{$this->__sessionName}.settings"), $this->settings);
		}

		$this->Session->write("{$this->__sessionName}.settings", $this->settings);

		//Save the filter to the session
		if ($this->Session->check("{$this->__sessionName}.filter")) {
			if (isset($controller->request->data['DataGridFilter'])) {
				$controller->request->data['DataGridFilter'] = array_merge($this->Session->read("{$this->__sessionName}.filter"), $controller->request->data['DataGridFilter']);
			} else {
				$controller->request->data['DataGridFilter'] = $this->Session->read("{$this->__sessionName}.filter");
			}
		}

		if (isset($controller->request->data['DataGridFilter'])) {
			$this->Session->write("{$this->__sessionName}.filter", $controller->request->data['DataGridFilter']);
		}

		//Save the column filter to the session
		if ($this->Session->check("{$this->__sessionName}.column_filter")) {
			if (isset($controller->request->data['DataGridColumnFilter'])) {
				$controller->request->data['DataGridColumnFilter'] = array_merge($this->Session->read("{$this->__sessionName}.column_filter"), $controller->request->data['DataGridColumnFilter']);
			} else {
				$controller->request->data['DataGridColumnFilter'] = $this->Session->read("{$this->__sessionName}.column_filter");
			}
		}

		if (isset($controller->request->data['DataGridColumnFilter'])) {
			$this->Session->write("{$this->__sessionName}.column_filter", $controller->request->data['DataGridColumnFilter']);

			foreach ($controller->request->data['DataGridColumnFilter'] as $fieldName => $value) {
				if (!empty($value)) {
					$this->settings['conditions'][$fieldName] = $value;
				} else {
					unset($this->settings['conditions'][$fieldName]);
				}
			}
		}
	}

/**
 * Inherited paginate method
 * ---
 *
 * This method inherits the paginate method from the Paginator Component.
 * Before the original method is called, we save the extra settings to the Session.
 *
 * @param Model|string $object Model to paginate (e.g: model instance, or 'Model', or 'Model.InnerModel')
 * @param string|array $scope Additional find conditions to use while paginating
 * @param array $whitelist List of allowed fields for ordering. This allows you to prevent ordering
 *   on non-indexed, or undesirable columns. See PaginatorComponent::validateSort() for additional details
 *   on how the whitelisting and sort field validation works.
 * @return array Model query results
 * @throws MissingModelException
 * @throws NotFoundException
 */
	public function paginate($object = null, $scope = array(), $whitelist = array()) {
		$this->settings = array_merge($this->defaultSettings, $this->settings);
		if ($this->Session->check("{$this->__sessionName}.settings")) {
			$this->settings = array_merge($this->Session->read("{$this->__sessionName}.settings"), $this->settings);
		}

		$this->Session->write("{$this->__sessionName}.settings", $this->settings);

		if (!$this->Session->check("{$this->__sessionName}.options.limit") && isset($this->settings['limit'])) {
			$this->Session->write("{$this->__sessionName}.options.limit", $this->settings['limit']);
		}

		//Call original paginate
		return parent::paginate($object, $scope, $whitelist);
	}

/**
 * Recall previous saved options
 * ---
 *
 * Inject the previous saved options to the controller
 */
	private function __recallOptions() {
		if ($this->Session->check("{$this->__sessionName}.options")) {
			$options = $this->Session->read("{$this->__sessionName}.options");

			$this->__controller->passedArgs = array_merge($this->__controller->passedArgs, $options);
			switch($this->settings['paramType']) {
				case 'named':
					$this->__controller->request->params['named'] = $options;
					break;
				case 'querystring':
					$this->__controller->request->query = $options;
					break;
			}
		}
	}
}