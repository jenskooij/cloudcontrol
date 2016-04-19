<label for="<?=$field->slug?>"><?=$field->title?></label>
<div class="file grid-wrapper">
	<div class="grid-container">
		<div class="grid-box-2">
			<div class="grid-inner">
				<div class="selected-file-type" id="<?=$field->slug?>_selectedFile"></div>
			</div>
		</div>
		<div class="grid-box-10">
			<div class="grid-inner">
				<a class="btn" onmousedown="fileSelect(this, '<?=$field->slug?>');">Select</a>
				<input required="required" readonly="readonly" id="<?=$field->slug?>_input" type="text" name="<?=$field->slug?>" />
				<ul class="file-selector" id="<?=$field->slug?>_fileSelector" style="display:none;"></ul>
			</div>
		</div>
	</div>
</div>