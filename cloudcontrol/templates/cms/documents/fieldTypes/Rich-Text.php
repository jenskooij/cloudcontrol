<?
if (isset($summernoteInstances)) {
	$summernoteInstances += 1;
} else {
	$summernoteInstances = 1;
}
?>
<div class="rte">
	<div id="summernote_<?=str_replace(']', '-', str_replace('[','-', $fieldPrefix)) . $field->slug?>_rte_<?=$summernoteInstances?>" class="summernote"><?=isset($value) ? $value : '' ?></div>
</div>
<textarea style="display:none;" id="summernote_<?=$field->slug?>_container_<?=$summernoteInstances?>" name="<?=$fieldPrefix?>[<?=$field->slug?>][]"></textarea>
<script>
	$(document).ready(function () {
		$('#summernote_<?=str_replace(']', '-', str_replace('[','-', $fieldPrefix)) . $field->slug?>_rte_<?=$summernoteInstances?>').summernote({
			height: 300,
			toolbar: [
				/*[groupname, [button list]]*/
				['style', ['bold', 'italic', 'underline', 'clear', 'style']],
				['font', ['strikethrough', 'superscript', 'subscript']],
				['para', ['ul', 'ol']],
				['insert', ['table', 'link', 'picture']],
				['misc', ['codeview']],
			]
		});
	});
</script>
<?
if (!isset($GLOBALS['rteList'])) {
	$GLOBALS['rteList'] = array();
}
$GLOBALS['rteList'][] = 'summernote_' . str_replace(']', '-', str_replace('[','-', $fieldPrefix)) . $field->slug . '_rte_' . $summernoteInstances ?>
