<?php
if(!empty($filters)) {
	echo $this->Form->create(null, array('class' => 'filter_form'));

	foreach ($filters as $filter) {
		echo $this->Form->input($filter['fieldName'], $filter['options']);
	}

	$submit = isset($options['submit']) && $options['submit'] ? $options['submit'] : null;

	echo $this->Form->end($submit);
}
?>