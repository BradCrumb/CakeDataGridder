<table <?php echo $options;?>>
	<?php
	if (!empty($header)) {
		?>
		<thead>
			<?php echo $header;?>
		</thead>
		<?php
	}

	if (!empty($rows)) {
		?>
		<tbody>
			<?php echo $rows;?>
		</tbody>
		<?php
	}

	if (!empty($pagination)) {
		?>
		<tfoot>
			<tr>
				<td class="pagination">
					<?php echo $pagination;?>
				</td>
			</tr>
		</tfoot>
		<?php
	}?>
</table>