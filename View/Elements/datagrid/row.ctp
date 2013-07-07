<tr>
	<?php
	foreach ($rowData as $columnData) {
		?>
		<td <?php echo $columnData['htmlAttributes'];?>><?php echo $columnData['text'];?></td>
		<?php
	}
	?>
</tr>