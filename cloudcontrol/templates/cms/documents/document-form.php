<script id="jqueryScript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>
<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
<script>var smallestImage = '<?=$smallestImage?>';</script>
<?$copyable=''?>
<section class="documents">
	<h2><i class="fa fa-file-text-o"></i> Documents</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/documents" title="Back">
					Back
				</a>
			</li>
		</ul>
	</nav>
	<ul class="documents grid-wrapper">
		<li class="grid-container">
			<div class="grid-box-12">
				<i class="fa fa-terminal" title="Path"></i>
				<i id="pathHolder"><?=$request::$get['path']?></i>
			</div>
		</li>
	</ul>
	<form method="<?= isset($documentType) ? 'post' : 'get' ?>" onsubmit="return processRtes();">
		<input type="hidden" name="path" value="<?=$request::$get['path']?>" />
		<? if (isset($documentType)) : ?>
			<input type="hidden" name="documentType" value="<?=$documentType->slug?>" />
			<? if (isset($document)) : ?>
			<input type="hidden" name="creationDate" value="<?=$document->creationDate?>" />
			<? endif ?>
			<div class="title">
				<label for="title">Title</label>
				<input required="required" value="<?=isset($document) ? $document->title : '' ?>" type="text" id="title" name="title" placeholder="Title" />
			</div>
			<div class="state">
				<label for="state">Published</label>
				<input<?=isset($document) && $document->state == 'published' ? ' checked="checked"' : '' ?> type="checkbox" id="state" name="state" placeholder="State" />
			</div>
			<?$fieldPrefix='fields';?>
			<? foreach ($documentType->fields as $field) : ?>
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
							$iterable = isset($document->fields->$fieldSlug) ? $document->fields->$fieldSlug : array();
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
			<hr />
			<? $static_brick_nr = 0;?>
			<? foreach ($documentType->bricks as $brick) : ?>
			<div class="brick">
				<label><?=$brick->title?></label>
				<? if ($brick->multiple == 'true') : ?>
					<input type="hidden" value="<?=$brick->brickSlug?>"/>
					<input type="hidden" value="<?=$brick->slug?>"/>
					<?$myBrickSlug=$brick->slug;?>
					<ul id="newBrickDropzone_<?=$static_brick_nr?>" class="dynamicBricks sortable">
						<? if (isset($document)) : ?>
							<? foreach ($document->bricks as $currentBrickSlug => $brickArray) : ?>
								<? foreach ($brickArray as $dynamicBrick) : ?>
									<? foreach ($bricks as $brick) :
										if (is_object($dynamicBrick) && isset($dynamicBrick->type) && $brick->slug === $dynamicBrick->type && $currentBrickSlug === $myBrickSlug) {
											break;
										}
									endforeach ?>
									<? if (is_object($dynamicBrick) && isset($dynamicBrick->type) && $brick->slug === $dynamicBrick->type && $currentBrickSlug === $myBrickSlug) : ?>
									<li class="brick form-element">
										<label><?=$brick->title?></label>
										<?$static = true; ?>
										<?include(__DIR__ . '/brick.php')?>
									</li>
									<? endif ?>
								<? endforeach ?>
							<? endforeach ?>
						<? endif ?>
					</ul>
					<a class="btn" onclick="addDynamicBrick(this, 'true', 'newBrickDropzone_<?=$static_brick_nr?>');">+</a>
					<?$static_brick_nr += 1?>
				<? else : ?>
					<?$fieldPrefix='bricks[' . $brick->slug . '][fields]';?>
					<input type="hidden" name="bricks[<?=$brick->slug?>][type]" value="<?=$brick->brickSlug?>" />
					<? foreach ($brick->structure->fields as $field) : ?>
						<div class="form-element">
						<label for="<?=$field->slug?>"><?=$field->title?></label>
						<? if (isset($document)) :
							$brickSlug = $brick->slug;
							$fieldSlug = $field->slug;
							$value = isset($document->bricks->$brickSlug->fields->$fieldSlug) ? current($document->bricks->$brickSlug->fields->$fieldSlug) : '';
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
								$brickSlug = $brick->slug;
								$fieldSlug = $field->slug;
								$iterable = isset($document->bricks->$brickSlug->fields->$fieldSlug) ? $document->bricks->$brickSlug->fields->$fieldSlug : array();
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
								$brickSlug = $brick->slug;
								$fieldSlug = $field->slug;
								$iterable = isset($document->bricks->$brickSlug->fields->$fieldSlug) ? $document->bricks->$brickSlug->fields->$fieldSlug : array();
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
				<? endif ?>
				</div>
				<hr />
			<? endforeach;?>



			<? if (count($documentType->dynamicBricks) > 0) : ?>
			<div class="dynamicBrickWrapper">
				<label>Dynamic Bricks</label>
				<select>
					<? foreach ($documentType->dynamicBricks as $dynamicBrick) : ?>
					<option value="<?=$dynamicBrick->slug?>"><?=$dynamicBrick->title?></option>
					<? endforeach ?>
				</select><a class="btn" onclick="addDynamicBrick(this, 'false', 'dynamicBrickDropzone');">+</a>
				<ul id="dynamicBrickDropzone" class="dynamicBricks sortable">
				<? if (isset($document)) : ?>
					<? foreach ($document->dynamicBricks as $dynamicBrick) : ?>
						<? foreach ($bricks as $brick) :
						if ($brick->slug == $dynamicBrick->type) {
							break;
						}
						endforeach ?>
						<li class="brick form-element">
							<label><?=$brick->title?></label>
							<?$static = false; ?>
							<?include(__DIR__ . '/brick.php')?>
						</li>
					<? endforeach ?>
				<? endif ?>
				</ul>
			</div>
			<? endif ?>
		<? else : ?>
		<div class="form-element">
			<label for="documentType">Document Type</label>
			<select id="documentType" name="documentType">
				<? foreach ($documentTypes as $documentType) : ?>
				<option value="<?=$documentType->slug?>"><?=$documentType->title?></option>
				<? endforeach ?>
			</select>
		</div>
		<? endif ?>
		<div class="form-element">
			<input class="btn" type="submit" value="Save" />
		</div>
	</form>
</section>

<script>
	$(function() {
		"use strict";
		$( ".sortable" ).sortable({
			placeholder: "ui-state-highlight",
			axis: "y",
			forcePlaceholderSize: true,
			tolerance: "pointer",
			handle: "a.move",
			stop: function( event, ui ) {
				window.onbeforeunload = function(e) {
					return 'You have unsaved changes. Are you sure you want to leave this page?';
				};
			}
		});
		applyDeleteButtons();
		applyAddButtons();
	});
</script>
<div style="display:none;" id="cloneableCollection"></div>
