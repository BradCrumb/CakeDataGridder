<table>
	<?php
	if (!empty($header)) {
		?>
		<thead>
			<?php echo $header;?>
		</thead>
		<?php
	}?>

	<?php
	if (!empty($rows)) {
		?>
		<tbody>
			<?php echo $rows;?>
		</tbody>
		<?php
	}?>
</table>