<label for="<?=$field->slug?>"><?=$field->title?></label>
<div class="document grid-wrapper">
	<div class="grid-container">
		<div class="grid-box-2">
			<div class="grid-inner">
				<div class="selected-file-type" id="<?=$field->slug?>_selectedDocument">
					<i class="fa fa-ellipsis-h"></i>
				</div>
			</div>
		</div>
		<div class="grid-box-10">
			<div class="grid-inner">
				<a class="btn" onmousedown="documentSelect(this, '<?=$field->slug?>');">Select</a>
				<input placeholder="No document selected" required="required" readonly="readonly" id="<?=$field->slug?>_input" type="text" name="<?=$fieldPrefix?>[<?=$field->slug?>][]" />
				<ul class="document-selector" id="<?=$field->slug?>_documentSelector" style="display:none;">
					<li class="search"><input type="text" placeholder="Search..." /></li>
					<li class="no-results">No documents found.</li>
				</ul>
			</div>
		</div>
	</div>
</div>