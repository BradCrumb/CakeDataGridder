<tr>
	<?php
	foreach($headers as $header) {
		?>
		<th <?php echo $header['options']['htmlAttributes'];?>><?php echo $header['options']['sort'] ? $this->Paginator->sort($header['value_path'], $header['label'], array('class' => 'sort')) : $header['label'] ?></th>
		<?php
	} ?>
</tr>