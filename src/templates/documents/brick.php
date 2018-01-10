<div class="handles">
  <a class="btn error js-deletemultiple"><i class="fa fa-trash"></i></a>
  <a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
</div>
<label><?= $brick->title ?></label>
<?php if ($static == true) {
    $fieldPrefix = 'bricks[' . $myBrickSlug . '][' . str_replace('.', '', str_replace(' ', '', microtime())) . '][fields]';
} else {
    $fieldPrefix = 'dynamicBricks[' . $brick->slug . '][' . str_replace('.', '', str_replace(' ', '', microtime())) . ']';
} ?>
<?php foreach ($brick->fields as $field) : ?>
			<div class="form-element">
				<label for="<?=$field->slug?>"><?=$field->title?></label>
				<?php if (isset($dynamicBrick)) :
					$fieldSlug = $field->slug;
					$value = isset($dynamicBrick->fields->$fieldSlug) ? current($dynamicBrick->fields->$fieldSlug) : '';
				else :
					$value = '';
				endif ?>
				<?php if ($field->multiple == true && $field->type != 'Rich Text') : ?>
				<ul class="grid-wrapper sortable">
					<li class="grid-container">
						<div class="grid-box-10">
							<div class="grid-inner form-element">

				<?php endif ?>
				<?php if ($field->multiple == true && $field->type == 'Rich Text') : ?>
					<ul class="sortable">
						<li>
							<a class="btn error js-deletemultiple"><i class="fa fa-trash"></i></a>
							<a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
							<div class="form-element">
				<?php endif ?>
					<?php include(__DIR__ . '/fieldTypes/' . str_replace(' ', '-', $field->type) . '.php') ?>
				<?php if ($field->multiple == true && $field->type != 'Rich Text') : ?>

							</div>
						</div>
						<div class="grid-box-2">
							<div class="grid-inner">
								<a class="btn error js-deletemultiple"><i class="fa fa-trash"></i></a>
								<a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
							</div>
						</div>
					</li>
					<?php if (isset($dynamicBrick)) :
						$fieldSlug = $field->slug;
						$iterable = isset($dynamicBrick->fields->$fieldSlug) ? $dynamicBrick->fields->$fieldSlug : array();
						array_shift($iterable);
						?>
						<?php foreach ($iterable as $value) : ?>

						<li class="grid-container">
							<div class="grid-box-10">
								<div class="grid-inner form-element">
									<?php include(__DIR__ . '/fieldTypes/' . str_replace(' ', '-', $field->type) . '.php') ?>
								</div>
							</div>
							<div class="grid-box-2">
								<div class="grid-inner">
									<a class="btn error js-deletemultiple"><i class="fa fa-trash"></i></a>
									<a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
								</div>
							</div>
						</li>
						<?$value='';?>
						<?php endforeach ?>
					<?php endif ?>
				</ul>
				<a class="btn js-addmultiple">+</a>
				<?php elseif ($field->multiple == true) : ?>
					<?php if (isset($dynamicBrick)) :
						$fieldSlug = $field->slug;
						$iterable = isset($dynamicBrick->fields->$fieldSlug) ? $dynamicBrick->fields->$fieldSlug : array();
						array_shift($iterable);
						?>
						<?php foreach ($iterable as $value) : ?>

						<li>
							<a class="btn error js-deletemultiple"><i class="fa fa-trash"></i></a>
							<a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
							<div class="form-element">
							<?php include(__DIR__ . '/fieldTypes/' . str_replace(' ', '-', $field->type) . '.php') ?>
							</div>
						</li>
						<?$value='';?>
					<?php endforeach ?>
					<?php endif ?>
					</div>
					</li>
				</ul>
				<a class="btn js-addrtemultiple">+</a>
				<?php endif ?>
			</div>
			<?$value='';?>
		<?php endforeach ?>
