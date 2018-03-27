<?php if (isset($summernoteInstances)) {
    $summernoteInstances += 1;
} else {
    $summernoteInstances = 1;
}
?>
<div class="rte">
  <div id="summernote_<?= str_replace(']', '-', str_replace('[', '-',
      $fieldPrefix)) . $field->slug ?>_rte_<?= $summernoteInstances ?>" class="summernote"><?= isset($value) ? $value : '' ?></div>
</div>
<textarea style="display:none;" id="summernote_<?= $field->slug ?>_container_<?= $summernoteInstances ?>" name="<?= $fieldPrefix ?>[<?= $field->slug ?>][]"></textarea>
<script>
    <?php $summernoteName = str_replace(']', '-',
            str_replace('[', '-', $fieldPrefix)) . $field->slug . '_rte_' . $summernoteInstances; ?>
    function uploadImage (file) {
      "use strict";
      var xhr,
        formData;
      console.log(file);
      formData = new FormData();
      formData.append("file", file, file.name);
      xhr = new XMLHttpRequest();
      xhr.open("POST", cmsSubfolders + '/images/new-ajax', true);
      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
          var image = JSON.parse(xhr.responseText);
          console.log(image);
          var imageNode = $('<img>').attr('src', subfolders + 'images/' + image.set[smallestImage])
            .attr('data-original', image.set['original']);
          $('#summernote_<?=$summernoteName?>').summernote("insertNode", imageNode[0]);
        }
      };
      xhr.send(formData);
    }

    $(document).ready(function () {
      $('#summernote_<?=$summernoteName?>').summernote({
        height: 300,
        toolbar: [
          /*[groupname, [button list]]*/
          ['style', ['bold', 'italic', 'underline', 'clear', 'style']],
          ['font', ['strikethrough', 'superscript', 'subscript']],
          ['para', ['ul', 'ol']],
          ['insert', ['table', 'link', 'picture', 'video']],
          ['misc', ['codeview']]
        ],
        callbacks: {
          onImageUpload: function (image) {
            uploadImage(image[0]);
          }
        }
      });
    });

</script>
<?php if (!isset($GLOBALS['rteList'])) {
    $GLOBALS['rteList'] = array();
}
$GLOBALS['rteList'][] = 'summernote_' . str_replace(']', '-',
        str_replace('[', '-', $fieldPrefix)) . $field->slug . '_rte_' . $summernoteInstances ?>
