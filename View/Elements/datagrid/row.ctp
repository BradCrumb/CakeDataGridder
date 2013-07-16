<tr data-depth="<?php echo $rowData['depth'];?>">
	<?php
	foreach ($rowData['columns'] as $columnData) {
		?>
		<td <?php echo $columnData['htmlAttributes'];?>>
			<?php
			$text = $columnData['text'];
			if($columnData['indentOnThread']) {
				$indent = str_repeat('&nbsp;', $columnData['indentSize']);
				for ($i = 0;$i < $rowData['depth'];$i++) {
					$text = $indent . $text;
				}
			}
			echo $text;?>
		</td>
		<?php
	}
	?>
</tr>