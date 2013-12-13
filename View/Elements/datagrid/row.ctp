<tr<?php if(!empty($rowId)) echo ' id="' . $rowId . '"';?> data-depth="<?php echo $rowData['depth'];?>"<?php if (isset($rowData['action'])) { ?> data-action="<?php echo $rowData['action'];?>"<?php } ?>>
	<?php
	foreach ($rowData['columns'] as $columnData) {
		?>
		<td <?php echo $columnData['htmlAttributes'];?>>
			<?php
			$text = $columnData['text'];
			if ($columnData['indentOnThread']) {
				$text = '<span>' . $text . '</span>';
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