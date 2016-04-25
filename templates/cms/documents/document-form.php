<script id="jqueryScript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>
<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">

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
			<div class="title">
				<label for="title">Title</label>
				<input required="required" value="<?=isset($document) ? $document->title : '' ?>" type="text" id="title" name="title" placeholder="Title" />
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
			<hr />
			<? foreach ($documentType->bricks as $brick) : ?>
				<label><?=$brick->title?></label>
				<?$fieldPrefix='bricks[' . $brick->slug . '][fields]';?>
				<input type="hidden" name="bricks[<?=$brick->slug?>][type]" value="<?=$brick->brickSlug?>" />
				<? foreach ($brick->structure->fields as $field) : ?>
					<div class="form-element">
						<label for="<?=$field->slug?>"><?=$field->title?></label>
						<? if ($field->multiple == true && $field->type != 'Rich Text') : ?>
						<ul class="grid-wrapper sortable">
							<li class="grid-container">
								<div class="grid-box-10">
									<div class="grid-inner">
						<? endif ?>
						<? if ($field->multiple == true && $field->type == 'Rich Text') : ?>
						<ul class="sortable">
							<li>
								<a class="btn error js-deletemultiple"><i class="fa fa-times"></i></a>
								<a class="btn move ui-sortable-handle"><i class="fa fa-arrows-v"></i></a>
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
						</ul>
						<a class="btn js-addmultiple">+</a>
						<? elseif ($field->multiple == true) : ?>
							</li>
							</ul>
							<a class="btn js-addrtemultiple">+</a>
						<? endif ?>
					</div>
				<? endforeach ?>
				<hr />
			<? endforeach;?>
			<? if (count($documentType->dynamicBricks) > 0) : ?>
				<label>Bricks</label>
				<select>
					<? foreach ($documentType->dynamicBricks as $dynamicBrick) : ?>
					<option value="<?=$dynamicBrick->slug?>"><?=$dynamicBrick->title?></option>
					<? endforeach ?>
				</select>
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
