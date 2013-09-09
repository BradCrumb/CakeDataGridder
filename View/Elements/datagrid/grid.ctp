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
		}

		if (!empty($pagination)) {
			?>
			<tfoot>
				<tr>
					<td class="pagination" colspan="<?php echo isset($limit) ? $amountOfColumns - 1 : $amountOfColumns; ?>">
						<?php echo $pagination;?>
					</td>

					<?php
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