<?php
foreach ($actions as $action) {
	echo $this->Html->link($action['name'], $action['url'], $action['options']);
}