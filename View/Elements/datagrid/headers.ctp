<tr>
	<?php
	foreach($headers as $header) {
		?>
		<th <?php echo $header['options']['header'];?>><?php echo $header['options']['sort'] && $this->Paginator->hasPage(1)  ? $this->Paginator->sort($header['value_path'], $header['label'], array('class' => 'sort')) : $header['label'] ?></th>
		<?php
	} ?>
</tr>