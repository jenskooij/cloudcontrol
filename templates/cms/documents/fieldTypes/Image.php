<label for="<?=$field->slug?>"><?=$field->title?></label>
<div class="image grid-wrapper">
	<div class="grid-container">
		<div class="grid-box-2">
			<div class="grid-inner">
				<div class="selected-image" id="<?=$field->slug?>_selectedImage"></div>
			</div>
		</div>
		<div class="grid-box-10">
			<div class="grid-inner">
				<a class="btn" onmousedown="imageSelect(this, '<?=$field->slug?>');">Select</a>
				<input placeholder="No image selected" required="required" readonly="readonly" id="<?=$field->slug?>_input" type="text" name="<?=$fieldPrefix?>[<?=$field->slug?>][]" />
				<div class="image-selector" id="<?=$field->slug?>_imageSelector" style="display:none;"></div>
			</div>
		</div>
	</div>
</div>
<script>
if (smallestImage === undefined) {
	var smallestImage = '<?=$smallestImage?>';
}
</script>