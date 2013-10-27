<?php
foreach ($actions as $action) {
	echo $this->Html->link(
		$action['name'],
		$action['url'],
		array_merge(
			$action['options'], 
			array(
				'title' => $action['name']
			)
		)
	);
}