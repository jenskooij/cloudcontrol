<div class="image grid-wrapper">
  <div class="grid-container">
    <div class="grid-box-2">
      <div class="grid-inner">
        <div<?= isset($value) && !empty($value) ? ' style="background-image:url(\'' . $request::$subfolders . 'images/' . $value . '\');"' : '' ?> class="selected-image" id="<?= $field->slug ?>_selectedImage"></div>
      </div>
    </div>
    <div class="grid-box-10">
      <div class="grid-inner">
        <a class="btn js-imageSelector" onmousedown="imageSelect(this, '<?= $field->slug ?>');" onclick="">Select</a>
        <input value="<?= isset($value) && !empty($value) ? $value : '' ?>" placeholder="No image selected" required="required" readonly="readonly" id="<?= $field->slug ?>_input" type="text" name="<?= $fieldPrefix ?>[<?= $field->slug ?>][]"/>
        <div class="image-selector" id="<?= $field->slug ?>_imageSelector" style="display:none;"></div>
      </div>
    </div>
  </div>
</div>
<script>
  if (smallestImage === undefined) {
    var smallestImage = '<?=$smallestImage?>';
  }
</script>