<?php
/**
 * DataGrid helper
 */
class DataGridHelper extends AppHelper {

	public $helpers = array('Html', 'Paginator', 'ImageCropResize.Image');

	private $__columns = array();

	private $__actions = array();

	private $__filters = array();

	private $__elementsDir = 'datagrid';

	private $__pluginName = null;

	private $__defaults = array(
		'ajax' => true,
		'update' => '#content',
		'column' => array(
			'sort'				=> false,
			'type'				=> 'string',
			'htmlAttributes'	=> false,
			'header'			=> false,
			'iconClass'			=> 'icon',
			'indentOnThread'	=> false,
			'indentSize'		=> 2,
			'rawData'			=> false
		),
		'grid' => array(
			'class' => 'data_grid'
		),
		'pagination' => array(
			'numbers' => array()
		),
		'filter' => array()
	);

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);

		$this->__defaults = array_merge($this->__defaults, $settings);

		$explode = explode('/', realpath(__DIR__ . DS . '..' . DS . '..'));
		$this->__pluginName = end($explode);
	}

	public function addColumn($label, $valuePath, array $options = array()) {
		$options = array_merge($this->__defaults['column'], $options);

		$slug = Inflector::slug($label);

		$this->__columns[$slug] = array(
			'label' => $label,
			'value_path' => $valuePath,
			'options' => $options
		);

		return $slug;
	}

	public function addColumns($columns) {
		foreach ($columns as $column) {
			if (!isset($column['label'])) {
				throw new CakeException(__('No column label specified'));
			}

			$this->addColumn($column['label'], isset($column['valuePath']) ? $column['valuePath'] : null, isset($column['options']) ? $column['options'] : array());
		}
	}

	public function addFilter($fieldName, array $options = array()) {
		$options = array_merge($this->__defaults['filter'], $options);

		$this->__filters[$fieldName] = array(
			'fieldName' => $fieldName,
			'options' => $options
		);

		return $fieldName;
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

	public function rows($dataRows, $returnAsArray = false, $depth = 0) {
		$rows = array();
		foreach ($dataRows as $row) {
			$renderedRow = $this->row($row, $depth);

			$rows[] = $renderedRow;

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

	public function filter() {
		return $this->_View->element($this->__pluginName . '.' . $this->__elementsDir . DS . 'filter', array(
			'filters' => $this->__filters
		));
	}

	private function __generateColumnData($data, $column) {
		$value = isset($column['options']['rawData']) && $column['options']['rawData'] ? $column['options']['rawData'] : Set::extract($column['value_path'], $data);
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

	private function __stringColumnData($value, $data, $column) {
		if (isset($column['options']['url'])) {
			$trailingParams = array();
			if (!empty($column['options']['trailingParams'])) {
				foreach ($column['options']['trailingParams'] as $key => $param) {
					$trailingParams[$key] = Set::extract($param, $data);
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

	private function __switcherColumnData($value, $column, $data) {
		$value = intval($value);
		$link = isset($column['options']['url']) ? $column['options']['url'] : '#';
		$icon = isset($column['options']['icon']) ? ' ' . $column['options']['iconClass'] . ' ' . $column['options']['icon'] : '';

		$class = $value == 1 ? 'enabled' : 'disabled';

		$trailingParams = array();
		if (!empty($column['options']['trailingParams'])) {
			foreach ($column['options']['trailingParams'] as $key => $param) {
				$trailingParams[$key] = Set::extract($param, $data);
			}
		}

		if (is_array($link)) {
			$link += $trailingParams;
		}

		$enabledLabel = isset($column['options']['label']['enabled']) ? $column['options']['label']['enabled'] : __('Enabled');
		$disabledLabel = isset($column['options']['label']['disabled']) ? $column['options']['label']['disabled'] : __('Disabled');

		$label = $value == 1 ? $enabledLabel : $disabledLabel;

		return $this->Html->link($label, $link, array('class' => 'switcher ' . $class . $icon));
	}

	private function __actionsColumnData($data) {
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
				'url' => $action['url'] + $trailingParams,
				'options' => $action['options'],
				'confirmMessage' => $action['confirmMessage']
			);
		}

		return $this->_View->element($this->__pluginName . '.' . $this->__elementsDir . DS . 'actions_column', array(
			'actions' => $actions
		));
	}

	private function __imageColumnData($value, $column) {
		if (isset($column['options']['resize']) && $column['options']['resize']) {
			$image = $this->Image->resize($value, $column['options']['resize']);
		} else {
			$image = $this->Html->image($value, $column['options']);
		}

		if (isset($column['url'])) {
			$image = $this->Html->link($image, $column['url'], array('escape' => false));
		}

		return $image;
	}

	private function __conditionalColumnData($data, $column) {
		$result = 'true';

		foreach ($column['options']['conditions'] as $key => $value) {
			if (Set::extract($key, $data) != $value) {
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

	public function generate($data, array $options = array()) {
		$header = $this->header();
		$rows = $this->rows($data);
		$pagination = $this->pagination();
		$filter = $this->filter();

		$options = array_merge($this->__defaults['grid'], $options);

		if (!isset($options['id'])) {
			$options['id'] = 'DataGrid';
		}

		if ($this->__defaults['ajax']) {
			$this->__addAjaxSort($options);
			$this->__addAjaxPagination($options);
			$this->__addAjaxSwitcher($options);
			$this->__addAjaxFilter($options);
		}

		$this->__expandRowEvents($options);

		return $this->_View->element($this->__pluginName . '.' . $this->__elementsDir . DS . 'grid', array(
			'header' => $header,
			'rows' => $rows,
			'pagination' => $pagination,
			'filter' => $filter,
			'options' => $this->_parseAttributes($options)
		));
	}

	private function __expandRowEvents($gridOptions) {
		$selector = '#' . $gridOptions['id'];

		$script = <<<EXPAND
			$('{$selector} tr[data-depth]').css('cursor', 'pointer');

			$('{$selector} tr[data-depth]').filter(function() {
				return $(this).data('depth') > 0;
			}).hide();

			$('{$selector} tr[data-depth]').each(function() {
				if($(this).data('depth') < $(this).next().data('depth')) {
					$(this).addClass('expandable');
				}
			});

			$('body').on('click', '{$selector} tr[data-depth]', function(ev) {
				if(ev.target.nodeName.toLowerCase() != 'a') {
					ev.preventDefault();

					var nextDepth = $(this).data('depth') + 1,
						next = $(this).next('tr[data-depth='+nextDepth+']'),
						hide = false;

					$(this).removeClass('collapsed').addClass('expanded');
					if(next.is(':visible')) {
						hide = true;
						$(this).addClass('collapsed').removeClass('expanded');
					}

					if(next.length == 0) {
						$(this).removeClass('collapsed').removeClass('expanded');
					}

					while(next.length > 0) {
						if(!hide && next.data('depth') == nextDepth) {
							next.show();
							next.removeClass('collapsed').addClass('expanded');
						}
						else {
							next.hide();
							next.addClass('collapsed').removeClass('expanded');
						}

						if(next.next().length == 0 || next.next().data('depth') <= next.data('depth')+1) {
							next.removeClass('collapsed').removeClass('expanded');
						}

						next = next.next('tr[data-depth]').filter(function() {
							return $(this).data('depth') >= nextDepth;
						});
					}
				}
			});
EXPAND;

		$this->Html->scriptBlock($script, array('inline' => false));
	}

	private function __addAjaxSort(array $gridOptions) {
		$selector = '#' . $gridOptions['id'];

		$script = <<<AJAXSORT
			$('body').on('click', '{$selector} .sort', function(ev) {
				ev.preventDefault();

				$.get($(this).attr('href'), function(data) {
					$('{$this->__defaults['update']}').html(data);
				});
			});
AJAXSORT;

		$this->Html->scriptBlock($script, array('inline' => false));
	}

	private function __addAjaxPagination(array $gridOptions) {
		$selector = '#' . $gridOptions['id'];

		$script = <<<AJAXSORT
			$('body').on('click', '{$selector} .pagination a', function(ev) {
				ev.preventDefault();

				$.get($(this).attr('href'), function(data) {
					$('{$this->__defaults['update']}').html(data);
				});
			});
AJAXSORT;

		$this->Html->scriptBlock($script, array('inline' => false));
	}

	private function __addAjaxSwitcher(array $gridOptions) {
		$selector = '#' . $gridOptions['id'];

		$script = <<<AJAXSORT
			var switcher = function(el) {
				if(el.hasClass('disabled')) {
					el.removeClass('disabled');
					el.text(1);
				}
				else {
					el.addClass('disabled');
					el.text(0);
				}
			};

			$('body').on('click', '{$selector} .switcher', function(ev) {
				ev.preventDefault();

				if($(this).attr('href') && $(this).attr('href') != '#') {
					$.post($(this).attr('href'), $.proxy(function() {
						switcher($(this));
					},this));
				}
				else {
					switcher($(this));
				}
			});
AJAXSORT;

		$this->Html->scriptBlock($script, array('inline' => false));
	}

	private function __addAjaxFilter(array $gridOptions) {
		$selector = '#' . $gridOptions['id'];

		$script = <<<AJAXSORT
			$('body').on('submit', '{$selector} .filter_form', function(ev) {
				ev.preventDefault();

				var action = $(this).attr('action');
				var search = $(this).find('.searchFormGrid').val();

				var data = $(this).serialize();

				$.post(action, data, function(html){
					$('{$this->__defaults['update']}').html(html);
				});
			});
AJAXSORT;

		$this->Html->scriptBlock($script, array('inline' => false));
	}

	public function pagination(array $options = array()) {
		$options = array_merge($this->__defaults['pagination'], $options);

		if ($this->Paginator->hasPage(1)) {
			return $this->Paginator->numbers($options['numbers']);
		}
	}

	public function reset() {
		$this->__columns = array();
		$this->__actions = array();
	}

	public function defaults($options) {
		$this->__defaults = array_merge($this->__defaults, $options);
	}
}