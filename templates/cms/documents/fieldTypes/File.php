<label for="<?=$field->slug?>"><?=$field->title?></label>
<div class="file grid-wrapper">
	<div class="grid-container">
		<div class="grid-box-2">
			<div class="grid-inner">
				<div class="selected-file-type" id="<?=$field->slug?>_selectedFile">
					<i class="fa fa-ellipsis-h"></i>
				</div>
			</div>
		</div>
		<div class="grid-box-10">
			<div class="grid-inner">
				<a class="btn" onmousedown="fileSelect(this, '<?=$field->slug?>');">Select</a>
				<input placeholder="No file selected" required="required" readonly="readonly" id="<?=$field->slug?>_input" type="text" name="<?=$fieldPrefix?>[<?=$field->slug?>][]" />
				<ul class="file-selector" id="<?=$field->slug?>_fileSelector" style="display:none;">
					<li class="search"><input type="text" placeholder="Search..." /></li>
					<li class="no-results">No files found.</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<?/*
// TODO
Ajax upload to be implemented
http://igstan.ro/posts/2009-01-11-ajax-file-upload-with-pure-javascript.html
*/?>