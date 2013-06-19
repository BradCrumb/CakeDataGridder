<tr>
	<?php
	foreach($headers as $header) {
		?>
		<th><?php echo $header['options']['sort'] ? $this->Paginator->sort($header['value_path'], $header['label']) : $header['label'] ?></th>
		<?php
	} ?>
</tr>