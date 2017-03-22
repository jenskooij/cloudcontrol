<section class="dashboard configuration">
	<h2><i class="fa fa-cogs"></i> <a href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration">Configuration</a> &raquo; Document Types</h2>
	<nav class="actions">
		<ul>
			<li>
				<a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/configuration/document-types" title="Back">Back</a>
			</li>
		</ul>
	</nav>
	<form method="post" class="panel" id="documentTypesForm">
		<div class="form-element">
			<label for="title">Title</label>
			<input required="required" id="title" type="text" name="title" placeholder="Title" value="<?=isset($documentType) ? $documentType->title : '' ?>" />
		</div>
		<div class="form-element">
			<label>Fields</label>
			<ul id="dropZone" class="sortable">
			<? if (isset($documentType)) : ?>
				<? foreach ($documentType->fields as $field) : ?>
					<li class="form-element fields">
						<input type="text" required="required" name="fieldTitles[]" placeholder="Field Title" value="<?=$field->title?>" />
						<select name="fieldTypes[]">
							<option<?=$field->type == 'String' ? ' selected="selected"' : '' ?>>String</option>
							<option<?=$field->type == 'Text' ? ' selected="selected"' : '' ?>>Text</option>
							<option<?=$field->type == 'Rich Text' ? ' selected="selected"' : '' ?>>Rich Text</option>
							<option<?=$field->type == 'Boolean' ? ' selected="selected"' : '' ?>>Boolean</option>		
							<option<?=$field->type == 'Image' ? ' selected="selected"' : '' ?>>Image</option>		
							<option<?=$field->type == 'File' ? ' selected="selected"' : '' ?>>File</option>		
							<option<?=$field->type == 'Document' ? ' selected="selected"' : '' ?>>Document</option>		
						</select>
						<select name="fieldRequired[]">
							<option<?=$field->required ? '' : ' selected="selected"' ?> value="false">Not Required</option>
							<option<?=$field->required ? ' selected="selected"' : '' ?> value="true">Required</option>
						</select>
						<select name="fieldMultiple[]">
							<option<?=$field->multiple ? '' : ' selected="selected"' ?> value="false">Not Multiple</option>
							<option<?=$field->multiple ? ' selected="selected"' : '' ?> value="true">Multiple</option>
						</select>
						<a class="btn error" id="sitemap_remove_parameter"><i class="fa fa-trash"></i></a>
						<a class="btn move"><i class="fa fa-arrows-v"></i></a>
					</li>
				<? endforeach ?>
			<? endif ?>
			</ul>
			<a class="btn add-parameter" id="documentTypes_add_field">+</a>
		</div>
		<? if (count($bricks) > 0) : ?>
		<div class="form-element">
			<label>Bricks</label>
			<ul id="brickDropZone" class="sortable">
			<? if (isset($documentType)) : ?>
				<? foreach ($documentType->bricks as $myBrick) : ?>
				<li class="form-element bricks">
					<input type="text" required="required" name="brickTitles[]" placeholder="Brick Title" value="<?=$myBrick->title?>" />
					<select name="brickMultiples[]">
						<option<?=$myBrick->multiple ? '' : ' selected="selected"' ?> value="false">Not Multiple</option>
						<option<?=$myBrick->multiple ? ' selected="selected"' : '' ?> value="true">Multiple</option>
					</select>
					<select name="brickBricks[]">
						<? foreach ($bricks as $brick) : ?>
						<option<?=$myBrick->brickSlug == $brick->slug ? ' selected="selected"' : '' ?> value="<?=$brick->slug?>"><?=$brick->title?></option>
						<? endforeach ?>
					</select>
					<a class="btn error"><i class="fa fa-trash"></i></a>
					<a class="btn move"><i class="fa fa-arrows-v"></i></a>
				</li>
				<? endforeach ?>
			<? endif ?>
			</ul>
			<a class="btn add-parameter" id="documentTypes_add_brick">+</a>
		</div>
		<div class="form-element">
			<label>Dynamic Bricks</label>
			<select name="dynamicBricks[]" multiple="multiple">
				<? foreach ($bricks as $brick) : ?>
				<option<?=isset($documentType) && in_array($brick->slug, $documentType->dynamicBricks) ? ' selected="selected"' : '' ?> value="<?=$brick->slug?>"><?=$brick->title?></option>
				<? endforeach ?>
			</select>
		</div>
		<? endif ?>
		<div class="form-element">
			<input onmousedown="window.onbeforeunload=null;" class="btn" type="submit" value="Save" />
		</div>
	</form>
</section>
<li class="form-element fields" id="fieldPlaceholder" style="display:none;">
	<input type="text" required="required" name="fieldTitles[]" placeholder="Field Title" />
	<select name="fieldTypes[]">
		<option>String</option>
		<option>Text</option>
		<option>Rich Text</option>
		<option>Boolean</option>		
		<option>Image</option>		
		<option>File</option>		
		<option>Document</option>		
	</select>
	<select name="fieldRequired[]">
		<option value="false">Not Required</option>
		<option value="true">Required</option>
	</select>
	<select name="fieldMultiple[]">
		<option value="false">Not Multiple</option>
		<option value="true">Multiple</option>
	</select>
	<a class="btn error" id="sitemap_remove_parameter"><i class="fa fa-trash"></i></a>
	<a class="btn move"><i class="fa fa-arrows-v"></i></a>
</li>
<li class="form-element bricks" id="bricksPlaceholder" style="display:none;">
	<input type="text" required="required" name="brickTitles[]" placeholder="Brick Title" />
	<select name="brickBricks[]">
		<? foreach ($bricks as $brick) : ?>
		<option value="<?=$brick->slug?>"><?=$brick->title?></option>
		<? endforeach ?>
	</select>
	<select name="brickMultiples[]">
		<option value="false">Not Multiple</option>
		<option value="true">Multiple</option>
	</select>
	<a class="btn error"><i class="fa fa-trash"></i></a>
	<a class="btn move"><i class="fa fa-arrows-v"></i></a>
</li>
<script id="jqueryScript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script>
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
			$('#save').show();
			window.onbeforeunload = function(e) {
				return 'You have unsaved changes. Are you sure you want to leave this page?';
			};
		}
	});
	$( ".sortable" ).disableSelection();
	createCloneable('documentTypes_add_field', 'fieldPlaceholder', 'dropZone');
	createCloneable('documentTypes_add_brick', 'bricksPlaceholder', 'brickDropZone');
});
</script>