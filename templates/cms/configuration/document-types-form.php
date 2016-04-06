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
			<input required="required" id="title" type="text" name="title" placeholder="Title" value="<?=isset($sitemapItem) ? $sitemapItem->title : '' ?>" />
		</div>
		<div class="form-element">
			<label for="title">Fields</label>
			<ul id="dropZone" class="sortable">
			
			</ul>
			<a class="btn add-parameter" id="sitemap_add_parameter">+</a>
		</div>
		<div class="form-element">
			<input onmousedown="window.onbeforeunload=null;" class="btn" type="submit" value="Save" />
		</div>
	</form>
</section>
<li class="form-element fields" id="parameterPlaceholder" style="display:none;">
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
	<a class="btn error" id="sitemap_remove_parameter">x</a>
	<a class="btn move"><i class="fa fa-arrows-v"></i></a>
</li>
<script id="jqueryScript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script>
$(function() {
	$( ".sortable" ).sortable({
		placeholder: "ui-state-highlight",
		axis: "y",
		forcePlaceholderSize: true,
		tolerance: "pointer",
		stop: function( event, ui ) {
			$('#save').show();
			window.onbeforeunload = function(e) {
				return 'You have unsaved changes. Are you sure you want to leave this page?';
			};
		}
	});
	$( ".sortable" ).disableSelection();
});
</script>