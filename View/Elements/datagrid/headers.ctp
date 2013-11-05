<tr>
	<?php
	foreach ($headers as $header) {
		?>
		<th <?php echo $header['options']['header'];?>>
			<?php
			$sortKey = isset($header['options']['sort_key']) ? $header['options']['sort_key'] : $header['value_path'];
			echo $header['options']['sort'] && $this->Paginator->hasPage(1) ? $this->Paginator->sort($sortKey, $header['label'], array('class' => 'sort', 'model' => $model)) : $header['label'] ?>

			<?php
			if (isset($header['options']['filter']['options']) && !empty($header['options']['filter']['options'])) {
				echo $this->Html->link($header['options']['filter']['label'], '#', $header['options']['filter']['htmlAttributes']);

				?>
				<ul class="column-filter-options">
					<li <?php
						if (empty($header['options']['filter']['active_field'])) {
							echo 'class="active"';
						}?>>
						<a 	href="#"
							title="<?php echo __('Show all');?>"
							data-key=""
							data-field="<?php echo $header['value_path'];?>">
							<?php echo __('Show all');?>
						</a>
					</li>
					<?php
					foreach ($header['options']['filter']['options'] as $key => $value) {
						if (is_array($value)) {
							debug($value);
						}
						?>
						<li <?php
							if ($header['options']['filter']['active_field'] == $key) {
								echo 'class="active"';
							}?>>
							<a 	href="#"
								title="<?php echo $value;?>"
								data-key="<?php echo $key;?>"
								data-field="<?php echo $header['value_path'];?>">
								<?php echo $value;?>
							</a>
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