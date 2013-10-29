<?php
App::uses('PaginatorHelper', 'View/Helper');

/**
 * DataGrid helper
 * ===
 *
 * @author Marc-Jan Barnhoorn <github-bradcrumb@marc-jan.nl>
 * @copyright 2013 (c), Marc-Jan Barnhoorn
 * @package CakeDataGridder
 * @license http://opensource.org/licenses/GPL-3.0 GNU GENERAL PUBLIC LICENSE
 */
class DataGridPaginatorHelper extends PaginatorHelper {

/**
 * Merges passed URL options with current pagination state to generate a pagination URL.
 *
 * @param array $options Pagination/URL options array
 * @param boolean $asArray Return the url as an array, or a URI string
 * @param string $model Which model to paginate on
 * @return mixed By default, returns a full pagination URL string for use in non-standard contexts (i.e. JavaScript)
 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/paginator.html#PaginatorHelper::url
 */
	public function url($options = array(), $asArray = false, $model = null) {
		$paging = $this->params($model);
		$url = array_merge(array_filter($paging['options']), $options);

		if (isset($url['order'])) {
			$sort = $direction = null;
			if (is_array($url['order'])) {
				list($sort, $direction) = array($this->sortKey($model, $url), current($url['order']));
			}
			unset($url['order']);
			$url = array_merge($url, compact('sort', 'direction'));
		}
		$url = $this->_convertUrlKeys($url, $paging['paramType']);

		if (!empty($url['?']['page']) && $url['?']['page'] == 1) {
			unset($url['?']['page']);
		}
		if ($asArray) {
			return $url;
		}
		return parent::url($url);
	}
}