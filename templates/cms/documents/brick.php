<div class="handles">
	<a class="btn error js-deletemultiple"><i class="fa fa-times"></i></a>
	<a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
</div>
<?$fieldPrefix = 'dynamicBricks[' . $brick->slug . '][' . time() . ']'?>
<? foreach ($brick->fields as $field) : ?>
			<div class="form-element">
				<label for="<?=$field->slug?>"><?=$field->title?></label>
				<? if (isset($document)) :
					$fieldSlug = $field->slug;
					$value = isset($document->fields->$fieldSlug) ? current($document->fields->$fieldSlug) : '';
				else :
					$value = '';
				endif ?>
				<? if ($field->multiple == true && $field->type != 'Rich Text') : ?>
				<ul class="grid-wrapper sortable">
					<li class="grid-container">
						<div class="grid-box-10">
							<div class="grid-inner form-element">

				<? endif ?>
				<? if ($field->multiple == true && $field->type == 'Rich Text') : ?>
					<ul class="sortable">
						<li>
							<a class="btn error js-deletemultiple"><i class="fa fa-times"></i></a>
							<a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
							<div class="form-element">
				<? endif ?>
					<? include(__DIR__ . '/fieldTypes/' . str_replace(' ', '-', $field->type) . '.php') ?>
				<? if ($field->multiple == true && $field->type != 'Rich Text') : ?>

							</div>
						</div>
						<div class="grid-box-2">
							<div class="grid-inner">
								<a class="btn error js-deletemultiple"><i class="fa fa-times"></i></a>
								<a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
							</div>
						</div>
					</li>
					<? if (isset($document)) :
						$fieldSlug = $field->slug;
						$iterable = isset($document->fields->$fieldSlug) ? $document->fields->$fieldSlug : array();
						array_shift($iterable);
						?>
						<? foreach ($iterable as $value) : ?>

						<li class="grid-container">
							<div class="grid-box-10">
								<div class="grid-inner form-element">
									<? include(__DIR__ . '/fieldTypes/' . str_replace(' ', '-', $field->type) . '.php') ?>
								</div>
							</div>
							<div class="grid-box-2">
								<div class="grid-inner">
									<a class="btn error js-deletemultiple"><i class="fa fa-times"></i></a>
									<a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
								</div>
							</div>
						</li>
						<?$value='';?>
						<? endforeach ?>
					<? endif ?>
				</ul>
				<a class="btn js-addmultiple">+</a>
				<? elseif ($field->multiple == true) : ?>
					<? if (isset($document)) :
						$fieldSlug = $field->slug;
						$iterable = $document->fields->$fieldSlug;
						array_shift($iterable);
						?>
						<? foreach ($iterable as $value) : ?>

						<li>
							<a class="btn error js-deletemultiple"><i class="fa fa-times"></i></a>
							<a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
							<div class="form-element">
							<? include(__DIR__ . '/fieldTypes/' . str_replace(' ', '-', $field->type) . '.php') ?>
							</div>
						</li>
						<?$value='';?>
					<? endforeach ?>
					<? endif ?>
					</div>
					</li>
				</ul>
				<a class="btn js-addrtemultiple">+</a>
				<? endif ?>
			</div>
			<?$value='';?>
		<? endforeach ?>
