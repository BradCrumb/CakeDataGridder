<div <?php echo $options;?>>
	<?php
	if (!empty($filter)) {
		echo $filter;
	}
	?>
	<table>
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
		} else {
			?>
			<tbody>
				<tr>
					<td colspan="<?php echo $amountOfColumns; ?>">
						<?php echo $noResultsMessage;?>
					</td>
				</tr>
			</tbody>
			<?php
		}

		if (!empty($pagination) || !empty($limit)) {
			?>
			<tfoot>
				<tr>
					<?php
					if(isset($pagination)) {?>
						<td class="pagination" colspan="<?php echo isset($limit) ? $amountOfColumns - 1 : $amountOfColumns; ?>">
							<?php echo $pagination;?>
						</td>
					<?php
					}
					if(isset($limit)) {
						?>
						<td class="limit">
							<?php echo $limit;?>
						</td>
						<?php
					}
					?>
				</tr>
			</tfoot>
			<?php
		}?>
	</table>
</div>