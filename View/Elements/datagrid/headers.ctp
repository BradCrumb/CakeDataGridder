<tr>
	<?php
	foreach($headers as $header) {
		?>
		<th <?php echo $header['options']['header'];?>>
			<?php echo $header['options']['sort'] && $this->Paginator->hasPage(1)  ? $this->Paginator->sort($header['value_path'], $header['label'], array('class' => 'sort')) : $header['label'] ?>

			<?php
			if(isset($header['options']['filter']['options']) && !empty($header['options']['filter']['options'])) {
				echo $this->Html->link($header['options']['filter']['label'], '#', $header['options']['filter']['htmlAttributes']);

				?>
				<ul class="column-filter-options">
					<li <?php echo !empty($header['options']['filter']['active_field']) ?: 'class="active"';?>>
						<a href="#" title="<?php echo __('Show all');?>" data-key="" data-field="<?php echo $header['value_path'];?>"><?php echo __('Show all');?></a>
					</li>
					<?php
					foreach ($header['options']['filter']['options'] as $key => $value) {
						?>
						<li <?php echo $header['options']['filter']['active_field'] != $key ?: 'class="active"';?>>
							<a href="#" title="<?php echo $value;?>" data-key="<?php echo $key;?>" data-field="<?php echo $header['value_path'];?>"><?php echo $value;?></a>
						</li>
						<?php
					}?>
				</ul>
				<?php
			}
			?>
		</th>
		<?php
	} ?>
</tr>