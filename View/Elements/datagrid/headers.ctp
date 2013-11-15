<tr>
	<?php
	foreach ($headers as $header) {
		?>
		<th <?php echo $header['options']['header'];?>>
			<?php
			$sortKey = isset($header['options']['sort_key']) ? $header['options']['sort_key'] : $header['value_path'];
			
			if ($header['options']['type'] != 'actions' && (bool)$header['options']['sort'] && $this->Paginator->hasPage(1)) {
				$directionClass = 'sort';

				foreach($paging as $pModel => $pOptions) {
					// This allows the setting of a default direction class, otherwise the paginator is not supplying that
					if (is_array($pOptions) && array_key_exists('order', $pOptions) && 
						is_array($pOptions['order']) && array_key_exists($sortKey, $pOptions['order'])) {
						$directionClass .= ' ' . strtolower($pOptions['order'][$sortKey]);
						break;
					}
				}

				echo $this->Paginator->sort($sortKey, $header['label'], array('class' => $directionClass, 'model' => $model));
			} else {
				echo $header['label'];
			}

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
