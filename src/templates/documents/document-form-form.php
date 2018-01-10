<form method="<?= isset($documentType) ? 'post' : 'get' ?>" onsubmit="return processRtes();" action="<?= isset($formId) ? '#' . $formId : '' ?>">
    <?php if (!isset($hideTitleAndState)) : ?>
      <input type="hidden" name="path" value="<?= $request::$get['path'] ?>"/>
    <?php else : ?>
      <input type="hidden" name="formId" value="<?= $formId ?>"/>
      <a name="<?= $formId ?>"></a>
    <?php endif ?>
    <?php if (isset($documentType)) : ?>
		    <?php if (!isset($hideTitleAndState)) : ?>
                <input type="hidden" name="documentType" value="<?=$documentType->slug?>" />
                <?php if (isset($document)) : ?>
                <input type="hidden" name="creationDate" value="<?=$document->creationDate?>" />
                <?php endif ?>
                <input type="hidden" name="state" value="<?=isset($document) ? $document->state : 'unpublished' ?>" />
                <div class="title">
                    <label for="title">Document Title</label>
                    <input required="required" value="<?=isset($document) ? $document->title : '' ?>"<?= isset($document, $document->title) && !empty($document->title) ? ' readonly="readonly"' : '' ?> type="text" id="title" name="title" placeholder="Title" />
                </div>
			<?php endif ?>
			<?php $fieldPrefix='fields';?>
			<?php foreach ($documentType->fields as $field) : ?>
				<div class="form-element">
					<label for="<?=$field->slug?>"><?=$field->title?></label>
					<?php if (isset($document)) :
						$fieldSlug = $field->slug;
						$value = isset($document->fields->$fieldSlug) ? current($document->fields->$fieldSlug) : '';
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
						<?php if (isset($document)) :
							$fieldSlug = $field->slug;
							$iterable = isset($document->fields->$fieldSlug) ? $document->fields->$fieldSlug : array();
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
						<?php if (isset($document)) :
							$fieldSlug = $field->slug;
							$iterable = isset($document->fields->$fieldSlug) ? $document->fields->$fieldSlug : array();
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
			<hr />
			<?php $static_brick_nr = 0;?>
			<?php foreach ($documentType->bricks as $brick) : ?>
			<div class="brick">
				<label><?=$brick->title?></label>
				<?php if ($brick->multiple == 'true') : ?>
					<input type="hidden" value="<?=$brick->brickSlug?>"/>
					<input type="hidden" value="<?=$brick->slug?>"/>
					<?$myBrickSlug=$brick->slug;?>
					<ul id="newBrickDropzone_<?=$static_brick_nr?>" class="dynamicBricks sortable">
						<?php if (isset($document)) : ?>
							<?php foreach ($document->bricks as $currentBrickSlug => $brickArray) : ?>
								<?php foreach ($brickArray as $dynamicBrick) : ?>
									<?php foreach ($bricks as $brick) :
										if (is_object($dynamicBrick) && isset($dynamicBrick->type) && $brick->slug === $dynamicBrick->type && $currentBrickSlug === $myBrickSlug) {
											break;
										}
									endforeach ?>
									<?php if (is_object($dynamicBrick) && isset($dynamicBrick->type) && $brick->slug === $dynamicBrick->type && $currentBrickSlug === $myBrickSlug) : ?>
									<li class="brick form-element">

										<?$static = true; ?>
										<?include(__DIR__ . '/brick.php')?>
									</li>
									<?php endif ?>
								<?php endforeach ?>
							<?php endforeach ?>
						<?php endif ?>
					</ul>
					<a class="btn" onclick="addDynamicBrick(this, 'true', 'newBrickDropzone_<?=$static_brick_nr?>');">+</a>
					<?$static_brick_nr += 1?>
				<?php else : ?>
					<?$fieldPrefix='bricks[' . $brick->slug . '][fields]';?>
					<input type="hidden" name="bricks[<?=$brick->slug?>][type]" value="<?=$brick->brickSlug?>" />
					<?php foreach ($brick->structure->fields as $field) : ?>
						<div class="form-element">
						<label for="<?=$field->slug?>"><?=$field->title?></label>
						<?php if (isset($document)) :
							$brickSlug = $brick->slug;
							$fieldSlug = $field->slug;
							$value = isset($document->bricks->$brickSlug->fields->$fieldSlug) ? current($document->bricks->$brickSlug->fields->$fieldSlug) : '';
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
							<?php if (isset($document)) :
								$brickSlug = $brick->slug;
								$fieldSlug = $field->slug;
								$iterable = isset($document->bricks->$brickSlug->fields->$fieldSlug) ? $document->bricks->$brickSlug->fields->$fieldSlug : array();
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
							<?php if (isset($document)) :
								$brickSlug = $brick->slug;
								$fieldSlug = $field->slug;
								$iterable = isset($document->bricks->$brickSlug->fields->$fieldSlug) ? $document->bricks->$brickSlug->fields->$fieldSlug : array();
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
				<?php endif ?>
				</div>
				<hr />
			<?php endforeach;?>



			<?php if (count($documentType->dynamicBricks) > 0) : ?>
			<div class="dynamicBrickWrapper">
				<label>Bricks</label>
				<select>
					<?php foreach ($documentType->dynamicBricks as $dynamicBrick) : ?>
					<option value="<?=$dynamicBrick->slug?>"><?=$dynamicBrick->title?></option>
					<?php endforeach ?>
				</select><a class="btn" onclick="addDynamicBrick(this, 'false', 'dynamicBrickDropzone');">+</a>
				<ul id="dynamicBrickDropzone" class="dynamicBricks sortable">
				<?php if (isset($document)) : ?>
					<?php foreach ($document->dynamicBricks as $dynamicBrick) : ?>
						<?php foreach ($bricks as $brick) :
						if ($brick->slug == $dynamicBrick->type) {
							break;
						}
						endforeach ?>
						<li class="brick form-element">
							<label><?=$brick->title?></label>
							<?$static = false; ?>
							<?include(__DIR__ . '/brick.php')?>
						</li>
					<?php endforeach ?>
				<?php endif ?>
				</ul>
			</div>
			<?php endif ?>
		<?php else : ?>
		<div class="form-element">
			<label for="documentType">Document Type</label>
			<select id="documentType" name="documentType">
				<?php foreach ($documentTypes as $documentType) : ?>
				<option value="<?=$documentType->slug?>"><?=$documentType->title?></option>
				<?php endforeach ?>
			</select>
		</div>
		<?php endif ?>
  <div class="form-element">
    <input class="btn" type="submit" value="Save"/>
    &nbsp;
    <input class="btn" type="submit" name="btn_save_and_publish" value="Save and publish"/>
  </div>
</form>